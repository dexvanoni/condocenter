<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_users');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Usuário pode ver a si mesmo ou ter permissão
        return $user->id === $model->id || $user->can('view_users');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('manage_users');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Usuário pode editar a si mesmo ou ter permissão
        if ($user->id === $model->id) {
            return true;
        }

        return $user->can('manage_users');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Não pode deletar a si mesmo
        if ($user->id === $model->id) {
            return false;
        }

        return $user->can('manage_users');
    }

    /**
     * Determine whether the user can manage sindico users.
     */
    public function manageSindico(User $user): bool
    {
        // Apenas administrador pode gerenciar síndicos
        return $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can manage conselho fiscal users.
     */
    public function manageConselhoFiscal(User $user): bool
    {
        // Apenas administrador pode gerenciar conselho fiscal
        return $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can assign a role to another user.
     */
    public function assignRole(User $user, string $roleName): bool
    {
        // Apenas administrador pode atribuir Síndico e Conselho Fiscal
        if (in_array($roleName, ['Síndico', 'Conselho Fiscal'])) {
            return $user->hasRole('Administrador');
        }

        // Síndico e Admin podem atribuir outros roles
        return $user->hasRole(['Administrador', 'Síndico']);
    }

    /**
     * Determine whether the user can view the user's history.
     */
    public function viewHistory(User $user, User $model): bool
    {
        // Usuário pode ver seu próprio histórico ou ter permissão
        return $user->id === $model->id || $user->can('view_user_history');
    }

    /**
     * Determine whether the user can export the user's history.
     */
    public function exportHistory(User $user, User $model): bool
    {
        return $user->can('export_user_history');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->can('manage_users');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->can('manage_users');
    }
}

