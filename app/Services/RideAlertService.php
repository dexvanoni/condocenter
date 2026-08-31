<?php

namespace App\Services;

use App\Helpers\SidebarHelper;
use App\Models\Notification;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Support\Collection;

class RideAlertService
{
    public function unreadPublishedAlerts(User $user, int $limit = 5): Collection
    {
        if (!$user->tenantCondominiumId() || !SidebarHelper::canAccessModule($user, 'rides')) {
            return collect();
        }

        $notifications = $user->notifications()
            ->unread()
            ->where('type', 'ride_published')
            ->orderByDesc('created_at')
            ->limit($limit * 3)
            ->get();

        if ($notifications->isEmpty()) {
            return collect();
        }

        $rideIds = $notifications
            ->pluck('data.ride_id')
            ->filter()
            ->unique()
            ->values();

        $activeRideIds = Ride::query()
            ->whereIn('id', $rideIds)
            ->whereIn('status', [Ride::STATUS_OPEN, Ride::STATUS_FULL])
            ->where('departure_at', '>', now())
            ->pluck('id')
            ->flip();

        return $notifications
            ->filter(fn (Notification $notification) => isset($activeRideIds[$notification->data['ride_id'] ?? null]))
            ->take($limit)
            ->values();
    }

    public function markPublishedAlertAsRead(User $user, int $notificationId): void
    {
        $notification = $user->notifications()
            ->where('id', $notificationId)
            ->where('type', 'ride_published')
            ->first();

        $notification?->markAsRead();
    }
}
