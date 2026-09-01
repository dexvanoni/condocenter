<?php

namespace App\Services;

use App\Models\Condominium;

class CondominiumWhatsAppSettingsService
{
    public function __construct(
        private PlatformSettingsService $platformSettings,
    ) {}

    public function getConfig(Condominium $condominium): array
    {
        return [
            'enabled' => (bool) $condominium->whatsapp_enabled,
            'api_url' => $condominium->evolution_api_url,
            'api_key' => $condominium->evolution_api_key,
            'instance' => $condominium->evolution_instance,
            'default_country_code' => config('whatsapp.default_country_code', '55'),
            'timeout' => (int) config('whatsapp.timeout', 15),
            'configured_in_db' => filled($condominium->evolution_api_url)
                || filled($condominium->evolution_api_key)
                || filled($condominium->evolution_instance),
        ];
    }

    public function getEnabledGroups(Condominium $condominium): array
    {
        $groups = $condominium->whatsapp_notify_groups;

        return is_array($groups) ? $groups : [];
    }

    public function isEnabled(Condominium $condominium): bool
    {
        return (bool) $condominium->whatsapp_enabled;
    }

    public function isConfigured(Condominium $condominium): bool
    {
        $config = $this->getConfig($condominium);

        return filled($config['api_url'])
            && filled($config['api_key'])
            && filled($config['instance']);
    }

    public function isGroupEnabled(Condominium $condominium, string $group): bool
    {
        if (!$this->isEnabled($condominium) || !$this->isConfigured($condominium)) {
            return false;
        }

        $groups = $this->getEnabledGroups($condominium);

        return (bool) ($groups[$group] ?? false);
    }

    public function isTypeEnabled(Condominium $condominium, string $type): bool
    {
        $group = $this->platformSettings->resolveWhatsAppGroupForType($type);

        if (!$group || $this->isPlatformOnlyGroup($group)) {
            return false;
        }

        return $this->isGroupEnabled($condominium, $group);
    }

    public function isPlatformOnlyGroup(string $group): bool
    {
        return in_array($group, config('whatsapp.platform_only_groups', ['subscription']), true);
    }

    public function condominiumGroupKeys(): array
    {
        $platformOnly = config('whatsapp.platform_only_groups', ['subscription']);

        return array_values(array_diff(array_keys(config('whatsapp.groups', [])), $platformOnly));
    }

    public function groupsForUi(Condominium $condominium): array
    {
        $groups = config('whatsapp.groups', []);
        $enabled = $this->getEnabledGroups($condominium);
        $keys = $this->condominiumGroupKeys();

        return collect($keys)
            ->map(function (string $key) use ($groups, $enabled) {
                $group = $groups[$key] ?? [];

                return array_merge($group, [
                    'key' => $key,
                    'enabled' => (bool) ($enabled[$key] ?? false),
                ]);
            })
            ->values()
            ->all();
    }

    public function updateSettings(Condominium $condominium, array $data): void
    {
        $payload = [];

        if (array_key_exists('enabled', $data)) {
            $payload['whatsapp_enabled'] = (bool) $data['enabled'];
        }

        if (array_key_exists('api_url', $data)) {
            $payload['evolution_api_url'] = $data['api_url'];
        }

        if (array_key_exists('api_key', $data) && filled($data['api_key'])) {
            $payload['evolution_api_key'] = $data['api_key'];
        }

        if (array_key_exists('instance', $data)) {
            $payload['evolution_instance'] = $data['instance'];
        }

        if (array_key_exists('notify_groups', $data) && is_array($data['notify_groups'])) {
            $filtered = [];

            foreach ($this->condominiumGroupKeys() as $groupKey) {
                $filtered[$groupKey] = !empty($data['notify_groups'][$groupKey]);
            }

            $payload['whatsapp_notify_groups'] = $filtered;
        }

        if ($payload !== []) {
            $condominium->update($payload);
        }
    }
}
