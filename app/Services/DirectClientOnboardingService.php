<?php

namespace App\Services;

use App\Mail\ClientWelcomeMail;
use App\Models\Condominium;
use App\Models\CondominiumSubscription;
use App\Models\Organization;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Support\CondominiumModules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class DirectClientOnboardingService
{
    public function __construct(
        private OrganizationProvisioningService $provisioning,
        private OrganizationSubscriptionService $organizationSubscriptions,
    ) {}

    /**
     * Modelo A — síndico / condomínio direto.
     *
     * @param  array{
     *     organization?: array<string, mixed>,
     *     condominium: array<string, mixed>,
     *     user: array{name: string, email: string, phone?: ?string},
     *     subscription_plan_id?: int|null
     * }  $payload
     * @return array{organization: Organization, condominium: Condominium, user: User, subscription: ?CondominiumSubscription}
     */
    public function onboardDirect(array $payload, ?User $admin = null): array
    {
        return DB::transaction(function () use ($payload, $admin) {
            $condoData = $payload['condominium'];
            $userData = $payload['user'];
            $orgOverrides = $payload['organization'] ?? [];

            $organization = Organization::query()->create(array_merge([
                'type' => Organization::TYPE_CONDOMINIUM,
                'legal_name' => $condoData['name'],
                'trade_name' => $condoData['name'],
                'document' => $condoData['cnpj'] ?? null,
                'email' => $condoData['email'] ?? $userData['email'],
                'phone' => $condoData['phone'] ?? ($userData['phone'] ?? null),
                'address' => $condoData['address'] ?? null,
                'neighborhood' => $condoData['neighborhood'] ?? null,
                'city' => $condoData['city'] ?? null,
                'state' => $condoData['state'] ?? null,
                'zip_code' => $condoData['zip_code'] ?? null,
                'status' => Organization::STATUS_ACTIVE,
            ], $orgOverrides));

            $plan = !empty($payload['subscription_plan_id'])
                ? SubscriptionPlan::query()->find($payload['subscription_plan_id'])
                : null;
            $unitsLimit = $condoData['units_limit'] ?? $plan?->max_units;

            $condominium = Condominium::query()->create([
                'organization_id' => $organization->id,
                'name' => $condoData['name'],
                'units_limit' => $unitsLimit !== null && $unitsLimit !== '' ? (int) $unitsLimit : null,
                'cnpj' => $condoData['cnpj'] ?? null,
                'address' => $condoData['address'] ?? '',
                'neighborhood' => $condoData['neighborhood'] ?? null,
                'city' => $condoData['city'] ?? '',
                'state' => $condoData['state'] ?? '',
                'zip_code' => $condoData['zip_code'] ?? '',
                'phone' => $condoData['phone'] ?? ($userData['phone'] ?? null),
                'email' => $condoData['email'] ?? $userData['email'],
                'description' => $condoData['description'] ?? null,
                'is_active' => true,
                'financial_mode' => $condoData['financial_mode'] ?? 'simplified',
                'enabled_modules' => CondominiumModules::keys(),
                'registration_code' => Condominium::generateUniqueRegistrationCode(),
            ]);

            $user = $this->createTemporaryUser([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'phone' => $userData['phone'] ?? null,
                'condominium_id' => $condominium->id,
            ]);

            $user->assignRole('Síndico');

            $this->provisioning->attachUser($organization, (int) $user->id, Organization::ROLE_OWNER);

            $subscription = null;
            $planId = $payload['subscription_plan_id'] ?? null;

            if ($planId) {
                $subscription = $this->createCondominiumSubscriptionDraft(
                    $condominium,
                    (int) $planId,
                    $admin,
                    $user
                );
            }

            $this->sendWelcome($user, $condominium->name, ClientWelcomeMail::AUDIENCE_SINDICO);

            return [
                'organization' => $organization->fresh(),
                'condominium' => $condominium->fresh(),
                'user' => $user->fresh(),
                'subscription' => $subscription,
            ];
        });
    }

    /**
     * Modelo B — administradora profissional.
     *
     * @param  array{
     *     organization: array<string, mixed>,
     *     user: array{name: string, email: string, phone?: ?string},
     *     subscription_plan_id?: int|null
     * }  $payload
     * @return array{organization: Organization, user: User, subscription: ?\App\Models\OrganizationSubscription}
     */
    public function onboardManagement(array $payload, ?User $admin = null): array
    {
        return DB::transaction(function () use ($payload, $admin) {
            $orgData = $payload['organization'];
            $userData = $payload['user'];

            $organization = $this->provisioning->createManagementCompany([
                'legal_name' => $orgData['legal_name'],
                'trade_name' => $orgData['trade_name'] ?? $orgData['legal_name'],
                'document' => $orgData['document'] ?? null,
                'email' => $orgData['email'] ?? $userData['email'],
                'phone' => $orgData['phone'] ?? ($userData['phone'] ?? null),
                'address' => $orgData['address'] ?? null,
                'neighborhood' => $orgData['neighborhood'] ?? null,
                'city' => $orgData['city'] ?? null,
                'state' => $orgData['state'] ?? null,
                'zip_code' => $orgData['zip_code'] ?? null,
                'notes' => $orgData['notes'] ?? null,
                'status' => Organization::STATUS_ACTIVE,
            ]);

            $user = $this->createTemporaryUser([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'phone' => $userData['phone'] ?? null,
                'condominium_id' => null,
            ]);

            $this->provisioning->attachUser($organization, (int) $user->id, Organization::ROLE_OWNER);

            $subscription = null;
            $planId = $payload['subscription_plan_id'] ?? null;

            if ($planId) {
                $subscription = $this->organizationSubscriptions->createDraft($organization, [
                    'subscription_plan_id' => (int) $planId,
                    'financial_responsible_user_id' => $user->id,
                    'financial_contact_name' => $user->name,
                    'financial_contact_email' => $user->email,
                    'financial_contact_phone' => $user->phone,
                    'financial_cnpj' => $organization->document,
                ], $admin);
            }

            $this->sendWelcome(
                $user,
                $organization->displayName(),
                ClientWelcomeMail::AUDIENCE_ADMINISTRADORA
            );

            return [
                'organization' => $organization->fresh(),
                'user' => $user->fresh(),
                'subscription' => $subscription,
            ];
        });
    }

    protected function sendWelcome(User $user, string $placeName, string $audience): void
    {
        $token = Password::createToken($user);
        $setupUrl = route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);

        Mail::to($user->email)->send(new ClientWelcomeMail(
            $user,
            $setupUrl,
            $placeName,
            $audience,
            (int) config('auth.passwords.users.expire', 60),
        ));
    }

    /**
     * @param  array{name: string, email: string, phone?: ?string, condominium_id?: int|null}  $data
     */
    protected function createTemporaryUser(array $data): User
    {
        $randomPassword = Str::password(32);

        return User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'condominium_id' => $data['condominium_id'] ?? null,
            'password' => Hash::make($randomPassword),
            'senha_temporaria' => true,
            'is_active' => true,
            'registration_status' => 'approved',
        ]);
    }

    protected function createCondominiumSubscriptionDraft(
        Condominium $condominium,
        int $planId,
        ?User $admin,
        User $financialResponsible
    ): CondominiumSubscription {
        $plan = SubscriptionPlan::query()->findOrFail($planId);
        $defaults = $plan->toContractDefaults();

        return CondominiumSubscription::query()->create([
            'condominium_id' => $condominium->id,
            'subscription_plan_id' => $plan->id,
            'financial_responsible_user_id' => $financialResponsible->id,
            'created_by' => $admin?->id,
            'billing_metric' => $defaults['billing_metric'],
            'unit_price' => $defaults['unit_price'] ?? 0,
            'user_price' => $defaults['user_price'] ?? 0,
            'fixed_price' => $defaults['fixed_price'] ?? 0,
            'billing_cycle' => $defaults['billing_cycle'],
            'trial_days' => (int) ($defaults['trial_days'] ?? 0),
            'payment_method' => $defaults['payment_method'],
            'financial_cnpj' => $condominium->cnpj,
            'financial_contact_name' => $financialResponsible->name,
            'financial_contact_email' => $financialResponsible->email,
            'financial_contact_phone' => $financialResponsible->phone,
            'billable_quantity' => 0,
            'recurring_amount' => 0,
            'status' => CondominiumSubscription::STATUS_DRAFT,
        ]);
    }
}
