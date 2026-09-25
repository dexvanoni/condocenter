<?php

namespace App\Services;

use App\Models\Condominium;
use App\Models\CondominiumSubscription;
use App\Models\Organization;
use App\Models\User;

class SaasCondominiumAccessService
{
    public function __construct(
        private ActiveCondominiumService $activeCondominium,
        private OrganizationSubscriptionService $organizationSubscriptions,
    ) {}

    public function enforcementEnabled(): bool
    {
        return (bool) config('saas.enforce_subscription', true);
    }

    public function resolveCondominiumForUser(User $user): ?Condominium
    {
        return $this->activeCondominium->getActiveCondominium($user) ?? $user->condominium;
    }

    public function condominiumAllowsResidents(Condominium $condominium): bool
    {
        if ($this->condominiumIsComplimentary($condominium)) {
            return true;
        }

        if ($condominium->organization_id) {
            $organization = $condominium->relationLoaded('organization')
                ? $condominium->organization
                : $condominium->organization()->first();

            if ($organization?->isManagementCompany()) {
                return $this->organizationSubscriptions->organizationAccessAllowed($organization);
            }
        }

        $subscriptions = $condominium->relationLoaded('subscriptions')
            ? $condominium->subscriptions
            : $condominium->subscriptions()->get();

        if ($subscriptions->isEmpty()) {
            return false;
        }

        return $subscriptions->contains(
            fn (CondominiumSubscription $subscription) => $this->subscriptionAllowsResidents($subscription)
        );
    }

    public function subscriptionAllowsResidents(CondominiumSubscription $subscription): bool
    {
        if ($subscription->status === CondominiumSubscription::STATUS_DRAFT) {
            return false;
        }

        return $subscription->isAccessAllowed();
    }

    public function userBlockedByCondominiumContract(User $user): bool
    {
        if (!$this->enforcementEnabled() || $user->isAdmin()) {
            return false;
        }

        $condominium = $this->resolveCondominiumForUser($user);

        if (!$condominium) {
            return false;
        }

        return !$this->condominiumAllowsResidents($condominium);
    }

    /**
     * @return array{condominium: Condominium, status_label: ?string, status: ?string}
     */
    public function blockedContext(User $user): ?array
    {
        if (!$this->userBlockedByCondominiumContract($user)) {
            return null;
        }

        $condominium = $this->resolveCondominiumForUser($user);
        if (!$condominium) {
            return null;
        }

        $subscription = $condominium->subscription;

        return [
            'condominium' => $condominium,
            'status_label' => $subscription?->statusLabel(),
            'status' => $subscription?->status,
        ];
    }

    protected function condominiumIsComplimentary(Condominium $condominium): bool
    {
        if (array_key_exists('saas_complimentary', $condominium->getAttributes())) {
            return $condominium->isSaasComplimentary();
        }

        return (bool) Condominium::query()
            ->whereKey($condominium->id)
            ->value('saas_complimentary');
    }

    public function supportContact(): string
    {
        $contact = trim((string) config('saas.developer_contact', ''));

        if ($contact !== '') {
            return $contact;
        }

        return (string) config('mail.from.address', 'admin@condomanager.com');
    }
}
