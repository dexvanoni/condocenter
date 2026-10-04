<?php

namespace App\Services;

use App\Models\Condominium;

class CondominiumWhatsAppProvisioningService
{
    public function __construct(
        private CondominiumWhatsAppSettingsService $condominiumSettings,
        private EvolutionInstanceService $instances,
        private EvolutionApiService $evolution,
        private PlatformSettingsService $platformSettings,
    ) {}

    /**
     * @return array{ok: bool, message: string, connected?: bool, instance?: string|null, qrcode_base64?: string|null, connection?: array}
     */
    public function connect(Condominium $condominium): array
    {
        if ($this->condominiumSettings->isConfigured($condominium)) {
            $connection = $this->evolution->connectionState($condominium->id);

            if ($connection['ok']) {
                return [
                    'ok' => true,
                    'connected' => true,
                    'message' => $connection['message'] ?? 'Instância conectada ao WhatsApp.',
                    'instance' => $condominium->evolution_instance,
                    'connection' => $connection,
                ];
            }

            if (!filled($condominium->evolution_api_key)) {
                return [
                    'ok' => false,
                    'message' => 'Este condomínio já tem nome de instância cadastrado, mas falta a API Key no sistema. Peça ao administrador da plataforma para completar o cadastro em Modo avançado ou recriar a instância.',
                    'instance' => $condominium->evolution_instance,
                    'connection' => $connection,
                ];
            }

            $config = $this->condominiumSettings->getConfig($condominium);
            $qr = $this->instances->fetchQrCode([
                'api_url' => $config['api_url'],
                'api_key' => $config['api_key'],
                'instance' => $config['instance'],
                'timeout' => $config['timeout'],
            ]);

            return array_merge($qr, [
                'connected' => false,
                'instance' => $config['instance'],
                'connection' => $connection,
            ]);
        }

        if (!$this->platformSettings->hasEvolutionGlobalApiKey()) {
            return [
                'ok' => false,
                'message' => 'A plataforma ainda não configurou a API Key global da Evolution. O administrador deve informá-la em Plataforma → WhatsApp.',
            ];
        }

        $provisioning = $this->platformSettings->getEvolutionProvisioningConfig();

        if (!filled($provisioning['api_url'])) {
            return [
                'ok' => false,
                'message' => 'URL da Evolution API não configurada na plataforma.',
            ];
        }

        $instanceName = $this->instances->instanceNameForCondominium($condominium);
        $created = $this->instances->createInstance($instanceName);

        if (!$created['ok']) {
            return $created;
        }

        $this->condominiumSettings->updateSettings($condominium, [
            'api_url' => $provisioning['api_url'],
            'instance' => $instanceName,
            'api_key' => $created['instance_api_key'],
        ]);

        if (!filled($created['qrcode_base64'])) {
            $config = $this->condominiumSettings->getConfig($condominium);
            $qr = $this->instances->fetchQrCode([
                'api_url' => $config['api_url'],
                'api_key' => $config['api_key'],
                'instance' => $config['instance'],
                'timeout' => $config['timeout'],
            ]);

            return array_merge($qr, [
                'connected' => false,
                'instance' => $instanceName,
                'connection' => $this->evolution->connectionState($condominium->id),
            ]);
        }

        return [
            'ok' => true,
            'connected' => false,
            'message' => $created['message'],
            'instance' => $instanceName,
            'qrcode_base64' => $created['qrcode_base64'],
            'connection' => $this->evolution->connectionState($condominium->id),
        ];
    }

    /**
     * @return array{ok: bool, message: string, connection?: array}
     */
    public function disconnect(Condominium $condominium): array
    {
        if (!$this->condominiumSettings->isConfigured($condominium)) {
            return [
                'ok' => false,
                'message' => 'Nenhuma instância configurada para este condomínio.',
            ];
        }

        $config = $this->condominiumSettings->getConfig($condominium);

        if (!filled($config['api_key'])) {
            return [
                'ok' => false,
                'message' => 'API Key da instância não encontrada. Contate o administrador da plataforma.',
            ];
        }

        $result = $this->instances->logout([
            'api_url' => $config['api_url'],
            'api_key' => $config['api_key'],
            'instance' => $config['instance'],
            'timeout' => $config['timeout'],
        ]);

        return array_merge($result, [
            'connection' => $this->evolution->connectionState($condominium->id),
        ]);
    }
}
