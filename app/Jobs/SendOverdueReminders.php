<?php

namespace App\Jobs;

use App\Models\Charge;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOverdueReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Buscar cobranças vencidas
            $overdueCharges = Charge::with(['unit.users', 'condominium'])
                ->where('status', 'pending')
                ->where('due_date', '<', now())
                ->get();

            foreach ($overdueCharges as $charge) {
                $channel = data_get($charge->metadata, 'payment_channel');
                if ($channel === 'payroll') {
                    continue;
                }

                // Atualizar status para overdue
                $charge->update(['status' => 'overdue']);

                // Calcular dias de atraso
                $daysLate = now()->diffInDays($charge->due_date);
                
                // Calcular valor com multa e juros
                $totalWithFines = $charge->calculateTotal();

                // Notificar moradores da unidade
                if (!$charge->unit || !$charge->unit->is_active) {
                    continue;
                }

                $residents = $charge->unit->users->filter(fn($user) => $user->is_active);

                foreach ($residents as $resident) {
                    Notification::create([
                        'condominium_id' => $charge->condominium_id,
                        'user_id' => $resident->id,
                        'type' => 'payment_overdue',
                        'title' => 'Pagamento em Atraso',
                        'message' => "A cobrança '{$charge->title}' está em atraso há {$daysLate} dia(s). Valor original: R$ " . number_format($charge->amount, 2, ',', '.') . ". Valor atualizado com multa e juros: R$ " . number_format($totalWithFines, 2, ',', '.'),
                        'data' => [
                            'charge_id' => $charge->id,
                            'due_date' => $charge->due_date,
                            'days_late' => $daysLate,
                            'original_amount' => $charge->amount,
                            'total_with_fines' => $totalWithFines,
                        ],
                        'channel' => 'database',
                        'sent' => true,
                        'sent_at' => now(),
                    ]);
                }
            }

            Log::info("Lembretes de atraso enviados", [
                'total_charges' => $overdueCharges->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao enviar lembretes de atraso: ' . $e->getMessage());
            throw $e;
        }
    }
}
