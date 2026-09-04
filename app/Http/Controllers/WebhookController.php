<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use App\Models\Condominium;
use App\Services\AsaasService;
use App\Services\CondominiumSubscriptionService;
use App\Services\PlatformSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        protected AsaasService $asaasService,
        protected CondominiumSubscriptionService $subscriptionService,
        protected PlatformSettingsService $platformSettings,
    ) {}

    /**
     * Webhook Asaas — cobranças internas do condomínio (taxas/reservas).
     * Mantido para compatibilidade com integrações legadas.
     */
    public function asaas(Request $request)
    {
        if (!$this->authorizeLegacyWebhook($request)) {
            return response()->json(['status' => 'unauthorized'], 401);
        }

        try {
            $paymentId = data_get($request->all(), 'payment.id');

            Log::info('Webhook Asaas (condomínio legado) recebido', [
                'event' => $request->input('event'),
                'payment_id' => $paymentId,
            ]);

            $condominiumId = null;

            if ($paymentId) {
                $condominiumId = Charge::where('asaas_payment_id', $paymentId)->value('condominium_id');
            }

            $service = $condominiumId
                ? $this->asaasService->forCondominium((int) $condominiumId)
                : $this->asaasService;

            $result = $service->processWebhook($request->all());

            if ($result) {
                return response()->json(['status' => 'success'], 200);
            }

            return response()->json(['status' => 'ignored'], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook Asaas: ' . $e->getMessage());

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Webhook Asaas — cobranças do condomínio (conta Asaas do síndico).
     */
    public function asaasCondominium(Request $request, Condominium $condominium)
    {
        if (!$this->validateCondominiumWebhookToken($request, $condominium)) {
            return response()->json(['status' => 'unauthorized'], 401);
        }

        try {
            Log::info('Webhook Asaas (condomínio) recebido', [
                'condominium_id' => $condominium->id,
                'event' => $request->input('event'),
            ]);

            $result = $this->asaasService
                ->forCondominium((int) $condominium->id)
                ->processWebhook($request->all());

            return response()->json(['status' => $result ? 'success' : 'ignored'], 200);
        } catch (\Exception $e) {
            Log::error('Erro webhook Asaas condomínio: ' . $e->getMessage(), [
                'condominium_id' => $condominium->id,
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Webhook Asaas — assinaturas SaaS da plataforma.
     */
    public function asaasPlatform(Request $request)
    {
        if (!$this->validatePlatformWebhookToken($request)) {
            return response()->json(['status' => 'unauthorized'], 401);
        }

        try {
            Log::info('Webhook Asaas (plataforma) recebido', [
                'event' => $request->input('event'),
            ]);

            $handled = $this->subscriptionService->handlePlatformWebhook($request->all());

            return response()->json(['status' => $handled ? 'success' : 'ignored'], 200);
        } catch (\Exception $e) {
            Log::error('Erro webhook Asaas plataforma: ' . $e->getMessage());

            return response()->json(['status' => 'error'], 500);
        }
    }

    protected function authorizeLegacyWebhook(Request $request): bool
    {
        $paymentId = data_get($request->all(), 'payment.id');

        if ($paymentId) {
            $condominiumId = Charge::where('asaas_payment_id', $paymentId)->value('condominium_id');

            if ($condominiumId) {
                $condominium = Condominium::find($condominiumId);

                return $condominium
                    && $this->validateCondominiumWebhookToken($request, $condominium);
            }
        }

        return $this->validatePlatformWebhookToken($request);
    }

    protected function validatePlatformWebhookToken(Request $request): bool
    {
        $expected = $this->platformSettings->getAsaasConfig()['webhook_token'] ?? null;

        return $this->validateWebhookToken($request, $expected, 'plataforma');
    }

    protected function validateCondominiumWebhookToken(Request $request, Condominium $condominium): bool
    {
        return $this->validateWebhookToken($request, $condominium->asaas_webhook_token, 'condomínio');
    }

    protected function validateWebhookToken(Request $request, ?string $expected, string $context): bool
    {
        if (!$expected) {
            if (app()->environment('local', 'testing')) {
                Log::warning("Webhook Asaas ({$context}) aceito sem token — permitido apenas em ambiente local/testing.");

                return true;
            }

            Log::error("Webhook Asaas ({$context}) rejeitado: token não configurado.");

            return false;
        }

        $provided = $request->header('asaas-access-token')
            ?: $request->input('accessToken');

        return hash_equals((string) $expected, (string) $provided);
    }
}
