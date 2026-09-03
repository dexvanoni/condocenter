<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasService
{
    protected ?string $apiKey = null;
    protected string $apiUrl = 'https://sandbox.asaas.com/api/v3';
    protected bool $isSandbox = true;
    protected ?int $condominiumId = null;

    public function __construct(
        private CondominiumAsaasSettingsService $condominiumSettings,
    ) {
        $this->bootFromGlobalConfig();
    }

    public function forCondominium(?int $condominiumId): self
    {
        $instance = clone $this;
        $instance->condominiumId = $condominiumId;
        $instance->bootFromCondominium($condominiumId);

        return $instance;
    }

    public function isConfigured(): bool
    {
        return filled($this->apiKey);
    }

    public function getCondominiumId(): ?int
    {
        return $this->condominiumId;
    }

    protected function bootFromGlobalConfig(): void
    {
        $this->isSandbox = (bool) config('services.asaas.sandbox', true);
        $this->apiKey = config('services.asaas.api_key') ?: null;
        $this->apiUrl = $this->isSandbox
            ? 'https://sandbox.asaas.com/api/v3'
            : 'https://www.asaas.com/api/v3';
    }

    protected function bootFromCondominium(?int $condominiumId): void
    {
        if (!$condominiumId) {
            $this->bootFromGlobalConfig();

            return;
        }

        $condominium = \App\Models\Condominium::query()->find($condominiumId);

        if (!$condominium || !$this->condominiumSettings->isConfigured($condominium)) {
            $this->bootFromGlobalConfig();

            return;
        }

        $config = $this->condominiumSettings->getApiConfig($condominium);
        $this->apiKey = $config['api_key'] ?: null;
        $this->isSandbox = (bool) $config['sandbox'];
        $this->apiUrl = $config['api_url'];
    }

    public function findCustomerByExternalReference(string $externalReference): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'access_token' => $this->apiKey,
            ])->get("{$this->apiUrl}/customers", [
                'externalReference' => $externalReference,
                'limit' => 1,
            ]);

            if (!$response->successful()) {
                return null;
            }

            $customers = $response->json()['data'] ?? [];

            return $customers[0] ?? null;
        } catch (\Exception $e) {
            Log::error('Exceção ao buscar cliente no Asaas: ' . $e->getMessage(), [
                'condominium_id' => $this->condominiumId,
            ]);

            return null;
        }
    }

    public function updateCustomer(string $customerId, array $data): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'access_token' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->put("{$this->apiUrl}/customers/{$customerId}", $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Erro ao atualizar cliente no Asaas', [
                'condominium_id' => $this->condominiumId,
                'response' => $response->json(),
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exceção ao atualizar cliente no Asaas: ' . $e->getMessage(), [
                'condominium_id' => $this->condominiumId,
            ]);

            return null;
        }
    }

    public function createOrUpdateCustomer($data)
    {
        $externalReference = $data['externalReference'] ?? null;

        if ($externalReference) {
            $existing = $this->findCustomerByExternalReference($externalReference);

            if ($existing) {
                return $this->updateCustomer($existing['id'], $data) ?? $existing;
            }
        }

        try {
            $response = Http::withHeaders([
                'access_token' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/customers", $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Erro ao criar cliente no Asaas', [
                'condominium_id' => $this->condominiumId,
                'response' => $response->json(),
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exceção ao criar cliente no Asaas: ' . $e->getMessage(), [
                'condominium_id' => $this->condominiumId,
            ]);

            return null;
        }
    }

    public function tokenizeCreditCard(string $customerId, array $creditCard, array $holderInfo): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'access_token' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/creditCard/tokenize", [
                'customer' => $customerId,
                'creditCard' => $creditCard,
                'creditCardHolderInfo' => $holderInfo,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Erro ao tokenizar cartão no Asaas', [
                'condominium_id' => $this->condominiumId,
                'response' => $response->json(),
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exceção ao tokenizar cartão no Asaas: ' . $e->getMessage(), [
                'condominium_id' => $this->condominiumId,
            ]);

            return null;
        }
    }

    public function createPayment($data)
    {
        try {
            $response = Http::withHeaders([
                'access_token' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/payments", $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Erro ao criar cobrança no Asaas', [
                'condominium_id' => $this->condominiumId,
                'response' => $response->json(),
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exceção ao criar cobrança no Asaas: ' . $e->getMessage(), [
                'condominium_id' => $this->condominiumId,
            ]);

            return null;
        }
    }

    public function getPayment($paymentId)
    {
        try {
            $response = Http::withHeaders([
                'access_token' => $this->apiKey,
            ])->get("{$this->apiUrl}/payments/{$paymentId}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Exceção ao consultar pagamento no Asaas: ' . $e->getMessage());

            return null;
        }
    }

    public function getPixQRCode($paymentId)
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'access_token' => $this->apiKey,
            ])->get("{$this->apiUrl}/payments/{$paymentId}/pixQrCode");

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Falha ao obter QR Code PIX no Asaas', [
                'payment_id' => $paymentId,
                'condominium_id' => $this->condominiumId,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exceção ao gerar QR Code PIX: ' . $e->getMessage(), [
                'payment_id' => $paymentId,
                'condominium_id' => $this->condominiumId,
            ]);

            return null;
        }
    }

    public function createSubscription($data)
    {
        try {
            $response = Http::withHeaders([
                'access_token' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/subscriptions", $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Erro ao criar assinatura no Asaas', [
                'response' => $response->json(),
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exceção ao criar assinatura no Asaas: ' . $e->getMessage());

            return null;
        }
    }

    public function cancelSubscription($subscriptionId)
    {
        try {
            $response = Http::withHeaders([
                'access_token' => $this->apiKey,
            ])->delete("{$this->apiUrl}/subscriptions/{$subscriptionId}");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Exceção ao cancelar assinatura no Asaas: ' . $e->getMessage());

            return false;
        }
    }

    public function createWebhook($url, $events = [], ?string $email = null)
    {
        try {
            $defaultEvents = [
                'PAYMENT_CREATED',
                'PAYMENT_UPDATED',
                'PAYMENT_CONFIRMED',
                'PAYMENT_RECEIVED',
                'PAYMENT_OVERDUE',
                'PAYMENT_DELETED',
            ];

            $response = Http::withHeaders([
                'access_token' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/webhooks", [
                'url' => $url,
                'email' => $email ?: config('services.asaas.webhook_email', 'admin@condomanager.com'),
                'enabled' => true,
                'interrupted' => false,
                'events' => empty($events) ? $defaultEvents : $events,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Exceção ao criar webhook no Asaas: ' . $e->getMessage());

            return null;
        }
    }

    public function processWebhook($payload)
    {
        try {
            $event = $payload['event'] ?? null;

            if ($event === 'WEBHOOK_TEST') {
                return true;
            }

            $payment = $payload['payment'] ?? null;

            if (!$event || !$payment) {
                return false;
            }

            Log::info('Webhook Asaas recebido', [
                'event' => $event,
                'payment_id' => $payment['id'],
                'condominium_id' => $this->condominiumId,
            ]);

            $charge = \App\Models\Charge::where('asaas_payment_id', $payment['id'])->first();

            if (!$charge) {
                Log::warning('Cobrança não encontrada para o payment_id: ' . $payment['id']);

                return false;
            }

            switch ($event) {
                case 'PAYMENT_CONFIRMED':
                case 'PAYMENT_RECEIVED':
                    if ($charge->status === 'paid') {
                        return true;
                    }

                    $charge->update(['asaas_payment_id' => $payment['id']]);

                    app(ChargeSettlementService::class)->markAsPaid(
                        $charge->fresh(),
                        \Carbon\Carbon::parse($payment['paymentDate'] ?? now()),
                        $this->mapAsaasPaymentMethod($payment['billingType'] ?? null),
                        'Pagamento confirmado via Asaas.'
                    );
                    break;

                case 'PAYMENT_OVERDUE':
                    $charge->update(['status' => 'overdue']);
                    break;

                case 'PAYMENT_DELETED':
                    $charge->update(['status' => 'cancelled']);
                    break;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook Asaas: ' . $e->getMessage());

            return false;
        }
    }

    protected function mapAsaasPaymentMethod($billingType)
    {
        $map = [
            'BOLETO' => 'boleto',
            'CREDIT_CARD' => 'credit_card',
            'PIX' => 'pix',
            'DEBIT_CARD' => 'debit_card',
            'UNDEFINED' => 'other',
        ];

        return $map[$billingType] ?? 'other';
    }
}
