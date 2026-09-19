<?php

namespace App\Policies;

use App\Models\ServiceOrder;
use App\Models\Unit;
use App\Models\User;
use App\Policies\Concerns\ResolvesUnitOccupancy;

class ServiceOrderPolicy
{
    use ResolvesUnitOccupancy;

    public function viewAny(User $user): bool
    {
        return $user->can('view_service_orders') || $user->can('manage_service_orders');
    }

    public function view(User $user, ServiceOrder $order): bool
    {
        if ($user->can('manage_service_orders') && $user->tenantCondominiumId() === $order->condominium_id) {
            return true;
        }

        if ($user->tenantCondominiumId() !== $order->condominium_id || !$user->can('view_service_orders')) {
            return false;
        }

        if ($order->user_id === $user->id) {
            return true;
        }

        if ($order->visible_to_tenant
            && $user->isMorador()
            && $order->unit_id
            && (int) $user->unit_id === (int) $order->unit_id) {
            return true;
        }

        if ($order->unit_id && $user->isProprietario()) {
            $unit = $order->relationLoaded('unit') ? $order->unit : $order->unit()->first();

            return $unit && $this->occupancyService()->userOwnsUnit($user, $unit);
        }

        return false;
    }

    public function create(User $user): bool
    {
        if (!$user->can('create_service_orders') || !$user->tenantCondominiumId()) {
            return false;
        }

        if ($user->isMorador() && $user->unit_id) {
            $unit = Unit::query()->find($user->unit_id);

            if ($unit && $this->occupancyService()->isRental($unit) && !$this->occupancyService()->userOwnsUnit($user, $unit)) {
                return $user->isProprietario();
            }
        }

        return true;
    }

    public function createForUnit(User $user, Unit $unit): bool
    {
        return $user->can('create_service_orders')
            && $user->tenantCondominiumId() === $unit->condominium_id
            && $this->userCanCreateServiceOrderForUnit($user, $unit);
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
