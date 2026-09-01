<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionApiService
{
    public function __construct(
        private PlatformSettingsService $platformSettings,
        private CondominiumWhatsAppSettingsService $condominiumSettings,
    ) {}

    public function getConfig(?int $condominiumId = null): array
    {
        if ($condominiumId) {
            $condominium = \App\Models\Condominium::query()->find($condominiumId);

            if ($condominium) {
                return $this->condominiumSettings->getConfig($condominium);
            }
        }

        return $this->platformSettings->getWhatsAppConfig();
    }

    public function isConfigured(?int $condominiumId = null): bool
    {
        $config = $this->getConfig($condominiumId);

        return filled($config['api_url'])
            && filled($config['api_key'])
            && filled($config['instance']);
    }

    public function connectionState(?int $condominiumId = null): array
    {
        if (!$this->isConfigured($condominiumId)) {
            return [
                'ok' => false,
                'state' => null,
                'message' => 'Evolution API não configurada.',
            ];
        }

        $config = $this->getConfig($condominiumId);
        $url = $this->buildEndpoint($config, "/instance/connectionState/{$config['instance']}");

        try {
            $response = $this->client($config)->get($url);

            if (!$response->successful()) {
                return [
                    'ok' => false,
                    'state' => null,
                    'message' => 'HTTP ' . $response->status() . ': ' . ($response->json('message') ?? $response->body()),
                ];
            }

            $state = $response->json('instance.state')
                ?? $response->json('state')
                ?? $response->json('status');

            $connected = in_array(strtolower((string) $state), ['open', 'connected'], true);

            return [
                'ok' => $connected,
                'state' => $state,
                'message' => $connected
                    ? 'Instância conectada ao WhatsApp.'
                    : 'Instância não conectada (estado: ' . ($state ?: 'desconhecido') . ').',
            ];
        } catch (\Throwable $e) {
            Log::warning('Evolution API connectionState failed: ' . $e->getMessage(), [
                'condominium_id' => $condominiumId,
            ]);

            return [
                'ok' => false,
                'state' => null,
                'message' => 'Falha ao consultar instância: ' . $e->getMessage(),
            ];
        }
    }

    public function sendText(string $phone, string $text, ?int $condominiumId = null): array
    {
        if (!$this->isConfigured($condominiumId)) {
            return ['ok' => false, 'message' => 'Evolution API não configurada.'];
        }

        $config = $this->getConfig($condominiumId);
        $number = $this->normalizePhone($phone, $config['default_country_code']);

        if (!$number) {
            return ['ok' => false, 'message' => 'Telefone inválido para WhatsApp.'];
        }

        $url = $this->buildEndpoint($config, "/message/sendText/{$config['instance']}");

        try {
            $response = $this->client($config)->post($url, [
                'number' => $number,
                'text' => $text,
            ]);

            if ($response->successful()) {
                return [
                    'ok' => true,
                    'message' => 'Mensagem enviada.',
                    'response' => $response->json(),
                ];
            }

            return [
                'ok' => false,
                'message' => 'HTTP ' . $response->status() . ': ' . ($response->json('message') ?? $response->body()),
            ];
        } catch (\Throwable $e) {
            Log::warning('Evolution API sendText failed: ' . $e->getMessage(), [
                'phone' => $number,
                'condominium_id' => $condominiumId,
            ]);

            return [
                'ok' => false,
                'message' => 'Falha ao enviar: ' . $e->getMessage(),
            ];
        }
    }

    public function normalizePhone(?string $phone, ?string $countryCode = null): ?string
    {
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return null;
        }

        $countryCode = preg_replace('/\D+/', '', (string) ($countryCode ?: config('whatsapp.default_country_code', '55'))) ?? '55';

        if (str_starts_with($digits, '0')) {
            $digits = ltrim($digits, '0');
        }

        if (!str_starts_with($digits, $countryCode) && strlen($digits) <= 11) {
            $digits = $countryCode . $digits;
        }

        return strlen($digits) >= 12 ? $digits : null;
    }

    protected function buildEndpoint(array $config, string $path): string
    {
        $base = rtrim($config['api_url'] ?? '', '/');

        return $base . $path;
    }

    protected function client(array $config)
    {
        return Http::withHeaders([
            'apikey' => $config['api_key'],
            'Content-Type' => 'application/json',
        ])->timeout((int) ($config['timeout'] ?? 15));
    }
}
