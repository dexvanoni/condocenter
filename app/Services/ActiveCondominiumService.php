<?php

namespace App\Services;

use App\Models\Condominium;
use App\Models\User;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ActiveCondominiumService
{
    public const SESSION_KEY = 'active_condominium_id';

    /**
     * Apenas o administrador da plataforma alterna condomínio via sessão.
     */
    public function canUseCondominiumContext(User $user): bool
    {
        return $user->isAdmin();
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

        return collect();
    }

    public function accessibleCondominiumIds(User $user): Collection
    {
        return $this->accessibleCondominiums($user)->pluck('id');
    }

    public function getActiveCondominiumId(User $user): ?int
    {
        // Demais perfis: sempre o condomínio vinculado no cadastro
        if (!$user->isAdmin()) {
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
        if (!$user->isAdmin()) {
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
        if (!$user->isAdmin()) {
            throw new InvalidArgumentException('Somente administradores podem alternar condomínios.');
        }

        if (!$this->accessibleCondominiumIds($user)->contains($condominiumId)) {
            throw new InvalidArgumentException('Condomínio não acessível para este usuário.');
        }

        session([self::SESSION_KEY => $condominiumId]);
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

        return (int) $user->condominium_id === $condominiumId;
    }

    protected function resolveDefaultCondominiumId(User $user, Collection $accessibleIds): int
    {
        $id = (int) $accessibleIds->first();

        session([self::SESSION_KEY => $id]);

        return $id;
    }
}
