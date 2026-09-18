<?php

namespace App\Policies;

use App\Models\RecurringReservation;
use App\Models\User;
use App\Policies\Concerns\ChecksActiveCondominium;
use Illuminate\Auth\Access\HandlesAuthorization;

class RecurringReservationPolicy
{
    use HandlesAuthorization;
    use ChecksActiveCondominium;

    protected function canManage(User $user): bool
    {
        return $user->isAdmin() || $user->isSindico();
    }

    public function viewAny(User $user): bool
    {
        return $this->canManage($user) && $user->tenantCondominiumId() !== null;
    }

    public function view(User $user, RecurringReservation $recurringReservation): bool
    {
        return $this->canManage($user)
            && $this->belongsToActiveCondominium($user, (int) $recurringReservation->condominium_id);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user) && $user->tenantCondominiumId() !== null;
    }

    public function update(User $user, RecurringReservation $recurringReservation): bool
    {
        return $this->canManage($user)
            && $this->belongsToActiveCondominium($user, (int) $recurringReservation->condominium_id);
    }

    public function delete(User $user, RecurringReservation $recurringReservation): bool
    {
        return $this->canManage($user)
            && $this->belongsToActiveCondominium($user, (int) $recurringReservation->condominium_id);
    }

    public function restore(User $user, RecurringReservation $recurringReservation): bool
    {
        return $this->canManage($user)
            && $this->belongsToActiveCondominium($user, (int) $recurringReservation->condominium_id);
    }

    public function forceDelete(User $user, RecurringReservation $recurringReservation): bool
    {
        return $user->isAdmin()
            && $this->belongsToActiveCondominium($user, (int) $recurringReservation->condominium_id);
    }
}
