<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\PanicAlert;
use App\Models\User;
use App\Jobs\SendPanicAlert;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class PanicAlertController extends Controller
{
    /**
     * Envia alerta de pânico
     */
    public function send(Request $request)
    {
        try {
            Log::info('Iniciando envio de alerta de pânico', [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'headers' => $request->headers->all()
            ]);

            // Verificar se o usuário está autenticado
            if (!Auth::check()) {
                Log::error('Usuário não autenticado');
                return response()->json(['error' => 'Usuário não autenticado'], 401);
            }

            $validator = Validator::make($request->all(), [
                'alert_type' => 'required|in:fire,lost_child,flood,robbery,police,domestic_violence,ambulance',
                'additional_info' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                Log::error('Validação falhou', ['errors' => $validator->errors()]);
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = Auth::user();
            
            Log::info('Usuário autenticado', ['user_id' => $user->id, 'condominium_id' => $user->condominium_id]);
        
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

        // Criar alerta de pânico
        $panicAlert = PanicAlert::create([
            'condominium_id' => $user->condominium_id,
            'user_id' => $user->id,
            'alert_type' => $request->alert_type,
            'title' => $alertTitle,
            'description' => $request->additional_info ?? 'Alerta de emergência ativado',
            'location' => $user->unit?->full_identifier,
            'severity' => 'high',
            'status' => 'active',
            'metadata' => [
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'user_phone' => $user->phone,
            ]
        ]);

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
            'related_item_id' => $panicAlert->id,
        ]);

        // Dados completos do alerta
        $alertData = [
            'alert_id' => $panicAlert->id,
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

        // Enviar emails para perfis específicos (síndico, administrador, porteiro, secretaria)
        $this->sendPanicEmails($alertData);

        // Enviar notificação FCM (se habilitada)
        $this->sendFCMNotification($panicAlert, $alertData);

            Log::info('Alerta de pânico enviado com sucesso', [
                'alert_id' => $panicAlert->id,
                'user_id' => $user->id
            ]);

            return response()->json([
                'message' => 'Alerta de pânico enviado! Todos os moradores e a administração foram notificados.',
                'alert_id' => $panicAlert->id,
                'timestamp' => now()->toISOString(),
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erro ao enviar alerta de pânico', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível enviar o alerta de pânico. Tente novamente.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Verifica se há alertas de pânico ativos
     */
    public function checkActiveAlerts()
    {
        $user = Auth::user();
        
        $activeAlerts = PanicAlert::active()
            ->forCondominium($user->condominium_id)
            ->with(['user', 'condominium'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'has_active_alerts' => $activeAlerts->count() > 0,
            'alerts' => $activeAlerts,
            'alert_count' => $activeAlerts->count()
        ]);
    }

    /**
     * Resolve um alerta de pânico
     */
    public function resolve(Request $request, $id)
    {
        $user = Auth::user();
        
        $alert = PanicAlert::findOrFail($id);
        
        // Verificar se o alerta pertence ao condomínio do usuário
        if ($alert->condominium_id !== $user->condominium_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Verificar se o alerta ainda está ativo
        if (!$alert->isActive()) {
            return response()->json(['error' => 'Este alerta já foi resolvido'], 400);
        }

        // Resolver o alerta
        $alert->resolve($user);

        // Enviar notificação FCM de resolução (se habilitada)
        $this->sendFCMResolutionNotification($alert);

        return response()->json([
            'message' => 'Alerta de pânico resolvido com sucesso',
            'resolved_by' => $user->name,
            'resolved_at' => $alert->resolved_at->toISOString()
        ]);
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

    /**
     * Envia emails de alerta de pânico para perfis específicos
     */
    protected function sendPanicEmails(array $alertData): void
    {
        try {
            // Perfis que devem receber emails de alerta de pânico
            $targetRoles = ['Síndico', 'Administrador', 'Porteiro', 'Secretaria'];
            
            // Buscar usuários com os perfis específicos no mesmo condomínio
            $users = User::where('condominium_id', $alertData['condominium_id'])
                ->where('is_active', true)
                ->whereHas('roles', function ($query) use ($targetRoles) {
                    $query->whereIn('name', $targetRoles);
                })
                ->get();

            Log::info('Enviando emails de alerta de pânico', [
                'alert_id' => $alertData['alert_id'],
                'target_roles' => $targetRoles,
                'users_count' => $users->count()
            ]);

            $sentCount = 0;
            foreach ($users as $user) {
                try {
                    // Verificar se o usuário tem pelo menos um dos perfis desejados
                    $hasTargetRole = false;
                    foreach ($targetRoles as $role) {
                        if ($user->hasRole($role)) {
                            $hasTargetRole = true;
                            break;
                        }
                    }

                    if ($hasTargetRole) {
                        Mail::to($user->email)->send(
                            new \App\Mail\PanicAlertNotification($alertData)
                        );
                        
                        $sentCount++;
                        
                        Log::info("Email de alerta de pânico enviado para: {$user->name} ({$user->email})", [
                            'user_id' => $user->id,
                            'user_roles' => $user->roles->pluck('name')->toArray()
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error("Erro ao enviar email de alerta de pânico para {$user->email}: " . $e->getMessage(), [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info("Emails de alerta de pânico enviados com sucesso", [
                'alert_id' => $alertData['alert_id'],
                'total_users' => $users->count(),
                'emails_sent' => $sentCount
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao enviar emails de alerta de pânico', [
                'alert_id' => $alertData['alert_id'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Envia notificação FCM para alerta de pânico
     */
    protected function sendFCMNotification(PanicAlert $panicAlert, array $alertData): void
    {
        try {
            $firebaseService = new FirebaseNotificationService();
            
            $fcmData = [
                'alert_id' => $panicAlert->id,
                'alert_type' => $panicAlert->alert_type,
                'user_name' => $alertData['user_name'],
                'location' => $alertData['user_unit'],
                'severity' => $panicAlert->severity
            ];

            $sentCount = $firebaseService->sendPanicAlert($fcmData);
            
            Log::info('Notificação FCM de pânico enviada', [
                'alert_id' => $panicAlert->id,
                'sent_count' => $sentCount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificação FCM de pânico', [
                'alert_id' => $panicAlert->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Envia notificação FCM para resolução de alerta de pânico
     */
    protected function sendFCMResolutionNotification(PanicAlert $panicAlert): void
    {
        try {
            $firebaseService = new FirebaseNotificationService();
            
            $fcmData = [
                'alert_id' => $panicAlert->id,
                'alert_type' => $panicAlert->alert_type,
                'resolved_by' => $panicAlert->resolvedBy->name ?? 'Usuário'
            ];

            $sentCount = $firebaseService->sendPanicAlertResolved($fcmData);
            
            Log::info('Notificação FCM de resolução enviada', [
                'alert_id' => $panicAlert->id,
                'sent_count' => $sentCount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificação FCM de resolução', [
                'alert_id' => $panicAlert->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Exibe todos os alertas de pânico para administradores e síndicos
     */
    public function index(Request $request)
    {
        $query = PanicAlert::with(['user', 'resolvedBy', 'condominium']);

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por tipo de emergência
        if ($request->filled('type')) {
            $query->where('alert_type', $request->type);
        }

        // Filtro por período
        if ($request->filled('period')) {
            $now = now();
            switch ($request->period) {
                case 'today':
                    $query->whereDate('created_at', $now->toDateString());
                    break;
                case 'week':
                    $query->where('created_at', '>=', $now->startOfWeek());
                    break;
                case 'month':
                    $query->where('created_at', '>=', $now->startOfMonth());
                    break;
                case 'year':
                    $query->where('created_at', '>=', $now->startOfYear());
                    break;
            }
        }

        $alerts = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('panic-alerts.index', compact('alerts'));
    }

    /**
     * Exibe detalhes de um alerta específico
     */
    public function show($id)
    {
        $alert = PanicAlert::with(['user', 'resolvedBy', 'condominium'])->findOrFail($id);
        
        $html = view('panic-alerts.details', compact('alert'))->render();
        
        return response()->json(['html' => $html]);
    }
}
