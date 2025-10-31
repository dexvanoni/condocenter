<?php

namespace App\Policies;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PetPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any pets.
     */
    public function viewAny(User $user): bool
    {
        // Todos podem ver os pets
        return true;
    }

    /**
     * Determine whether the user can view the pet.
     */
    public function view(User $user, Pet $pet): bool
    {
        // Todos podem ver um pet específico
        return true;
    }

    /**
     * Determine whether the user can create pets.
     */
    public function create(User $user): bool
    {
        // Apenas moradores podem criar pets (não agregados)
        return $user->isMorador() || $user->isAdmin() || $user->isSindico();
    }

    /**
     * Determine whether the user can update the pet.
     */
    public function update(User $user, Pet $pet): bool
    {
        // Administrador, Síndico ou o próprio dono
        return $user->isAdmin() || 
               $user->isSindico() || 
               $pet->owner_id === $user->id;
    }

    /**
     * Determine whether the user can delete the pet.
     */
    public function delete(User $user, Pet $pet): bool
    {
        // Administrador, Síndico ou o próprio dono
        return $user->isAdmin() || 
               $user->isSindico() || 
               $pet->owner_id === $user->id;
    }

    /**
     * Determine whether the user can restore the pet.
     */
    public function restore(User $user, Pet $pet): bool
    {
        return $user->isAdmin() || $user->isSindico();
    }

    /**
     * Determine whether the user can permanently delete the pet.
     */
    public function forceDelete(User $user, Pet $pet): bool
    {
        return $user->isAdmin();
    }
}

