<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use App\Services\ActiveCondominiumService;

class OrganizationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->can('manage_organizations') || $user->isOrganizationMember();
    }

    public function view(User $user, Organization $organization): bool
    {
        if ($user->isAdmin() || $user->can('manage_organizations')) {
            return true;
        }

        return app(ActiveCondominiumService::class)
            ->userCanAccessOrganization($user, (int) $organization->id);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->can('manage_organizations');
    }

    public function update(User $user, Organization $organization): bool
    {
        if ($user->isAdmin() || $user->can('manage_organizations')) {
            return true;
        }

        if (!$this->view($user, $organization)) {
            return false;
        }

        $role = $user->organizationRoleFor((int) $organization->id);

        return in_array($role, [
            Organization::ROLE_OWNER,
            Organization::ROLE_ADMIN,
        ], true);
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $user->isAdmin();
    }

    public function manageUsers(User $user, Organization $organization): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (!$this->view($user, $organization)) {
            return false;
        }

        $role = $user->organizationRoleFor((int) $organization->id);

        return in_array($role, [
            Organization::ROLE_OWNER,
            Organization::ROLE_ADMIN,
        ], true);
    }

    public function manageCondominiums(User $user, Organization $organization): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (!$organization->isManagementCompany()) {
            return false;
        }

        if (!$this->view($user, $organization)) {
            return false;
        }

        $role = $user->organizationRoleFor((int) $organization->id);

        return in_array($role, [
            Organization::ROLE_OWNER,
            Organization::ROLE_ADMIN,
            Organization::ROLE_MANAGER,
        ], true);
    }

    public function enterCondominium(User $user, Organization $organization): bool
    {
        return $this->view($user, $organization) && $organization->isManagementCompany();
    }
}
