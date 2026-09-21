<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DirectConversationService
{
    public function findPeerConversation(User $user, User $peer, int $condominiumId): ?Conversation
    {
        if ($user->id === $peer->id) {
            return null;
        }

        return Conversation::query()
            ->where('condominium_id', $condominiumId)
            ->where('type', 'direct')
            ->where(function ($q) {
                $q->where('channel', Conversation::CHANNEL_PEER)
                    ->orWhereNull('channel');
            })
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $peer->id))
            ->latest('id')
            ->first();
    }

    public function findOrCreatePeerConversation(User $user, User $peer, int $condominiumId): Conversation
    {
        $existing = $this->findPeerConversation($user, $peer, $condominiumId);

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($user, $peer, $condominiumId) {
            $conversation = Conversation::create([
                'condominium_id' => $condominiumId,
                'created_by' => $user->id,
                'subject' => null,
                'type' => 'direct',
                'channel' => Conversation::CHANNEL_PEER,
                'priority' => 'normal',
            ]);

            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'role' => 'owner',
                'joined_at' => now(),
            ]);

            ConversationParticipant::updateOrCreate(
                ['conversation_id' => $conversation->id, 'user_id' => $peer->id],
                ['role' => 'participant', 'joined_at' => now()]
            );

            return $conversation;
        });
    }
}
