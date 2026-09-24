<?php

namespace App\Services;

use App\Models\Condominium;
use App\Models\CondominiumSubscription;
use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\User;
use Illuminate\Support\Collection;

class PlatformSubscriptionStatsService
{
    public function dashboardMetrics(): array
    {
        $subscriptions = CondominiumSubscription::query()
            ->with('condominium:id,name,saas_complimentary')
            ->get();

        $orgSubscriptions = OrganizationSubscription::query()
            ->with('organization:id,legal_name,trade_name,type')
            ->get();

        $billableSubscriptions = $subscriptions->filter(
            fn (CondominiumSubscription $sub) => !$sub->condominium?->isSaasComplimentary()
        );

        $activeLike = $billableSubscriptions->whereIn('status', [
            CondominiumSubscription::STATUS_ACTIVE,
            CondominiumSubscription::STATUS_TRIAL,
        ]);

        $orgActiveLike = $orgSubscriptions->whereIn('status', [
            OrganizationSubscription::STATUS_ACTIVE,
            OrganizationSubscription::STATUS_TRIAL,
        ]);

        $complimentaryCondominiums = Condominium::query()
            ->where('saas_complimentary', true)
            ->orderBy('name')
            ->get(['id', 'name', 'saas_complimentary_notes']);

        return [
            'total_condominiums' => Condominium::count(),
            'total_organizations' => Organization::count(),
            'management_companies' => Organization::query()->where('type', Organization::TYPE_MANAGEMENT_COMPANY)->count(),
            'direct_organizations' => Organization::query()->where('type', Organization::TYPE_CONDOMINIUM)->count(),
            'total_units' => \App\Models\Unit::count(),
            'total_users' => User::count(),
            'complimentary' => $complimentaryCondominiums->count(),
            'complimentary_list' => $complimentaryCondominiums->take(8),
            'total_contracts' => $subscriptions->count(),
            'org_contracts' => $orgSubscriptions->count(),
            'active' => $subscriptions->where('status', CondominiumSubscription::STATUS_ACTIVE)->count()
                + $orgSubscriptions->where('status', OrganizationSubscription::STATUS_ACTIVE)->count(),
            'trial' => $subscriptions->where('status', CondominiumSubscription::STATUS_TRIAL)->count()
                + $orgSubscriptions->where('status', OrganizationSubscription::STATUS_TRIAL)->count(),
            'past_due' => $subscriptions->where('status', CondominiumSubscription::STATUS_PAST_DUE)->count()
                + $orgSubscriptions->where('status', OrganizationSubscription::STATUS_PAST_DUE)->count(),
            'suspended' => $subscriptions->where('status', CondominiumSubscription::STATUS_SUSPENDED)->count()
                + $orgSubscriptions->where('status', OrganizationSubscription::STATUS_SUSPENDED)->count(),
            'cancelled' => $subscriptions->where('status', CondominiumSubscription::STATUS_CANCELLED)->count()
                + $orgSubscriptions->where('status', OrganizationSubscription::STATUS_CANCELLED)->count(),
            'draft' => $subscriptions->where('status', CondominiumSubscription::STATUS_DRAFT)->count()
                + $orgSubscriptions->where('status', OrganizationSubscription::STATUS_DRAFT)->count(),
            'mrr' => round($this->calculateMrr($activeLike) + $this->calculateOrgMrr($orgActiveLike), 2),
            'arr' => round(($this->calculateMrr($activeLike) + $this->calculateOrgMrr($orgActiveLike)) * 12, 2),
            'trials_expiring_soon' => $this->trialsExpiringSoon($subscriptions),
            'past_due_list' => $subscriptions
                ->where('status', CondominiumSubscription::STATUS_PAST_DUE)
                ->sortByDesc('updated_at')
                ->take(8)
                ->values(),
            'recent_activations' => $subscriptions
                ->whereNotNull('activated_at')
                ->sortByDesc('activated_at')
                ->take(8)
                ->values(),
        ];
    }

    public function calculateMrr(Collection $subscriptions): float
    {
        return $subscriptions->sum(function (CondominiumSubscription $sub) {
            $amount = (float) $sub->recurring_amount;

            return match ($sub->billing_cycle) {
                CondominiumSubscription::CYCLE_QUARTERLY => $amount / 3,
                CondominiumSubscription::CYCLE_SEMIANNUAL => $amount / 6,
                CondominiumSubscription::CYCLE_ANNUAL => $amount / 12,
                default => $amount,
            };
        });
    }

    public function calculateOrgMrr(Collection $subscriptions): float
    {
        return $subscriptions->sum(function (OrganizationSubscription $sub) {
            $amount = (float) $sub->recurring_amount;

            return match ($sub->billing_cycle) {
                OrganizationSubscription::CYCLE_QUARTERLY => $amount / 3,
                OrganizationSubscription::CYCLE_SEMIANNUAL => $amount / 6,
                OrganizationSubscription::CYCLE_ANNUAL => $amount / 12,
                default => $amount,
            };
        });
    }

    protected function trialsExpiringSoon(Collection $subscriptions): Collection
    {
        return $subscriptions
            ->filter(function (CondominiumSubscription $sub) {
                return $sub->status === CondominiumSubscription::STATUS_TRIAL
                    && $sub->trial_ends_at
                    && $sub->trial_ends_at->isBetween(now(), now()->addDays(7));
            })
            ->sortBy('trial_ends_at')
            ->values();
    }
}
