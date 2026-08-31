<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccessAuthorization;
use App\Models\AccessListGroup;
use App\Models\AccessListItem;
use App\Models\AccessMovement;
use App\Models\ServiceProvider;
use App\Services\AccessControlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AccessControlController extends Controller
{
    public function __construct(private AccessControlService $accessControl) {}

    public function porteiroPanel(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->can('process_access')) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $condominiumId = $user->tenantCondominiumId();

        if (!$condominiumId) {
            return response()->json(['error' => 'Condomínio não identificado'], 403);
        }

        return response()->json($this->accessControl->porteiroPanel($condominiumId));
    }

    public function myAuthorizations(Request $request): JsonResponse
    {
        $user = $request->user();
        $condominiumId = $user->tenantCondominiumId();

        $query = AccessAuthorization::with(['unit', 'authorizedBy'])
            ->forCondominium($condominiumId)
            ->where(function ($q) use ($user) {
                $q->where('authorized_by', $user->id)
                    ->orWhere('notify_user_id', $user->id);
            })
            ->orderByDesc('scheduled_at');

        return response()->json($query->paginate(20));
    }

    public function myLists(Request $request): JsonResponse
    {
        $user = $request->user();
        $condominiumId = $user->tenantCondominiumId();

        $lists = AccessListGroup::with(['unit', 'items'])
            ->forCondominium($condominiumId)
            ->where(function ($q) use ($user) {
                $q->where('authorized_by', $user->id)
                    ->orWhere('notify_user_id', $user->id);
            })
            ->orderByDesc('scheduled_at')
            ->paginate(15);

        return response()->json($lists);
    }

    public function myProviders(Request $request): JsonResponse
    {
        $user = $request->user();
        $condominiumId = $user->tenantCondominiumId();

        $query = ServiceProvider::with(['unit', 'authorizedBy'])
            ->forCondominium($condominiumId);

        if (!$user->can('manage_condominium_service_providers')) {
            $unitId = $user->unit_id ?? $user->moradorVinculado?->unit_id;
            $query->where(function ($q) use ($user, $unitId) {
                $q->where('authorized_by', $user->id);
                if ($unitId) {
                    $q->orWhere('unit_id', $unitId);
                }
            });
        }

        return response()->json($query->orderBy('name')->paginate(20));
    }

    public function storeAuthorization(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'visitor_name' => 'required|string|max:255',
            'authorization_type' => 'required|in:allow,deny',
            'scheduled_at' => 'required|date',
            'valid_until' => 'nullable|date|after:scheduled_at',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->input('authorization_type') === 'deny') {
            return response()->json([
                'errors' => [
                    'authorization_type' => ['Use o cadastro rápido de proibição.'],
                ],
            ], 422);
        }

        try {
            $auth = $this->accessControl->createAuthorization($request->user(), $validator->validated());

            return response()->json(['message' => 'Liberação criada.', 'authorization' => $auth->load('unit')], 201);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function storeProhibition(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'visitor_name' => 'required|string|max:255',
            'visitor_document' => 'nullable|string|max:80',
            'never_expires' => 'nullable|boolean',
            'expires_at' => 'nullable|date|after_or_equal:today',
            'unit_id' => 'nullable|integer|exists:units,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $neverExpires = $request->boolean('never_expires');

        if (!$neverExpires && empty($data['expires_at'])) {
            return response()->json([
                'errors' => ['expires_at' => ['Informe a data de expiração ou marque "Nunca expira".']],
            ], 422);
        }

        try {
            $prohibition = $this->accessControl->createProhibition($request->user(), [
                ...$data,
                'never_expires' => $neverExpires,
            ]);

            return response()->json([
                'message' => 'Proibição registrada.',
                'prohibition' => $prohibition->load(['unit', 'authorizedBy', 'notifyUser']),
            ], 201);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function storeList(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'scheduled_at' => 'required|date',
            'valid_until' => 'nullable|date|after:scheduled_at',
            'visitor_names' => 'required|array|min:1',
            'visitor_names.*' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $group = $this->accessControl->createListGroup($request->user(), $validator->validated());

            return response()->json(['message' => 'Lista criada.', 'list' => $group], 201);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function storeProvider(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'scope' => 'required|in:unit,condominium',
            'document' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'contract_valid_until' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('access/providers', 'public');
            }

            $provider = $this->accessControl->createServiceProvider(
                $request->user(),
                $validator->validated(),
                $photoPath
            );

            return response()->json(['message' => 'Prestador cadastrado.', 'provider' => $provider->load(['unit', 'authorizedBy'])], 201);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function updateProvider(Request $request, ServiceProvider $provider): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'scope' => 'required|in:unit,condominium',
            'document' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'contract_valid_until' => 'required|date',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'remove_photo' => 'nullable|boolean',
            'photo' => 'nullable|image|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('access/providers', 'public');
            }

            $data = $validator->validated();
            $data['is_active'] = $request->boolean('is_active');

            $provider = $this->accessControl->updateServiceProvider(
                $request->user(),
                $provider,
                $data,
                $photoPath,
                $request->boolean('remove_photo')
            );

            return response()->json(['message' => 'Prestador atualizado.', 'provider' => $provider]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function deactivateProvider(Request $request, ServiceProvider $provider): JsonResponse
    {
        try {
            $provider = $this->accessControl->deactivateServiceProvider($request->user(), $provider);

            return response()->json(['message' => 'Prestador desativado.', 'provider' => $provider]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function processAuthorization(Request $request, AccessAuthorization $authorization): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:entered,denied',
            'notes' => 'nullable|string|max:500',
            'early_entry_confirmed' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $movement = $this->accessControl->processAuthorization(
                $request->user(),
                $authorization,
                $request->input('action'),
                $request->input('notes'),
                $request->boolean('early_entry_confirmed')
            );

            return response()->json(['message' => 'Registrado.', 'movement' => $movement]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function alertProhibitionAttempt(Request $request, AccessAuthorization $authorization): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $movement = $this->accessControl->alertProhibitionAttempt(
                $request->user(),
                $authorization,
                $request->input('notes')
            );

            return response()->json([
                'message' => 'Morador alertado com sucesso.',
                'movement' => $movement,
            ]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function processListItem(Request $request, AccessListItem $listItem): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:entered,denied',
            'notes' => 'nullable|string|max:500',
            'early_entry_confirmed' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $movement = $this->accessControl->processListItem(
                $request->user(),
                $listItem,
                $request->input('action'),
                $request->input('notes'),
                $request->boolean('early_entry_confirmed')
            );

            return response()->json(['message' => 'Registrado.', 'movement' => $movement]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function processProviderEntry(Request $request, ServiceProvider $provider): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $movement = $this->accessControl->processServiceProviderEntry(
                $request->user(),
                $provider,
                $request->input('notes')
            );

            return response()->json(['message' => 'Entrada registrada.', 'movement' => $movement]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function cancelAuthorization(Request $request, AccessAuthorization $authorization): JsonResponse
    {
        try {
            $auth = $this->accessControl->cancelAuthorization($request->user(), $authorization);

            return response()->json(['message' => 'Liberação cancelada.', 'authorization' => $auth]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function updateList(Request $request, AccessListGroup $list): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'scheduled_at' => 'required|date',
            'valid_until' => 'nullable|date|after:scheduled_at',
            'visitor_names' => 'sometimes|array|min:1',
            'visitor_names.*' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $group = $this->accessControl->updateListGroup($request->user(), $list, $validator->validated());

            return response()->json(['message' => 'Lista atualizada.', 'list' => $group]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function cancelList(Request $request, AccessListGroup $list): JsonResponse
    {
        try {
            $group = $this->accessControl->cancelListGroup($request->user(), $list);

            return response()->json(['message' => 'Lista cancelada.', 'list' => $group]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function movements(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->can('view_access_movements')) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $condominiumId = $user->tenantCondominiumId();

        $movements = $this->accessControl->listMovements($condominiumId, [
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'unit_id' => $request->input('unit_id'),
        ]);

        return response()->json(['data' => $movements]);
    }

    public function updateAgregadoSetting(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isMorador()) {
            return response()->json(['error' => 'Somente moradores podem alterar esta configuração.'], 403);
        }

        $request->validate(['agregado_can_authorize_access' => 'required|boolean']);

        $user->update([
            'agregado_can_authorize_access' => $request->boolean('agregado_can_authorize_access'),
        ]);

        return response()->json([
            'message' => 'Configuração atualizada.',
            'agregado_can_authorize_access' => $user->agregado_can_authorize_access,
        ]);
    }

    protected function errorResponse(\Throwable $e): JsonResponse
    {
        $status = 422;
        if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
            $status = 403;
        }

        return response()->json(['error' => $e->getMessage()], $status);
    }
}
