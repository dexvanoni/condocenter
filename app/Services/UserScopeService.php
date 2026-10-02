<?php

namespace App\Services;

use App\Models\Condominium;
use App\Models\User;
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

        if ($this->isActingAsManagementCompany($actor)) {
            return !in_array($roleName, ['Administrador'], true);
        }

        if ($this->isActingAsSyndic($actor)) {
            return !in_array($roleName, ['Administrador', 'Síndico', 'Conselho Fiscal'], true);
        }

        return false;
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
