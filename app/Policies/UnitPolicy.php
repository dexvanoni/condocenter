<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Unit;
use App\Policies\Concerns\ChecksActiveCondominium;

class UnitPolicy
{
    use ChecksActiveCondominium;
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_units');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Unit $unit): bool
    {
        return $user->can('view_units')
            && $this->belongsToActiveCondominium($user, (int) $unit->condominium_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_units');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Unit $unit): bool
    {
        return $user->can('edit_units')
            && $this->belongsToActiveCondominium($user, (int) $unit->condominium_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Unit $unit): bool
    {
        return $user->can('delete_units')
            && $this->belongsToActiveCondominium($user, (int) $unit->condominium_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Unit $unit): bool
    {
        return $user->can('manage_units');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Unit $unit): bool
    {
        return $user->can('manage_units');
    }
}

