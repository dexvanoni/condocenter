<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Route;

class NotificationRedirectService
{
    public function resolve(Notification $notification, ?User $user = null): string
    {
        $user = $user ?? $notification->user;
        $data = $notification->data ?? [];
        $type = (string) $notification->type;

        if (! empty($data['url']) && is_string($data['url']) && $this->isSafeInternalUrl($data['url'])) {
            return $data['url'];
        }

        if (str_starts_with($type, 'charge_') || $type === 'payment_overdue') {
            return $this->chargeUrl($data, $user);
        }

        if (str_starts_with($type, 'reservation_')) {
            if ($type === 'reservation_charge_created' && ! empty($data['charge_id'])) {
                return $this->chargeUrl($data, $user);
            }

            return $this->reservationUrl($data, $user, $type);
        }

        if (str_starts_with($type, 'package_')) {
            return $this->routeIf('packages.index');
        }

        if (str_starts_with($type, 'saas_')) {
            return $this->routeIf('syndic-subscription.show');
        }

        if (str_starts_with($type, 'assembly_')) {
            return $this->assemblyUrl($data);
        }

        if (str_starts_with($type, 'service_order_')) {
            if ($type === 'service_order_charge_created' && ! empty($data['charge_id'])) {
                return $this->chargeUrl($data, $user);
            }

            return $this->serviceOrderUrl($data, $user);
        }

        if (str_starts_with($type, 'occurrence_book_')) {
            return $this->occurrenceBookUrl($data, $user, $type);
        }

        if (str_starts_with($type, 'ride_')) {
            return $this->rideUrl($data, $notification);
        }

        if (in_array($type, ['access_entered', 'access_denied', 'access_prohibition_critical'], true)) {
            return $this->accessUrl($data, $user);
        }

        return match ($type) {
            'fine_issued', 'fine_due_date_updated' => $this->fineUrl($data),
            'fee_invalidated' => $this->feeUrl($data),
            'conversation_message' => $this->conversationUrl($data),
            'registration_pending' => $this->registrationPendingUrl($data),
            'registration_approved', 'registration_rejected' => $this->routeIf('dashboard'),
            'panic_alert', 'panic_resolved' => $this->panicUrl($data, $user),
            default => $this->routeIf('notifications.index'),
        };
    }

    private function chargeUrl(array $data, User $user): string
    {
        $chargeId = $data['charge_id'] ?? null;

        if ($chargeId && $user->can('view_charges')) {
            return $this->routeIf('charges.show', ['charge' => $chargeId], $this->routeIf('my-charges.index'));
        }

        return $this->routeIf('my-charges.index');
    }

    private function reservationUrl(array $data, User $user, string $type): string
    {
        $reservationId = $data['reservation_id'] ?? null;

        if (! $reservationId) {
            return $this->routeIf('reservations.index');
        }

        if (
            in_array($type, ['reservation_pending_approval', 'reservation_cancelled'], true)
            && $user->can('manage_reservations')
        ) {
            return $this->routeIf(
                'reservations.manage.show',
                ['id' => $reservationId],
                $this->routeIf('reservations.manage')
            );
        }

        if ($user->can('approve_reservations')) {
            return $this->routeIf(
                'reservations.show',
                ['id' => $reservationId],
                $this->routeIf('reservations.index')
            );
        }

        return $this->routeIf('reservations.index');
    }

    private function fineUrl(array $data): string
    {
        $fineId = $data['fine_id'] ?? null;

        if ($fineId) {
            return $this->routeIf('fines.show', ['fine' => $fineId], $this->routeIf('fines.index'));
        }

        return $this->routeIf('fines.index');
    }

    private function feeUrl(array $data): string
    {
        $feeId = $data['fee_id'] ?? null;

        if ($feeId) {
            return $this->routeIf('fees.show', ['fee' => $feeId], $this->routeIf('fees.index'));
        }

        return $this->routeIf('fees.index');
    }

    private function conversationUrl(array $data): string
    {
        $conversationId = $data['conversation_id'] ?? null;

        if ($conversationId) {
            return $this->routeIf('messages.index', ['conversation' => $conversationId]);
        }

        return $this->routeIf('messages.index');
    }

    private function registrationPendingUrl(array $data): string
    {
        $userId = $data['user_id'] ?? null;

        if ($userId) {
            return $this->routeIf('users.show', ['user' => $userId], $this->routeIf('users.index'));
        }

        return $this->routeIf('users.index');
    }

    private function assemblyUrl(array $data): string
    {
        $assemblyId = $data['assembly_id'] ?? null;

        if ($assemblyId) {
            return $this->routeIf('assemblies.index', ['assembly' => $assemblyId]);
        }

        return $this->routeIf('assemblies.index');
    }

    private function serviceOrderUrl(array $data, User $user): string
    {
        $serviceOrderId = $data['service_order_id'] ?? null;

        if (! $serviceOrderId) {
            return $this->routeIf('service-orders.index');
        }

        if ($user->can('manage_service_orders')) {
            return $this->routeIf(
                'service-orders.manage.show',
                ['serviceOrder' => $serviceOrderId],
                $this->routeIf('service-orders.manage.index')
            );
        }

        return $this->routeIf(
            'service-orders.show',
            ['serviceOrder' => $serviceOrderId],
            $this->routeIf('service-orders.index')
        );
    }

    private function occurrenceBookUrl(array $data, User $user, string $type): string
    {
        $entryId = $data['occurrence_book_entry_id'] ?? null;

        if (! $entryId) {
            return $this->routeIf('occurrence-book.index');
        }

        if ($type === 'occurrence_book_new' && $user->can('manage_occurrence_book')) {
            return $this->routeIf(
                'occurrence-book.manage.show',
                ['entry' => $entryId],
                $this->routeIf('occurrence-book.manage.index')
            );
        }

        return $this->routeIf(
            'occurrence-book.show',
            ['entry' => $entryId],
            $this->routeIf('occurrence-book.index')
        );
    }

    private function rideUrl(array $data, Notification $notification): string
    {
        $params = array_filter([
            'notification' => $notification->id,
            'highlight' => $data['ride_id'] ?? null,
        ]);

        return $this->routeIf('rides.index', $params);
    }

    private function accessUrl(array $data, User $user): string
    {
        if ($user->can('view_access_movements')) {
            return $this->routeIf('access-control.reports');
        }

        return $this->routeIf('access-control.index');
    }

    private function panicUrl(array $data, User $user): string
    {
        $alertId = $data['alert_id'] ?? null;

        if ($alertId && $user->can('manage_panic_alerts')) {
            return $this->routeIf('panic-alerts.show', ['id' => $alertId], $this->routeIf('panic-alerts.index'));
        }

        return $this->routeIf('dashboard');
    }

    private function routeIf(string $name, array $params = [], ?string $fallback = null): string
    {
        if (Route::has($name)) {
            return route($name, $params);
        }

        return $fallback ?? route('notifications.index');
    }

    private function isSafeInternalUrl(string $url): bool
    {
        if (str_starts_with($url, '/')) {
            return true;
        }

        $parsed = parse_url($url);

        if (! isset($parsed['host'])) {
            return true;
        }

        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        return $appHost && $parsed['host'] === $appHost;
    }
}
