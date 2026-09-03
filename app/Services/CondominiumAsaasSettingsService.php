<?php

namespace App\Services;

use App\Models\Condominium;
use Illuminate\Support\Str;

class CondominiumAsaasSettingsService
{
    public function getReceivingMode(Condominium $condominium): string
    {
        return $condominium->payment_receiving_mode === 'platform' ? 'platform' : 'manual';
    }

    public function isPlatformReceiving(Condominium $condominium): bool
    {
        return $this->getReceivingMode($condominium) === 'platform';
    }

    public function isManualReceiving(Condominium $condominium): bool
    {
        return !$this->isPlatformReceiving($condominium);
    }

    public function getConfig(Condominium $condominium): array
    {
        return [
            'receiving_mode' => $this->getReceivingMode($condominium),
            'api_key' => $condominium->asaas_api_key,
            'sandbox' => (bool) $condominium->asaas_sandbox,
            'webhook_email' => $condominium->asaas_webhook_email,
            'webhook_token' => $condominium->asaas_webhook_token,
            'setup_completed_at' => $condominium->asaas_setup_completed_at,
            'configured_in_db' => filled($condominium->asaas_api_key),
        ];
    }

    public function getApiConfig(Condominium $condominium): array
    {
        $sandbox = (bool) $condominium->asaas_sandbox;

        return [
            'api_key' => $condominium->asaas_api_key,
            'sandbox' => $sandbox,
            'api_url' => $sandbox
                ? 'https://sandbox.asaas.com/api/v3'
                : 'https://www.asaas.com/api/v3',
            'webhook_email' => $condominium->asaas_webhook_email,
            'webhook_token' => $condominium->asaas_webhook_token,
        ];
    }

    public function isConfigured(Condominium $condominium): bool
    {
        return filled($condominium->asaas_api_key);
    }

    public function isSetupCompleted(Condominium $condominium): bool
    {
        return $this->isPlatformReceiving($condominium)
            && $this->isConfigured($condominium)
            && filled($condominium->asaas_setup_completed_at);
    }

    public function acceptsOnlinePayments(Condominium $condominium): bool
    {
        return $this->isSetupCompleted($condominium);
    }

    public function webhookUrl(Condominium $condominium): string
    {
        return rtrim(config('app.url'), '/')
            . '/webhooks/asaas/condominium/' . $condominium->id;
    }

    public function asaasPanelUrl(Condominium $condominium): string
    {
        return $condominium->asaas_sandbox
            ? 'https://sandbox.asaas.com'
            : 'https://www.asaas.com';
    }

    public function asaasSignupUrl(Condominium $condominium): string
    {
        return $condominium->asaas_sandbox
            ? 'https://sandbox.asaas.com/onboarding/createAccount'
            : 'https://www.asaas.com/onboarding/createAccount';
    }

    public function setupProgress(Condominium $condominium): array
    {
        $modeChosen = $this->isPlatformReceiving($condominium);
        $credentialsSaved = $this->isConfigured($condominium);
        $webhookToken = filled($condominium->asaas_webhook_token);
        $setupCompleted = $this->isSetupCompleted($condominium);

        $steps = [
            ['key' => 'mode', 'label' => 'Modo de recebimento', 'done' => true],
            ['key' => 'account', 'label' => 'Conta Asaas', 'done' => $modeChosen],
            ['key' => 'credentials', 'label' => 'API Key', 'done' => $credentialsSaved],
            ['key' => 'webhook', 'label' => 'Webhook', 'done' => $webhookToken],
            ['key' => 'test', 'label' => 'Testes', 'done' => $setupCompleted],
        ];

        $completed = collect($steps)->where('done', true)->count();
        $total = count($steps);

        return [
            'steps' => $steps,
            'percent' => (int) round(($completed / max($total, 1)) * 100),
            'current_step' => $this->resolveCurrentStep($condominium, $steps),
        ];
    }

    public function updateReceivingMode(Condominium $condominium, string $mode): void
    {
        $mode = $mode === 'platform' ? 'platform' : 'manual';

        $payload = ['payment_receiving_mode' => $mode];

        if ($mode === 'manual') {
            $payload['asaas_setup_completed_at'] = null;
        }

        $condominium->update($payload);
    }

    public function updateCredentials(Condominium $condominium, array $data): void
    {
        $payload = [];

        if (array_key_exists('sandbox', $data)) {
            $payload['asaas_sandbox'] = (bool) $data['sandbox'];
        }

        if (array_key_exists('webhook_email', $data)) {
            $payload['asaas_webhook_email'] = $data['webhook_email'] ?: null;
        }

        if (array_key_exists('api_key', $data) && filled($data['api_key'])) {
            $payload['asaas_api_key'] = $data['api_key'];
        }

        if (array_key_exists('webhook_token', $data) && filled($data['webhook_token'])) {
            $payload['asaas_webhook_token'] = $data['webhook_token'];
        } elseif (!filled($condominium->asaas_webhook_token)) {
            $payload['asaas_webhook_token'] = Str::random(48);
        }

        if ($payload !== []) {
            $condominium->update($payload);
        }
    }

    public function markSetupCompleted(Condominium $condominium): void
    {
        $condominium->update([
            'asaas_setup_completed_at' => now(),
        ]);
    }

    public function resetSetup(Condominium $condominium): void
    {
        $condominium->update([
            'asaas_setup_completed_at' => null,
        ]);
    }

    protected function resolveCurrentStep(Condominium $condominium, array $steps): string
    {
        if ($this->isManualReceiving($condominium)) {
            return 'mode';
        }

        if (!$this->isConfigured($condominium)) {
            return 'credentials';
        }

        if (!filled($condominium->asaas_webhook_token)) {
            return 'webhook';
        }

        if (!$this->isSetupCompleted($condominium)) {
            return 'test';
        }

        return 'done';
    }
}
