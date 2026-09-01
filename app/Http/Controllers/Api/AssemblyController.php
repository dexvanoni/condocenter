<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assembly;
use App\Models\AssemblyAttachment;
use App\Models\AssemblyItem;
use App\Models\User;
use App\Services\Assembly\AssemblyMinutesService;
use App\Services\Assembly\AssemblyNotificationService;
use App\Services\Assembly\AssemblyResponseService;
use App\Services\Assembly\AssemblyService;
use App\Services\Assembly\AssemblyVotingStatsService;
use App\Services\Assembly\AssemblyVotingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AssemblyController extends Controller
{
    public function __construct(
        private readonly AssemblyService $assemblyService,
        private readonly AssemblyVotingService $votingService,
        private readonly AssemblyMinutesService $minutesService,
        private readonly AssemblyResponseService $responseService,
        private readonly AssemblyNotificationService $notificationService,
        private readonly AssemblyVotingStatsService $votingStatsService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $relationships = [
            'creator',
            'allowedRoles',
            'attachments',
        ];

        if (!$request->has('with_items') || $request->boolean('with_items')) {
            $relationships[] = 'items';
            $relationships[] = 'items.votes.voter';
            $relationships[] = 'items.votes.unit';
        }

        $query = Assembly::query()
            ->with($relationships)
            ->withCount('votes')
            ->where('condominium_id', $user->tenantCondominiumId());

        if ($status = $request->input('status')) {
            if ($status === 'scheduled') {
                $query->whereNotIn('status', ['completed', 'cancelled'])
                    ->where(function ($q) {
                        $q->whereNull('voting_opens_at')
                            ->orWhere('voting_opens_at', '>', Carbon::now());
                    });
            } elseif ($status === 'in_progress') {
                $query->whereNotIn('status', ['completed', 'cancelled'])
                    ->where(function ($q) {
                        $now = Carbon::now();
                        $q->where(function ($inner) use ($now) {
                            $inner->whereNotNull('voting_opens_at')
                                ->where('voting_opens_at', '<=', $now)
                                ->where(function ($window) use ($now) {
                                    $window->whereNull('voting_closes_at')
                                        ->orWhere('voting_closes_at', '>=', $now);
                                });
                        })->orWhere(function ($inner) use ($now) {
                            $inner->whereNotNull('voting_closes_at')
                                ->where('voting_closes_at', '<', $now);
                        });
                    });
            } else {
                $query->where('status', $status);
            }
        }

        if ($urgency = $request->input('urgency')) {
            $query->where('urgency', $urgency);
        }

        if ($request->boolean('only_open_for_voting')) {
            $now = now();
            $query->whereNotIn('status', ['completed', 'cancelled'])
                ->where(function ($q) use ($now) {
                    $q->whereNull('voting_opens_at')->orWhere('voting_opens_at', '<=', $now);
                })->where(function ($q) use ($now) {
                    $q->whereNull('voting_closes_at')->orWhere('voting_closes_at', '>=', $now);
                });
        }

        $assemblies = $query
            ->orderByDesc('voting_opens_at')
            ->orderByDesc('scheduled_at')
            ->paginate($request->integer('per_page', 15));

        $assemblies->setCollection(
            $this->responseService->sanitizeCollection(
                $assemblies->getCollection()->each(function (Assembly $assembly) use ($user) {
                    $assembly->append(['vote_summary', 'display_status', 'is_voting_open']);
                    $this->attachVotingStats($assembly, $user);
                }),
                $user
            )
        );

        return response()->json($assemblies);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->can('create_assemblies')) {
            return response()->json(['error' => 'Sem permissão para criar assembleias'], 403);
        }

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'voting_opens_at' => 'required|date|after:now',
            'voting_closes_at' => 'required|date|after:voting_opens_at',
            'voting_type' => ['required', Rule::in(['open', 'secret'])],
            'urgency' => ['required', Rule::in(['low', 'normal', 'high', 'critical'])],
            'results_visibility' => ['required', Rule::in(['final_only', 'real_time'])],
            'allow_comments' => 'boolean',
            'allowed_roles' => 'nullable|array|min:1',
            'allowed_roles.*' => 'string',
            'items' => 'required_without:agenda|array|min:1',
            'items.*.title' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.options' => 'nullable|array|min:1',
            'items.*.options.*' => 'string|max:100',
            'items.*.position' => 'nullable|integer|min:0',
            'attachments' => 'nullable|array|max:10',
            'attachments.*' => 'file|mimes:png,jpg,jpeg,webp,pdf|max:5120',
            'agenda' => 'nullable|array',
        ];

        $messages = [
            'voting_opens_at.after' => 'O início da votação deve ser uma data futura.',
            'voting_closes_at.after' => 'O término da votação deve ser posterior ao início.',
            'allowed_roles.min' => 'Selecione ao menos um perfil autorizado a votar.',
            'results_visibility.in' => 'Selecione uma opção válida para a visibilidade dos resultados.',
        ];

        $validated = Validator::make($request->all(), $rules, $messages)->validate();

        $payload = $validated;
        $payload['scheduled_at'] = $validated['voting_opens_at'];

        if (!empty($validated['allowed_roles'])) {
            $payload['allowed_role_ids'] = $this->resolveRoleIds($validated['allowed_roles']);
        }

        $payload['items'] = $this->normalizeItems($payload['items'] ?? [], $validated['agenda'] ?? []);
        $payload['attachments'] = $request->file('attachments', []);

        $assembly = $this->assemblyService->createAssembly($payload, $user);

        $this->notificationService->notifyEligibleUsers($assembly->fresh('allowedRoles'), 'created');

        $assembly = $assembly->load(['items', 'attachments', 'allowedRoles']);
        $this->responseService->sanitize($assembly, $user);

        return response()->json([
            'message' => 'Assembleia criada com sucesso.',
            'assembly' => $assembly,
        ], 201);
    }

    public function show(Assembly $assembly): JsonResponse
    {
        $this->ensureSameCondominium($assembly);

        /** @var User $user */
        $user = Auth::user();

        $assembly->load([
            'creator',
            'items.votes.voter',
            'items.votes.unit',
            'attachments',
            'statusLogs',
            'allowedRoles',
        ]);
        $assembly->loadCount('votes');
        $assembly->append(['vote_summary', 'display_status', 'is_voting_open']);
        $this->attachVotingStats($assembly, $user);

        return response()->json($this->responseService->sanitize($assembly, $user));
    }

    public function update(Request $request, Assembly $assembly): JsonResponse
    {
        $this->ensureSameCondominium($assembly);
        $this->ensureCanManage(Auth::user(), $assembly);

        if ($assembly->votes()->exists()) {
            return response()->json([
                'error' => 'Não é possível editar assembleias com votos registrados.',
            ], 422);
        }

        $rules = [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'voting_opens_at' => 'sometimes|date',
            'voting_closes_at' => 'sometimes|date|after:voting_opens_at',
            'voting_type' => ['sometimes', Rule::in(['open', 'secret'])],
            'urgency' => ['sometimes', Rule::in(['low', 'normal', 'high', 'critical'])],
            'results_visibility' => ['sometimes', Rule::in(['final_only', 'real_time'])],
            'allow_comments' => 'sometimes|boolean',
            'status' => ['sometimes', Rule::in(['scheduled', 'in_progress', 'completed', 'cancelled'])],
            'status_context' => 'sometimes|array',
            'allowed_roles' => 'sometimes|array|min:1',
            'allowed_roles.*' => 'string',
            'items' => 'sometimes|array',
            'items.*.id' => 'sometimes|integer|exists:assembly_items,id',
            'items.*.title' => 'required_with:items|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.options' => 'nullable|array|min:1',
            'items.*.options.*' => 'string|max:100',
            'items.*.position' => 'nullable|integer|min:0',
            'items.*.status' => ['nullable', Rule::in(['pending', 'open', 'closed', 'cancelled'])],
            'attachments_to_add' => 'sometimes|array|max:10',
            'attachments_to_add.*' => 'file|mimes:png,jpg,jpeg,webp,pdf|max:5120',
            'attachments_to_remove' => 'sometimes|array',
            'attachments_to_remove.*' => 'integer|exists:assembly_attachments,id',
        ];

        $messages = [
            'voting_closes_at.after' => 'O término da votação deve ser posterior ao início.',
            'results_visibility.in' => 'Selecione uma opção válida para a visibilidade dos resultados.',
        ];

        $validated = Validator::make($request->all(), $rules, $messages)->validate();

        if (!empty($validated['voting_opens_at'])) {
            $validated['scheduled_at'] = $validated['voting_opens_at'];
        }

        $payload = $validated;

        if (array_key_exists('allowed_roles', $validated)) {
            $payload['allowed_role_ids'] = $this->resolveRoleIds($validated['allowed_roles'] ?? []);
        }

        if (array_key_exists('items', $validated)) {
            $payload['items'] = $this->normalizeItems($validated['items'] ?? []);
        }

        $payload['attachments_to_add'] = $request->file('attachments_to_add', []);

        if (!empty($validated['attachments_to_remove'] ?? [])) {
            $payload['attachments_to_remove'] = AssemblyAttachment::query()
                ->where('assembly_id', $assembly->id)
                ->whereIn('id', $validated['attachments_to_remove'])
                ->get();
        }

        $updated = $this->assemblyService->updateAssembly($assembly, $payload, Auth::user());
        $updated = $updated->load(['items', 'attachments', 'allowedRoles']);
        $this->responseService->sanitize($updated, Auth::user());

        return response()->json([
            'message' => 'Assembleia atualizada com sucesso.',
            'assembly' => $updated,
        ]);
    }

    public function destroy(Assembly $assembly): JsonResponse
    {
        $this->ensureSameCondominium($assembly);
        $this->ensureCanManage(Auth::user(), $assembly);

        if ($assembly->votes()->exists()) {
            return response()->json([
                'error' => 'Não é possível remover assembleias com votos registrados.',
            ], 422);
        }

        if ($assembly->status === 'completed') {
            return response()->json([
                'error' => 'Não é possível remover assembleias concluídas.',
            ], 400);
        }

        $assembly->delete();

        return response()->json([
            'message' => 'Assembleia removida com sucesso.',
        ]);
    }

    public function vote(Request $request, Assembly $assembly, AssemblyItem $item): JsonResponse
    {
        $this->ensureSameCondominium($assembly);

        if ($item->assembly_id !== $assembly->id) {
            abort(404);
        }

        $request->validate([
            'choice' => 'required|string|max:100',
            'comment' => 'nullable|string|max:1000',
        ]);

        $vote = $this->votingService->recordVote(
            $assembly,
            $item,
            Auth::user(),
            $request->input('choice'),
            $request->input('comment')
        );

        $votePayload = $vote->load(['voter', 'unit']);
        if ($assembly->voting_type === 'secret') {
            $votePayload->makeHidden(['choice', 'encrypted_choice']);
        }

        return response()->json([
            'message' => 'Voto registrado com sucesso.',
            'vote' => $votePayload,
        ], 201);
    }

    public function start(Request $request, Assembly $assembly): JsonResponse
    {
        $this->ensureSameCondominium($assembly);
        $this->ensureCanManage(Auth::user(), $assembly);

        $context = [
            'started_at' => $request->input('started_at')
                ? Carbon::parse($request->input('started_at'))
                : now(),
            'reason' => $request->input('reason'),
        ];

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $this->assemblyService->transitionStatus($assembly, 'in_progress', Auth::user(), $context);

        return response()->json([
            'message' => 'Assembleia iniciada com sucesso.',
            'assembly' => $assembly->fresh(),
        ]);
    }

    public function complete(Request $request, Assembly $assembly): JsonResponse
    {
        $this->ensureSameCondominium($assembly);
        $this->ensureCanManage(Auth::user(), $assembly);

        $context = [
            'ended_at' => $request->input('ended_at')
                ? Carbon::parse($request->input('ended_at'))
                : now(),
            'reason' => $request->input('reason'),
        ];

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $this->assemblyService->transitionStatus($assembly, 'completed', Auth::user(), $context);

        return response()->json([
            'message' => 'Assembleia concluída com sucesso.',
            'assembly' => $assembly->fresh(['items.votes', 'minutes']),
        ]);
    }

    public function cancel(Request $request, Assembly $assembly): JsonResponse
    {
        $this->ensureSameCondominium($assembly);
        $this->ensureCanManage(Auth::user(), $assembly);

        $context = [
            'reason' => $request->input('reason'),
            'ended_at' => now(),
        ];

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $this->assemblyService->transitionStatus($assembly, 'cancelled', Auth::user(), $context);

        return response()->json([
            'message' => 'Assembleia cancelada com sucesso.',
            'assembly' => $assembly->fresh(),
        ]);
    }

    public function reopen(Request $request, Assembly $assembly): JsonResponse
    {
        $this->ensureSameCondominium($assembly);
        $this->ensureCanManage(Auth::user(), $assembly);

        $validated = $request->validate([
            'voting_opens_at' => 'required|date',
            'voting_closes_at' => 'required|date|after:voting_opens_at',
        ], [
            'voting_closes_at.after' => 'O término da votação deve ser posterior ao início.',
        ]);

        $updated = $this->assemblyService->reopenVoting($assembly, $validated, Auth::user());
        $this->notificationService->notifyEligibleUsers($updated, 'reopened');

        return response()->json([
            'message' => 'Votação reaberta com sucesso.',
            'assembly' => $this->responseService->sanitize($updated->load(['items', 'attachments', 'allowedRoles']), Auth::user()),
        ]);
    }

    public function exportMinutes(Assembly $assembly): JsonResponse
    {
        $this->ensureSameCondominium($assembly);

        if ($assembly->status !== 'completed' || empty($assembly->minutes)) {
            return response()->json([
                'error' => 'Ata ainda não disponível para exportação.',
            ], 400);
        }

        $data = $this->minutesService->buildMinutesData($assembly->loadMissing(['items.votes.voter', 'items.votes.unit']));

        return response()->json([
            'minutes' => $assembly->minutes,
            'data' => $data,
            'pdf_url' => $assembly->minutes_pdf ? Storage::url($assembly->minutes_pdf) : null,
        ]);
    }

    protected function ensureSameCondominium(Assembly $assembly): void
    {
        /** @var User $user */
        $user = Auth::user();
        if ($assembly->condominium_id !== $user->tenantCondominiumId()) {
            abort(403, 'Não autorizado.');
        }
    }

    protected function ensureCanManage(User $user, Assembly $assembly): void
    {
        if ($user->can('manage_assemblies')) {
            return;
        }

        if ($assembly->created_by === $user->id) {
            return;
        }

        if ($user->hasAnyRole(['Síndico', 'Administrador'])) {
            return;
        }

        abort(403, 'Você não possui permissão para esta ação.');
    }

    protected function resolveRoleIds(?array $roles): array
    {
        if (empty($roles)) {
            return [];
        }

        $roleIds = Role::query()
            ->whereIn('name', $roles)
            ->pluck('id')
            ->all();

        if (count($roleIds) !== count($roles)) {
            throw ValidationException::withMessages([
                'allowed_roles' => 'Algumas funções informadas não foram encontradas.',
            ]);
        }

        return $roleIds;
    }

    protected function normalizeItems(array $items, array $agenda = []): array
    {
        if (empty($items) && !empty($agenda)) {
            $items = collect($agenda)
                ->map(fn ($title) => ['title' => is_array($title) ? Arr::get($title, 'title') : $title])
                ->filter(fn ($item) => filled($item['title']))
                ->values()
                ->all();
        }

        return collect($items)
            ->map(function ($item, $index) {
                $item['position'] = $item['position'] ?? $index;
                return $item;
            })
            ->values()
            ->all();
    }

    protected function attachVotingStats(Assembly $assembly, User $user): void
    {
        if (!$this->userCanViewVotingStats($user, $assembly)) {
            return;
        }

        $assembly->setAttribute('voting_stats', $this->votingStatsService->compute($assembly));
    }

    protected function userCanViewVotingStats(User $user, Assembly $assembly): bool
    {
        return $user->can('manage_assemblies')
            || $assembly->created_by === $user->id
            || $user->hasAnyRole(['Síndico', 'Administrador']);
    }
}
