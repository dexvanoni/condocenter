<?php

namespace App\Services;

use App\Models\Condominium;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionInstanceService
{
    public function __construct(
        private PlatformSettingsService $platformSettings,
    ) {}

    public function instanceNameForCondominium(Condominium $condominium): string
    {
        $prefix = preg_replace('/[^a-z0-9_-]/i', '', (string) config('whatsapp.instance_prefix', 'sindcon')) ?: 'sindcon';
        $env = preg_replace('/[^a-z0-9_-]/i', '', (string) config('app.env', 'production')) ?: 'production';

        return strtolower("{$prefix}-{$env}-condo-{$condominium->id}");
    }

    /**
     * @return array{ok: bool, message: string, instance_name?: string, instance_api_key?: string, qrcode_base64?: string|null}
     */
    public function createInstance(string $instanceName): array
    {
        $provisioning = $this->platformSettings->getEvolutionProvisioningConfig();

        if (!filled($provisioning['api_url']) || !filled($provisioning['global_api_key'])) {
            return [
                'ok' => false,
                'message' => 'Servidor Evolution ou API Key global não configurados. O administrador da plataforma deve informar a URL e a chave global em Plataforma → WhatsApp.',
            ];
        }

        $url = rtrim($provisioning['api_url'], '/') . '/instance/create';

        try {
            $response = $this->globalClient($provisioning)->post($url, [
                'instanceName' => $instanceName,
                'qrcode' => true,
                'integration' => 'WHATSAPP-BAILEYS',
            ]);

            if (!$response->successful()) {
                $message = $response->json('message') ?? $response->json('error') ?? $response->body();

                return [
                    'ok' => false,
                    'message' => 'Evolution não criou a instância: HTTP ' . $response->status() . ' — ' . (is_string($message) ? $message : json_encode($message)),
                ];
            }

            $body = $response->json();
            $instanceApiKey = $body['hash']
                ?? $body['apikey']
                ?? $body['instance']['apikey']
                ?? $body['instance']['token']
                ?? null;

            $qrcodeBase64 = $this->extractQrBase64($body);

            if (!filled($instanceApiKey)) {
                return [
                    'ok' => false,
                    'message' => 'Instância criada, mas a Evolution não retornou a API Key da instância. Verifique a versão da Evolution API.',
                ];
            }

            return [
                'ok' => true,
                'message' => 'Instância criada. Escaneie o QR Code no WhatsApp.',
                'instance_name' => $instanceName,
                'instance_api_key' => (string) $instanceApiKey,
                'qrcode_base64' => $qrcodeBase64,
            ];
        } catch (\Throwable $e) {
            Log::warning('Evolution createInstance failed: ' . $e->getMessage(), [
                'instance' => $instanceName,
            ]);

            return [
                'ok' => false,
                'message' => 'Falha ao criar instância: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * @param  array{api_url: string, api_key: string, instance: string, timeout?: int}  $config
     * @return array{ok: bool, message: string, qrcode_base64?: string|null}
     */
    public function fetchQrCode(array $config): array
    {
        if (!filled($config['api_url']) || !filled($config['api_key']) || !filled($config['instance'])) {
            return [
                'ok' => false,
                'message' => 'Instância Evolution incompleta no cadastro do condomínio.',
            ];
        }

        $url = rtrim($config['api_url'], '/') . '/instance/connect/' . $config['instance'];

        try {
            $response = $this->instanceClient($config)->get($url);

            if (!$response->successful()) {
                $message = $response->json('message') ?? $response->body();

                return [
                    'ok' => false,
                    'message' => 'HTTP ' . $response->status() . ': ' . (is_string($message) ? $message : json_encode($message)),
                ];
            }

            $qrcodeBase64 = $this->extractQrBase64($response->json());

            if (!$qrcodeBase64) {
                return [
                    'ok' => false,
                    'message' => 'A Evolution não retornou QR Code. A instância pode já estar conectada ou aguardando outro passo.',
                ];
            }

            return [
                'ok' => true,
                'message' => 'QR Code gerado. Escaneie no WhatsApp em até cerca de 40 segundos.',
                'qrcode_base64' => $qrcodeBase64,
            ];
        } catch (\Throwable $e) {
            Log::warning('Evolution fetchQrCode failed: ' . $e->getMessage(), [
                'instance' => $config['instance'] ?? null,
            ]);

            return [
                'ok' => false,
                'message' => 'Falha ao obter QR Code: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * @param  array{api_url: string, api_key: string, instance: string, timeout?: int}  $config
     */
    public function logout(array $config): array
    {
        if (!filled($config['api_url']) || !filled($config['api_key']) || !filled($config['instance'])) {
            return [
                'ok' => false,
                'message' => 'Instância Evolution incompleta.',
            ];
        }

        $url = rtrim($config['api_url'], '/') . '/instance/logout/' . $config['instance'];

        try {
            $response = $this->instanceClient($config)->delete($url);

            if (!$response->successful()) {
                $message = $response->json('message') ?? $response->body();

                return [
                    'ok' => false,
                    'message' => 'HTTP ' . $response->status() . ': ' . (is_string($message) ? $message : json_encode($message)),
                ];
            }

            return [
                'ok' => true,
                'message' => 'Sessão desconectada. Gere um novo QR Code para vincular outro aparelho.',
            ];
        } catch (\Throwable $e) {
            Log::warning('Evolution logout failed: ' . $e->getMessage(), [
                'instance' => $config['instance'] ?? null,
            ]);

            return [
                'ok' => false,
                'message' => 'Falha ao desconectar: ' . $e->getMessage(),
            ];
        }
    }

    protected function extractQrBase64(mixed $body): ?string
    {
        if (!is_array($body)) {
            return null;
        }

        $explicit = [
            $body['base64'] ?? null,
            is_array($body['qrcode'] ?? null) ? ($body['qrcode']['base64'] ?? null) : null,
        ];

        foreach ($explicit as $value) {
            $normalized = $this->normalizeQrString($value);
            if ($normalized) {
                return $normalized;
            }
        }

        $fallbacks = [
            is_string($body['qrcode'] ?? null) ? $body['qrcode'] : null,
            $body['code'] ?? null,
            $body['pairingCode'] ?? null,
        ];

        foreach ($fallbacks as $value) {
            $normalized = $this->normalizeQrString($value);
            if ($normalized && strlen($normalized) > 100) {
                return $normalized;
            }
        }

        return null;
    }

    protected function normalizeQrString(mixed $value): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        if (str_starts_with($value, 'data:image')) {
            $parts = explode(',', $value, 2);

            return $parts[1] ?? $value;
        }

        if (str_contains($value, ' ')) {
            return null;
        }

        return $value;
    }

    protected function globalClient(array $provisioning)
    {
        return Http::withHeaders([
            'apikey' => $provisioning['global_api_key'],
            'Content-Type' => 'application/json',
        ])->timeout((int) ($provisioning['timeout'] ?? 30));
    }

    protected function instanceClient(array $config)
    {
        return Http::withHeaders([
            'apikey' => $config['api_key'],
            'Content-Type' => 'application/json',
        ])->timeout((int) ($config['timeout'] ?? 30));
    }
}
