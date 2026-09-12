<?php

namespace App\Console\Commands;

use App\Models\Charge;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkOverdueChargesCommand extends Command
{
    protected $signature = 'charges:mark-overdue {--date= : Data de referência (Y-m-d)}';

    protected $description = 'Marca cobranças pendentes vencidas como em atraso (exceto desconto em folha)';

    public function handle(): int
    {
        $referenceDate = $this->option('date')
            ? Carbon::parse($this->option('date'))->startOfDay()
            : now()->startOfDay();

        $updated = 0;

        Charge::query()
            ->where('status', 'pending')
            ->whereDate('due_date', '<', $referenceDate->toDateString())
            ->orderBy('id')
            ->chunkById(100, function ($charges) use (&$updated) {
                foreach ($charges as $charge) {
                    if ($charge->paymentChannel() === 'payroll') {
                        continue;
                    }

                    $charge->update(['status' => 'overdue']);
                    $updated++;
                }
            });

        $this->info("✓ {$updated} cobrança(s) marcada(s) como em atraso.");

        return Command::SUCCESS;
    }
}
