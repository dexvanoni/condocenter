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

    private ?int $cacheUserId = null;

    /** @var Collection<int, Condominium>|null */
    private ?Collection $accessibleCondominiumsCache = null;

    private ?bool $professionalSyndicCache = null;

    /** @var Collection<int, int>|null */
    private ?Collection $accessibleOrganizationIdsCache = null;

    /** @var list<int>|null */
    private ?array $managedCondominiumIdsCache = null;

    public function canUseCondominiumContext(User $user): bool
    {
        return $user->isAdmin()
            || $this->isManagementCompanyMember($user)
            || $this->isProfessionalSyndic($user);
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
        if ($this->cacheUserId === $user->id && $this->accessibleCondominiumsCache !== null) {
            return $this->accessibleCondominiumsCache;
        }

        $this->resetCacheFor($user);

        if ($user->isAdmin()) {
            $this->accessibleCondominiumsCache = Condominium::query()
                ->select(['id', 'name', 'organization_id', 'city', 'state', 'is_active', 'saas_complimentary'])
                ->orderBy('name')
                ->get();
        } elseif ($this->isManagementCompanyMember($user)) {
            $organizationIds = $this->accessibleOrganizationIds($user);

            $this->accessibleCondominiumsCache = $organizationIds->isEmpty()
                ? collect()
                : Condominium::query()
                    ->select(['id', 'name', 'organization_id', 'city', 'state', 'is_active', 'saas_complimentary'])
                    ->whereIn('organization_id', $organizationIds->all())
                    ->orderBy('name')
                    ->get();
        } elseif ($this->isProfessionalSyndic($user)) {
            $this->accessibleCondominiumsCache = $user->managedCondominiums()
                ->select(['condominiums.id', 'condominiums.name', 'condominiums.organization_id', 'condominiums.city', 'condominiums.state', 'condominiums.is_active', 'condominiums.saas_complimentary'])
                ->orderBy('condominiums.name')
                ->get();
        } else {
            $this->accessibleCondominiumsCache = collect();
        }

        return $this->accessibleCondominiumsCache;
    }

    public function accessibleCondominiumIds(User $user): Collection
    {
        return $this->accessibleCondominiums($user)->pluck('id');
    }

    public function accessibleOrganizationIds(User $user): Collection
    {
        if ($this->cacheUserId === $user->id && $this->accessibleOrganizationIdsCache !== null) {
            return $this->accessibleOrganizationIdsCache;
        }

        $this->resetCacheFor($user);

        $this->accessibleOrganizationIdsCache = $user->isAdmin()
            ? Organization::query()->pluck('id')
            : $user->organizations()
                ->where('organizations.type', Organization::TYPE_MANAGEMENT_COMPANY)
                ->pluck('organizations.id');

        return $this->accessibleOrganizationIdsCache;
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
        if (session('active_role') === 'Morador' && $user->condominium_id) {
            return (int) $user->condominium_id;
        }

        if (session('active_role') === User::PROFILE_ADMINISTRADORA && session(self::SESSION_KEY) === null) {
            return null;
        }

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

        if (!$id) {
            return null;
        }

        $cached = $this->accessibleCondominiums($user)->firstWhere('id', $id);

        return $cached instanceof Condominium
            ? $cached
            : Condominium::query()->find($id);
    }

    public function hasActiveCondominium(User $user): bool
    {
        return $this->getActiveCondominiumId($user) !== null;
    }

    public function setActiveCondominium(User $user, int $condominiumId): void
    {
        if (!$this->canUseCondominiumContext($user)) {
            throw new InvalidArgumentException('Somente administradores, administradoras e síndicos profissionais podem alternar condomínios.');
        }

        if (!$this->accessibleCondominiumIds($user)->contains($condominiumId)) {
            throw new InvalidArgumentException('Condomínio não acessível para este usuário.');
        }

        session([self::SESSION_KEY => $condominiumId]);

        $condominium = Condominium::query()->find($condominiumId);
        if ($condominium?->organization_id && $this->userCanAccessOrganization($user, (int) $condominium->organization_id)) {
            $this->setActiveOrganization($user, (int) $condominium->organization_id);
        }
    }

    public function clearActiveCondominium(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function userCanAccessCondominium(User $user, int $condominiumId): bool
    {
        if ($user->isAdmin() || $this->isManagementCompanyMember($user) || $this->isProfessionalSyndic($user)) {
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

    public function isProfessionalSyndic(User $user): bool
    {
        if ($this->cacheUserId === $user->id && $this->professionalSyndicCache !== null) {
            return $this->professionalSyndicCache;
        }

        $this->resetCacheFor($user);

        if (!$user->hasAssignedRole('Síndico') || $user->isAdmin() || $user->isManagementCompanyMember()) {
            $this->professionalSyndicCache = false;

            return false;
        }

        $managedIds = $this->managedCondominiumIds($user);
        $managedCount = count($managedIds);

        if ($managedCount > 1) {
            $this->professionalSyndicCache = true;

            return true;
        }

        if ($managedCount !== 1) {
            $this->professionalSyndicCache = false;

            return false;
        }

        $managedId = $managedIds[0];
        $this->professionalSyndicCache = !$user->unit_id || (int) $user->condominium_id !== $managedId;

        return $this->professionalSyndicCache;
    }

    /**
     * @return list<int>
     */
    protected function managedCondominiumIds(User $user): array
    {
        if ($this->cacheUserId === $user->id && $this->managedCondominiumIdsCache !== null) {
            return $this->managedCondominiumIdsCache;
        }

        $this->resetCacheFor($user);

        $this->managedCondominiumIdsCache = $user->managedCondominiums()
            ->pluck('condominiums.id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return $this->managedCondominiumIdsCache;
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

    protected function resetCacheFor(User $user): void
    {
        if ($this->cacheUserId !== $user->id) {
            $this->cacheUserId = $user->id;
            $this->accessibleCondominiumsCache = null;
            $this->professionalSyndicCache = null;
            $this->accessibleOrganizationIdsCache = null;
            $this->managedCondominiumIdsCache = null;
        }
    }
}
