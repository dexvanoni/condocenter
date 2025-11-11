<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Services\OneSignalNotificationService;

class SendPanicAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $alertData;
    public $message;

    /**
     * Create a new job instance.
     */
    public function __construct(array $alertData, $message)
    {
        $this->alertData = $alertData;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Buscar TODOS os usuários do condomínio
            $users = User::where('condominium_id', $this->alertData['condominium_id'])
                ->where('is_active', true)
                ->get();

            Log::critical('🚨 ALERTA DE PÂNICO ACIONADO', $this->alertData);

            foreach ($users as $user) {
                // Criar notificação no banco de dados
                Notification::create([
                    'condominium_id' => $this->alertData['condominium_id'],
                    'user_id' => $user->id,
                    'type' => 'panic_alert',
                    'title' => '🚨 ALERTA DE PÂNICO: ' . $this->alertData['alert_title'],
                    'message' => $this->buildNotificationMessage(),
                    'data' => $this->alertData,
                    'is_read' => false,
                    'channel' => 'database',
                    'sent' => true,
                    'sent_at' => now(),
                ]);

                // Enviar email urgente
                try {
                    Mail::to($user->email)->send(
                        new \App\Mail\PanicAlertNotification($this->alertData)
                    );
                    
                    Log::info("Email de pânico enviado para: {$user->email}");
                } catch (\Exception $e) {
                    Log::error("Erro ao enviar email de pânico para {$user->email}: " . $e->getMessage());
                }
            }

            Log::info("Alerta de pânico enviado para {$users->count()} usuários", [
                'alert_type' => $this->alertData['alert_type'],
                'sender' => $this->alertData['user_name'],
            ]);

            /** @var OneSignalNotificationService $oneSignal */
            $oneSignal = app(OneSignalNotificationService::class);
            if ($oneSignal->isEnabled()) {
                $oneSignal->sendPanicAlert(
                    $users->pluck('id')->all(),
                    [
                        'alert_id' => $this->alertData['alert_id'],
                        'alert_type' => $this->alertData['alert_type'],
                        'alert_title' => $this->alertData['alert_title'],
                        'user_name' => $this->alertData['user_name'],
                        'user_unit' => $this->alertData['user_unit'] ?? 'Condomínio',
                        'severity' => $this->alertData['severity'] ?? 'high',
                        'additional_info' => $this->alertData['additional_info'] ?? null,
                    ]
                );
            }

        } catch (\Exception $e) {
            Log::error('Erro ao enviar alerta de pânico: ' . $e->getMessage(), [
                'alert_data' => $this->alertData,
            ]);
            throw $e;
        }
    }

    /**
     * Constrói mensagem da notificação
     */
    protected function buildNotificationMessage(): string
    {
        $msg = "🚨 EMERGÊNCIA NO CONDOMÍNIO!\n\n";
        $msg .= "Tipo: {$this->alertData['alert_title']}\n";
        $msg .= "Enviado por: {$this->alertData['user_name']}\n";
        $msg .= "Unidade: {$this->alertData['user_unit']}\n";
        $msg .= "Telefone: {$this->alertData['user_phone']}\n";
        $msg .= "Data/Hora: {$this->alertData['timestamp']}\n";
        
        if (!empty($this->alertData['additional_info'])) {
            $msg .= "\nInformações: {$this->alertData['additional_info']}\n";
        }
        
        $msg .= "\n⚠️ TOME AS MEDIDAS NECESSÁRIAS IMEDIATAMENTE!";
        
        return $msg;
    }
}
