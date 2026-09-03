<?php

namespace App\Services;

use App\Http\Controllers\WebhookController;
use App\Models\Condominium;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CondominiumAsaasIntegrationTestService
{
    public function __construct(
        private CondominiumAsaasSettingsService $settings,
        private AsaasService $asaasService,
    ) {}

    public function runAll(Condominium $condominium): array
    {
        return [
            'asaas' => $this->testAsaasConnection($condominium),
            'webhook' => $this->testWebhook($condominium),
        ];
    }

    public function testAsaasConnection(Condominium $condominium): array
    {
        if (!$this->settings->isConfigured($condominium)) {
            return [
                'ok' => false,
                'message' => 'Informe a API Key do Asaas antes de testar a conexão.',
            ];
        }

        $config = $this->settings->getApiConfig($condominium);

        try {
            $response = Http::withHeaders([
                'access_token' => $config['api_key'],
                'Content-Type' => 'application/json',
            ])
                ->timeout(20)
                ->get("{$config['api_url']}/finance/balance");

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'ok' => true,
                    'message' => 'Conexão com a API Asaas do condomínio estabelecida com sucesso.',
                    'details' => [
                        'environment' => $config['sandbox'] ? 'sandbox' : 'production',
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

    public function testWebhook(Condominium $condominium): array
    {
        $webhookUrl = $this->settings->webhookUrl($condominium);
        $internal = $this->testWebhookInternally($condominium);

        if (!$internal['ok']) {
            return $internal;
        }

        $external = $this->testWebhookExternal($condominium, $webhookUrl);

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
            'message' => 'Webhook processado corretamente no servidor. A URL pública não respondeu neste teste — comum em ambiente local; o Asaas (externo) deve alcançar em produção.',
            'details' => array_merge($internal['details'] ?? [], [
                'url_cadastrada_asaas' => $webhookUrl,
                'url_publica' => $external['message'],
            ]),
        ];
    }

    protected function testWebhookInternally(Condominium $condominium): array
    {
        $token = $condominium->asaas_webhook_token;

        $payload = [
            'event' => 'WEBHOOK_TEST',
            'id' => 'evt_test_' . now()->timestamp,
            'dateCreated' => now()->toIso8601String(),
        ];

        $request = Request::create(
            '/webhooks/asaas/condominium/' . $condominium->id,
            'POST',
            $payload
        );
        $request->headers->set('Accept', 'application/json');

        if ($token) {
            $request->headers->set('asaas-access-token', $token);
        }

        try {
            $response = app(WebhookController::class)->asaasCondominium($request, $condominium);
            $status = $response->getStatusCode();
            $body = json_decode($response->getContent(), true) ?: [];

            if ($status === 401) {
                return [
                    'ok' => false,
                    'message' => 'Token do webhook inválido (401). Confira o token salvo e o painel Asaas.',
                ];
            }

            $responseStatus = $body['status'] ?? null;
            $ok = $status === 200 && in_array($responseStatus, ['success', 'ignored'], true);

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

    protected function testWebhookExternal(Condominium $condominium, string $webhookUrl): array
    {
        $token = $condominium->asaas_webhook_token;

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
                    : 'Timeout ou erro HTTP ' . $status . ' (comum em ambiente local).',
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => 'Indisponível externamente: ' . $e->getMessage(),
            ];
        }
    }
}
