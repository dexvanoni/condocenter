<?php

namespace App\Jobs;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CancelExpiredPrereservations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Iniciando cancelamento de pré-reservas expiradas');

        // Buscar pré-reservas que expiraram
        $expiredPrereservations = Reservation::where('prereservation_status', 'pending_payment')
            ->where('payment_deadline', '<', now())
            ->with(['user', 'space'])
            ->get();

        $cancelledCount = 0;

        foreach ($expiredPrereservations as $reservation) {
            try {
                // Verificar se o espaço ainda tem cancelamento automático ativo
                if ($reservation->space->prereservation_auto_cancel) {
                    // Marcar como expirado
                    $reservation->markAsExpired();
                    
                    // Log da ação
                    Log::info("Pré-reserva expirada cancelada", [
                        'reservation_id' => $reservation->id,
                        'user_id' => $reservation->user_id,
                        'space_id' => $reservation->space_id,
                        'reservation_date' => $reservation->reservation_date,
                        'payment_deadline' => $reservation->payment_deadline
                    ]);

                    // TODO: Enviar notificação para o usuário
                    // TODO: Enviar notificação para o síndico (nova vaga disponível)
                    
                    $cancelledCount++;
                }
            } catch (\Exception $e) {
                Log::error("Erro ao cancelar pré-reserva expirada", [
                    'reservation_id' => $reservation->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info("Cancelamento de pré-reservas expiradas concluído", [
            'total_expired' => $expiredPrereservations->count(),
            'cancelled_count' => $cancelledCount
        ]);
    }
}