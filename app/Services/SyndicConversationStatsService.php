<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Collection;

class SyndicConversationStatsService
{
    public function forCondominium(int $condominiumId): array
    {
        $conversations = Conversation::query()
            ->where('condominium_id', $condominiumId)
            ->where('channel', Conversation::CHANNEL_SYNDIC)
            ->with(['participants.user:id,name', 'creator:id,name'])
            ->orderByDesc('updated_at')
            ->get();

        $responseTimes = $this->collectResponseTimes($conversations);
        $pending = $conversations->filter(fn (Conversation $c) => $this->isPendingResponse($c))->count();

        return [
            'total' => $conversations->count(),
            'open' => $conversations->where('is_closed', false)->count(),
            'closed' => $conversations->where('is_closed', true)->count(),
            'pending_response' => $pending,
            'responded' => $conversations->whereNotNull('syndic_first_response_at')->count(),
            'avg_response_minutes' => $this->averageMinutes($responseTimes),
            'median_response_minutes' => $this->medianMinutes($responseTimes),
            'response_under_1h' => $responseTimes->filter(fn (int $m) => $m <= 60)->count(),
            'response_under_24h' => $responseTimes->filter(fn (int $m) => $m <= 1440)->count(),
            'this_month' => $conversations->filter(
                fn (Conversation $c) => $c->created_at?->isCurrentMonth()
            )->count(),
            'conversations' => $conversations->map(fn (Conversation $c) => $this->serializeConversation($c)),
        ];
    }

    public function isPendingResponse(Conversation $conversation): bool
    {
        if ($conversation->is_closed || $conversation->syndic_first_response_at) {
            return false;
        }

        return (bool) $conversation->resident_first_message_at
            || $conversation->messages()->exists();
    }

    public function responseMinutes(Conversation $conversation): ?int
    {
        if (!$conversation->resident_first_message_at || !$conversation->syndic_first_response_at) {
            return null;
        }

        return (int) $conversation->resident_first_message_at
            ->diffInMinutes($conversation->syndic_first_response_at);
    }

    public function trackMessageTimestamps(Conversation $conversation, User $sender): void
    {
        if (!$conversation->isSyndicChannel()) {
            return;
        }

        $updates = [];

        if ($sender->hasAssignedRole('Síndico') && !$conversation->syndic_first_response_at) {
            $updates['syndic_first_response_at'] = now();
        }

        if (!$sender->hasAssignedRole('Síndico') && !$conversation->resident_first_message_at) {
            $isOwner = $conversation->participants()
                ->where('user_id', $sender->id)
                ->where('role', 'owner')
                ->exists();

            if ($isOwner) {
                $updates['resident_first_message_at'] = now();
            }
        }

        if ($updates !== []) {
            $conversation->update($updates);
        }
    }

    private function collectResponseTimes(Collection $conversations): Collection
    {
        return $conversations
            ->map(fn (Conversation $c) => $this->responseMinutes($c))
            ->filter(fn (?int $minutes) => $minutes !== null)
            ->values();
    }

    private function averageMinutes(Collection $minutes): ?float
    {
        if ($minutes->isEmpty()) {
            return null;
        }

        return round($minutes->avg(), 1);
    }

    private function medianMinutes(Collection $minutes): ?float
    {
        if ($minutes->isEmpty()) {
            return null;
        }

        $sorted = $minutes->sort()->values();
        $middle = (int) floor($sorted->count() / 2);

        if ($sorted->count() % 2 === 0) {
            return round(($sorted[$middle - 1] + $sorted[$middle]) / 2, 1);
        }

        return (float) $sorted[$middle];
    }

    private function serializeConversation(Conversation $conversation): array
    {
        $resident = $conversation->participants
            ->firstWhere('role', 'owner')
            ?->user;

        return [
            'id' => $conversation->id,
            'subject' => $conversation->subject,
            'is_closed' => $conversation->is_closed,
            'priority' => $conversation->priority,
            'created_at' => $conversation->created_at?->toIso8601String(),
            'updated_at' => $conversation->updated_at?->toIso8601String(),
            'resident_first_message_at' => $conversation->resident_first_message_at?->toIso8601String(),
            'syndic_first_response_at' => $conversation->syndic_first_response_at?->toIso8601String(),
            'response_minutes' => $this->responseMinutes($conversation),
            'pending_response' => $this->isPendingResponse($conversation),
            'resident' => $resident ? ['id' => $resident->id, 'name' => $resident->name] : null,
            'participants_count' => $conversation->participants->count(),
        ];
    }
}
