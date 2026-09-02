<?php

namespace App\Services;

use App\Models\CondominiumSubscription;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class SyndicSubscriptionPaymentService
{
    public function __construct(
        private PlatformAsaasService $asaas,
        private PlatformSettingsService $platformSettings,
        private SubscriptionBillingService $billing,
    ) {}

    public function assertCanManage(User $user, CondominiumSubscription $subscription, ?int $activeCondominiumId = null): void
    {
        abort_unless($user->isSindico() || $user->isAdmin(), 403);

        if ($user->isAdmin()) {
            return;
        }

        abort_unless(
            (int) $subscription->condominium_id === (int) $activeCondominiumId,
            403,
            'Você não pode gerenciar a assinatura de outro condomínio.'
        );
    }

    public function getPixCheckout(CondominiumSubscription $subscription, string $paymentId): array
    {
        $this->assertPaymentBelongsToSubscription($subscription, $paymentId);

        $pix = $this->asaas->getPixQRCode($paymentId);

        if (!$pix) {
            throw ValidationException::withMessages([
                'payment' => 'Não foi possível gerar o PIX para esta cobrança. Tente pelo link da fatura.',
            ]);
        }

        return [
            'payment_id' => $paymentId,
            'encoded_image' => $pix['encodedImage'] ?? null,
            'payload' => $pix['payload'] ?? null,
            'expiration_date' => $pix['expirationDate'] ?? null,
        ];
    }

    public function createEarlyPayment(CondominiumSubscription $subscription, ?string $billingType = null): array
    {
        $this->assertUsesAsaas($subscription);

        if ($subscription->recurring_amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Valor recorrente zerado. Contate a administração.',
            ]);
        }

        $billingType = $billingType ?: $this->mapPaymentMethodToAsaas($subscription->payment_method);

        $payment = $this->asaas->createPayment([
            'customer' => $subscription->asaas_customer_id,
            'billingType' => $billingType,
            'value' => (float) $subscription->recurring_amount,
            'dueDate' => now()->format('Y-m-d'),
            'description' => 'Antecipação — Assinatura SindCON',
            'externalReference' => 'subscription:' . $subscription->id,
        ]);

        if (!$payment) {
            throw ValidationException::withMessages([
                'asaas' => 'Não foi possível gerar a cobrança antecipada no Asaas.',
            ]);
        }

        $result = $this->billing->normalizeRawPayment($payment);

        if (strtoupper($billingType) === 'PIX') {
            $pix = $this->asaas->getPixQRCode($payment['id']);
            $result['pix'] = [
                'encoded_image' => $pix['encodedImage'] ?? null,
                'payload' => $pix['payload'] ?? null,
                'expiration_date' => $pix['expirationDate'] ?? null,
            ];
        }

        return $result;
    }

    public function updatePaymentMethod(CondominiumSubscription $subscription, string $method, array $cardPayload = []): CondominiumSubscription
    {
        $this->assertUsesAsaas($subscription);

        if (!$subscription->asaas_subscription_id) {
            throw ValidationException::withMessages([
                'asaas' => 'Assinatura ainda não sincronizada com o Asaas.',
            ]);
        }

        $asaasBillingType = $this->mapPaymentMethodToAsaas($method);
        $updateData = [
            'billingType' => $asaasBillingType,
            'updatePendingPayments' => true,
        ];

        if ($method === CondominiumSubscription::PAYMENT_CREDIT_CARD) {
            $tokenResponse = $this->asaas->tokenizeCreditCard(
                $subscription->asaas_customer_id,
                [
                    'holderName' => $cardPayload['holder_name'],
                    'number' => preg_replace('/\D/', '', $cardPayload['number']),
                    'expiryMonth' => $cardPayload['expiry_month'],
                    'expiryYear' => $cardPayload['expiry_year'],
                    'ccv' => $cardPayload['ccv'],
                ],
                [
                    'name' => $cardPayload['holder_name'],
                    'email' => $cardPayload['email'],
                    'cpfCnpj' => preg_replace('/\D/', '', $cardPayload['cpf_cnpj']),
                    'postalCode' => preg_replace('/\D/', '', $cardPayload['postal_code']),
                    'addressNumber' => $cardPayload['address_number'],
                    'phone' => preg_replace('/\D/', '', $cardPayload['phone']),
                ]
            );

            if (!$tokenResponse || empty($tokenResponse['creditCardToken'])) {
                throw ValidationException::withMessages([
                    'card' => 'Não foi possível validar o cartão no Asaas. Verifique os dados informados.',
                ]);
            }

            $updateData['creditCardToken'] = $tokenResponse['creditCardToken'];
            $updateData['creditCardHolderInfo'] = [
                'name' => $cardPayload['holder_name'],
                'email' => $cardPayload['email'],
                'cpfCnpj' => preg_replace('/\D/', '', $cardPayload['cpf_cnpj']),
                'postalCode' => preg_replace('/\D/', '', $cardPayload['postal_code']),
                'addressNumber' => $cardPayload['address_number'],
                'phone' => preg_replace('/\D/', '', $cardPayload['phone']),
            ];
        }

        $updated = $this->asaas->updateSubscription($subscription->asaas_subscription_id, $updateData);

        if (!$updated) {
            throw ValidationException::withMessages([
                'asaas' => 'Não foi possível atualizar a forma de pagamento no Asaas.',
            ]);
        }

        $subscription->update(['payment_method' => $method]);

        return $subscription->fresh();
    }

    public function getAsaasSubscriptionSummary(CondominiumSubscription $subscription): ?array
    {
        if (!$subscription->asaas_subscription_id) {
            return null;
        }

        $data = $this->asaas->getSubscription($subscription->asaas_subscription_id);

        if (!$data) {
            return null;
        }

        return [
            'status' => $data['status'] ?? null,
            'billing_type' => $data['billingType'] ?? null,
            'next_due_date' => $data['nextDueDate'] ?? null,
            'credit_card_brand' => $data['creditCard']['creditCardBrand'] ?? null,
            'credit_card_number' => $data['creditCard']['creditCardNumber'] ?? null,
        ];
    }

    protected function assertUsesAsaas(CondominiumSubscription $subscription): void
    {
        if (!$subscription->usesAsaas()) {
            throw ValidationException::withMessages([
                'payment_method' => 'Este contrato utiliza depósito bancário manual.',
            ]);
        }

        if (!$this->platformSettings->isAsaasConfigured()) {
            throw ValidationException::withMessages([
                'asaas' => 'Integração Asaas indisponível no momento.',
            ]);
        }

        if (!$subscription->asaas_customer_id) {
            throw ValidationException::withMessages([
                'asaas' => 'Cliente Asaas não configurado. Solicite sincronização à administração.',
            ]);
        }
    }

    protected function assertPaymentBelongsToSubscription(CondominiumSubscription $subscription, string $paymentId): void
    {
        $this->assertUsesAsaas($subscription);

        $payment = $this->asaas->getPayment($paymentId);

        if (!$payment || ($payment['customer'] ?? null) !== $subscription->asaas_customer_id) {
            throw ValidationException::withMessages([
                'payment' => 'Cobrança não encontrada para este contrato.',
            ]);
        }
    }

    protected function mapPaymentMethodToAsaas(string $method): string
    {
        return match ($method) {
            CondominiumSubscription::PAYMENT_CREDIT_CARD => 'CREDIT_CARD',
            CondominiumSubscription::PAYMENT_PIX_RECURRING => 'PIX',
            default => 'BOLETO',
        };
    }
}
