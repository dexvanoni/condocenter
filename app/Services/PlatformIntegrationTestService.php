<?php

namespace App\Services;

use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PlatformIntegrationTestService
{
    public function __construct(
        private PlatformAsaasService $asaas,
        private PlatformSettingsService $settings,
    ) {}

    public function runAll(): array
    {
        $webhookUrl = rtrim(config('saas.webhook_base_url', config('app.url')), '/')
            . '/webhooks/asaas/platform';

        return [
            'asaas' => $this->testAsaasConnection(),
            'webhook' => $this->testWebhook($webhookUrl),
        ];
    }

    public function testAsaasConnection(): array
    {
        if (!$this->asaas->isConfigured()) {
            return [
                'ok' => false,
                'message' => 'API Key não configurada. Informe em `.env` (ASAAS_API_KEY) ou salve no formulário acima.',
            ];
        }

        $config = $this->settings->getAsaasConfig();
        $baseUrl = $config['sandbox']
            ? 'https://sandbox.asaas.com/api/v3'
            : 'https://www.asaas.com/api/v3';

        try {
            $response = Http::withHeaders([
                'access_token' => $config['api_key'],
                'Content-Type' => 'application/json',
            ])
                ->timeout(20)
                ->get("{$baseUrl}/finance/balance");

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'ok' => true,
                    'message' => 'Conexão com a API Asaas estabelecida com sucesso.',
                    'details' => [
                        'environment' => $config['sandbox'] ? 'sandbox' : 'production',
                        'api_url' => $baseUrl,
                        'balance' => isset($data['balance'])
                            ? 'R$ ' . number_format((float) $data['balance'], 2, ',', '.')
                            : null,
                    ],
                ];
            }

            $body = $response->json();
            $apiMessage = is_array($body)
                ? ($body['errors'][0]['description'] ?? $body['message'] ?? null)
                : null;

            return [
                'ok' => false,
                'message' => $apiMessage ?: 'A API Asaas retornou erro HTTP ' . $response->status() . '.',
                'details' => [
                    'environment' => $config['sandbox'] ? 'sandbox' : 'production',
                    'http_status' => $response->status(),
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => 'Não foi possível contactar o Asaas: ' . $e->getMessage(),
            ];
        }
    }

    public function testWebhook(string $webhookUrl): array
    {
        $internal = $this->testWebhookInternally();

        if (!$internal['ok']) {
            return $internal;
        }

        $external = $this->testWebhookExternal($webhookUrl);

        if ($external['ok']) {
            return [
                'ok' => true,
                'message' => 'Webhook OK (processamento interno e URL pública acessível).',
                'details' => array_merge($internal['details'] ?? [], [
                    'url_cadastrada_asaas' => $webhookUrl,
                    'url_publica' => 'Acessível',
                ]),
            ];
        }

        return [
            'ok' => true,
            'warning' => true,
            'message' => 'Webhook processado corretamente. A URL pública não respondeu neste teste — isso é comum ao testar do mesmo servidor via ngrok; o Asaas (externo) deve alcançar normalmente.',
            'details' => array_merge($internal['details'] ?? [], [
                'url_cadastrada_asaas' => $webhookUrl,
                'url_publica' => $external['message'],
            ]),
        ];
    }

    protected function testWebhookInternally(): array
    {
        $config = $this->settings->getAsaasConfig();
        $token = $config['webhook_token'] ?: null;

        $payload = [
            'event' => 'WEBHOOK_TEST',
            'id' => 'evt_test_' . now()->timestamp,
            'dateCreated' => now()->toIso8601String(),
        ];

        $request = Request::create('/webhooks/asaas/platform', 'POST', $payload);
        $request->headers->set('Accept', 'application/json');

        if ($token) {
            $request->headers->set('asaas-access-token', $token);
        }

        try {
            $response = app(WebhookController::class)->asaasPlatform($request);
            $status = $response->getStatusCode();
            $body = json_decode($response->getContent(), true) ?: [];

            if ($status === 401) {
                return [
                    'ok' => false,
                    'message' => 'Token do webhook inválido (401). Confira ASAAS_WEBHOOK_TOKEN e o painel Asaas.',
                    'details' => [
                        'token_configured' => filled($token) ? 'sim' : 'não',
                    ],
                ];
            }

            $responseStatus = $body['status'] ?? null;
            $ok = $status === 200 && $responseStatus === 'success';

            return [
                'ok' => $ok,
                'message' => $ok
                    ? 'Handler do webhook respondeu corretamente.'
                    : 'Webhook retornou HTTP ' . $status . '.',
                'details' => [
                    'teste' => 'interno (simula POST do Asaas)',
                    'http_status' => $status,
                    'response_status' => $responseStatus,
                    'token_configured' => filled($token) ? 'sim' : 'não',
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => 'Erro ao processar webhook: ' . $e->getMessage(),
            ];
        }
    }

    protected function testWebhookExternal(string $webhookUrl): array
    {
        $config = $this->settings->getAsaasConfig();
        $token = $config['webhook_token'] ?: null;

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'ngrok-skip-browser-warning' => 'true',
        ];

        if ($token) {
            $headers['asaas-access-token'] = $token;
        }

        $payload = [
            'event' => 'WEBHOOK_TEST',
            'id' => 'evt_test_ext_' . now()->timestamp,
            'dateCreated' => now()->toIso8601String(),
        ];

        try {
            $response = Http::withHeaders($headers)
                ->connectTimeout(5)
                ->timeout(8)
                ->post($webhookUrl, $payload);

            $body = $response->json();
            $status = $response->status();
            $responseStatus = is_array($body) ? ($body['status'] ?? null) : null;

            if ($status === 401) {
                return [
                    'ok' => false,
                    'message' => 'Token rejeitado na URL pública (401).',
                ];
            }

            $ok = $response->successful() && in_array($responseStatus, ['success', 'ignored'], true);

            return [
                'ok' => $ok,
                'message' => $ok
                    ? 'URL pública respondeu HTTP ' . $status . '.'
                    : 'Timeout ou erro HTTP ' . $status . ' (comum em loop ngrok no mesmo servidor).',
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => 'Indisponível externamente: ' . $this->shortenCurlError($e->getMessage()),
            ];
        }
    }

    protected function shortenCurlError(string $message): string
    {
        if (str_contains($message, 'cURL error 28')) {
            return 'timeout ao chamar a própria URL via ngrok (esperado em ambiente local).';
        }

        return $message;
    }
}
