<?php

namespace App\Services;

use App\Models\AccessMovement;
use App\Models\User;
use Illuminate\Support\Collection;

class AccessAlertService
{
    public const ACCESS_NOTIFICATION_TYPES = [
        'access_entered',
        'access_denied',
        'access_prohibition_critical',
    ];

    public function unreadAccessAlerts(User $user, int $limit = 5): Collection
    {
        if (!$user->tenantCondominiumId()) {
            return collect();
        }

        if (!$user->receivesAccessMovementAlerts()) {
            return collect();
        }

        $alerts = $user->notifications()
            ->unread()
            ->whereIn('type', self::ACCESS_NOTIFICATION_TYPES)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        if ($user->isSindico() || $user->isAdmin()) {
            return $this->filterStaffToOwnResidentAlerts($user, $alerts);
        }

        return $alerts;
    }

    protected function filterStaffToOwnResidentAlerts(User $user, Collection $alerts): Collection
    {
        $movementIds = $alerts
            ->pluck('data.movement_id')
            ->filter()
            ->unique()
            ->values();

        if ($movementIds->isEmpty()) {
            return collect();
        }

        $movements = AccessMovement::query()
            ->whereIn('id', $movementIds)
            ->get()
            ->keyBy('id');

        return $alerts
            ->filter(function ($alert) use ($user, $movements) {
                $movementId = $alert->data['movement_id'] ?? null;

                if (!$movementId || !$movements->has($movementId)) {
                    return false;
                }

                return (int) $movements[$movementId]->notify_user_id === (int) $user->id;
            })
            ->values();
    }

    public function markAccessAlertAsRead(User $user, int $notificationId): void
    {
        $notification = $user->notifications()
            ->where('id', $notificationId)
            ->whereIn('type', self::ACCESS_NOTIFICATION_TYPES)
            ->first();

        $notification?->markAsRead();
    }
}
