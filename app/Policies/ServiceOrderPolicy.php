<?php

namespace App\Policies;

use App\Models\ServiceOrder;
use App\Models\User;

class ServiceOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_service_orders') || $user->can('manage_service_orders');
    }

    public function view(User $user, ServiceOrder $order): bool
    {
        if ($user->can('manage_service_orders') && $user->tenantCondominiumId() === $order->condominium_id) {
            return true;
        }

        return $user->can('view_service_orders')
            && $user->tenantCondominiumId() === $order->condominium_id
            && $order->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('create_service_orders') && (bool) $user->tenantCondominiumId();
    }

    public function update(User $user, ServiceOrder $order): bool
    {
        return $user->can('manage_service_orders')
            && $user->tenantCondominiumId() === $order->condominium_id;
    }

    public function manage(User $user): bool
    {
        return $user->can('manage_service_orders') && (bool) $user->tenantCondominiumId();
    }

    public function message(User $user, ServiceOrder $order): bool
    {
        return $order->canReceiveMessagesFrom($user);
    }
}
