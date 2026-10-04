<?php

namespace App\Services;

use App\Models\Condominium;
use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class UserScopeService
{
    public function __construct(
        private ActiveCondominiumService $activeCondominium,
    ) {}

    public function activeAccessRole(User $actor): ?string
    {
        return session('active_role') ?: $actor->getActiveRoleName();
    }

    public function isActingAsPlatformAdmin(User $actor): bool
    {
        return $actor->hasAssignedRole('Administrador')
            && $this->activeAccessRole($actor) === 'Administrador';
    }

    public function isActingAsManagementCompany(User $actor): bool
    {
        return $this->activeAccessRole($actor) === User::PROFILE_ADMINISTRADORA
            && $actor->canUseManagementCompanyProfile();
    }

    public function isActingAsSyndic(User $actor): bool
    {
        return $actor->hasAssignedRole('Síndico')
            && $this->activeAccessRole($actor) === 'Síndico';
    }

    public function canManageUser(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) {
            return true;
        }

        if (!$actor->can('manage_users') && !$this->isActingAsManagementCompany($actor)) {
            return false;
        }

        if ($this->isActingAsPlatformAdmin($actor)) {
            return $this->targetVisibleToPlatformAdmin($actor, $target);
        }

        if ($this->isActingAsManagementCompany($actor)) {
            return $this->targetInOrganizationScope($actor, $target);
        }

        if ($this->isActingAsSyndic($actor)) {
            return $this->targetInSyndicCondominiumScope($actor, $target);
        }

        if ($actor->can('manage_users')) {
            return $this->targetInSyndicCondominiumScope($actor, $target)
                || $this->belongsToActorHomeCondominium($actor, $target);
        }

        return false;
    }

    public function canAssignRole(User $actor, string $roleName): bool
    {
        if ($roleName === User::PROFILE_ADMINISTRADORA) {
            return false;
        }

        if ($roleName === 'Administrador') {
            return $this->isActingAsPlatformAdmin($actor);
        }

        if ($this->isActingAsPlatformAdmin($actor)) {
            return true;
        }

        if ($this->canAssignCondominiumRoles($actor)) {
            return true;
        }

        return false;
    }

    /**
     * Síndico (ou administradora) que pode atribuir papéis no condomínio.
     *
     * Alinhado a {@see canManageUser}: quem edita usuários com manage_users e papel
     * Síndico atribuído deve poder marcar Síndico/Conselho, não só quando
     * session('active_role') === 'Síndico'.
     */
    public function canAssignCondominiumRoles(User $actor): bool
    {
        if ($this->isActingAsManagementCompany($actor)) {
            return true;
        }

        if ($this->isActingAsSyndic($actor)) {
            return true;
        }

        if (!$actor->hasAssignedRole('Síndico')) {
            return false;
        }

        if (!$actor->can('manage_users')) {
            return false;
        }

        return $this->activeCondominium->hasActiveCondominium($actor)
            || $this->activeCondominium->isProfessionalSyndic($actor)
            || $actor->condominium_id !== null;
    }

    /**
     * Papéis Spatie que o ator pode marcar na ficha de usuário.
     *
     * @return Collection<int, Role>
     */
    public function assignableRoles(User $actor): Collection
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->filter(fn (Role $role) => $this->canAssignRole($actor, $role->name))
            ->values();
    }

    /**
     * Papéis exibidos no formulário (atribuíveis + já vinculados ao usuário editado).
     *
     * @return Collection<int, Role>
     */
    public function rolesForUserForm(User $actor, ?User $subject = null): Collection
    {
        $roles = $this->assignableRoles($actor);

        if ($subject === null) {
            return $roles;
        }

        $subject->loadMissing('roles');
        $missingNames = $subject->roles
            ->pluck('name')
            ->filter(fn (string $name) => $roles->doesntContain('name', $name));

        if ($missingNames->isEmpty()) {
            return $roles;
        }

        $extra = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $missingNames->all())
            ->orderBy('name')
            ->get();

        return $roles->merge($extra)->sortBy('name')->values();
    }

    /**
     * Mantém papéis que o alvo já tem e o ator não pode atribuir/remover (ex.: Administrador).
     *
     * @param  list<string>  $requestedRoles
     * @return list<string>
     */
    public function mergeUnassignableExistingRoles(User $actor, User $target, array $requestedRoles): array
    {
        $preserved = $target->roles
            ->pluck('name')
            ->filter(fn (string $name) => !$this->canAssignRole($actor, $name))
            ->all();

        return array_values(array_unique(array_merge($requestedRoles, $preserved)));
    }

    public function canSendPasswordResetLink(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) {
            return false;
        }

        return $this->canManageUser($actor, $target);
    }

    protected function targetVisibleToPlatformAdmin(User $actor, User $target): bool
    {
        return true;
    }

    protected function targetInOrganizationScope(User $actor, User $target): bool
    {
        $organizationIds = $this->activeCondominium->accessibleOrganizationIds($actor);

        if ($organizationIds->isEmpty()) {
            return false;
        }

        if ($target->condominium_id) {
            return Condominium::query()
                ->whereIn('organization_id', $organizationIds->all())
                ->whereKey($target->condominium_id)
                ->exists();
        }

        return $target->organizations()
            ->whereIn('organizations.id', $organizationIds->all())
            ->exists();
    }

    protected function targetInSyndicCondominiumScope(User $actor, User $target): bool
    {
        $activeId = $this->activeCondominium->getActiveCondominiumId($actor);
        if ($activeId !== null && $target->belongsToCondominium((int) $activeId)) {
            return true;
        }

        if (!$target->condominium_id) {
            return false;
        }

        $targetCondoId = (int) $target->condominium_id;

        if ($this->activeCondominium->isProfessionalSyndic($actor)) {
            return $this->activeCondominium->accessibleCondominiumIds($actor)->contains($targetCondoId);
        }

        return $this->belongsToActorHomeCondominium($actor, $target);
    }

    protected function belongsToActorHomeCondominium(User $actor, User $target): bool
    {
        return $actor->condominium_id
            && (int) $actor->condominium_id === (int) $target->condominium_id;
    }
}
