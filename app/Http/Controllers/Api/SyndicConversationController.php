<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use App\Services\ConversationAuthorization;
use App\Services\SyndicConversationStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Jobs\SendConversationNotifications;

class SyndicConversationController extends Controller
{
    public function __construct(
        private SyndicConversationStatsService $statsService
    ) {}

    public function stats(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if (!ConversationAuthorization::canManageSyndicChannel($user)) {
            return response()->json(['error' => 'Somente o perfil Síndico pode acessar este canal sigiloso'], 403);
        }

        return response()->json(
            $this->statsService->forCondominium((int) $user->tenantCondominiumId())
        );
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->isAdmin() && !$user->isSindico()) {
            return response()->json(['error' => 'Canal sigiloso indisponível para administradores'], 403);
        }

        $validator = Validator::make($request->all(), [
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
            'priority' => ['nullable', Rule::in(['low', 'normal', 'high', 'urgent'])],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return DB::transaction(function () use ($user, $request) {
            $priority = $request->get('priority', 'normal');

            $conversation = Conversation::create([
                'condominium_id' => $user->tenantCondominiumId(),
                'created_by' => $user->id,
                'subject' => $request->get('subject'),
                'type' => 'direct',
                'channel' => Conversation::CHANNEL_SYNDIC,
                'priority' => $priority,
                'is_active' => true,
            ]);

            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'role' => 'owner',
                'joined_at' => now(),
            ]);

            $this->attachSyndicParticipants($conversation);

            $message = null;
            if ($request->filled('message')) {
                $message = Message::create([
                    'condominium_id' => $user->tenantCondominiumId(),
                    'conversation_id' => $conversation->id,
                    'from_user_id' => $user->id,
                    'type' => 'syndic_channel_message',
                    'subject' => $request->get('subject'),
                    'message' => $request->get('message'),
                    'priority' => $priority,
                ]);

                $this->statsService->trackMessageTimestamps($conversation->fresh(), $user);

                SendConversationNotifications::dispatchSync(
                    $conversation->id,
                    $user->id,
                    $request->get('message'),
                    $priority
                );
            }

            return response()->json([
                'conversation' => $conversation->load('participants.user:id,name'),
                'message' => $message,
            ], 201);
        });
    }

    public static function attachSyndicParticipants(Conversation $conversation): void
    {
        $syndics = User::query()
            ->byCondominium($conversation->condominium_id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'Síndico'))
            ->get(['id']);

        foreach ($syndics as $syndic) {
            ConversationParticipant::updateOrCreate(
                ['conversation_id' => $conversation->id, 'user_id' => $syndic->id],
                ['role' => 'participant', 'joined_at' => now()]
            );
        }
    }
}
