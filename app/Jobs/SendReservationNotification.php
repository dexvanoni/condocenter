<?php

namespace App\Jobs;

use App\Models\Reservation;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\OneSignalNotificationService;

class SendReservationNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $reservation;
    public $type;

    /**
     * Create a new job instance.
     */
    public function __construct(Reservation $reservation, string $type)
    {
        $this->reservation = $reservation;
        $this->type = $type; // approved, rejected, pending_approval
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $messages = [
                'approved' => [
                    'title' => 'Reserva Aprovada! ✓',
                    'message' => "Sua reserva do(a) {$this->reservation->space->name} para {$this->reservation->reservation_date->format('d/m/Y')} das {$this->reservation->start_time} às {$this->reservation->end_time} foi aprovada!",
                ],
                'rejected' => [
                    'title' => 'Reserva Rejeitada',
                    'message' => "Sua reserva do(a) {$this->reservation->space->name} foi rejeitada. Motivo: {$this->reservation->rejection_reason}",
                ],
                'pending_approval' => [
                    'title' => 'Nova Reserva Pendente',
                    'message' => "{$this->reservation->user->name} solicitou reserva do(a) {$this->reservation->space->name} para {$this->reservation->reservation_date->format('d/m/Y')}. Aguardando aprovação.",
                ],
            ];

            $messageData = $messages[$this->type] ?? $messages['approved'];

            // Determinar destinatário
            $sindicos = collect();

            if ($this->type === 'pending_approval') {
                // Notificar síndico
                $sindicos = \App\Models\User::role('Síndico')
                    ->where('condominium_id', $this->reservation->space->condominium_id)
                    ->get();

                foreach ($sindicos as $sindico) {
                    Notification::create([
                        'condominium_id' => $this->reservation->space->condominium_id,
                        'user_id' => $sindico->id,
                        'type' => 'reservation_pending_approval',
                        'title' => $messageData['title'],
                        'message' => $messageData['message'],
                        'data' => [
                            'reservation_id' => $this->reservation->id,
                            'space_name' => $this->reservation->space->name,
                            'reservation_date' => $this->reservation->reservation_date,
                        ],
                        'channel' => 'database',
                        'sent' => true,
                        'sent_at' => now(),
                    ]);
                }
            } else {
                // Notificar usuário que fez a reserva
                Notification::create([
                    'condominium_id' => $this->reservation->space->condominium_id,
                    'user_id' => $this->reservation->user_id,
                    'type' => 'reservation_' . $this->type,
                    'title' => $messageData['title'],
                    'message' => $messageData['message'],
                    'data' => [
                        'reservation_id' => $this->reservation->id,
                        'space_name' => $this->reservation->space->name,
                        'reservation_date' => $this->reservation->reservation_date,
                    ],
                    'channel' => 'database',
                    'sent' => true,
                    'sent_at' => now(),
                ]);
            }

            Log::info("Notificação de reserva enviada", [
                'reservation_id' => $this->reservation->id,
                'type' => $this->type,
            ]);

            /** @var OneSignalNotificationService $oneSignal */
            $oneSignal = app(OneSignalNotificationService::class);
            if ($oneSignal->isEnabled()) {
                $payload = [
                    'reservation_id' => $this->reservation->id,
                    'space_name' => $this->reservation->space->name,
                    'reservation_date' => $this->reservation->reservation_date,
                    'reservation_date_label' => $this->reservation->reservation_date?->format('d/m/Y'),
                    'start_time' => $this->reservation->start_time,
                    'end_time' => $this->reservation->end_time,
                    'message' => $messageData['message'],
                ];

                if ($this->type === 'pending_approval') {
                    $oneSignal->sendReservationNotification(
                        $sindicos->pluck('id')->all(),
                        $this->type,
                        $payload
                    );
                } else {
                    $oneSignal->sendReservationNotification(
                        [$this->reservation->user_id],
                        $this->type,
                        $payload
                    );
                }
            }

        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificação de reserva: ' . $e->getMessage());
            throw $e;
        }
    }
}
