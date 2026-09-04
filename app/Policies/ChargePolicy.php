<?php

namespace App\Policies;

use App\Models\Charge;
use App\Models\User;

class ChargePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_charges');
    }

    public function view(User $user, Charge $charge): bool
    {
        if ((int) $charge->condominium_id !== (int) $user->tenantCondominiumId()) {
            return false;
        }

        if ($user->can('manage_charges')) {
            return true;
        }

        if ($user->isMorador() && $user->unit_id) {
            return $user->can('view_charges')
                && (int) $charge->unit_id === (int) $user->unit_id;
        }

        return $user->can('view_charges');
    }

    public function create(User $user): bool
    {
        return $user->can('manage_charges') && (bool) $user->tenantCondominiumId();
    }

    public function update(User $user, Charge $charge): bool
    {
        return $user->can('manage_charges')
            && (int) $charge->condominium_id === (int) $user->tenantCondominiumId();
    }

    public function delete(User $user, Charge $charge): bool
    {
        return $this->update($user, $charge);
    }

    public function generatePayment(User $user, Charge $charge): bool
    {
        if ((int) $charge->condominium_id !== (int) $user->tenantCondominiumId()) {
            return false;
        }

        if ($user->can('manage_charges')) {
            return true;
        }

        return $user->can('view_charges')
            && $user->isMorador()
            && $user->unit_id
            && (int) $charge->unit_id === (int) $user->unit_id;
    }
}
