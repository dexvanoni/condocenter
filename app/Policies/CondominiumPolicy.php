<?php

namespace App\Policies;

use App\Models\Condominium;
use App\Models\User;

class CondominiumPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Condominium $condominium): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isSindico()
            && (int) $user->condominium_id === (int) $condominium->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Condominium $condominium): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isSindico()
            && (int) $user->condominium_id === (int) $condominium->id;
    }

    public function delete(User $user, Condominium $condominium): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Condominium $condominium): bool
    {
        return $user->isAdmin();
    }

    public function regenerateRegistrationCode(User $user, Condominium $condominium): bool
    {
        return $user->isAdmin();
    }

    public function toggleActive(User $user, Condominium $condominium): bool
    {
        return $user->isAdmin();
    }
}
