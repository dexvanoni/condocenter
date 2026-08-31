<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePlatformAsaasSettingsRequest;
use App\Services\PlatformIntegrationTestService;
use App\Services\PlatformSettingsService;

class PlatformSettingsController extends Controller
{
    public function __construct(
        private PlatformSettingsService $settings,
        private PlatformIntegrationTestService $integrationTests,
    ) {}

    public function asaas()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $config = $this->settings->getAsaasConfig();
        $maskedKey = $config['api_key']
            ? str_repeat('•', max(strlen($config['api_key']) - 4, 8)) . substr($config['api_key'], -4)
            : null;

        return view('platform.settings.asaas', [
            'config' => $config,
            'maskedKey' => $maskedKey,
            'webhookUrl' => rtrim(config('saas.webhook_base_url', config('app.url')), '/') . '/webhooks/asaas/platform',
        ]);
    }

    public function updateAsaas(UpdatePlatformAsaasSettingsRequest $request)
    {
        $this->settings->updateAsaasSettings([
            'api_key' => $request->input('api_key'),
            'sandbox' => $request->boolean('sandbox'),
            'webhook_email' => $request->input('webhook_email'),
            'webhook_token' => $request->input('webhook_token'),
        ]);

        return redirect()
            ->route('platform.settings.asaas')
            ->with('success', 'Configurações do Asaas salvas com sucesso.');
    }

    public function testAsaas()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        return response()->json($this->integrationTests->runAll());
    }
}
