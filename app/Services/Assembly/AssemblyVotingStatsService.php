<?php

namespace App\Services\Assembly;

use App\Models\Assembly;
use App\Models\AssemblyVote;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class AssemblyVotingStatsService
{
    public function compute(Assembly $assembly): array
    {
        $roleNames = $this->resolveAllowedRoleNames($assembly);

        $eligibleUsers = User::query()
            ->where('condominium_id', $assembly->condominium_id)
            ->where('is_active', true)
            ->whereHas('roles', fn ($query) => $query->whereIn('name', $roleNames))
            ->with(['roles:id,name', 'unit:id,number,block'])
            ->get(['id', 'name', 'unit_id']);

        $votedUserIds = AssemblyVote::query()
            ->where('assembly_id', $assembly->id)
            ->distinct()
            ->pluck('voter_id')
            ->filter()
            ->map(fn ($id) => (int) $id);

        $votedUnitIds = AssemblyVote::query()
            ->where('assembly_id', $assembly->id)
            ->whereNotNull('unit_id')
            ->distinct()
            ->pluck('unit_id')
            ->filter()
            ->map(fn ($id) => (int) $id);

        $eligibleUserCount = $eligibleUsers->count();
        $votedUserCount = $eligibleUsers->whereIn('id', $votedUserIds)->count();

        $eligibleUnitIds = $eligibleUsers
            ->pluck('unit_id')
            ->filter()
            ->unique()
            ->values();

        $votedEligibleUnitIds = $eligibleUnitIds->intersect($votedUnitIds);
        $eligibleUnitCount = $eligibleUnitIds->count();
        $votedUnitCount = $votedEligibleUnitIds->count();

        return [
            'allowed_roles' => $roleNames->values()->all(),
            'users' => $this->buildBucket($eligibleUserCount, $votedUserCount),
            'units' => $this->buildBucket($eligibleUnitCount, $votedUnitCount),
            'by_role' => $this->buildRoleBreakdown($eligibleUsers, $votedUserIds, $roleNames),
        ];
    }

    protected function resolveAllowedRoleNames(Assembly $assembly): Collection
    {
        $assembly->loadMissing('allowedRoles');

        $roles = $assembly->allowedRoles
            ->pluck('name')
            ->merge(Arr::wrap($assembly->voter_scope))
            ->filter()
            ->unique()
            ->values();

        if ($roles->isEmpty()) {
            return collect(['Morador', 'Síndico']);
        }

        return $roles;
    }

    protected function buildBucket(int $eligible, int $voted): array
    {
        $pending = max(0, $eligible - $voted);
        $rate = $eligible > 0 ? round(($voted / $eligible) * 100, 1) : 0.0;

        return [
            'eligible' => $eligible,
            'voted' => $voted,
            'pending' => $pending,
            'participation_rate' => $rate,
        ];
    }

    protected function buildRoleBreakdown(Collection $eligibleUsers, Collection $votedUserIds, Collection $roleNames): array
    {
        return $roleNames->map(function (string $roleName) use ($eligibleUsers, $votedUserIds) {
            $roleUsers = $eligibleUsers->filter(
                fn (User $user) => $user->roles->contains('name', $roleName)
            );

            $eligible = $roleUsers->count();
            $voted = $roleUsers->whereIn('id', $votedUserIds)->count();

            return array_merge(
                ['role' => $roleName],
                $this->buildBucket($eligible, $voted)
            );
        })->values()->all();
    }
}
