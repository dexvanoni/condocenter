<?php

namespace App\Console\Commands;

use App\Models\Condominium;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class GenerateMonthlyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:generate-monthly {condominium_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera relatório financeiro mensal em PDF';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $condominiumId = $this->argument('condominium_id');

        if ($condominiumId) {
            $condominiums = [Condominium::findOrFail($condominiumId)];
        } else {
            $condominiums = Condominium::where('is_active', true)->get();
        }

        $this->info("Gerando relatórios para " . count($condominiums) . " condomínio(s)...");

        foreach ($condominiums as $condominium) {
            $this->generateReportForCondominium($condominium);
        }

        $this->info('✓ Relatórios gerados com sucesso!');

        return Command::SUCCESS;
    }

    protected function generateReportForCondominium(Condominium $condominium)
    {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        // Buscar transações do mês
        $transactions = Transaction::where('condominium_id', $condominium->id)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date')
            ->get();

        $totalReceitas = $transactions->where('type', 'income')->sum('amount');
        $totalDespesas = $transactions->where('type', 'expense')->sum('amount');
        $saldo = $totalReceitas - $totalDespesas;

        // Gerar PDF
        $pdf = Pdf::loadView('reports.monthly-financial', [
            'condominium' => $condominium,
            'transactions' => $transactions,
            'totalReceitas' => $totalReceitas,
            'totalDespesas' => $totalDespesas,
            'saldo' => $saldo,
            'period' => now()->locale('pt_BR')->translatedFormat('F/Y'),
        ]);

        // Salvar PDF
        $filename = 'relatorio-' . $condominium->id . '-' . now()->format('Y-m') . '.pdf';
        Storage::put('reports/' . $filename, $pdf->output());

        $this->info("  ✓ Relatório gerado para: {$condominium->name}");
    }
}
