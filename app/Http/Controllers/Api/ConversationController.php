<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\ConversationRecipient;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Jobs\SendConversationNotifications;
use App\Services\ConversationAuthorization;
use App\Services\SyndicConversationStatsService;
use Barryvdh\DomPDF\Facade\Pdf;

class ConversationController extends Controller
{
    public function __construct(
        private SyndicConversationStatsService $statsService
    ) {}

    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $query = Conversation::query()
            ->with(['participants.user:id,name', 'meeting']);

        ConversationAuthorization::applyVisibilityScope($query, $user);

        if ($request->filled('channel')) {
            if ($request->get('channel') === Conversation::CHANNEL_PEER) {
                $query->where(function ($sub) {
                    $sub->where('channel', Conversation::CHANNEL_PEER)
                        ->orWhereNull('channel');
                });
            } else {
                $query->where('channel', $request->get('channel'));
            }
        } else {
            $query->where(function ($sub) {
                $sub->whereNull('channel')
                    ->orWhere('channel', '!=', Conversation::CHANNEL_SYNDIC);
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->get('priority'));
        }
        if ($request->boolean('only_active', false)) {
            $now = now();
            $query->where('is_active', true)
                ->where(function ($q) use ($now) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', $now);
                });
        }

        $conversations = $query->orderByDesc('created_at')->paginate(20);
        return response()->json($conversations);
    }

    public function show(Request $request, Conversation $conversation)
    {
        /** @var User $user */
        $user = $request->user();

        if ($denied = ConversationAuthorization::denyIfUnauthorized($user, $conversation)) {
            return response()->json(['error' => $denied['error']], $denied['status']);
        }

        if (!$user->isSindico() && !$user->isAdmin() && $conversation->type !== 'announcement') {
            $isParticipant = $conversation->participants()->where('user_id', $user->id)->exists();
            if (!$isParticipant) {
                return response()->json(['error' => 'Não autorizado'], 403);
            }
        }

        $conversation->load([
            'participants.user:id,name',
            'messages' => fn ($q) => $q->with(['fromUser:id,name', 'attachments'])->orderBy('created_at'),
            'meeting',
        ]);

        return response()->json($conversation);
    }

    public function storeAnnouncement(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        if (!($user->isSindico() || $user->isAdmin())) {
            return response()->json(['error' => 'Sem permissão para enviar avisos'], 403);
        }

        $validator = Validator::make($request->all(), [
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'priority' => ['nullable', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'recipients' => ['required', 'array', 'min:1'], // ex: [{type: all|role|user, value: ...}]
            'recipients.*.type' => ['required', Rule::in(['all', 'role', 'user'])],
            'recipients.*.value' => ['nullable'],
            'expires_at' => ['nullable', 'date'],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $priority = $request->get('priority', 'normal');

        return DB::transaction(function () use ($user, $request, $priority) {
            $conversation = Conversation::create([
                'condominium_id' => $user->tenantCondominiumId(),
                'created_by' => $user->id,
                'subject' => $request->get('subject'),
                'type' => 'announcement',
                'priority' => $priority,
                'is_active' => true,
                'expires_at' => $request->get('expires_at'),
            ]);

            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'role' => 'owner',
                'joined_at' => now(),
            ]);

            foreach ($request->get('recipients', []) as $rcp) {
                ConversationRecipient::create([
                    'conversation_id' => $conversation->id,
                    'target_type' => $rcp['type'],
                    'target_value' => $rcp['value'] ?? null,
                ]);
            }

            $message = Message::create([
                'condominium_id' => $user->tenantCondominiumId(),
                'conversation_id' => $conversation->id,
                'from_user_id' => $user->id,
                'type' => 'announcement',
                'subject' => $request->get('subject'),
                'message' => $request->get('message'),
                'priority' => $priority,
            ]);

            $response = response()->json([
                'conversation' => $conversation->load('recipients'),
                'message' => $message,
            ], 201);

            // Dispara notificações (execução síncrona para refletir imediatamente no dashboard/notifications)
            SendConversationNotifications::dispatchSync($conversation->id, $user->id, $request->get('message'), $priority);

            return $response;
        });
    }

    public function updateStatus(Request $request, Conversation $conversation)
    {
        /** @var User $user */
        $user = $request->user();
        if (!($user->isSindico() || $user->isAdmin())) {
            return response()->json(['error' => 'Sem permissão'], 403);
        }
        if ($conversation->condominium_id !== $user->tenantCondominiumId() || $conversation->type !== 'announcement') {
            return response()->json(['error' => 'Operação inválida'], 422);
        }

        $validator = Validator::make($request->all(), [
            'is_active' => ['required', 'boolean'],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $conversation->update(['is_active' => $request->boolean('is_active')]);
        return response()->json(['message' => 'Status atualizado', 'conversation' => $conversation]);
    }

    public function destroy(Request $request, Conversation $conversation)
    {
        /** @var User $user */
        $user = $request->user();
        if (!($user->isSindico() || $user->isAdmin())) {
            return response()->json(['error' => 'Sem permissão'], 403);
        }
        if ($conversation->condominium_id !== $user->tenantCondominiumId() || $conversation->type !== 'announcement') {
            return response()->json(['error' => 'Operação inválida'], 422);
        }
        $conversation->delete();
        return response()->json(['message' => 'Aviso excluído']);
    }

    public function storeDirect(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'], // Opcional: pode criar conversa vazia
            'priority' => ['nullable', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'user_id' => ['nullable', 'integer', 'exists:users,id'], // ID do destinatário para conversa direta específica
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Conversas diretas entre usuários (canal peer)
        return DB::transaction(function () use ($user, $request) {
            $priority = $request->get('priority', 'normal');

            if (!$request->filled('user_id')) {
                return response()->json([
                    'error' => 'Informe o destinatário. Para falar com o síndico, use o canal sigiloso.',
                ], 422);
            }

            $targetUser = User::find($request->get('user_id'));
            if (!$targetUser || $targetUser->condominium_id !== $user->tenantCondominiumId()) {
                return response()->json(['error' => 'Usuário inválido'], 422);
            }

            $conversation = Conversation::create([
                'condominium_id' => $user->tenantCondominiumId(),
                'created_by' => $user->id,
                'subject' => $request->get('subject'),
                'type' => 'direct',
                'channel' => Conversation::CHANNEL_PEER,
                'priority' => $priority,
            ]);

            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'role' => 'owner',
                'joined_at' => now(),
            ]);

            ConversationParticipant::updateOrCreate(
                ['conversation_id' => $conversation->id, 'user_id' => $targetUser->id],
                ['role' => 'participant', 'joined_at' => now()]
            );

            // Criar mensagem apenas se fornecida
            $message = null;
            if ($request->filled('message')) {
                $message = Message::create([
                    'condominium_id' => $user->tenantCondominiumId(),
                    'conversation_id' => $conversation->id,
                    'from_user_id' => $user->id,
                    'type' => 'direct_message',
                    'subject' => $request->get('subject'),
                    'message' => $request->get('message'),
                    'priority' => $priority,
                ]);
            }

            $response = response()->json([
                'conversation' => $conversation->load('participants.user:id,name'),
                'message' => $message,
            ], 201);

            if ($message) {
                SendConversationNotifications::dispatchSync($conversation->id, $user->id, $request->get('message'), $priority);
            }

            return $response;
        });
    }

    public function addParticipant(Request $request, Conversation $conversation)
    {
        /** @var User $user */
        $user = $request->user();

        if ($denied = ConversationAuthorization::denyIfUnauthorized($user, $conversation)) {
            return response()->json(['error' => $denied['error']], $denied['status']);
        }

        if (!ConversationAuthorization::canAddParticipant($user, $conversation)) {
            return response()->json(['error' => 'Sem permissão'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'exists:users,id'],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $target = User::findOrFail($request->get('user_id'));
        if ($target->condominium_id !== $user->tenantCondominiumId()) {
            return response()->json(['error' => 'Usuário de outro condomínio'], 422);
        }

        ConversationParticipant::updateOrCreate(
            ['conversation_id' => $conversation->id, 'user_id' => $target->id],
            ['role' => 'participant', 'joined_at' => now()]
        );

        return response()->json(['message' => 'Participante adicionado com sucesso']);
    }

    public function storeMessage(Request $request, Conversation $conversation)
    {
        /** @var User $user */
        $user = $request->user();

        if ($denied = ConversationAuthorization::denyIfUnauthorized($user, $conversation)) {
            return response()->json(['error' => $denied['error']], $denied['status']);
        }

        if ($conversation->is_closed) {
            return response()->json(['error' => 'Conversa encerrada. Não é possível enviar novas mensagens.'], 400);
        }

        if (!$user->isSindico() && !$user->isAdmin()) {
            $isParticipant = $conversation->participants()->where('user_id', $user->id)->exists();
            if (!$isParticipant) {
                // Permitir resposta quando o usuário for destinatário de um aviso (announcement)
                if ($conversation->type === 'announcement') {
                    $userRoleNames = $user->roles?->pluck('name')->all() ?? [];
                    $isRecipient = $conversation->recipients()
                        ->where(function ($q) use ($user, $userRoleNames) {
                            $q->where('target_type', 'all')
                              ->orWhere(function ($r) use ($userRoleNames) {
                                  $r->where('target_type', 'role')->whereIn('target_value', $userRoleNames);
                              })
                              ->orWhere(function ($r) use ($user) {
                                  $r->where('target_type', 'user')->where('target_value', (string) $user->id);
                              });
                        })
                        ->exists();

                    if ($isRecipient) {
                        // Adiciona o usuário como participante para permitir o chat
                        $conversation->participants()->updateOrCreate(
                            ['user_id' => $user->id],
                            ['role' => 'participant', 'joined_at' => now()]
                        );
                    } else {
                        return response()->json(['error' => 'Não autorizado'], 403);
                    }
                } else {
                    return response()->json(['error' => 'Não autorizado'], 403);
                }
            }
        }

        $validator = Validator::make($request->all(), [
            'message' => ['required', 'string'],
            'priority' => ['nullable', Rule::in(['low', 'normal', 'high', 'urgent'])],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $messageType = match (true) {
            $conversation->type === 'announcement' => 'announcement',
            $conversation->isSyndicChannel() => 'syndic_channel_message',
            default => 'direct_message',
        };

        $msg = Message::create([
            'condominium_id' => $user->tenantCondominiumId(),
            'conversation_id' => $conversation->id,
            'from_user_id' => $user->id,
            'type' => $messageType,
            'message' => $request->get('message'),
            'priority' => $request->get('priority', $conversation->priority),
        ]);

        $this->statsService->trackMessageTimestamps($conversation->fresh(), $user);

        // Notificar demais participantes com base na prioridade - síncrono para refletir imediatamente
        SendConversationNotifications::dispatchSync($conversation->id, $user->id, $request->get('message'), $msg->priority);

        return response()->json($msg, 201);
    }

    public function uploadAttachment(Request $request, Conversation $conversation, Message $message)
    {
        /** @var User $user */
        $user = $request->user();

        if ($conversation->id !== $message->conversation_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        if ($denied = ConversationAuthorization::denyIfUnauthorized($user, $conversation)) {
            return response()->json(['error' => $denied['error']], $denied['status']);
        }

        if ($message->from_user_id !== $user->id && !$user->isSindico() && !($user->isAdmin() && !$conversation->isSyndicChannel())) {
            return response()->json(['error' => 'Sem permissão'], 403);
        }

        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                'max:10240',
                'mimes:jpg,jpeg,png,gif,webp,pdf,heic,heif,doc,docx,xls,xlsx,txt',
            ],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Arquivo inválido. Envie imagens (JPG, PNG, GIF, WEBP), PDF ou documentos (DOC, DOCX, XLS, XLSX, TXT) de até 10 MB.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $uploaded = $request->file('file');
        $path = $uploaded->store('message_attachments', 'public');

        $attachment = MessageAttachment::create([
            'message_id' => $message->id,
            'path' => $path,
            'mime_type' => $uploaded->getMimeType(),
            'size' => $uploaded->getSize(),
            'original_name' => $uploaded->getClientOriginalName(),
        ]);

        return response()->json($attachment, 201);
    }

    public function createMeeting(Request $request, Conversation $conversation)
    {
        /** @var User $user */
        $user = $request->user();
        if (!($user->isSindico() || $user->isAdmin())) {
            return response()->json(['error' => 'Somente administração pode criar reunião'], 403);
        }
        if ($conversation->condominium_id !== $user->tenantCondominiumId()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $validator = Validator::make($request->all(), [
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Jitsi: sala única baseada no conversation id com slug aleatório simples
        $roomSlug = 'condo-' . $conversation->id . '-' . substr(md5(uniqid('', true)), 0, 8);
        $joinUrl = 'https://meet.jit.si/' . $roomSlug;

        $meeting = Meeting::updateOrCreate(
            ['conversation_id' => $conversation->id],
            [
                'created_by' => $user->id,
                'provider' => 'jitsi',
                'join_url' => $joinUrl,
                'starts_at' => $request->get('starts_at'),
                'ends_at' => $request->get('ends_at'),
            ]
        );

        return response()->json($meeting, 201);
    }

    public function exportCsv(Request $request, Conversation $conversation)
    {
        /** @var User $user */
        $user = $request->user();

        if ($denied = ConversationAuthorization::denyIfUnauthorized($user, $conversation)) {
            return response()->json(['error' => $denied['error']], $denied['status']);
        }

        $conversation->load([
            'participants.user:id,name,email',
            'messages' => fn ($q) => $q->with(['fromUser:id,name,email'])->orderBy('created_at'),
        ]);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="conversation-' . $conversation->id . '.csv"',
        ];

        $callback = function () use ($conversation) {
            $output = fopen('php://output', 'w');
            // BOM UTF-8
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, ['ID', 'Data/Hora', 'Autor', 'E-mail', 'Prioridade', 'Mensagem']);
            foreach ($conversation->messages as $m) {
                fputcsv($output, [
                    $m->id,
                    $m->created_at?->format('Y-m-d H:i:s'),
                    $m->fromUser?->name ?? 'N/D',
                    $m->fromUser?->email ?? 'N/D',
                    $m->priority,
                    $m->message,
                ]);
            }
            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request, Conversation $conversation)
    {
        /** @var User $user */
        $user = $request->user();

        if ($denied = ConversationAuthorization::denyIfUnauthorized($user, $conversation)) {
            return response()->json(['error' => $denied['error']], $denied['status']);
        }

        $conversation->load([
            'participants.user:id,name,email',
            'messages' => fn ($q) => $q->with(['fromUser:id,name,email'])->orderBy('created_at'),
        ]);

        $pdf = Pdf::loadView('conversations.export-pdf', [
            'conversation' => $conversation,
        ])->setPaper('a4');

        $filename = 'conversation-' . $conversation->id . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Retorna o último aviso ativo/não expirado destinado ao usuário atual.
     * Inclui o ID da notificação não lida (se existir) para permitir "Marcar como lido".
     */
    public function latestAnnouncement(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $now = now();
        $userRoleNames = $user->roles?->pluck('name')->all() ?? [];

        $conversation = Conversation::query()
            ->with(['messages' => fn ($q) => $q->with(['fromUser:id,name', 'attachments'])->orderBy('created_at')])
            ->where('condominium_id', $user->tenantCondominiumId())
            ->where('type', 'announcement')
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', $now);
            })
            ->where(function ($q) use ($user, $userRoleNames) {
                $q->whereHas('recipients', fn ($r) => $r->where('target_type', 'all'))
                  ->orWhereHas('recipients', fn ($r) => $r->where('target_type', 'role')->whereIn('target_value', $userRoleNames))
                  ->orWhereHas('recipients', fn ($r) => $r->where('target_type', 'user')->where('target_value', (string) $user->id));
            })
            ->orderByDesc('created_at')
            ->first();

        if (!$conversation) {
            return response()->json(['conversation' => null]);
        }

        // Tenta localizar notificação não lida deste aviso
        $notification = \App\Models\Notification::query()
            ->where('user_id', $user->id)
            ->where('type', 'conversation_message')
            ->where('is_read', false)
            ->where('data->conversation_id', $conversation->id)
            ->orderByDesc('created_at')
            ->first(['id']);

        return response()->json([
            'conversation' => $conversation,
            'notification_id' => $notification?->id,
        ]);
    }

    /**
     * Lista todos os avisos ativos e não expirados destinados ao usuário atual, em ordem de criação (desc).
     */
    public function listAnnouncements(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $now = now();
        $userRoleNames = $user->roles?->pluck('name')->all() ?? [];

        $conversations = Conversation::query()
            ->with(['messages' => fn ($q) => $q->with(['fromUser:id,name', 'attachments'])->orderBy('created_at')])
            ->where('condominium_id', $user->tenantCondominiumId())
            ->where('type', 'announcement')
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', $now);
            })
            ->where(function ($q) use ($user, $userRoleNames) {
                $q->whereHas('recipients', fn ($r) => $r->where('target_type', 'all'))
                  ->orWhereHas('recipients', fn ($r) => $r->where('target_type', 'role')->whereIn('target_value', $userRoleNames))
                  ->orWhereHas('recipients', fn ($r) => $r->where('target_type', 'user')->where('target_value', (string) $user->id));
            })
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['conversations' => $conversations]);
    }

    /**
     * Encerra a conversa para novos envios (participantes ainda podem visualizar histórico).
     */
    public function close(Request $request, Conversation $conversation)
    {
        /** @var User $user */
        $user = $request->user();

        if ($denied = ConversationAuthorization::denyIfUnauthorized($user, $conversation)) {
            return response()->json(['error' => $denied['error']], $denied['status']);
        }

        $isParticipant = $conversation->participants()->where('user_id', $user->id)->exists();
        $canCloseAsStaff = $user->isSindico() || ($user->isAdmin() && !$conversation->isSyndicChannel());

        if (!$isParticipant && !$canCloseAsStaff) {
            return response()->json(['error' => 'Sem permissão'], 403);
        }
        if ($conversation->is_closed) {
            return response()->json(['message' => 'Conversa já encerrada', 'conversation' => $conversation]);
        }
        $conversation->update([
            'is_closed' => true,
            'closed_at' => now(),
            'closed_by' => $user->id,
        ]);
        return response()->json(['message' => 'Conversa encerrada com sucesso', 'conversation' => $conversation]);
    }
}


