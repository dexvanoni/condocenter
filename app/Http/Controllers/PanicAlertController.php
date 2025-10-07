<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Jobs\SendPanicAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PanicAlertController extends Controller
{
    /**
     * Envia alerta de pânico
     */
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'alert_type' => 'required|in:fire,lost_child,flood,robbery,police,domestic_violence,ambulance',
            'additional_info' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        
        // Mapear tipos de alerta
        $alertTypes = [
            'fire' => '🔥 INCÊNDIO',
            'lost_child' => '👶 CRIANÇA PERDIDA',
            'flood' => '🌊 ENCHENTE',
            'robbery' => '🚨 ROUBO/FURTO',
            'police' => '🚓 CHAMEM A POLÍCIA',
            'domestic_violence' => '⚠️ VIOLÊNCIA DOMÉSTICA',
            'ambulance' => '🚑 CHAMEM UMA AMBULÂNCIA',
        ];

        $alertTitle = $alertTypes[$request->alert_type] ?? 'EMERGÊNCIA';

        // Obter IP do dispositivo
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();

        // Criar mensagem de pânico
        $message = Message::create([
            'condominium_id' => $user->condominium_id,
            'from_user_id' => $user->id,
            'to_user_id' => null, // null = para TODOS
            'type' => 'panic_alert',
            'subject' => "ALERTA DE PÂNICO: {$alertTitle}",
            'message' => $this->buildAlertMessage($user, $alertTitle, $request->additional_info),
            'priority' => 'urgent',
            'related_item_type' => 'panic_alert',
        ]);

        // Dados completos do alerta
        $alertData = [
            'alert_type' => $request->alert_type,
            'alert_title' => $alertTitle,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_unit' => $user->unit?->full_identifier ?? 'N/A',
            'user_phone' => $user->phone,
            'timestamp' => now()->format('d/m/Y H:i:s'),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'additional_info' => $request->additional_info,
            'condominium_id' => $user->condominium_id,
            'condominium_name' => $user->condominium->name,
        ];

        // Despachar job para enviar alerta para TODOS
        SendPanicAlert::dispatch($alertData, $message);

        return response()->json([
            'message' => 'Alerta de pânico enviado! Todos os moradores e a administração foram notificados.',
            'alert_id' => $message->id,
            'timestamp' => now()->toISOString(),
        ], 201);
    }

    /**
     * Constrói mensagem detalhada do alerta
     */
    protected function buildAlertMessage($user, $alertTitle, $additionalInfo)
    {
        $message = "⚠️⚠️⚠️ ALERTA DE EMERGÊNCIA ⚠️⚠️⚠️\n\n";
        $message .= "Tipo: {$alertTitle}\n\n";
        $message .= "Enviado por: {$user->name}\n";
        $message .= "Unidade: " . ($user->unit?->full_identifier ?? 'N/A') . "\n";
        $message .= "Telefone: {$user->phone}\n";
        $message .= "Data/Hora: " . now()->format('d/m/Y H:i:s') . "\n\n";
        
        if ($additionalInfo) {
            $message .= "Informações Adicionais:\n{$additionalInfo}\n\n";
        }
        
        $message .= "ATENÇÃO: Esta é uma situação de emergência. Tome as medidas necessárias imediatamente!";
        
        return $message;
    }
}
