<?php

namespace App\Jobs;

use App\Models\Reservation;
use App\Models\Space;
use App\Services\ReservationChargeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateReservationPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation,
        public Space $space,
    ) {
    }

    public function handle(ReservationChargeService $reservationChargeService): void
    {
        try {
            $charge = $reservationChargeService->createForReservation(
                $this->reservation,
                $this->space,
                context: 'job'
            );

            if (!$charge) {
                return;
            }

            Log::info('Cobrança de reserva gerada', [
                'reservation_id' => $this->reservation->id,
                'charge_id' => $charge->id,
                'amount' => $charge->amount,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar pagamento de reserva: ' . $e->getMessage(), [
                'reservation_id' => $this->reservation->id,
                'space_id' => $this->space->id,
            ]);

            $this->release(60);
        }
    }
}
