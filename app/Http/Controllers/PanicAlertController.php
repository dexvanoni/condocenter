<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Notification;
use App\Models\PanicAlert;
use App\Models\User;
use App\Jobs\SendPanicAlert;
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
            
            Log::info('Usuário autenticado', ['user_id' => $user->id, 'condominium_id' => $user->tenantCondominiumId()]);
        
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
            'condominium_id' => $user->tenantCondominiumId(),
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
            'condominium_id' => $user->tenantCondominiumId(),
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
            'condominium_id' => $user->tenantCondominiumId(),
            'condominium_name' => $user->condominium->name,
        ];

        // Despachar job para enviar alerta para TODOS
        SendPanicAlert::dispatch($alertData, $message);

        // Enviar emails para perfis específicos (síndico, administrador, porteiro, secretaria)
        $this->sendPanicEmails($alertData);

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
        
        if (!$user) {
            Log::warning('Tentativa de verificar alertas sem usuário autenticado');
            return response()->json([
                'has_active_alerts' => false,
                'alerts' => [],
                'alert_count' => 0,
                'error' => 'Usuário não autenticado'
            ], 401);
        }
        
        Log::info('Verificando alertas ativos', [
            'user_id' => $user->id,
            'condominium_id' => $user->tenantCondominiumId(),
            'user_name' => $user->name
        ]);
        
        // Verificar todos os alertas com status 'active' primeiro (para debug)
        $allActiveAlerts = PanicAlert::where('status', 'active')->get();
        Log::info('Total de alertas ativos no sistema', [
            'count' => $allActiveAlerts->count(),
            'alerts' => $allActiveAlerts->map(function($alert) {
                return [
                    'id' => $alert->id,
                    'condominium_id' => $alert->condominium_id,
                    'status' => $alert->status
                ];
            })->toArray()
        ]);
        
        // Buscar alertas ativos do condomínio do usuário
        $activeAlerts = PanicAlert::where('status', 'active')
            ->where('condominium_id', $user->tenantCondominiumId())
            ->with(['user', 'condominium'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Debug: Verificar se o alerta id=4 está sendo encontrado
        $alert4 = PanicAlert::find(4);
        if ($alert4) {
            Log::info('Alerta ID=4 encontrado', [
                'id' => $alert4->id,
                'status' => $alert4->status,
                'condominium_id' => $alert4->condominium_id,
                'user_condominium_id' => $user->tenantCondominiumId(),
                'matches' => $alert4->condominium_id == $user->tenantCondominiumId() && $alert4->status == 'active'
            ]);
        } else {
            Log::warning('Alerta ID=4 não encontrado no banco de dados');
        }
        
        Log::info('Alertas ativos encontrados para o condomínio', [
            'condominium_id' => $user->tenantCondominiumId(),
            'count' => $activeAlerts->count(),
            'alerts' => $activeAlerts->map(function($alert) {
                return [
                    'id' => $alert->id,
                    'title' => $alert->title,
                    'status' => $alert->status,
                    'condominium_id' => $alert->condominium_id
                ];
            })->toArray()
        ]);

        $response = [
            'has_active_alerts' => $activeAlerts->count() > 0,
            'alerts' => $activeAlerts,
            'alert_count' => $activeAlerts->count(),
            'debug' => [
                'user_condominium_id' => $user->tenantCondominiumId(),
                'total_active_in_system' => $allActiveAlerts->count()
            ]
        ];
        
        Log::info('Resposta da verificação de alertas', $response);

        return response()->json($response);
    }

    /**
     * Resolve um alerta de pânico
     */
    public function resolve(Request $request, $id)
    {
        $user = Auth::user();
        
        $alert = PanicAlert::findOrFail($id);
        
        // Verificar se o alerta pertence ao condomínio do usuário
        if ($alert->condominium_id !== $user->tenantCondominiumId()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Verificar se o alerta ainda está ativo
        if (!$alert->isActive()) {
            return response()->json(['error' => 'Este alerta já foi resolvido'], 400);
        }

        // Resolver o alerta
        $alert->resolve($user);

        $this->notifyPanicResolved($alert, $user);

        return response()->json([
            'message' => 'Alerta de pânico resolvido com sucesso',
            'resolved_by' => $user->name,
            'resolved_at' => $alert->resolved_at->toISOString()
        ]);
    }

    /**
     * Notifica moradores sobre resolução do alerta (sistema + WhatsApp via observer).
     */
    protected function notifyPanicResolved(PanicAlert $alert, User $resolver): void
    {
        $users = User::where('condominium_id', $alert->condominium_id)
            ->eligibleForWhatsApp()
            ->get();

        $message = sprintf(
            'O alerta "%s" foi resolvido por %s em %s.',
            $alert->title,
            $resolver->name,
            now()->format('d/m/Y H:i:s')
        );

        foreach ($users as $resident) {
            Notification::create([
                'condominium_id' => $alert->condominium_id,
                'user_id' => $resident->id,
                'type' => 'panic_resolved',
                'title' => '✅ Alerta de pânico resolvido',
                'message' => $message,
                'data' => [
                    'alert_id' => $alert->id,
                    'alert_type' => $alert->alert_type,
                    'resolved_by' => $resolver->id,
                    'resolved_by_name' => $resolver->name,
                ],
                'channel' => 'database',
                'sent' => true,
                'sent_at' => now(),
            ]);
        }
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
                ->eligibleForWhatsApp()
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

    /**
     * Exibe a tela de alerta de pânico ativo
     */
    public function activeAlert()
    {
        $user = Auth::user();
        
        if (!$user) {
            Log::warning('Tentativa de acessar tela de alerta ativo sem usuário autenticado');
            return redirect()->route('login');
        }
        
        Log::info('Buscando alerta ativo para exibir', [
            'user_id' => $user->id,
            'condominium_id' => $user->tenantCondominiumId(),
            'user_name' => $user->name
        ]);
        
        // Verificar todos os alertas com status 'active' primeiro (para debug)
        $allActiveAlerts = PanicAlert::where('status', 'active')->get();
        Log::info('Total de alertas ativos no sistema', [
            'count' => $allActiveAlerts->count(),
            'alerts' => $allActiveAlerts->map(function($alert) {
                return [
                    'id' => $alert->id,
                    'condominium_id' => $alert->condominium_id,
                    'status' => $alert->status,
                    'title' => $alert->title
                ];
            })->toArray()
        ]);
        
        $activeAlert = PanicAlert::active()
            ->forCondominium($user->tenantCondominiumId())
            ->with(['user', 'condominium'])
            ->orderBy('created_at', 'desc')
            ->first();

        Log::info('Alerta ativo encontrado para o condomínio', [
            'condominium_id' => $user->tenantCondominiumId(),
            'alert_found' => $activeAlert ? true : false,
            'alert_id' => $activeAlert ? $activeAlert->id : null,
            'alert_status' => $activeAlert ? $activeAlert->status : null
        ]);

        // Se não houver alerta ativo, redirecionar para dashboard
        if (!$activeAlert) {
            Log::warning('Nenhum alerta ativo encontrado para o condomínio', [
                'condominium_id' => $user->tenantCondominiumId(),
                'total_active_in_system' => $allActiveAlerts->count()
            ]);
            return redirect()->route('dashboard')->with('info', 'Não há alertas de pânico ativos no momento.');
        }

        Log::info('Exibindo tela de alerta ativo', [
            'alert_id' => $activeAlert->id,
            'alert_title' => $activeAlert->title,
            'alert_status' => $activeAlert->status
        ]);

        return view('panic-alerts.active', compact('activeAlert'));
    }

    /**
     * Confirma que o usuário está ciente do alerta
     */
    public function confirmAware($id)
    {
        $user = Auth::user();
        
        $alert = PanicAlert::findOrFail($id);
        
        // Verificar se o alerta pertence ao condomínio do usuário
        if ($alert->condominium_id !== $user->tenantCondominiumId()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Verificar se o alerta ainda está ativo
        if (!$alert->isActive()) {
            return response()->json(['error' => 'Este alerta já foi resolvido'], 400);
        }

        // Apenas registrar que o usuário está ciente (não resolve o alerta)
        // O alerta continua ativo para outros usuários
        Log::info('Usuário confirmou estar ciente do alerta', [
            'alert_id' => $alert->id,
            'user_id' => $user->id,
            'user_name' => $user->name
        ]);

        return response()->json([
            'message' => 'Você confirmou estar ciente do alerta. O alerta continua ativo para outros moradores.',
            'alert_id' => $alert->id
        ]);
    }
}
