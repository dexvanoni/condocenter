<?php

namespace Tests\Unit;

use App\Models\Condominium;
use App\Services\EvolutionInstanceService;
use App\Services\PlatformSettingsService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EvolutionInstanceServiceTest extends TestCase
{
    public function test_create_instance_returns_hash_and_qrcode(): void
    {
        Http::fake([
            'evolution.test/instance/create' => Http::response([
                'hash' => 'instance-secret-key',
                'qrcode' => ['base64' => 'abc123qr'],
            ], 201),
        ]);

        $service = $this->makeService();
        $result = $service->createInstance('sindcon-testing-condo-9');

        $this->assertTrue($result['ok']);
        $this->assertSame('instance-secret-key', $result['instance_api_key']);
        $this->assertSame('abc123qr', $result['qrcode_base64']);

        Http::assertSent(function ($request) {
            return $request->url() === 'http://evolution.test/instance/create'
                && $request['instanceName'] === 'sindcon-testing-condo-9'
                && $request['integration'] === 'WHATSAPP-BAILEYS'
                && $request->hasHeader('apikey', 'global-key');
        });
    }

    public function test_fetch_qr_code_from_connect_endpoint(): void
    {
        Http::fake([
            'evolution.test/instance/connect/my-instance' => Http::response([
                'base64' => 'qr-data',
            ], 200),
        ]);

        $service = $this->makeService();
        $result = $service->fetchQrCode([
            'api_url' => 'http://evolution.test',
            'api_key' => 'inst-key',
            'instance' => 'my-instance',
        ]);

        $this->assertTrue($result['ok']);
        $this->assertSame('qr-data', $result['qrcode_base64']);
    }

    public function test_logout_calls_delete_endpoint(): void
    {
        Http::fake([
            'evolution.test/instance/logout/my-instance' => Http::response(['status' => 'SUCCESS'], 200),
        ]);

        $service = $this->makeService();
        $result = $service->logout([
            'api_url' => 'http://evolution.test',
            'api_key' => 'inst-key',
            'instance' => 'my-instance',
        ]);

        $this->assertTrue($result['ok']);
        Http::assertSent(fn ($request) => $request->method() === 'DELETE');
    }

    public function test_instance_name_includes_condominium_id(): void
    {
        $condominium = new Condominium;
        $condominium->id = 42;
        $service = $this->makeService();

        $name = $service->instanceNameForCondominium($condominium);

        $this->assertStringContainsString('condo-42', $name);
    }

    private function makeService(): EvolutionInstanceService
    {
        $platform = $this->createMock(PlatformSettingsService::class);
        $platform->method('getEvolutionProvisioningConfig')->willReturn([
            'api_url' => 'http://evolution.test',
            'global_api_key' => 'global-key',
            'instance_prefix' => 'sindcon',
            'timeout' => 15,
        ]);

        return new EvolutionInstanceService($platform);
    }
}
