<?php

namespace App\Console\Commands;

use App\Services\FeeService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateUpcomingFeeChargesCommand extends Command
{
    protected $signature = 'fees:generate-upcoming
                            {--date= : Data de referência (Y-m-d) para avaliar competências}
                            {--condominium= : ID do condomínio (opcional)}';

    protected $description = 'Gera a próxima cobrança das taxas com geração automática ativa';

    public function handle(FeeService $feeService): int
    {
        $referenceDate = $this->option('date')
            ? Carbon::parse($this->option('date'))->startOfDay()
            : now()->startOfDay();

        $condominiumId = $this->option('condominium') ? (int) $this->option('condominium') : null;

        $this->info('Gerando cobranças automáticas para '.$referenceDate->format('d/m/Y').'...');

        $result = $feeService->generateUpcomingChargesForActiveFees($referenceDate, $condominiumId);

        $this->info(sprintf(
            '✓ %d taxa(s) processada(s), %d cobrança(s) criada(s).',
            $result['fees_processed'],
            $result['charges_created']
        ));

        if ($result['fees_skipped'] > 0) {
            $this->line("  {$result['fees_skipped']} taxa(s) aguardando início da competência.");
        }

        return Command::SUCCESS;
    }
}
