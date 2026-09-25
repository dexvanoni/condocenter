<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\OrganizationSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class OrganizationQuotaService
{
    /**
     * @return array{
     *     subscription: ?OrganizationSubscription,
     *     max_condominiums: ?int,
     *     max_units: ?int,
     *     condominiums_used: int,
     *     units_used: int,
     *     units_reserved: int,
     *     units_remaining_allocation: ?int
     * }
     */
    public function snapshot(Organization $organization, ?Collection $condominiums = null): array
    {
        $cacheKey = 'organization_quota_snapshot_'.$organization->id;
        $request = app()->bound('request') ? app(Request::class) : null;

        if ($condominiums === null && $request?->attributes->has($cacheKey)) {
            /** @var array<string, mixed> $cached */
            $cached = $request->attributes->get($cacheKey);

            return $cached;
        }

        $subscription = $this->governingSubscription($organization);

        if ($condominiums === null) {
            $condominiums = $organization->condominiums()->withCount('units')->get();
        }

        $snapshot = $this->buildSnapshot($subscription, $condominiums);

        if ($request) {
            $request->attributes->set($cacheKey, $snapshot);
        }

        return $snapshot;
    }

    public function canCreateCondominium(Organization $organization): bool
    {
        $snapshot = $this->snapshot($organization);

        if ($snapshot['subscription'] === null) {
            return false;
        }

        if ($snapshot['max_condominiums'] === null) {
            return true;
        }

        return $snapshot['condominiums_used'] < $snapshot['max_condominiums'];
    }

    public function remainingUnitSlots(Organization $organization): ?int
    {
        $snapshot = $this->snapshot($organization);

        return $snapshot['units_remaining_allocation'];
    }

    public function acceptsCondominiumUnitLimit(Organization $organization, int $unitsLimit): bool
    {
        $snapshot = $this->snapshot($organization);

        if ($snapshot['max_units'] === null) {
            return true;
        }

        return ($snapshot['units_reserved'] + $unitsLimit) <= $snapshot['max_units'];
    }

    public function canCreateUnit(Organization $organization): bool
    {
        $remaining = $this->remainingUnitSlots($organization);

        return $remaining === null || $remaining > 0;
    }

    protected function governingSubscription(Organization $organization): ?OrganizationSubscription
    {
        return $organization->subscriptions()
            ->orderByDesc('id')
            ->get()
            ->first(fn (OrganizationSubscription $subscription) => $subscription->isAccessAllowed());
    }

    /**
     * @param  Collection<int, \App\Models\Condominium>  $condominiums
     * @return array{
     *     subscription: ?OrganizationSubscription,
     *     max_condominiums: ?int,
     *     max_units: ?int,
     *     condominiums_used: int,
     *     units_used: int,
     *     units_reserved: int,
     *     units_remaining_allocation: ?int
     * }
     */
    protected function buildSnapshot(?OrganizationSubscription $subscription, Collection $condominiums): array
    {
        $maxUnits = $subscription?->max_units !== null
            ? (int) $subscription->max_units
            : null;
        $unitsReserved = (int) $condominiums->sum(fn ($condo) => (int) ($condo->units_limit ?? $condo->units_count));

        return [
            'subscription' => $subscription,
            'max_condominiums' => $subscription?->max_condominiums !== null
                ? (int) $subscription->max_condominiums
                : null,
            'max_units' => $maxUnits,
            'condominiums_used' => $condominiums->count(),
            'units_used' => (int) $condominiums->sum('units_count'),
            'units_reserved' => $unitsReserved,
            'units_remaining_allocation' => $maxUnits !== null
                ? max(0, $maxUnits - $unitsReserved)
                : null,
        ];
    }
}
