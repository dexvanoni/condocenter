<?php

namespace Tests\Unit;

use App\Services\CondominiumWhatsAppSettingsService;
use App\Services\EvolutionApiService;
use App\Services\PlatformSettingsService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EvolutionApiServiceTest extends TestCase
{
    public function test_send_text_skips_when_instance_is_connecting(): void
    {
        Http::fake([
            'evolution.test/instance/connectionState/CondoManager' => Http::response([
                'instance' => ['instanceName' => 'CondoManager', 'state' => 'connecting'],
            ], 200),
            'evolution.test/message/sendText/CondoManager' => Http::response(['key' => ['id' => 'should-not-send']], 201),
        ]);

        $result = $this->makeService()->sendText('67991224547', 'Teste SindCON');

        $this->assertFalse($result['ok']);
        $this->assertSame('connecting', $result['state']);
        $this->assertStringContainsString('QR Code', $result['message']);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'connectionState'));
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'sendText'));
    }

    public function test_send_text_explains_timeout_as_evolution_backend(): void
    {
        Http::fake(function ($request) {
            if (str_contains($request->url(), 'connectionState')) {
                return Http::response([
                    'instance' => ['instanceName' => 'CondoManager', 'state' => 'open'],
                ], 200);
            }

            throw new \Illuminate\Http\Client\ConnectionException(
                'cURL error 28: Operation timed out after 15000 milliseconds with 0 bytes received'
            );
        });

        $result = $this->makeService()->sendText('67991224547', 'Teste SindCON');

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('Redis', $result['message']);
        $this->assertStringNotContainsString('cURL error 28', $result['message']);
    }

    public function test_send_text_posts_when_instance_is_open(): void
    {
        Http::fake([
            'evolution.test/instance/connectionState/CondoManager' => Http::response([
                'instance' => ['instanceName' => 'CondoManager', 'state' => 'open'],
            ], 200),
            'evolution.test/message/sendText/CondoManager' => Http::response([
                'key' => ['id' => 'msg-1'],
            ], 201),
        ]);

        $result = $this->makeService()->sendText('67991224547', 'Teste SindCON');

        $this->assertTrue($result['ok']);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'sendText')
            && $request['number'] === '5567991224547'
            && $request['text'] === 'Teste SindCON');
    }

    private function makeService(): EvolutionApiService
    {
        $config = [
            'enabled' => true,
            'api_url' => 'http://evolution.test',
            'api_key' => 'test-key',
            'instance' => 'CondoManager',
            'default_country_code' => '55',
            'timeout' => 15,
        ];

        $platform = $this->createMock(PlatformSettingsService::class);
        $platform->method('getWhatsAppConfig')->willReturn($config);

        $condo = $this->createMock(CondominiumWhatsAppSettingsService::class);

        return new EvolutionApiService($platform, $condo);
    }
}
