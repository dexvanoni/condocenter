<?php

namespace App\Jobs;

use App\Models\Condominium;
use App\Models\Charge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateMonthlyCharges implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $condominiumId;
    public $amount;
    public $dueDay;

    /**
     * Create a new job instance.
     */
    public function __construct(int $condominiumId, float $amount, int $dueDay = 10)
    {
        $this->condominiumId = $condominiumId;
        $this->amount = $amount;
        $this->dueDay = $dueDay;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $condominium = Condominium::with('units')->findOrFail($this->condominiumId);
            
            // Determinar data de vencimento (dia X do próximo mês)
            $dueDate = now()->addMonth()->setDay($this->dueDay);
            $monthYear = $dueDate->locale('pt_BR')->translatedFormat('F/Y');

            $chargesCreated = 0;

            foreach ($condominium->units as $unit) {
                if (!$unit->is_active) {
                    continue;
                }

                // Verificar se já existe cobrança para este mês/unidade
                $exists = Charge::where('condominium_id', $this->condominiumId)
                    ->where('unit_id', $unit->id)
                    ->where('title', 'like', "%{$monthYear}%")
                    ->exists();

                if ($exists) {
                    continue; // Pular se já existe
                }

                // Calcular valor baseado na fração ideal
                $unitAmount = $this->amount * $unit->ideal_fraction;

                Charge::create([
                    'condominium_id' => $this->condominiumId,
                    'unit_id' => $unit->id,
                    'title' => "Taxa Condominial - {$monthYear}",
                    'description' => "Cobrança mensal do condomínio referente a {$monthYear}",
                    'amount' => $unitAmount,
                    'due_date' => $dueDate,
                    'recurrence_period' => $dueDate->format('Y-m'),
                    'fine_percentage' => 2.00,
                    'interest_rate' => 1.00,
                    'type' => 'regular',
                    'status' => 'pending',
                    'generated_by' => 'manual',
                    'metadata' => [
                        'legacy_generator' => 'GenerateMonthlyChargesJob',
                    ],
                ]);

                $chargesCreated++;
            }

            Log::info("Cobranças mensais geradas", [
                'condominium_id' => $this->condominiumId,
                'charges_created' => $chargesCreated,
                'month' => $monthYear,
                'due_date' => $dueDate,
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar cobranças mensais: ' . $e->getMessage());
            throw $e;
        }
    }
}
