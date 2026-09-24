<?php

namespace App\Services;

use App\Models\Condominium;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ActiveCondominiumService
{
    public const SESSION_KEY = 'active_condominium_id';
    public const ORGANIZATION_SESSION_KEY = 'active_organization_id';

    /**
     * Quem pode alternar condomínio via sessão: Administrador da plataforma
     * ou membro de administradora profissional.
     */
    public function canUseCondominiumContext(User $user): bool
    {
        return $user->isAdmin() || $this->isManagementCompanyMember($user);
    }

    public function canSwitchCondominiums(User $user): bool
    {
        if (!$this->canUseCondominiumContext($user)) {
            return false;
        }

        return $this->accessibleCondominiums($user)->count() > 1;
    }

    public function accessibleCondominiums(User $user): Collection
    {
        if ($user->isAdmin()) {
            return Condominium::query()->orderBy('name')->get();
        }

        if ($this->isManagementCompanyMember($user)) {
            $organizationIds = $this->accessibleOrganizationIds($user);

            if ($organizationIds->isEmpty()) {
                return collect();
            }

            return Condominium::query()
                ->whereIn('organization_id', $organizationIds->all())
                ->orderBy('name')
                ->get();
        }

        return collect();
    }

    public function accessibleCondominiumIds(User $user): Collection
    {
        return $this->accessibleCondominiums($user)->pluck('id');
    }

    public function accessibleOrganizationIds(User $user): Collection
    {
        if ($user->isAdmin()) {
            return Organization::query()->pluck('id');
        }

        return $user->organizations()
            ->where('organizations.type', Organization::TYPE_MANAGEMENT_COMPANY)
            ->pluck('organizations.id');
    }

    public function getActiveOrganizationId(User $user): ?int
    {
        if ($user->isAdmin()) {
            $sessionId = session(self::ORGANIZATION_SESSION_KEY);

            return $sessionId !== null ? (int) $sessionId : null;
        }

        $ids = $this->accessibleOrganizationIds($user);

        if ($ids->isEmpty()) {
            return null;
        }

        $sessionId = session(self::ORGANIZATION_SESSION_KEY);

        if ($sessionId !== null && $ids->contains((int) $sessionId)) {
            return (int) $sessionId;
        }

        if ($ids->count() === 1) {
            $id = (int) $ids->first();
            session([self::ORGANIZATION_SESSION_KEY => $id]);

            return $id;
        }

        return (int) $ids->first();
    }

    public function setActiveOrganization(User $user, int $organizationId): void
    {
        if ($user->isAdmin()) {
            session([self::ORGANIZATION_SESSION_KEY => $organizationId]);

            return;
        }

        if (!$this->accessibleOrganizationIds($user)->contains($organizationId)) {
            throw new InvalidArgumentException('Organização não acessível para este usuário.');
        }

        session([self::ORGANIZATION_SESSION_KEY => $organizationId]);
    }

    public function getActiveCondominiumId(User $user): ?int
    {
        if (!$this->canUseCondominiumContext($user)) {
            return $user->condominium_id ? (int) $user->condominium_id : null;
        }

        $accessibleIds = $this->accessibleCondominiumIds($user);

        if ($accessibleIds->isEmpty()) {
            return null;
        }

        $sessionId = session(self::SESSION_KEY);

        if ($sessionId !== null && $accessibleIds->contains((int) $sessionId)) {
            return (int) $sessionId;
        }

        if ($accessibleIds->count() === 1) {
            return $this->resolveDefaultCondominiumId($user, $accessibleIds);
        }

        return null;
    }

    public function getActiveCondominium(User $user): ?Condominium
    {
        if (!$this->canUseCondominiumContext($user)) {
            return $user->condominium;
        }

        $id = $this->getActiveCondominiumId($user);

        return $id ? Condominium::find($id) : null;
    }

    public function hasActiveCondominium(User $user): bool
    {
        return $this->getActiveCondominiumId($user) !== null;
    }

    public function setActiveCondominium(User $user, int $condominiumId): void
    {
        if (!$this->canUseCondominiumContext($user)) {
            throw new InvalidArgumentException('Somente administradores e membros de administradora podem alternar condomínios.');
        }

        if (!$this->accessibleCondominiumIds($user)->contains($condominiumId)) {
            throw new InvalidArgumentException('Condomínio não acessível para este usuário.');
        }

        session([self::SESSION_KEY => $condominiumId]);

        $condominium = Condominium::query()->find($condominiumId);
        if ($condominium?->organization_id) {
            $this->setActiveOrganization($user, (int) $condominium->organization_id);
        }
    }

    public function clearActiveCondominium(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function userCanAccessCondominium(User $user, int $condominiumId): bool
    {
        if ($user->isAdmin()) {
            return $this->accessibleCondominiumIds($user)->contains($condominiumId);
        }

        if ($this->isManagementCompanyMember($user)) {
            return $this->accessibleCondominiumIds($user)->contains($condominiumId);
        }

        return (int) $user->condominium_id === $condominiumId;
    }

    public function userCanAccessOrganization(User $user, int $organizationId): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->accessibleOrganizationIds($user)->contains($organizationId);
    }

    protected function isManagementCompanyMember(User $user): bool
    {
        return $user->isManagementCompanyMember();
    }

    protected function resolveDefaultCondominiumId(User $user, Collection $accessibleIds): int
    {
        $id = (int) $accessibleIds->first();

        session([self::SESSION_KEY => $id]);

        return $id;
    }
}
