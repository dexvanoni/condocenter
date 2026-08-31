<?php

namespace App\Http\Controllers;

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
     */
    public function asaas(Request $request)
    {
        try {
            Log::info('Webhook Asaas (condomínio) recebido', $request->all());

            $result = $this->asaasService->processWebhook($request->all());

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
     * Webhook Asaas — assinaturas SaaS da plataforma.
     */
    public function asaasPlatform(Request $request)
    {
        if (!$this->validatePlatformWebhookToken($request)) {
            return response()->json(['status' => 'unauthorized'], 401);
        }

        try {
            Log::info('Webhook Asaas (plataforma) recebido', $request->all());

            $handled = $this->subscriptionService->handlePlatformWebhook($request->all());

            return response()->json(['status' => $handled ? 'success' : 'ignored'], 200);
        } catch (\Exception $e) {
            Log::error('Erro webhook Asaas plataforma: ' . $e->getMessage());

            return response()->json(['status' => 'error'], 500);
        }
    }

    protected function validatePlatformWebhookToken(Request $request): bool
    {
        $expected = $this->platformSettings->getAsaasConfig()['webhook_token'] ?? null;

        if (!$expected) {
            return true;
        }

        $provided = $request->header('asaas-access-token')
            ?: $request->input('accessToken');

        return hash_equals((string) $expected, (string) $provided);
    }
}
