<?php

namespace App\Services;

use App\Models\Charge;
use App\Models\Condominium;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ChargePaymentService
{
    public function __construct(
        private AsaasService $asaasService,
        private CondominiumAsaasSettingsService $condominiumSettings,
        private ChargeSettlementService $settlementService,
    ) {}

    public function assertCanPay(User $user, Charge $charge): void
    {
        if (!in_array($charge->status, ['pending', 'overdue'], true)) {
            throw ValidationException::withMessages([
                'charge' => 'Esta cobrança não está disponível para pagamento.',
            ]);
        }

        if (!$user->unit_id || (int) $charge->unit_id !== (int) $user->unit_id) {
            abort(403, 'Você só pode pagar cobranças da sua unidade.');
        }

        $condominium = $charge->condominium ?? Condominium::query()->find($charge->condominium_id);

        if (!$condominium || !$this->condominiumSettings->acceptsOnlinePayments($condominium)) {
            throw ValidationException::withMessages([
                'payment' => 'O condomínio ainda não habilitou pagamentos online.',
            ]);
        }
    }

    public function getCheckout(User $user, Charge $charge, string $billingType = 'PIX'): array
    {
        $this->assertCanPay($user, $charge);

        $charge->loadMissing(['condominium', 'unit']);
        $asaas = $this->asaasForCharge($charge);

        if ($charge->asaas_payment_id) {
            $existing = $asaas->getPayment($charge->asaas_payment_id);

            if ($existing && in_array($existing['status'] ?? '', ['PENDING', 'OVERDUE'], true)) {
                $checkout = $this->formatCheckoutResponse($charge, $existing, $asaas);

                if ($this->checkoutNeedsPixPayment($checkout, $existing)) {
                    return $this->createCheckoutPayment($user, $charge, 'PIX', replaceExisting: true);
                }

                return $checkout;
            }

            if ($existing && in_array($existing['status'] ?? '', ['CONFIRMED', 'RECEIVED'], true)) {
                $this->syncPaidCharge($charge, $existing);

                throw ValidationException::withMessages([
                    'charge' => 'Esta cobrança já foi paga.',
                ]);
            }
        }

        return $this->createCheckoutPayment($user, $charge, $billingType);
    }

    public function payWithCreditCard(User $user, Charge $charge, array $cardPayload): array
    {
        $this->assertCanPay($user, $charge);

        $charge->loadMissing(['condominium', 'unit']);
        $asaas = $this->asaasForCharge($charge);
        $customer = $this->resolveAsaasCustomer($user, $charge, $asaas);

        $this->assertCanReplacePendingPayment($charge, $asaas, 'card');

        $tokenResponse = $asaas->tokenizeCreditCard(
            $customer['id'],
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
                'card' => 'Não foi possível validar o cartão. Verifique os dados informados.',
            ]);
        }

        $payment = $asaas->createPayment($this->buildPaymentPayload(
            $charge,
            $customer['id'],
            'CREDIT_CARD',
            [
                'creditCardToken' => $tokenResponse['creditCardToken'],
                'creditCardHolderInfo' => [
                    'name' => $cardPayload['holder_name'],
                    'email' => $cardPayload['email'],
                    'cpfCnpj' => preg_replace('/\D/', '', $cardPayload['cpf_cnpj']),
                    'postalCode' => preg_replace('/\D/', '', $cardPayload['postal_code']),
                    'addressNumber' => $cardPayload['address_number'],
                    'phone' => preg_replace('/\D/', '', $cardPayload['phone']),
                ],
            ]
        ));

        if (!$payment) {
            throw ValidationException::withMessages([
                'card' => 'Não foi possível processar o pagamento no cartão.',
            ]);
        }

        $charge->update([
            'asaas_payment_id' => $payment['id'],
            'boleto_url' => $payment['bankSlipUrl'] ?? null,
        ]);

        $paymentStatus = $payment['status'] ?? null;

        if (in_array($paymentStatus, ['CONFIRMED', 'RECEIVED'], true)) {
            $this->syncPaidCharge($charge->fresh(), $payment);
        }

        return [
            'payment_id' => $payment['id'],
            'status' => $paymentStatus,
            'charge_status' => $charge->fresh()->status,
            'message' => in_array($paymentStatus, ['CONFIRMED', 'RECEIVED'], true)
                ? 'Pagamento confirmado com sucesso!'
                : 'Pagamento em processamento. Você será notificado quando for confirmado.',
        ];
    }

    public function refreshStatus(Charge $charge): array
    {
        if (!$charge->asaas_payment_id) {
            return [
                'status' => $charge->status,
                'asaas_status' => null,
            ];
        }

        $asaas = $this->asaasForCharge($charge);
        $payment = $asaas->getPayment($charge->asaas_payment_id);

        if (!$payment) {
            return [
                'status' => $charge->status,
                'asaas_status' => null,
            ];
        }

        if (in_array($payment['status'] ?? '', ['CONFIRMED', 'RECEIVED'], true) && $charge->status !== 'paid') {
            $this->syncPaidCharge($charge, $payment);
        }

        return [
            'status' => $charge->fresh()->status,
            'asaas_status' => $payment['status'] ?? null,
        ];
    }

    protected function createCheckoutPayment(User $user, Charge $charge, string $billingType, bool $replaceExisting = false): array
    {
        $asaas = $this->asaasForCharge($charge);

        if ($charge->asaas_payment_id) {
            $this->assertCanReplacePendingPayment($charge, $asaas);
        }

        $customer = $this->resolveAsaasCustomer($user, $charge, $asaas);
        $normalizedBillingType = $this->normalizeBillingType($billingType);

        $payment = $asaas->createPayment($this->buildPaymentPayload(
            $charge,
            $customer['id'],
            $normalizedBillingType
        ));

        if (!$payment) {
            throw ValidationException::withMessages([
                'payment' => 'Não foi possível gerar o pagamento. Tente novamente em instantes.',
            ]);
        }

        $charge->update([
            'asaas_payment_id' => $payment['id'],
            'boleto_url' => $payment['bankSlipUrl'] ?? null,
            'pix_code' => null,
            'pix_qrcode' => null,
        ]);

        if ($replaceExisting) {
            Log::info('Pagamento Asaas substituído para gerar PIX inline', [
                'charge_id' => $charge->id,
                'payment_id' => $payment['id'],
                'billing_type' => $normalizedBillingType,
            ]);
        }

        return $this->formatCheckoutResponse($charge->fresh(), $payment, $asaas);
    }

    protected function checkoutNeedsPixPayment(array $checkout, array $payment): bool
    {
        if (!empty($checkout['pix_qrcode']) || !empty($checkout['pix_code'])) {
            return false;
        }

        return strtoupper((string) ($payment['billingType'] ?? '')) !== 'PIX';
    }

    protected function formatCheckoutResponse(Charge $charge, array $payment, AsaasService $asaas): array
    {
        $pixData = $asaas->getPixQRCode($payment['id']);

        if ($pixData) {
            $charge->update([
                'pix_code' => $pixData['payload'] ?? null,
                'pix_qrcode' => $this->normalizePixImage($pixData['encodedImage'] ?? null),
            ]);
        }

        return [
            'charge_id' => $charge->id,
            'payment_id' => $payment['id'],
            'title' => $charge->title,
            'amount' => (float) $charge->amount,
            'due_date' => $charge->due_date?->format('Y-m-d'),
            'status' => $charge->status,
            'asaas_status' => $payment['status'] ?? null,
            'billing_type' => $payment['billingType'] ?? null,
            'pix_code' => $pixData['payload'] ?? $charge->pix_code,
            'pix_qrcode' => $this->normalizePixImage($pixData['encodedImage'] ?? $charge->pix_qrcode),
            'invoice_url' => $payment['invoiceUrl'] ?? null,
            'boleto_url' => $payment['bankSlipUrl'] ?? $charge->boleto_url,
        ];
    }

    protected function normalizePixImage(?string $encodedImage): ?string
    {
        if (!$encodedImage) {
            return null;
        }

        if (str_starts_with($encodedImage, 'data:image')) {
            return preg_replace('#^data:image/[^;]+;base64,#', '', $encodedImage) ?: null;
        }

        return $encodedImage;
    }

    protected function buildPaymentPayload(Charge $charge, string $customerId, string $billingType, array $extra = []): array
    {
        $payload = array_merge([
            'customer' => $customerId,
            'billingType' => $billingType,
            'dueDate' => max($charge->due_date?->format('Y-m-d') ?? now()->format('Y-m-d'), now()->format('Y-m-d')),
            'value' => (float) $charge->amount,
            'description' => $charge->title,
            'externalReference' => 'CHARGE-' . $charge->id,
        ], $extra);

        if ($charge->fine_percentage > 0) {
            $payload['fine'] = ['value' => (float) $charge->fine_percentage];
        }

        if ($charge->interest_rate > 0) {
            $payload['interest'] = ['value' => (float) $charge->interest_rate];
        }

        return $payload;
    }

    protected function resolveAsaasCustomer(User $user, Charge $charge, AsaasService $asaas): array
    {
        if (!$user->cpf) {
            throw ValidationException::withMessages([
                'cpf' => 'Cadastre seu CPF no perfil antes de pagar online.',
            ]);
        }

        $condominium = $charge->condominium;
        $customerData = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?: $user->telefone_celular,
            'mobilePhone' => $user->telefone_celular ?: $user->phone,
            'cpfCnpj' => preg_replace('/\D/', '', $user->cpf),
            'postalCode' => preg_replace('/\D/', '', $condominium->zip_code ?? ''),
            'address' => $condominium->address,
            'addressNumber' => 'S/N',
            'province' => $condominium->city,
            'externalReference' => 'USER-' . $user->id,
        ];

        $customer = $asaas->createOrUpdateCustomer($customerData);

        if (!$customer) {
            throw ValidationException::withMessages([
                'payment' => 'Não foi possível registrar seus dados no gateway de pagamento.',
            ]);
        }

        return $customer;
    }

    protected function syncPaidCharge(Charge $charge, array $payment): void
    {
        if ($charge->status === 'paid') {
            return;
        }

        try {
            $this->settlementService->markAsPaid(
                $charge->fresh(),
                Carbon::parse($payment['paymentDate'] ?? $payment['clientPaymentDate'] ?? now()),
                $this->mapAsaasPaymentMethod($payment['billingType'] ?? null),
                'Pagamento confirmado via Asaas.'
            );
        } catch (\Throwable $e) {
            Log::error('Erro ao sincronizar pagamento da cobrança: ' . $e->getMessage(), [
                'charge_id' => $charge->id,
                'payment_id' => $payment['id'] ?? null,
            ]);
        }
    }

    protected function asaasForCharge(Charge $charge): AsaasService
    {
        return $this->asaasService->forCondominium((int) $charge->condominium_id);
    }

    protected function normalizeBillingType(string $billingType): string
    {
        return match (strtoupper($billingType)) {
            'CREDIT_CARD' => 'CREDIT_CARD',
            'BOLETO' => 'BOLETO',
            'UNDEFINED' => 'UNDEFINED',
            default => 'PIX',
        };
    }

    protected function mapAsaasPaymentMethod(?string $billingType): string
    {
        return match (strtoupper((string) $billingType)) {
            'BOLETO' => 'boleto',
            'CREDIT_CARD' => 'credit_card',
            'PIX' => 'pix',
            'DEBIT_CARD' => 'debit_card',
            default => 'other',
        };
    }

    /**
     * Cancela cobrança Asaas pendente/vencida vinculada à charge local antes de criar outra.
     */
    protected function assertCanReplacePendingPayment(Charge $charge, AsaasService $asaas, string $errorKey = 'payment'): void
    {
        $paymentId = $charge->asaas_payment_id;

        if (!$paymentId) {
            return;
        }

        $existing = $asaas->getPayment($paymentId);

        if (!$existing) {
            return;
        }

        $status = strtoupper((string) ($existing['status'] ?? ''));

        if (!in_array($status, ['PENDING', 'OVERDUE'], true)) {
            return;
        }

        if (!$asaas->deletePayment($paymentId)) {
            throw ValidationException::withMessages([
                $errorKey => 'Não foi possível atualizar o pagamento existente. Tente novamente em instantes.',
            ]);
        }

        $charge->update([
            'asaas_payment_id' => null,
            'pix_code' => null,
            'pix_qrcode' => null,
            'boleto_url' => null,
        ]);

        Log::info('Pagamento Asaas pendente cancelado antes de gerar novo', [
            'charge_id' => $charge->id,
            'previous_payment_id' => $paymentId,
            'condominium_id' => $charge->condominium_id,
        ]);
    }
}
