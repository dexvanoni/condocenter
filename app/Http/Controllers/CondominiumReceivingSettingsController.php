<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCondominiumAsaasSettingsRequest;
use App\Http\Requests\UpdatePaymentReceivingModeRequest;
use App\Models\Condominium;
use App\Services\CondominiumAsaasIntegrationTestService;
use App\Services\CondominiumAsaasSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CondominiumReceivingSettingsController extends Controller
{
    public function __construct(
        private CondominiumAsaasSettingsService $settings,
        private CondominiumAsaasIntegrationTestService $integrationTest,
    ) {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            $condominium = $request->route('condominium');

            if ($user?->isAdmin()) {
                return $next($request);
            }

            if ($user?->isSindico()
                && $condominium instanceof Condominium
                && (int) $user->tenantCondominiumId() === (int) $condominium->id) {
                return $next($request);
            }

            abort(403, 'Acesso negado. Somente administradores ou síndicos podem configurar o recebimento.');
        });
    }

    public function index(Condominium $condominium)
    {
        $config = $this->settings->getConfig($condominium);
        $maskedKey = $config['api_key']
            ? str_repeat('•', max(strlen($config['api_key']) - 4, 8)) . substr($config['api_key'], -4)
            : null;

        $maskedToken = $config['webhook_token']
            ? str_repeat('•', 12) . substr($config['webhook_token'], -6)
            : null;

        return view('finance.receiving.index', [
            'condominium' => $condominium,
            'config' => $config,
            'maskedKey' => $maskedKey,
            'maskedToken' => $maskedToken,
            'progress' => $this->settings->setupProgress($condominium),
            'webhookUrl' => $this->settings->webhookUrl($condominium),
            'asaasPanelUrl' => $this->settings->asaasPanelUrl($condominium),
            'asaasSignupUrl' => $this->settings->asaasSignupUrl($condominium),
        ]);
    }

    public function updateMode(UpdatePaymentReceivingModeRequest $request, Condominium $condominium)
    {
        $this->settings->updateReceivingMode($condominium, $request->input('payment_receiving_mode'));

        $message = $request->input('payment_receiving_mode') === 'platform'
            ? 'Modo alterado: recebimentos pelo SindCON via Asaas. Continue o assistente de configuração.'
            : 'Modo alterado: controle manual dos recebimentos fora da plataforma.';

        return redirect()
            ->route('condominiums.settings.receiving', $condominium)
            ->with('success', $message);
    }

    public function updateCredentials(UpdateCondominiumAsaasSettingsRequest $request, Condominium $condominium)
    {
        if (!$this->settings->isPlatformReceiving($condominium)) {
            return redirect()
                ->route('condominiums.settings.receiving', $condominium)
                ->with('error', 'Ative o modo "Receber pelo SindCON" antes de configurar o Asaas.');
        }

        $data = [
            'sandbox' => $request->boolean('sandbox'),
            'webhook_email' => $request->input('webhook_email'),
            'api_key' => $request->input('api_key'),
        ];

        if ($request->boolean('regenerate_webhook_token')) {
            $data['webhook_token'] = Str::random(48);
        } elseif ($request->filled('webhook_token')) {
            $data['webhook_token'] = $request->input('webhook_token');
        }

        $this->settings->updateCredentials($condominium, $data);
        $this->settings->resetSetup($condominium->fresh());

        return redirect()
            ->route('condominiums.settings.receiving', $condominium)
            ->with('success', 'Credenciais Asaas salvas. Configure o webhook e execute os testes.');
    }

    public function test(Request $request, Condominium $condominium)
    {
        if (!$this->settings->isPlatformReceiving($condominium)) {
            return response()->json([
                'ok' => false,
                'message' => 'O condomínio não está no modo de recebimento pela plataforma.',
            ], 422);
        }

        return response()->json($this->integrationTest->runAll($condominium->fresh()));
    }

    public function completeSetup(Request $request, Condominium $condominium)
    {
        if (!$this->settings->isPlatformReceiving($condominium)) {
            return redirect()
                ->route('condominiums.settings.receiving', $condominium)
                ->with('error', 'Ative o recebimento pela plataforma antes de concluir.');
        }

        if (!$this->settings->isConfigured($condominium)) {
            return redirect()
                ->route('condominiums.settings.receiving', $condominium)
                ->with('error', 'Informe a API Key do Asaas antes de concluir a configuração.');
        }

        $results = $this->integrationTest->runAll($condominium->fresh());

        if (!($results['asaas']['ok'] ?? false)) {
            return redirect()
                ->route('condominiums.settings.receiving', $condominium)
                ->with('error', 'Teste da API Asaas falhou: ' . ($results['asaas']['message'] ?? 'erro desconhecido'));
        }

        if (!($results['webhook']['ok'] ?? false)) {
            return redirect()
                ->route('condominiums.settings.receiving', $condominium)
                ->with('error', 'Teste do webhook falhou: ' . ($results['webhook']['message'] ?? 'erro desconhecido'));
        }

        $this->settings->markSetupCompleted($condominium->fresh());

        return redirect()
            ->route('condominiums.settings.receiving', $condominium)
            ->with('success', 'Recebimento online ativado! Moradores poderão pagar taxas, multas e reservas por PIX e cartão.');
    }
}
