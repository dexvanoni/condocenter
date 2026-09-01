<?php

namespace App\Services;

use App\Models\PlatformSetting;

class PlatformSettingsService
{
    public const KEY_ASAAS_API_KEY = 'asaas_api_key';
    public const KEY_ASAAS_SANDBOX = 'asaas_sandbox';
    public const KEY_ASAAS_WEBHOOK_EMAIL = 'asaas_webhook_email';
    public const KEY_ASAAS_WEBHOOK_TOKEN = 'asaas_webhook_token';

    public const KEY_WHATSAPP_ENABLED = 'whatsapp_enabled';
    public const KEY_EVOLUTION_API_URL = 'evolution_api_url';
    public const KEY_EVOLUTION_API_KEY = 'evolution_api_key';
    public const KEY_EVOLUTION_INSTANCE = 'evolution_instance';
    public const KEY_WHATSAPP_NOTIFY_GROUPS = 'whatsapp_notify_groups';

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

    public function getWhatsAppConfig(): array
    {
        $dbEnabled = PlatformSetting::getValue(self::KEY_WHATSAPP_ENABLED);
        $dbUrl = PlatformSetting::getValue(self::KEY_EVOLUTION_API_URL);
        $dbKey = PlatformSetting::getValue(self::KEY_EVOLUTION_API_KEY);
        $dbInstance = PlatformSetting::getValue(self::KEY_EVOLUTION_INSTANCE);

        return [
            'enabled' => $dbEnabled !== null
                ? filter_var($dbEnabled, FILTER_VALIDATE_BOOLEAN)
                : (bool) config('whatsapp.enabled', false),
            'api_url' => $dbUrl ?: config('whatsapp.api_url'),
            'api_key' => $dbKey ?: config('whatsapp.api_key'),
            'instance' => $dbInstance ?: config('whatsapp.instance'),
            'default_country_code' => config('whatsapp.default_country_code', '55'),
            'timeout' => (int) config('whatsapp.timeout', 15),
            'configured_in_db' => filled($dbUrl) || filled($dbKey) || filled($dbInstance),
        ];
    }

    public function getWhatsAppEnabledGroups(): array
    {
        $raw = PlatformSetting::getValue(self::KEY_WHATSAPP_NOTIFY_GROUPS);

        if (!$raw) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function isWhatsAppEnabled(): bool
    {
        return (bool) ($this->getWhatsAppConfig()['enabled'] ?? false);
    }

    public function isWhatsAppGroupEnabled(string $group): bool
    {
        if (!$this->isWhatsAppEnabled()) {
            return false;
        }

        $groups = $this->getWhatsAppEnabledGroups();

        return (bool) ($groups[$group] ?? false);
    }

    public function resolveWhatsAppGroupForType(string $type): ?string
    {
        foreach (config('whatsapp.groups', []) as $key => $group) {
            foreach ($group['types'] ?? [] as $pattern) {
                if ($pattern === $type) {
                    return $key;
                }

                if (str_ends_with($pattern, '*')) {
                    $prefix = rtrim($pattern, '*');
                    if (str_starts_with($type, $prefix)) {
                        return $key;
                    }
                }
            }
        }

        return null;
    }

    public function isWhatsAppConfigured(): bool
    {
        $config = $this->getWhatsAppConfig();

        return filled($config['api_url'])
            && filled($config['api_key'])
            && filled($config['instance']);
    }

    public function updateWhatsAppSettings(array $data): void
    {
        if (array_key_exists('enabled', $data)) {
            PlatformSetting::setValue(
                self::KEY_WHATSAPP_ENABLED,
                $data['enabled'] ? '1' : '0'
            );
        }

        if (array_key_exists('api_url', $data)) {
            PlatformSetting::setValue(self::KEY_EVOLUTION_API_URL, $data['api_url']);
        }

        if (array_key_exists('api_key', $data) && filled($data['api_key'])) {
            PlatformSetting::setValue(self::KEY_EVOLUTION_API_KEY, $data['api_key'], encrypt: true);
        }

        if (array_key_exists('instance', $data)) {
            PlatformSetting::setValue(self::KEY_EVOLUTION_INSTANCE, $data['instance']);
        }

        if (array_key_exists('notify_groups', $data) && is_array($data['notify_groups'])) {
            $allowed = config('whatsapp.platform_only_groups', ['subscription']);
            $filtered = [];

            foreach ($allowed as $groupKey) {
                $filtered[$groupKey] = !empty($data['notify_groups'][$groupKey]);
            }

            PlatformSetting::setValue(
                self::KEY_WHATSAPP_NOTIFY_GROUPS,
                json_encode($filtered)
            );
        }
    }
}
