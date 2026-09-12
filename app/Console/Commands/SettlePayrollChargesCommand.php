<?php

namespace App\Console\Commands;

use App\Services\ChargeSettlementService;
use Illuminate\Console\Command;

class SettlePayrollChargesCommand extends Command
{
    protected $signature = 'charges:settle-payroll {--date= : Data de referência (Y-m-d) para liquidar vencimentos até esta data}';

    protected $description = 'Liquida cobranças de desconto em folha cujo vencimento já ocorreu';

    public function handle(ChargeSettlementService $settlementService): int
    {
        $referenceDate = $this->option('date')
            ? \Carbon\Carbon::parse($this->option('date'))->startOfDay()
            : now()->startOfDay();

        $this->info('Liquidando cobranças em folha vencidas até '.$referenceDate->format('d/m/Y').'...');

        $settled = $settlementService->settleDuePayrollCharges($referenceDate);

        $this->info("✓ {$settled} cobrança(s) liquidada(s) via folha.");

        return Command::SUCCESS;
    }
}
