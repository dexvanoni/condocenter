<?php

namespace App\Services;

use App\Jobs\SendSubscriptionBillingNotification;
use App\Models\Condominium;
use App\Models\CondominiumSubscription;
use App\Models\CondominiumSubscriptionDocument;
use App\Models\CondominiumSubscriptionLog;
use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CondominiumSubscriptionService
{
    public function __construct(
        private PlatformAsaasService $asaas,
        private PlatformSettingsService $platformSettings,
    ) {}

    public function upsert(Condominium $condominium, array $data, User $admin): CondominiumSubscription
    {
        return DB::transaction(function () use ($condominium, $data, $admin) {
            $subscription = $condominium->subscription ?? new CondominiumSubscription([
                'condominium_id' => $condominium->id,
                'created_by' => $admin->id,
                'status' => CondominiumSubscription::STATUS_DRAFT,
            ]);

            $plan = null;
            if (!empty($data['subscription_plan_id'])) {
                $plan = SubscriptionPlan::query()->find($data['subscription_plan_id']);
                if ($plan) {
                    $subscription->subscription_plan_id = $plan->id;
                    $data = array_merge($plan->toContractDefaults(), $data);
                }
            }

            $subscription->fill([
                'subscription_plan_id' => $data['subscription_plan_id'] ?? $subscription->subscription_plan_id,
                'financial_responsible_user_id' => $data['financial_responsible_user_id'] ?? null,
                'billing_metric' => $data['billing_metric'],
                'unit_price' => $data['unit_price'] ?? 0,
                'user_price' => $data['user_price'] ?? 0,
                'fixed_price' => $data['fixed_price'] ?? 0,
                'billing_cycle' => $data['billing_cycle'],
                'trial_days' => (int) ($data['trial_days'] ?? 0),
                'payment_method' => $data['payment_method'],
                'financial_cnpj' => $data['financial_cnpj'] ?? $condominium->cnpj,
                'financial_contact_name' => $data['financial_contact_name'] ?? null,
                'financial_contact_email' => $data['financial_contact_email'] ?? null,
                'financial_contact_phone' => $data['financial_contact_phone'] ?? null,
                'contract_starts_at' => $data['contract_starts_at'] ?? null,
                'contract_ends_at' => $data['contract_ends_at'] ?? null,
                'admin_notes' => $data['admin_notes'] ?? null,
            ]);

            $this->refreshCalculatedAmounts($subscription, $condominium);
            $subscription->save();
            $this->applyUnitsLimit($condominium, $data, $plan ?? null);

            $this->log($subscription, $admin, 'updated', 'Contrato atualizado pelo administrador.');

            return $subscription->fresh(['financialResponsible', 'documents', 'logs', 'plan']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function applyUnitsLimit(Condominium $condominium, array $data, ?SubscriptionPlan $plan = null): void
    {
        $hasExplicitLimit = array_key_exists('units_limit', $data);
        $limit = $hasExplicitLimit ? $data['units_limit'] : null;

        if (!$hasExplicitLimit && $plan?->max_units) {
            $limit = $plan->max_units;
            $hasExplicitLimit = true;
        }

        if (!$hasExplicitLimit) {
            return;
        }

        $limit = $limit !== null && $limit !== '' ? (int) $limit : null;
        $inUse = $condominium->units()->count();

        if ($limit !== null && $limit < $inUse) {
            throw ValidationException::withMessages([
                'units_limit' => "O limite não pode ser menor que o número de unidades já cadastradas ({$inUse}).",
            ]);
        }

        $condominium->update(['units_limit' => $limit]);
    }

    public function billingPortalData(CondominiumSubscription $subscription): array
    {
        $payments = [];

        if ($subscription->asaas_subscription_id && $this->platformSettings->isAsaasConfigured()) {
            $payments = $this->asaas->listSubscriptionPayments($subscription->asaas_subscription_id, 6);
        }

        return [
            'payments' => collect($payments)->map(function (array $payment) {
                return [
                    'id' => $payment['id'] ?? null,
                    'status' => $payment['status'] ?? null,
                    'value' => $payment['value'] ?? 0,
                    'due_date' => $payment['dueDate'] ?? null,
                    'invoice_url' => $payment['invoiceUrl'] ?? null,
                    'bank_slip_url' => $payment['bankSlipUrl'] ?? null,
                    'billing_type' => $payment['billingType'] ?? null,
                ];
            })->values()->all(),
        ];
    }

    public function activate(CondominiumSubscription $subscription, User $admin): CondominiumSubscription
    {
        return DB::transaction(function () use ($subscription, $admin) {
            $condominium = $subscription->condominium()->firstOrFail();
            $this->refreshCalculatedAmounts($subscription, $condominium);

            if ($subscription->trial_days > 0 && !$subscription->trial_starts_at) {
                $subscription->trial_starts_at = now();
                $subscription->trial_ends_at = now()->addDays($subscription->trial_days);
                $subscription->status = CondominiumSubscription::STATUS_TRIAL;
            } else {
                $subscription->status = CondominiumSubscription::STATUS_ACTIVE;
            }

            if (!$subscription->contract_starts_at) {
                $subscription->contract_starts_at = today();
            }

            $subscription->activated_at = now();
            $subscription->suspended_at = null;
            $subscription->cancelled_at = null;
            $subscription->past_due_at = null;

            if ($subscription->usesAsaas()) {
                $this->syncAsaas($subscription, $condominium);
            }

            $subscription->save();
            $condominium->update(['is_active' => true]);

            $this->log($subscription, $admin, 'activated', 'Assinatura ativada.');

            return $subscription->fresh();
        });
    }

    public function suspend(CondominiumSubscription $subscription, User $admin, ?string $notes = null): CondominiumSubscription
    {
        if ($subscription->asaas_subscription_id && $subscription->usesAsaas()) {
            $this->asaas->cancelSubscription($subscription->asaas_subscription_id);
        }

        $subscription->update([
            'status' => CondominiumSubscription::STATUS_SUSPENDED,
            'suspended_at' => now(),
        ]);

        $subscription->condominium?->update(['is_active' => false]);
        $this->log($subscription, $admin, 'suspended', $notes ?: 'Assinatura suspensa.');

        return $subscription->fresh();
    }

    public function reactivate(CondominiumSubscription $subscription, User $admin): CondominiumSubscription
    {
        return $this->activate($subscription, $admin);
    }

    public function cancelAsaasPayment(CondominiumSubscription $subscription, User $admin, string $paymentId): void
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

        $this->log(
            $subscription,
            $admin,
            'payment_cancelled',
            "Cobrança {$paymentId} cancelada no Asaas.",
            ['payment_id' => $paymentId]
        );
    }

    public function refundAsaasPayment(CondominiumSubscription $subscription, User $admin, string $paymentId): void
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

        $this->log(
            $subscription,
            $admin,
            'payment_refunded',
            "Estorno solicitado para cobrança {$paymentId}.",
            ['payment_id' => $paymentId]
        );
    }

    /**
     * @param  array{value: float|int, due_date: string, billing_type?: string, description?: string}  $data
     */
    public function createManualAsaasCharge(CondominiumSubscription $subscription, User $admin, array $data): array
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

        $condominium = $subscription->condominium()->firstOrFail();
        $billingType = strtoupper((string) ($data['billing_type'] ?? 'BOLETO'));

        $payment = $this->asaas->createPayment([
            'customer' => $subscription->asaas_customer_id,
            'billingType' => $billingType,
            'value' => round((float) $data['value'], 2),
            'dueDate' => $data['due_date'],
            'description' => $data['description'] ?? "SindCON — {$condominium->name}",
            'externalReference' => 'subscription:' . $subscription->id,
        ]);

        if (!$payment) {
            throw ValidationException::withMessages([
                'charge' => 'Não foi possível criar a cobrança no Asaas.',
            ]);
        }

        $this->log(
            $subscription,
            $admin,
            'payment_created',
            'Cobrança avulsa criada no Asaas.',
            ['payment_id' => $payment['id'] ?? null]
        );

        return $payment;
    }

    public function resetForNewContract(CondominiumSubscription $subscription, User $admin, ?string $notes = null): CondominiumSubscription
    {
        if ($subscription->asaas_subscription_id && $subscription->usesAsaas()) {
            $this->asaas->cancelSubscription($subscription->asaas_subscription_id);
        }

        $subscription->update([
            'status' => CondominiumSubscription::STATUS_DRAFT,
            'asaas_subscription_id' => null,
            'activated_at' => null,
            'suspended_at' => null,
            'cancelled_at' => null,
            'past_due_at' => null,
            'trial_starts_at' => null,
            'trial_ends_at' => null,
        ]);

        $this->log(
            $subscription,
            $admin,
            'contract_reset',
            $notes ?: 'Contrato reiniciado em rascunho para novo ciclo.'
        );

        return $subscription->fresh();
    }

    public function cancel(CondominiumSubscription $subscription, User $admin, ?string $notes = null): CondominiumSubscription
    {
        if ($subscription->asaas_subscription_id && $subscription->usesAsaas()) {
            $this->asaas->cancelSubscription($subscription->asaas_subscription_id);
        }

        $subscription->update([
            'status' => CondominiumSubscription::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'asaas_subscription_id' => null,
        ]);

        $subscription->condominium?->update(['is_active' => false]);
        $this->log($subscription, $admin, 'cancelled', $notes ?: 'Assinatura cancelada.');

        return $subscription->fresh();
    }

    public function extend(CondominiumSubscription $subscription, User $admin, Carbon $newEndDate, ?string $notes = null): CondominiumSubscription
    {
        $subscription->update([
            'extended_until' => $newEndDate,
            'contract_ends_at' => $newEndDate,
            'status' => $subscription->status === CondominiumSubscription::STATUS_EXPIRED
                ? CondominiumSubscription::STATUS_ACTIVE
                : $subscription->status,
        ]);

        $subscription->condominium?->update(['is_active' => true]);
        $this->log($subscription, $admin, 'extended', $notes ?: "Contrato prorrogado até {$newEndDate->format('d/m/Y')}.");

        return $subscription->fresh();
    }

    public function syncAsaasSubscription(CondominiumSubscription $subscription, User $admin): CondominiumSubscription
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

        $condominium = $subscription->condominium()->firstOrFail();
        $this->refreshCalculatedAmounts($subscription, $condominium);
        $this->syncAsaas($subscription, $condominium);
        $subscription->save();

        $this->log($subscription, $admin, 'asaas_sync', 'Assinatura sincronizada com o Asaas.');

        return $subscription->fresh();
    }

    public function storeDocument(
        CondominiumSubscription $subscription,
        User $admin,
        UploadedFile $file,
        ?string $title = null
    ): CondominiumSubscriptionDocument {
        $path = $file->store("platform/subscriptions/{$subscription->id}", 'public');

        $document = $subscription->documents()->create([
            'uploaded_by' => $admin->id,
            'title' => $title ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);

        $this->log($subscription, $admin, 'document_uploaded', "Documento enviado: {$document->title}");

        return $document;
    }

    public function deleteDocument(CondominiumSubscriptionDocument $document, User $admin): void
    {
        Storage::disk('public')->delete($document->file_path);
        $subscription = $document->subscription;
        $title = $document->title;
        $document->delete();

        if ($subscription) {
            $this->log($subscription, $admin, 'document_deleted', "Documento removido: {$title}");
        }
    }

    public function refreshCalculatedAmounts(CondominiumSubscription $subscription, Condominium $condominium): void
    {
        if ($subscription->billing_metric === CondominiumSubscription::METRIC_FIXED) {
            $subscription->billable_quantity = 1;
            $subscription->recurring_amount = round((float) $subscription->fixed_price, 2);

            return;
        }

        $quantity = $this->resolveBillableQuantity($subscription, $condominium);
        $unitPrice = (float) ($subscription->billing_metric === CondominiumSubscription::METRIC_USER
            ? $subscription->user_price
            : $subscription->unit_price);

        $subscription->billable_quantity = $quantity;
        $subscription->recurring_amount = round($quantity * $unitPrice, 2);
    }

    public function resolveBillableQuantity(CondominiumSubscription $subscription, Condominium $condominium): int
    {
        if ($subscription->billing_metric === CondominiumSubscription::METRIC_FIXED) {
            return 1;
        }

        if ($subscription->billing_metric === CondominiumSubscription::METRIC_USER) {
            return max($condominium->users()
                ->where(function ($query) {
                    $query->where('registration_status', 'approved')
                        ->orWhereNull('registration_status');
                })
                ->where('is_active', true)
                ->count(), 1);
        }

        return max($condominium->units()->count(), 1);
    }

    public function handlePlatformWebhook(array $payload): bool
    {
        if (($payload['event'] ?? null) === 'WEBHOOK_TEST') {
            return true;
        }

        $event = $payload['event'] ?? null;
        $payment = $payload['payment'] ?? null;
        $subscriptionPayload = $payload['subscription'] ?? null;

        if ($subscriptionPayload) {
            $asaasId = $subscriptionPayload['id'] ?? null;

            $subscription = CondominiumSubscription::query()
                ->where('asaas_subscription_id', $asaasId)
                ->first();

            if ($subscription) {
                $status = strtoupper($subscriptionPayload['status'] ?? '');
                $updates = match ($status) {
                    'ACTIVE' => ['status' => CondominiumSubscription::STATUS_ACTIVE],
                    'EXPIRED' => ['status' => CondominiumSubscription::STATUS_EXPIRED],
                    default => [],
                };

                if ($updates !== []) {
                    $subscription->update($updates);
                }

                return true;
            }

            $orgSubscription = OrganizationSubscription::query()
                ->where('asaas_subscription_id', $asaasId)
                ->first();

            if (!$orgSubscription) {
                return false;
            }

            $status = strtoupper($subscriptionPayload['status'] ?? '');
            $updates = match ($status) {
                'ACTIVE' => ['status' => OrganizationSubscription::STATUS_ACTIVE],
                'EXPIRED' => ['status' => OrganizationSubscription::STATUS_EXPIRED],
                default => [],
            };

            if ($updates !== []) {
                $orgSubscription->update($updates);
            }

            return true;
        }

        if (!$event || !$payment) {
            return false;
        }

        $customerId = $payment['customer'] ?? null;

        $subscription = CondominiumSubscription::query()
            ->where('asaas_customer_id', $customerId)
            ->first();

        if ($subscription) {
            switch ($event) {
                case 'PAYMENT_CREATED':
                    SendSubscriptionBillingNotification::dispatchSync($subscription, $event, $payment);
                    break;
                case 'PAYMENT_CONFIRMED':
                case 'PAYMENT_RECEIVED':
                    $subscription->update([
                        'status' => CondominiumSubscription::STATUS_ACTIVE,
                        'past_due_at' => null,
                    ]);
                    $subscription->condominium?->update(['is_active' => true]);
                    SendSubscriptionBillingNotification::dispatchSync($subscription, $event, $payment);
                    break;
                case 'PAYMENT_OVERDUE':
                    $subscription->update([
                        'status' => CondominiumSubscription::STATUS_PAST_DUE,
                        'past_due_at' => $subscription->past_due_at ?? now(),
                    ]);
                    SendSubscriptionBillingNotification::dispatchSync($subscription, $event, $payment);
                    break;
            }

            return true;
        }

        $orgSubscription = OrganizationSubscription::query()
            ->where('asaas_customer_id', $customerId)
            ->first();

        if (!$orgSubscription) {
            return false;
        }

        switch ($event) {
            case 'PAYMENT_CONFIRMED':
            case 'PAYMENT_RECEIVED':
                $orgSubscription->update([
                    'status' => OrganizationSubscription::STATUS_ACTIVE,
                    'past_due_at' => null,
                ]);
                $orgSubscription->organization?->update(['status' => Organization::STATUS_ACTIVE]);
                break;
            case 'PAYMENT_OVERDUE':
                $orgSubscription->update([
                    'status' => OrganizationSubscription::STATUS_PAST_DUE,
                    'past_due_at' => $orgSubscription->past_due_at ?? now(),
                ]);
                break;
        }

        return true;
    }

    protected function syncAsaas(CondominiumSubscription $subscription, Condominium $condominium): void
    {
        $responsible = $subscription->financialResponsible;
        $customerData = [
            'name' => $subscription->financial_contact_name ?: $responsible?->name ?: $condominium->name,
            'email' => $subscription->financial_contact_email ?: $responsible?->email ?: $condominium->email,
            'phone' => $subscription->financial_contact_phone ?: $responsible?->phone ?: $condominium->phone,
            'cpfCnpj' => preg_replace('/\D/', '', $subscription->financial_cnpj ?: $condominium->cnpj ?: ''),
            'externalReference' => 'condominium:' . $condominium->id,
            'company' => $condominium->name,
        ];

        $customer = $this->asaas->createOrUpdateCustomer(array_filter($customerData));

        if (!$customer) {
            throw ValidationException::withMessages([
                'asaas' => 'Não foi possível criar o cliente no Asaas. Verifique CNPJ e dados de contato.',
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
            'customer' => $customer['id'],
            'billingType' => $this->mapPaymentMethodToAsaas($subscription->payment_method),
            'value' => (float) $subscription->recurring_amount,
            'nextDueDate' => $nextDueDate,
            'cycle' => $this->mapBillingCycleToAsaas($subscription->billing_cycle),
            'description' => "Assinatura SindCON — {$condominium->name}",
            'externalReference' => 'subscription:' . $subscription->id,
        ]);

        if (!$asaasSubscription) {
            throw ValidationException::withMessages([
                'asaas' => 'Não foi possível criar a assinatura no Asaas.',
            ]);
        }

        $subscription->asaas_subscription_id = $asaasSubscription['id'];
    }

    protected function mapBillingCycleToAsaas(string $cycle): string
    {
        return match ($cycle) {
            CondominiumSubscription::CYCLE_QUARTERLY => 'QUARTERLY',
            CondominiumSubscription::CYCLE_SEMIANNUAL => 'SEMIANNUALLY',
            CondominiumSubscription::CYCLE_ANNUAL => 'YEARLY',
            default => 'MONTHLY',
        };
    }

    protected function mapPaymentMethodToAsaas(string $method): string
    {
        return match ($method) {
            CondominiumSubscription::PAYMENT_CREDIT_CARD => 'CREDIT_CARD',
            CondominiumSubscription::PAYMENT_PIX_RECURRING => 'PIX',
            default => 'BOLETO',
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function assertPaymentBelongsToSubscription(CondominiumSubscription $subscription, string $paymentId): array
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
                'payment_id' => 'Esta cobrança não pertence a este condomínio.',
            ]);
        }

        return $payment;
    }

    protected function log(
        CondominiumSubscription $subscription,
        ?User $admin,
        string $action,
        ?string $notes = null,
        array $metadata = []
    ): void {
        CondominiumSubscriptionLog::create([
            'condominium_subscription_id' => $subscription->id,
            'user_id' => $admin?->id,
            'action' => $action,
            'notes' => $notes,
            'metadata' => $metadata,
        ]);
    }
}
