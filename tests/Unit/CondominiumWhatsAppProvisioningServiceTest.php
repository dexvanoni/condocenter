<?php

namespace Tests\Unit;

use App\Models\Condominium;
use App\Services\CondominiumWhatsAppProvisioningService;
use App\Services\CondominiumWhatsAppSettingsService;
use App\Services\EvolutionApiService;
use App\Services\EvolutionInstanceService;
use App\Services\PlatformSettingsService;
use Tests\TestCase;

class CondominiumWhatsAppProvisioningServiceTest extends TestCase
{
    public function test_connect_fails_without_global_api_key_when_unconfigured(): void
    {
        $condominium = $this->condominiumStub(id: 1, configured: false);

        $platform = $this->createMock(PlatformSettingsService::class);
        $platform->method('hasEvolutionGlobalApiKey')->willReturn(false);

        $settings = $this->createMock(CondominiumWhatsAppSettingsService::class);
        $settings->method('isConfigured')->willReturn(false);

        $service = new CondominiumWhatsAppProvisioningService(
            $settings,
            $this->createMock(EvolutionInstanceService::class),
            $this->createMock(EvolutionApiService::class),
            $platform,
        );

        $result = $service->connect($condominium);

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('API Key global', $result['message']);
    }

    public function test_connect_returns_connected_when_instance_is_open(): void
    {
        $condominium = $this->condominiumStub(id: 2, configured: true, instance: 'existing-instance');

        $settings = $this->createMock(CondominiumWhatsAppSettingsService::class);
        $settings->method('isConfigured')->willReturn(true);

        $evolution = $this->createMock(EvolutionApiService::class);
        $evolution->method('connectionState')->willReturn([
            'ok' => true,
            'state' => 'open',
            'message' => 'Instância conectada ao WhatsApp.',
        ]);

        $instances = $this->createMock(EvolutionInstanceService::class);
        $instances->expects($this->never())->method('createInstance');

        $service = new CondominiumWhatsAppProvisioningService(
            $settings,
            $instances,
            $evolution,
            $this->createMock(PlatformSettingsService::class),
        );

        $result = $service->connect($condominium);

        $this->assertTrue($result['ok']);
        $this->assertTrue($result['connected']);
    }

    public function test_connect_creates_instance_when_not_configured(): void
    {
        $condominium = $this->condominiumStub(id: 3, configured: false);

        $settings = $this->createMock(CondominiumWhatsAppSettingsService::class);
        $settings->method('isConfigured')->willReturn(false);
        $settings->expects($this->once())->method('updateSettings');

        $platform = $this->createMock(PlatformSettingsService::class);
        $platform->method('hasEvolutionGlobalApiKey')->willReturn(true);
        $platform->method('getEvolutionProvisioningConfig')->willReturn([
            'api_url' => 'http://evolution.test',
            'global_api_key' => 'global-key',
            'instance_prefix' => 'sindcon',
            'timeout' => 15,
        ]);

        $instances = $this->createMock(EvolutionInstanceService::class);
        $instances->method('instanceNameForCondominium')->willReturn('sindcon-testing-condo-3');
        $instances->method('createInstance')->willReturn([
            'ok' => true,
            'message' => 'Instância criada.',
            'instance_api_key' => 'new-key',
            'qrcode_base64' => 'qr-image',
        ]);

        $evolution = $this->createMock(EvolutionApiService::class);
        $evolution->method('connectionState')->willReturn([
            'ok' => false,
            'state' => 'close',
            'message' => 'desconectada',
        ]);

        $service = new CondominiumWhatsAppProvisioningService(
            $settings,
            $instances,
            $evolution,
            $platform,
        );

        $result = $service->connect($condominium);

        $this->assertTrue($result['ok']);
        $this->assertSame('qr-image', $result['qrcode_base64']);
    }

    private function condominiumStub(int $id, bool $configured, ?string $instance = null): Condominium
    {
        $condominium = new Condominium;
        $condominium->id = $id;
        $condominium->exists = true;
        $condominium->evolution_instance = $instance;
        $condominium->evolution_api_url = $configured ? 'http://evolution.test' : null;
        $condominium->evolution_api_key = $configured ? 'key' : null;

        return $condominium;
    }
}
