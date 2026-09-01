<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePlatformAsaasSettingsRequest;
use App\Http\Requests\UpdatePlatformWhatsAppSettingsRequest;
use App\Services\EvolutionApiService;
use App\Services\PlatformIntegrationTestService;
use App\Services\PlatformSettingsService;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\Request;

class PlatformSettingsController extends Controller
{
    public function __construct(
        private PlatformSettingsService $settings,
        private PlatformIntegrationTestService $integrationTests,
        private EvolutionApiService $evolution,
        private WhatsAppNotificationService $whatsapp,
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

    public function whatsapp()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $config = $this->settings->getWhatsAppConfig();
        $maskedKey = $config['api_key']
            ? str_repeat('•', max(strlen($config['api_key']) - 4, 8)) . substr($config['api_key'], -4)
            : null;

        return view('platform.settings.whatsapp', [
            'config' => $config,
            'maskedKey' => $maskedKey,
            'groups' => $this->whatsapp->platformGroupsForUi(),
        ]);
    }

    public function updateWhatsapp(UpdatePlatformWhatsAppSettingsRequest $request)
    {
        $this->settings->updateWhatsAppSettings([
            'enabled' => $request->boolean('enabled'),
            'api_url' => $request->input('api_url'),
            'api_key' => $request->input('api_key'),
            'instance' => $request->input('instance'),
            'notify_groups' => $request->input('notify_groups', []),
        ]);

        return redirect()
            ->route('platform.settings.whatsapp')
            ->with('success', 'Configurações do WhatsApp salvas com sucesso.');
    }

    public function testWhatsapp(Request $request)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $request->validate([
            'test_phone' => ['nullable', 'string', 'max:30'],
        ]);

        $connection = $this->evolution->connectionState();

        $testPhone = $request->input('test_phone') ?: auth()->user()?->phone;
        $testSend = null;

        if ($connection['ok'] && filled($testPhone)) {
            $message = $this->whatsapp->formatMessage(
                'Teste CondoCenter',
                'Esta é uma mensagem de teste da integração WhatsApp via Evolution API.'
            );
            $testSend = $this->evolution->sendText($testPhone, $message);
        }

        return response()->json([
            'connection' => $connection,
            'test_send' => $testSend,
            'configured' => $this->settings->isWhatsAppConfigured(),
            'enabled' => $this->settings->isWhatsAppEnabled(),
        ]);
    }
}
