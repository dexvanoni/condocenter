<?php

namespace App\Policies;

use App\Models\User;
use App\Services\UserScopeService;

class UserPolicy
{
    public function __construct(
        private UserScopeService $scope,
    ) {}

    public function viewAny(User $user): bool
    {
        return $user->can('view_users') || $this->scope->isActingAsManagementCompany($user);
    }

    public function view(User $user, User $model): bool
    {
        return $this->scope->canManageUser($user, $model);
    }

    public function create(User $user): bool
    {
        if ($this->scope->isActingAsManagementCompany($user)) {
            return true;
        }

        return $user->can('manage_users');
    }

    public function resetPassword(User $user, User $model): bool
    {
        return $this->scope->canSendPasswordResetLink($user, $model);
    }

    public function update(User $user, User $model): bool
    {
        return $this->scope->canManageUser($user, $model);
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        if (!$user->can('manage_users') && !$this->scope->isActingAsManagementCompany($user)) {
            return false;
        }

        return $this->scope->canManageUser($user, $model);
    }

    public function manageSindico(User $user): bool
    {
        return $this->scope->isActingAsPlatformAdmin($user)
            || $this->scope->isActingAsManagementCompany($user)
            || $this->scope->isActingAsSyndic($user);
    }

    public function manageConselhoFiscal(User $user): bool
    {
        return $this->scope->isActingAsPlatformAdmin($user)
            || $this->scope->isActingAsManagementCompany($user)
            || $this->scope->isActingAsSyndic($user);
    }

    public function assignRole(User $user, string $roleName): bool
    {
        return $this->scope->canAssignRole($user, $roleName);
    }

    public function viewHistory(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->can('view_user_history');
    }

    public function exportHistory(User $user, User $model): bool
    {
        return $user->can('export_user_history');
    }

    public function restore(User $user, User $model): bool
    {
        return $user->can('manage_users') && $this->scope->canManageUser($user, $model);
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $this->scope->isActingAsPlatformAdmin($user);
    }
}
