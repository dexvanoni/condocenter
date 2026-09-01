<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCondominiumWhatsAppSettingsRequest;
use App\Models\Condominium;
use App\Services\CondominiumWhatsAppSettingsService;
use App\Services\EvolutionApiService;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\Request;

class CondominiumWhatsAppSettingsController extends Controller
{
    public function __construct(
        private CondominiumWhatsAppSettingsService $settings,
        private EvolutionApiService $evolution,
        private WhatsAppNotificationService $whatsapp,
    ) {
        $this->middleware(function ($request, $next) {
            if (!$request->user()?->isAdmin()) {
                abort(403, 'Acesso negado. Somente administradores podem configurar o WhatsApp.');
            }

            return $next($request);
        });
    }

    public function index(Condominium $condominium)
    {
        $config = $this->settings->getConfig($condominium);
        $maskedKey = $config['api_key']
            ? str_repeat('•', max(strlen($config['api_key']) - 4, 8)) . substr($config['api_key'], -4)
            : null;

        return view('settings.whatsapp', [
            'condominium' => $condominium,
            'config' => $config,
            'maskedKey' => $maskedKey,
            'groups' => $this->settings->groupsForUi($condominium),
        ]);
    }

    public function update(UpdateCondominiumWhatsAppSettingsRequest $request, Condominium $condominium)
    {
        $this->settings->updateSettings($condominium, [
            'enabled' => $request->boolean('enabled'),
            'api_url' => $request->input('api_url'),
            'api_key' => $request->input('api_key'),
            'instance' => $request->input('instance'),
            'notify_groups' => $request->input('notify_groups', []),
        ]);

        return redirect()
            ->route('condominiums.settings.whatsapp', $condominium)
            ->with('success', 'Configurações do WhatsApp salvas com sucesso.');
    }

    public function test(Request $request, Condominium $condominium)
    {
        $request->validate([
            'test_phone' => ['nullable', 'string', 'max:30'],
        ]);

        $connection = $this->evolution->connectionState($condominium->id);

        $testPhone = $request->input('test_phone') ?: $request->user()?->phone;
        $testSend = null;

        if ($connection['ok'] && filled($testPhone)) {
            $message = $this->whatsapp->formatMessage(
                'Teste CondoCenter',
                "Esta é uma mensagem de teste da integração WhatsApp do condomínio {$condominium->name}."
            );
            $testSend = $this->evolution->sendText($testPhone, $message, $condominium->id);
        }

        return response()->json([
            'connection' => $connection,
            'test_send' => $testSend,
            'configured' => $this->settings->isConfigured($condominium),
            'enabled' => $this->settings->isEnabled($condominium),
        ]);
    }
}
