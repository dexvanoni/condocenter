<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;
use App\Policies\Concerns\ChecksActiveCondominium;

class ReservationPolicy
{
    use ChecksActiveCondominium;

    protected function belongsToReservationTenant(User $user, Reservation $reservation): bool
    {
        $condominiumId = $reservation->relationLoaded('space')
            ? $reservation->space?->condominium_id
            : $reservation->space()->value('condominium_id');

        return $condominiumId
            && $this->belongsToActiveCondominium($user, (int) $condominiumId);
    }

    public function viewAny(User $user): bool
    {
        return $user->can('view_reservations')
            || $user->can('make_reservations')
            || $user->can('manage_reservations')
            || $user->can('approve_reservations');
    }

    public function view(User $user, Reservation $reservation): bool
    {
        if (!$this->belongsToReservationTenant($user, $reservation)) {
            return false;
        }

        return $reservation->user_id === $user->id
            || $user->can('view_reservations')
            || $user->can('manage_reservations')
            || $user->can('approve_reservations');
    }

    public function create(User $user): bool
    {
        return $user->can('make_reservations');
    }

    public function update(User $user, Reservation $reservation): bool
    {
        if (!$this->belongsToReservationTenant($user, $reservation)) {
            return false;
        }

        if ($user->can('manage_reservations')) {
            return true;
        }

        return $reservation->user_id === $user->id
            && $reservation->status === 'pending';
    }

    public function delete(User $user, Reservation $reservation): bool
    {
        if (!$this->belongsToReservationTenant($user, $reservation)) {
            return false;
        }

        return $reservation->user_id === $user->id
            || $user->can('manage_reservations');
    }

    public function approve(User $user, Reservation $reservation): bool
    {
        return $this->belongsToReservationTenant($user, $reservation)
            && $user->can('approve_reservations');
    }

    public function restore(User $user, Reservation $reservation): bool
    {
        return $user->can('manage_reservations')
            && $this->belongsToReservationTenant($user, $reservation);
    }

    public function forceDelete(User $user, Reservation $reservation): bool
    {
        return $user->isAdmin()
            && $this->belongsToReservationTenant($user, $reservation);
    }
}
