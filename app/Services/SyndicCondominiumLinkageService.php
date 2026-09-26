<?php

namespace App\Services;

use App\Models\Condominium;
use App\Models\User;

class SyndicCondominiumLinkageService
{
    public function attach(User $user, int $condominiumId): void
    {
        if (!$user->hasAssignedRole('Síndico')) {
            return;
        }

        if (!Condominium::query()->whereKey($condominiumId)->exists()) {
            return;
        }

        $user->managedCondominiums()->syncWithoutDetaching([$condominiumId]);
    }

    /**
     * Condomínios em que o usuário atua como síndico (pivot + cadastro “morador/síndico” legado).
     *
     * @return list<int>
     */
    public function managedCondominiumIds(User $user): array
    {
        if (!$user->hasAssignedRole('Síndico')) {
            return [];
        }

        $ids = $user->managedCondominiums()
            ->pluck('condominiums.id')
            ->map(fn ($id) => (int) $id);

        if ($user->condominium_id) {
            $ids->push((int) $user->condominium_id);
        }

        return $ids->unique()->sort()->values()->all();
    }

    public function syncHomeCondominiumPivot(User $user): void
    {
        if ($user->condominium_id) {
            $this->attach($user, (int) $user->condominium_id);
        }
    }
}
