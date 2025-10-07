<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasService
{
    protected $apiKey;
    protected $apiUrl;
    protected $isSandbox;

    public function __construct()
    {
        $this->isSandbox = config('services.asaas.sandbox', true);
        $this->apiKey = config('services.asaas.api_key');
        $this->apiUrl = $this->isSandbox 
            ? 'https://sandbox.asaas.com/api/v3'
            : 'https://www.asaas.com/api/v3';
    }

    /**
     * Cria ou atualiza um cliente no Asaas
     */
    public function createOrUpdateCustomer($data)
    {
        try {
            $response = Http::withHeaders([
                'access_token' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/customers", $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Erro ao criar cliente no Asaas', [
                'response' => $response->json(),
                'status' => $response->status()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exceção ao criar cliente no Asaas: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Cria uma cobrança no Asaas
     */
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
                'response' => $response->json(),
                'status' => $response->status()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exceção ao criar cobrança no Asaas: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Consulta um pagamento no Asaas
     */
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

    /**
     * Gera QR Code PIX para um pagamento
     */
    public function getPixQRCode($paymentId)
    {
        try {
            $response = Http::withHeaders([
                'access_token' => $this->apiKey,
            ])->get("{$this->apiUrl}/payments/{$paymentId}/pixQrCode");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Exceção ao gerar QR Code PIX: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Cria uma assinatura recorrente
     */
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
                'status' => $response->status()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exceção ao criar assinatura no Asaas: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Cancela uma assinatura
     */
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

    /**
     * Cria um webhook para receber notificações
     */
    public function createWebhook($url, $events = [])
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
                'email' => config('services.asaas.webhook_email', 'admin@condomanager.com'),
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

    /**
     * Processa o webhook recebido do Asaas
     */
    public function processWebhook($payload)
    {
        try {
            $event = $payload['event'] ?? null;
            $payment = $payload['payment'] ?? null;

            if (!$event || !$payment) {
                return false;
            }

            Log::info('Webhook Asaas recebido', ['event' => $event, 'payment_id' => $payment['id']]);

            // Busca a cobrança no sistema
            $charge = \App\Models\Charge::where('asaas_payment_id', $payment['id'])->first();

            if (!$charge) {
                Log::warning('Cobrança não encontrada para o payment_id: ' . $payment['id']);
                return false;
            }

            // Atualiza status baseado no evento
            switch ($event) {
                case 'PAYMENT_CONFIRMED':
                case 'PAYMENT_RECEIVED':
                    $charge->markAsPaid();
                    
                    // Cria registro de pagamento
                    \App\Models\Payment::create([
                        'charge_id' => $charge->id,
                        'amount_paid' => $payment['value'],
                        'payment_date' => $payment['paymentDate'] ?? now(),
                        'payment_method' => $this->mapAsaasPaymentMethod($payment['billingType']),
                        'asaas_payment_id' => $payment['id'],
                    ]);
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

    /**
     * Mapeia o método de pagamento do Asaas para o sistema
     */
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

