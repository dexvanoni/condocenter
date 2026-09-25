<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrganizationSubscriptionService
{
    public function __construct(
        private PlatformAsaasService $asaas,
        private PlatformSettingsService $platformSettings,
    ) {}

    public function createDraft(Organization $organization, array $data, ?User $admin = null): OrganizationSubscription
    {
        return $this->upsert($organization, array_merge($data, [
            'status' => OrganizationSubscription::STATUS_DRAFT,
        ]), $admin);
    }

    public function upsert(Organization $organization, array $data, ?User $admin = null): OrganizationSubscription
    {
        return DB::transaction(function () use ($organization, $data, $admin) {
            $subscription = null;
            if (!empty($data['subscription_id'])) {
                $subscription = $organization->subscriptions()
                    ->whereKey($data['subscription_id'])
                    ->first();
            }

            $subscription ??= new OrganizationSubscription([
                'organization_id' => $organization->id,
                'created_by' => $admin?->id,
                'status' => OrganizationSubscription::STATUS_DRAFT,
            ]);

            if (!empty($data['subscription_plan_id'])) {
                $plan = SubscriptionPlan::query()->find($data['subscription_plan_id']);
                if ($plan) {
                    $subscription->subscription_plan_id = $plan->id;
                    $data = array_merge($plan->toContractDefaults(), [
                        'max_condominiums' => $plan->max_condominiums,
                        'max_units' => $plan->max_units,
                        'max_users' => $plan->max_users,
                    ], $data);
                }
            }

            $subscription->fill([
                'subscription_plan_id' => $data['subscription_plan_id'] ?? $subscription->subscription_plan_id,
                'financial_responsible_user_id' => $data['financial_responsible_user_id'] ?? $subscription->financial_responsible_user_id,
                'billing_metric' => $data['billing_metric'] ?? $subscription->billing_metric ?? OrganizationSubscription::METRIC_FIXED,
                'unit_price' => $data['unit_price'] ?? $subscription->unit_price ?? 0,
                'user_price' => $data['user_price'] ?? $subscription->user_price ?? 0,
                'fixed_price' => $data['fixed_price'] ?? $subscription->fixed_price ?? 0,
                'billing_cycle' => $data['billing_cycle'] ?? $subscription->billing_cycle ?? OrganizationSubscription::CYCLE_MONTHLY,
                'trial_days' => (int) ($data['trial_days'] ?? $subscription->trial_days ?? 0),
                'payment_method' => $data['payment_method'] ?? $subscription->payment_method ?? OrganizationSubscription::PAYMENT_BOLETO,
                'financial_cnpj' => $data['financial_cnpj'] ?? $subscription->financial_cnpj ?? $organization->document,
                'financial_contact_name' => $data['financial_contact_name'] ?? $subscription->financial_contact_name,
                'financial_contact_email' => $data['financial_contact_email'] ?? $subscription->financial_contact_email ?? $organization->email,
                'financial_contact_phone' => $data['financial_contact_phone'] ?? $subscription->financial_contact_phone ?? $organization->phone,
                'contract_starts_at' => $data['contract_starts_at'] ?? $subscription->contract_starts_at,
                'contract_ends_at' => $data['contract_ends_at'] ?? $subscription->contract_ends_at,
                'auto_renew' => array_key_exists('auto_renew', $data)
                    ? (bool) $data['auto_renew']
                    : (bool) $subscription->auto_renew,
                'max_condominiums' => $data['max_condominiums'] ?? $subscription->max_condominiums,
                'max_units' => $data['max_units'] ?? $subscription->max_units,
                'max_users' => $data['max_users'] ?? $subscription->max_users,
                'admin_notes' => $data['admin_notes'] ?? $subscription->admin_notes,
                'status' => $data['status'] ?? $subscription->status ?? OrganizationSubscription::STATUS_DRAFT,
                'created_by' => $subscription->created_by ?? $admin?->id,
            ]);

            $this->refreshCalculatedAmounts($subscription, $organization);
            $subscription->save();

            return $subscription->fresh(['plan', 'financialResponsible']);
        });
    }

    public function isAccessAllowed(?OrganizationSubscription $subscription): bool
    {
        if (!$subscription) {
            return true;
        }

        return $subscription->isAccessAllowed();
    }

    public function organizationAccessAllowed(Organization $organization): bool
    {
        if ($organization->status === Organization::STATUS_BLOCKED) {
            return false;
        }

        if ($organization->status === Organization::STATUS_SUSPENDED) {
            return false;
        }

        $subscriptions = $organization->subscriptions()->get();

        if ($subscriptions->isEmpty()) {
            return true;
        }

        return $subscriptions->contains(fn (OrganizationSubscription $subscription) => $this->isAccessAllowed($subscription));
    }

    public function activate(OrganizationSubscription $subscription, ?User $admin = null): OrganizationSubscription
    {
        return DB::transaction(function () use ($subscription, $admin) {
            $organization = $subscription->organization()->firstOrFail();
            $this->refreshCalculatedAmounts($subscription, $organization);

            if ($subscription->trial_days > 0 && !$subscription->trial_starts_at) {
                $subscription->trial_starts_at = now();
                $subscription->trial_ends_at = now()->addDays($subscription->trial_days);
                $subscription->status = OrganizationSubscription::STATUS_TRIAL;
            } else {
                $subscription->status = OrganizationSubscription::STATUS_ACTIVE;
            }

            if (!$subscription->contract_starts_at) {
                $subscription->contract_starts_at = today();
            }

            $subscription->activated_at = now();
            $subscription->suspended_at = null;
            $subscription->cancelled_at = null;
            $subscription->past_due_at = null;

            if ($subscription->usesAsaas()) {
                $this->syncAsaas($subscription, $organization);
            }

            $subscription->save();

            $organization->update(['status' => Organization::STATUS_ACTIVE]);

            return $subscription->fresh();
        });
    }

    public function suspend(OrganizationSubscription $subscription, ?User $admin = null, ?string $notes = null): OrganizationSubscription
    {
        if ($subscription->asaas_subscription_id && $subscription->usesAsaas() && $this->platformSettings->isAsaasConfigured()) {
            $this->asaas->cancelSubscription($subscription->asaas_subscription_id);
        }

        $subscription->update([
            'status' => OrganizationSubscription::STATUS_SUSPENDED,
            'suspended_at' => now(),
            'admin_notes' => $notes ? trim(($subscription->admin_notes ? $subscription->admin_notes."\n" : '').$notes) : $subscription->admin_notes,
        ]);

        $subscription->organization?->update(['status' => Organization::STATUS_SUSPENDED]);

        return $subscription->fresh();
    }

    public function cancel(OrganizationSubscription $subscription, ?User $admin = null, ?string $notes = null): OrganizationSubscription
    {
        if ($subscription->asaas_subscription_id && $subscription->usesAsaas() && $this->platformSettings->isAsaasConfigured()) {
            $this->asaas->cancelSubscription($subscription->asaas_subscription_id);
        }

        $subscription->update([
            'status' => OrganizationSubscription::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'asaas_subscription_id' => null,
            'admin_notes' => $notes ? trim(($subscription->admin_notes ? $subscription->admin_notes."\n" : '').$notes) : $subscription->admin_notes,
        ]);

        $subscription->organization?->update(['status' => Organization::STATUS_SUSPENDED]);

        return $subscription->fresh();
    }

    public function refreshCalculatedAmounts(OrganizationSubscription $subscription, Organization $organization): void
    {
        if ($subscription->billing_metric === OrganizationSubscription::METRIC_FIXED) {
            $subscription->billable_quantity = 1;
            $subscription->recurring_amount = round((float) $subscription->fixed_price, 2);

            return;
        }

        if ($subscription->billing_metric === OrganizationSubscription::METRIC_USER) {
            $quantity = max((int) $organization->users()->count(), 1);
            $subscription->billable_quantity = $quantity;
            $subscription->recurring_amount = round($quantity * (float) $subscription->user_price, 2);

            return;
        }

        $quantity = max((int) $organization->condominiums()->withCount('units')->get()->sum('units_count'), 1);
        $subscription->billable_quantity = $quantity;
        $subscription->recurring_amount = round($quantity * (float) $subscription->unit_price, 2);
    }

    /**
     * Sincroniza cliente e assinatura recorrente da administradora no Asaas da plataforma.
     */
    public function syncAsaas(OrganizationSubscription $subscription, Organization $organization): void
    {
        if (!$subscription->usesAsaas()) {
            return;
        }

        if (!$this->platformSettings->isAsaasConfigured()) {
            return;
        }

        $customerData = $this->asaasCustomerPayload($subscription, $organization);
        $missing = $this->missingAsaasCustomerFields($customerData);

        if ($missing !== []) {
            throw ValidationException::withMessages([
                'asaas' => 'Não foi possível criar o cliente no Asaas. Falta: '.implode('; ', $missing).'.',
            ]);
        }

        $customer = $this->asaas->createOrUpdateCustomer($customerData);

        if (!$customer) {
            throw ValidationException::withMessages([
                'asaas' => $this->asaasFailureMessage('Não foi possível criar o cliente da organização no Asaas.'),
            ]);
        }

        $subscription->asaas_customer_id = $customer['id'];

        if ($subscription->recurring_amount <= 0) {
            return;
        }

        if ($subscription->asaas_subscription_id) {
            $this->asaas->cancelSubscription($subscription->asaas_subscription_id);
            $subscription->asaas_subscription_id = null;
        }

        $nextDueDate = $subscription->trial_ends_at && $subscription->trial_ends_at->isFuture()
            ? $subscription->trial_ends_at->toDateString()
            : ($subscription->contract_starts_at?->toDateString() ?: now()->addDays(3)->toDateString());

        $asaasSubscription = $this->asaas->createSubscription([
            'customer' => $subscription->asaas_customer_id,
            'billingType' => $this->mapBillingType($subscription->payment_method),
            'value' => round((float) $subscription->recurring_amount, 2),
            'nextDueDate' => $nextDueDate,
            'cycle' => $this->mapCycle($subscription->billing_cycle),
            'description' => 'SindCON — '.$organization->displayName(),
            'externalReference' => 'organization_subscription:'.$subscription->id,
        ]);

        if (!$asaasSubscription) {
            throw ValidationException::withMessages([
                'asaas' => $this->asaasFailureMessage('Não foi possível criar a assinatura da organização no Asaas.'),
            ]);
        }

        $subscription->asaas_subscription_id = $asaasSubscription['id'] ?? null;
    }

    public function syncAsaasSubscription(OrganizationSubscription $subscription, ?User $admin = null): OrganizationSubscription
    {
        if (!$subscription->usesAsaas()) {
            throw ValidationException::withMessages([
                'payment_method' => 'Depósito bancário não utiliza assinatura automática no Asaas.',
            ]);
        }

        if (!$this->platformSettings->isAsaasConfigured()) {
            throw ValidationException::withMessages([
                'asaas' => 'Configure a API do Asaas nas configurações globais da plataforma.',
            ]);
        }

        $organization = $subscription->organization()->firstOrFail();
        $this->refreshCalculatedAmounts($subscription, $organization);
        $this->syncAsaas($subscription, $organization);
        $subscription->save();

        return $subscription->fresh();
    }

    /**
     * @param  array{value: float|int, due_date: string, billing_type?: string, description?: string}  $data
     * @return array<string, mixed>
     */
    public function createManualAsaasCharge(OrganizationSubscription $subscription, array $data): array
    {
        if (!$subscription->usesAsaas()) {
            throw ValidationException::withMessages([
                'charge' => 'Depósito bancário manual não gera cobrança no Asaas.',
            ]);
        }

        if (!$this->platformSettings->isAsaasConfigured()) {
            throw ValidationException::withMessages([
                'asaas' => 'Configure a API do Asaas na plataforma.',
            ]);
        }

        if (!$subscription->asaas_customer_id) {
            throw ValidationException::withMessages([
                'asaas' => 'Sincronize o cliente no Asaas antes de criar cobranças avulsas.',
            ]);
        }

        $organization = $subscription->organization()->firstOrFail();
        $billingType = strtoupper((string) ($data['billing_type'] ?? 'BOLETO'));

        $payment = $this->asaas->createPayment([
            'customer' => $subscription->asaas_customer_id,
            'billingType' => $billingType,
            'value' => round((float) $data['value'], 2),
            'dueDate' => $data['due_date'],
            'description' => $data['description'] ?? 'SindCON — '.$organization->displayName(),
            'externalReference' => 'organization_subscription:'.$subscription->id,
        ]);

        if (!$payment) {
            throw ValidationException::withMessages([
                'charge' => 'Não foi possível criar a cobrança no Asaas.',
            ]);
        }

        return $payment;
    }

    public function cancelAsaasPayment(OrganizationSubscription $subscription, string $paymentId): void
    {
        $payment = $this->assertPaymentBelongsToSubscription($subscription, $paymentId);
        $status = strtoupper((string) ($payment['status'] ?? ''));

        if (in_array($status, ['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH'], true)) {
            throw ValidationException::withMessages([
                'payment_id' => 'Cobranças já pagas devem ser estornadas (não canceladas).',
            ]);
        }

        if (!$this->asaas->deletePayment($paymentId)) {
            throw ValidationException::withMessages([
                'payment_id' => 'Não foi possível cancelar a cobrança no Asaas.',
            ]);
        }
    }

    public function refundAsaasPayment(OrganizationSubscription $subscription, string $paymentId): void
    {
        $payment = $this->assertPaymentBelongsToSubscription($subscription, $paymentId);
        $status = strtoupper((string) ($payment['status'] ?? ''));

        if (!in_array($status, ['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH'], true)) {
            throw ValidationException::withMessages([
                'payment_id' => 'Somente cobranças pagas podem ser estornadas.',
            ]);
        }

        if (!$this->asaas->refundPayment($paymentId)) {
            throw ValidationException::withMessages([
                'payment_id' => 'Não foi possível estornar a cobrança no Asaas.',
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function assertPaymentBelongsToSubscription(OrganizationSubscription $subscription, string $paymentId): array
    {
        if (!$this->platformSettings->isAsaasConfigured()) {
            throw ValidationException::withMessages([
                'asaas' => 'Asaas não configurado.',
            ]);
        }

        $payment = $this->asaas->getPayment($paymentId);
        if (!$payment) {
            throw ValidationException::withMessages([
                'payment_id' => 'Cobrança não encontrada no Asaas.',
            ]);
        }

        $customerId = $payment['customer'] ?? null;
        if ($subscription->asaas_customer_id && $customerId !== $subscription->asaas_customer_id) {
            throw ValidationException::withMessages([
                'payment_id' => 'Esta cobrança não pertence a esta organização.',
            ]);
        }

        return $payment;
    }

    /**
     * @return array<string, string>
     */
    protected function asaasCustomerPayload(OrganizationSubscription $subscription, Organization $organization): array
    {
        return array_filter([
            'name' => $subscription->financial_contact_name ?: $organization->displayName(),
            'email' => $subscription->financial_contact_email ?: $organization->email,
            'phone' => preg_replace('/\D/', '', (string) ($subscription->financial_contact_phone ?: $organization->phone ?: '')),
            'cpfCnpj' => preg_replace('/\D/', '', (string) ($subscription->financial_cnpj ?: $organization->document ?: '')),
            'externalReference' => 'organization:'.$organization->id,
            'company' => $organization->displayName(),
        ], fn ($value) => filled($value));
    }

    /**
     * @param  array<string, string>  $customerData
     * @return list<string>
     */
    protected function missingAsaasCustomerFields(array $customerData): array
    {
        $missing = [];

        if (!filled($customerData['name'] ?? null)) {
            $missing[] = 'nome da organização ou do contato financeiro';
        }

        if (!filled($customerData['email'] ?? null)) {
            $missing[] = 'e-mail financeiro ou e-mail da organização';
        }

        $document = (string) ($customerData['cpfCnpj'] ?? '');
        if (!in_array(strlen($document), [11, 14], true)) {
            $missing[] = 'CNPJ de faturamento ou documento da organização com 11 (CPF) ou 14 (CNPJ) dígitos'
                .($document !== '' ? ' (informado com '.strlen($document).' dígitos)' : '');
        }

        return $missing;
    }

    protected function asaasFailureMessage(string $fallback): string
    {
        $detail = $this->asaas->getLastErrorMessage();

        return $detail ? $fallback.' '.$detail : $fallback;
    }

    protected function mapBillingType(string $paymentMethod): string
    {
        return match ($paymentMethod) {
            OrganizationSubscription::PAYMENT_CREDIT_CARD => 'CREDIT_CARD',
            OrganizationSubscription::PAYMENT_PIX_RECURRING => 'PIX',
            default => 'BOLETO',
        };
    }

    protected function mapCycle(string $billingCycle): string
    {
        return match ($billingCycle) {
            OrganizationSubscription::CYCLE_QUARTERLY => 'QUARTERLY',
            OrganizationSubscription::CYCLE_SEMIANNUAL => 'SEMIANNUALLY',
            OrganizationSubscription::CYCLE_ANNUAL => 'YEARLY',
            default => 'MONTHLY',
        };
    }
}
