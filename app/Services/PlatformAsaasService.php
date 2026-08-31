<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlatformAsaasService
{
    protected ?string $apiKey = null;
    protected string $apiUrl;
    protected bool $isSandbox = true;

    public function __construct(private PlatformSettingsService $settings)
    {
        $config = $this->settings->getAsaasConfig();
        $this->apiKey = $config['api_key'] ?: null;
        $this->isSandbox = (bool) $config['sandbox'];
        $this->apiUrl = $this->isSandbox
            ? 'https://sandbox.asaas.com/api/v3'
            : 'https://www.asaas.com/api/v3';
    }

    public function isConfigured(): bool
    {
        return filled($this->apiKey);
    }

    public function createOrUpdateCustomer(array $data): ?array
    {
        return $this->post('/customers', $data);
    }

    public function createSubscription(array $data): ?array
    {
        return $this->post('/subscriptions', $data);
    }

    public function cancelSubscription(string $subscriptionId): bool
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->delete("{$this->apiUrl}/subscriptions/{$subscriptionId}");

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Platform Asaas cancel subscription failed: ' . $e->getMessage());

            return false;
        }
    }

    public function getSubscription(string $subscriptionId): ?array
    {
        return $this->get("/subscriptions/{$subscriptionId}");
    }

    public function listSubscriptionPayments(string $subscriptionId, int $limit = 12): array
    {
        $result = $this->get("/subscriptions/{$subscriptionId}/payments?limit={$limit}&offset=0");

        return $result['data'] ?? [];
    }

    public function listCustomerPayments(string $customerId, int $limit = 100, int $offset = 0): array
    {
        $result = $this->get("/payments?customer={$customerId}&limit={$limit}&offset={$offset}");

        return [
            'data' => $result['data'] ?? [],
            'hasMore' => (bool) ($result['hasMore'] ?? false),
        ];
    }

    public function fetchAllCustomerPayments(string $customerId, int $max = 500): array
    {
        $all = [];
        $offset = 0;
        $limit = 100;

        do {
            $page = $this->listCustomerPayments($customerId, $limit, $offset);
            $all = array_merge($all, $page['data']);
            $offset += $limit;
            $hasMore = $page['hasMore'] ?? false;
        } while ($hasMore && count($all) < $max);

        return $all;
    }

    public function fetchAllSubscriptionPayments(string $subscriptionId, int $max = 500): array
    {
        $all = [];
        $offset = 0;
        $limit = 100;

        do {
            $result = $this->get("/subscriptions/{$subscriptionId}/payments?limit={$limit}&offset={$offset}");
            $batch = $result['data'] ?? [];
            $all = array_merge($all, $batch);
            $offset += $limit;
            $hasMore = (bool) ($result['hasMore'] ?? false);
        } while ($hasMore && count($all) < $max);

        return $all;
    }

    public function getPayment(string $paymentId): ?array
    {
        return $this->get("/payments/{$paymentId}");
    }

    public function createPayment(array $data): ?array
    {
        return $this->post('/payments', $data);
    }

    public function updateSubscription(string $subscriptionId, array $data): ?array
    {
        return $this->put("/subscriptions/{$subscriptionId}", $data);
    }

    public function getPixQRCode(string $paymentId): ?array
    {
        return $this->get("/payments/{$paymentId}/pixQrCode");
    }

    public function tokenizeCreditCard(string $customerId, array $creditCard, array $holderInfo): ?array
    {
        return $this->post('/creditCard/tokenize', [
            'customer' => $customerId,
            'creditCard' => $creditCard,
            'creditCardHolderInfo' => $holderInfo,
        ]);
    }

    protected function put(string $path, array $data): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withHeaders($this->headers())->put("{$this->apiUrl}{$path}", $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Platform Asaas PUT error', [
                'path' => $path,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Platform Asaas PUT exception: ' . $e->getMessage());
        }

        return null;
    }

    protected function post(string $path, array $data): ?array
    {
        if (!$this->isConfigured()) {
            Log::warning('Platform Asaas não configurado.');

            return null;
        }

        try {
            $response = Http::withHeaders($this->headers())->post("{$this->apiUrl}{$path}", $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Platform Asaas POST error', [
                'path' => $path,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Platform Asaas POST exception: ' . $e->getMessage());
        }

        return null;
    }

    protected function get(string $path): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withHeaders($this->headers())->get("{$this->apiUrl}{$path}");

            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::error('Platform Asaas GET exception: ' . $e->getMessage());

            return null;
        }
    }

    protected function headers(): array
    {
        return [
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }
}
