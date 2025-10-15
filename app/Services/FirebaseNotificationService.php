<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class FirebaseNotificationService
{
    protected $config;
    protected $enabled;

    public function __construct()
    {
        $this->config = config('firebase');
        $this->enabled = $this->config['enabled'] ?? false;
    }

    /**
     * Verifica se o FCM está habilitado
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Verifica se as notificações de pânico estão habilitadas
     */
    public function isPanicNotificationsEnabled(): bool
    {
        return $this->enabled && ($this->config['panic_notifications'] ?? true);
    }

    /**
     * Verifica se as notificações gerais estão habilitadas
     */
    public function isGeneralNotificationsEnabled(): bool
    {
        return $this->enabled && ($this->config['general_notifications'] ?? true);
    }

    /**
     * Envia notificação para um usuário específico
     */
    public function sendToUser(int $userId, string $title, string $body, array $data = []): bool
    {
        if (!$this->enabled) {
            Log::info('FCM desabilitado - notificação não enviada', [
                'user_id' => $userId,
                'title' => $title
            ]);
            return false;
        }

        $user = User::find($userId);
        if (!$user || !$user->fcm_token || !$user->fcm_enabled) {
            Log::warning('Usuário sem token FCM ou FCM desabilitado', [
                'user_id' => $userId,
                'has_token' => !empty($user->fcm_token),
                'fcm_enabled' => $user->fcm_enabled ?? false
            ]);
            return false;
        }

        return $this->sendToToken($user->fcm_token, $title, $body, $data);
    }

    /**
     * Envia notificação para todos os usuários com FCM habilitado
     */
    public function sendToAllUsers(string $title, string $body, array $data = []): int
    {
        if (!$this->enabled) {
            Log::info('FCM desabilitado - notificação em massa não enviada', [
                'title' => $title
            ]);
            return 0;
        }

        $users = User::whereNotNull('fcm_token')
                    ->where('fcm_enabled', true)
                    ->get();

        $sentCount = 0;
        foreach ($users as $user) {
            if ($this->sendToToken($user->fcm_token, $title, $body, $data)) {
                $sentCount++;
            }
        }

        Log::info('Notificação FCM enviada para múltiplos usuários', [
            'total_users' => $users->count(),
            'sent_count' => $sentCount,
            'title' => $title
        ]);

        return $sentCount;
    }

    /**
     * Envia notificação para um tópico específico
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = []): bool
    {
        if (!$this->enabled) {
            Log::info('FCM desabilitado - notificação para tópico não enviada', [
                'topic' => $topic,
                'title' => $title
            ]);
            return false;
        }

        $payload = [
            'to' => '/topics/' . $topic,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'icon' => $this->config['default_notification']['icon'] ?? '/favicon.ico',
                'sound' => $this->config['default_notification']['sound'] ?? 'default',
                'click_action' => $this->config['default_notification']['click_action'] ?? '/',
            ],
            'data' => array_merge($data, [
                'timestamp' => now()->toISOString(),
                'topic' => $topic
            ])
        ];

        return $this->sendHttpRequest($payload);
    }

    /**
     * Envia notificação para um token específico
     */
    protected function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        $payload = [
            'to' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'icon' => $this->config['default_notification']['icon'] ?? '/favicon.ico',
                'sound' => $this->config['default_notification']['sound'] ?? 'default',
                'click_action' => $this->config['default_notification']['click_action'] ?? '/',
            ],
            'data' => array_merge($data, [
                'timestamp' => now()->toISOString()
            ])
        ];

        return $this->sendHttpRequest($payload);
    }

    /**
     * Envia requisição HTTP para FCM
     */
    protected function sendHttpRequest(array $payload): bool
    {
        $serverKey = $this->config['server_key'];
        
        if (empty($serverKey)) {
            Log::error('FCM Server Key não configurada');
            return false;
        }

        try {
            $response = Http::timeout($this->config['timeout'])
                ->connectTimeout($this->config['connect_timeout'])
                ->withHeaders([
                    'Authorization' => 'key=' . $serverKey,
                    'Content-Type' => 'application/json'
                ])
                ->post($this->config['api_url'], $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                
                if (isset($responseData['success']) && $responseData['success'] == 1) {
                    Log::info('Notificação FCM enviada com sucesso', [
                        'payload' => $payload,
                        'response' => $responseData
                    ]);
                    return true;
                } else {
                    Log::warning('FCM retornou erro', [
                        'payload' => $payload,
                        'response' => $responseData
                    ]);
                    return false;
                }
            } else {
                Log::error('Erro HTTP ao enviar FCM', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error('Exceção ao enviar FCM', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            return false;
        }
    }

    /**
     * Envia notificação de alerta de pânico
     */
    public function sendPanicAlert(array $alertData): int
    {
        if (!$this->isPanicNotificationsEnabled()) {
            Log::info('Notificações de pânico FCM desabilitadas');
            return 0;
        }

        $title = '🚨 ALERTA DE EMERGÊNCIA';
        $body = "{$alertData['user_name']} reportou: {$alertData['alert_type']}";
        
        $data = [
            'type' => 'panic_alert',
            'alert_id' => $alertData['alert_id'],
            'alert_type' => $alertData['alert_type'],
            'user_name' => $alertData['user_name'],
            'location' => $alertData['location'],
            'severity' => $alertData['severity'],
            'url' => url('/panic-alerts')
        ];

        return $this->sendToAllUsers($title, $body, $data);
    }

    /**
     * Envia notificação de resolução de alerta de pânico
     */
    public function sendPanicAlertResolved(array $alertData): int
    {
        if (!$this->isPanicNotificationsEnabled()) {
            Log::info('Notificações de pânico FCM desabilitadas');
            return 0;
        }

        $title = '✅ Alerta de Emergência Resolvido';
        $body = "O alerta de {$alertData['alert_type']} foi resolvido por {$alertData['resolved_by']}";
        
        $data = [
            'type' => 'panic_resolved',
            'alert_id' => $alertData['alert_id'],
            'alert_type' => $alertData['alert_type'],
            'resolved_by' => $alertData['resolved_by'],
            'url' => url('/panic-alerts')
        ];

        return $this->sendToAllUsers($title, $body, $data);
    }

    /**
     * Envia notificação de reserva
     */
    public function sendReservationNotification(int $userId, string $type, array $reservationData): bool
    {
        if (!$this->isGeneralNotificationsEnabled()) {
            Log::info('Notificações gerais FCM desabilitadas');
            return false;
        }

        $titles = [
            'approved' => '✅ Reserva Aprovada',
            'cancelled' => '❌ Reserva Cancelada',
            'pending' => '⏳ Reserva Pendente',
            'expired' => '⏰ Reserva Expirada'
        ];

        $title = $titles[$type] ?? '📅 Atualização de Reserva';
        $body = "Sua reserva para {$reservationData['space_name']} foi {$type}";
        
        $data = [
            'type' => 'reservation',
            'reservation_id' => $reservationData['reservation_id'],
            'space_name' => $reservationData['space_name'],
            'status' => $type,
            'url' => url('/reservations')
        ];

        return $this->sendToUser($userId, $title, $body, $data);
    }

    /**
     * Valida se as configurações do FCM estão corretas
     */
    public function validateConfiguration(): array
    {
        $errors = [];

        if (empty($this->config['server_key'])) {
            $errors[] = 'FCM Server Key não configurada';
        }

        if (empty($this->config['sender_id'])) {
            $errors[] = 'FCM Sender ID não configurado';
        }

        if (empty($this->config['project_id'])) {
            $errors[] = 'FCM Project ID não configurado';
        }

        return $errors;
    }
}
