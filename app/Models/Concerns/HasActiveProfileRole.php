<?php

namespace App\Models\Concerns;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

trait HasActiveProfileRole
{
    /**
     * Verifica papéis atribuídos no banco, ignorando o perfil ativo da sessão.
     */
    public function hasAssignedRole($roles, ?string $guard = null): bool
    {
        return $this->spatieHasRole($roles, $guard);
    }

    public function getActiveRoleName(): ?string
    {
        if ($this->hasMultipleRoles()) {
            return session('active_role') ?? $this->current_role ?? null;
        }

        return $this->roles->first()?->name;
    }

    public function shouldUseActiveRoleOnly(): bool
    {
        return $this->hasMultipleRoles() && (bool) session('active_role');
    }

    public function hasRole($roles, ?string $guard = null): bool
    {
        if (!$this->shouldUseActiveRoleOnly()) {
            return $this->spatieHasRole($roles, $guard);
        }

        $activeRole = $this->getActiveRoleName();

        if (!$activeRole || !$this->spatieHasRole($activeRole, $guard)) {
            return false;
        }

        return $this->activeRoleMatches($activeRole, $roles, $guard);
    }

    public function hasAnyRole(...$roles): bool
    {
        if (!$this->shouldUseActiveRoleOnly()) {
            return $this->spatieHasAnyRole(...$roles);
        }

        $activeRole = $this->getActiveRoleName();

        if (!$activeRole) {
            return false;
        }

        $rolesParam = count($roles) === 1 && is_array($roles[0]) ? $roles[0] : $roles;

        return $this->activeRoleMatches($activeRole, $rolesParam, null);
    }

    public function hasAllRoles($roles, ?string $guard = null): bool
    {
        if (!$this->shouldUseActiveRoleOnly()) {
            return $this->spatieHasAllRoles($roles, $guard);
        }

        $activeRole = $this->getActiveRoleName();

        if (!$activeRole) {
            return false;
        }

        $requiredRoles = $this->resolveRoleNames($roles, $guard);

        return count($requiredRoles) === 1 && $requiredRoles[0] === $activeRole;
    }

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if (!$this->shouldUseActiveRoleOnly()) {
            return $this->spatieHasPermissionTo($permission, $guardName);
        }

        $activeRoleName = $this->getActiveRoleName();

        if (!$activeRoleName) {
            return false;
        }

        $this->loadMissing('roles');

        $activeRole = $this->roles->firstWhere('name', $activeRoleName);

        if (!$activeRole) {
            return false;
        }

        return $activeRole->hasPermissionTo($permission, $guardName);
    }

    public function refreshActiveProfileCache(): void
    {
        $this->unsetRelation('roles');
        $this->unsetRelation('permissions');

        if (method_exists($this, 'forgetCachedPermissions')) {
            $this->forgetCachedPermissions();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function activeRoleMatches(string $activeRole, $roles, ?string $guard): bool
    {
        return in_array($activeRole, $this->resolveRoleNames($roles, $guard), true);
    }

    protected function resolveRoleNames($roles, ?string $guard = null): array
    {
        if ($roles instanceof Collection) {
            return $roles
                ->map(fn ($role) => $role instanceof Role ? $role->name : (string) $role)
                ->values()
                ->all();
        }

        if ($roles instanceof Role) {
            return [$roles->name];
        }

        if (is_string($roles)) {
            if (str_contains($roles, '|')) {
                return $this->convertPipeToArray($roles);
            }

            return [$roles];
        }

        if (is_array($roles)) {
            return array_values(array_map(function ($role) {
                if ($role instanceof Role) {
                    return $role->name;
                }

                if ($role instanceof \BackedEnum) {
                    return $role->value;
                }

                return (string) $role;
            }, $roles));
        }

        if ($roles instanceof \BackedEnum) {
            return [$roles->value];
        }

        return [];
    }
}
