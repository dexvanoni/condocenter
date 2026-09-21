<?php

namespace App\Services;

use App\Models\Conversation;
use Illuminate\Support\Carbon;

class AnnouncementExpirationService
{
    /**
     * Encerra avisos cujo expires_at já passou (marca is_closed e desativa).
     */
    public function closeExpired(?int $condominiumId = null): int
    {
        $now = Carbon::now();

        $query = Conversation::query()
            ->where('type', 'announcement')
            ->where('is_closed', false)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now);

        if ($condominiumId !== null) {
            $query->where('condominium_id', $condominiumId);
        }

        $ids = $query->pluck('id');
        if ($ids->isEmpty()) {
            return 0;
        }

        return Conversation::query()
            ->whereIn('id', $ids)
            ->update([
                'is_closed' => true,
                'is_active' => false,
                'closed_at' => $now,
            ]);
    }

    public function isExpired(Conversation $conversation): bool
    {
        if ($conversation->type !== 'announcement' || $conversation->expires_at === null) {
            return false;
        }

        return $conversation->expires_at->isPast();
    }
}
