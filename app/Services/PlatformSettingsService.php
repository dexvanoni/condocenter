<?php

namespace App\Services;

use App\Models\PlatformSetting;

class PlatformSettingsService
{
    public const KEY_ASAAS_API_KEY = 'asaas_api_key';
    public const KEY_ASAAS_SANDBOX = 'asaas_sandbox';
    public const KEY_ASAAS_WEBHOOK_EMAIL = 'asaas_webhook_email';
    public const KEY_ASAAS_WEBHOOK_TOKEN = 'asaas_webhook_token';

    public function getAsaasConfig(): array
    {
        $dbApiKey = PlatformSetting::getValue(self::KEY_ASAAS_API_KEY);
        $dbSandbox = PlatformSetting::getValue(self::KEY_ASAAS_SANDBOX);

        return [
            'api_key' => $dbApiKey ?: config('services.asaas.api_key'),
            'sandbox' => $dbSandbox !== null
                ? filter_var($dbSandbox, FILTER_VALIDATE_BOOLEAN)
                : (bool) config('services.asaas.sandbox', true),
            'webhook_email' => PlatformSetting::getValue(
                self::KEY_ASAAS_WEBHOOK_EMAIL,
                config('services.asaas.webhook_email', 'admin@condomanager.com')
            ),
            'webhook_token' => PlatformSetting::getValue(
                self::KEY_ASAAS_WEBHOOK_TOKEN,
                config('services.asaas.webhook_token')
            ),
            'configured_in_db' => filled($dbApiKey),
        ];
    }

    public function updateAsaasSettings(array $data): void
    {
        if (array_key_exists('api_key', $data) && filled($data['api_key'])) {
            PlatformSetting::setValue(self::KEY_ASAAS_API_KEY, $data['api_key'], encrypt: true);
        }

        if (array_key_exists('sandbox', $data)) {
            PlatformSetting::setValue(
                self::KEY_ASAAS_SANDBOX,
                $data['sandbox'] ? '1' : '0'
            );
        }

        if (array_key_exists('webhook_email', $data)) {
            PlatformSetting::setValue(self::KEY_ASAAS_WEBHOOK_EMAIL, $data['webhook_email']);
        }

        if (array_key_exists('webhook_token', $data)) {
            PlatformSetting::setValue(
                self::KEY_ASAAS_WEBHOOK_TOKEN,
                $data['webhook_token'],
                encrypt: true
            );
        }
    }

    public function isAsaasConfigured(): bool
    {
        return filled($this->getAsaasConfig()['api_key']);
    }
}
