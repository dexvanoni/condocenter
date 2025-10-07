<?php

namespace App\Http\Controllers;

use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected $asaasService;

    public function __construct(AsaasService $asaasService)
    {
        $this->asaasService = $asaasService;
    }

    /**
     * Handle Asaas webhook
     */
    public function asaas(Request $request)
    {
        try {
            Log::info('Webhook Asaas recebido', $request->all());

            $payload = $request->all();

            // Processa o webhook
            $result = $this->asaasService->processWebhook($payload);

            if ($result) {
                return response()->json(['status' => 'success'], 200);
            }

            return response()->json(['status' => 'error', 'message' => 'Failed to process webhook'], 400);
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook Asaas: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
