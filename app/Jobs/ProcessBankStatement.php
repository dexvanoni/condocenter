<?php

namespace App\Jobs;

use App\Models\BankStatement;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProcessBankStatement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $bankStatement;

    /**
     * Create a new job instance.
     */
    public function __construct(BankStatement $bankStatement)
    {
        $this->bankStatement = $bankStatement;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->bankStatement->update(['status' => 'processing']);

            // Ler arquivo CSV/OFX
            $filePath = Storage::path($this->bankStatement->storage_path);
            
            // Parse do CSV (simplificado - assumindo CSV com colunas: date, description, amount, type)
            $statementData = $this->parseCSV($filePath);

            $totalTransactions = count($statementData);
            $reconciledCount = 0;
            $unmatchedItems = [];

            // Buscar transações no período
            $transactions = Transaction::where('condominium_id', $this->bankStatement->condominium_id)
                ->whereBetween('transaction_date', [
                    $this->bankStatement->period_start,
                    $this->bankStatement->period_end
                ])
                ->get();

            // Algoritmo de conciliação
            foreach ($statementData as $item) {
                $matched = false;

                foreach ($transactions as $transaction) {
                    // Critérios de matching:
                    // 1. Valor igual (ou próximo com tolerância de R$ 0.50)
                    // 2. Data próxima (±3 dias)
                    $amountMatch = abs($transaction->amount - abs($item['amount'])) <= 0.50;
                    $dateMatch = abs($transaction->transaction_date->diffInDays($item['date'])) <= 3;

                    if ($amountMatch && $dateMatch) {
                        $reconciledCount++;
                        $matched = true;
                        
                        // Marcar transação como conciliada (adicionar campo se necessário)
                        // $transaction->update(['reconciled' => true]);
                        
                        break;
                    }
                }

                if (!$matched) {
                    $unmatchedItems[] = $item;
                }
            }

            // Atualizar extrato
            $this->bankStatement->update([
                'status' => 'reconciled',
                'total_transactions' => $totalTransactions,
                'reconciled_transactions' => $reconciledCount,
                'unmatched_items' => $unmatchedItems,
            ]);

            Log::info("Extrato bancário processado", [
                'bank_statement_id' => $this->bankStatement->id,
                'total' => $totalTransactions,
                'reconciled' => $reconciledCount,
                'unmatched' => count($unmatchedItems),
            ]);

        } catch (\Exception $e) {
            $this->bankStatement->update([
                'status' => 'failed',
                'notes' => 'Erro: ' . $e->getMessage(),
            ]);
            
            Log::error('Erro ao processar extrato bancário: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Parse CSV file
     */
    protected function parseCSV($filePath): array
    {
        $data = [];
        
        if (($handle = fopen($filePath, 'r')) !== false) {
            // Pular header
            fgetcsv($handle);
            
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($row) >= 3) {
                    $data[] = [
                        'date' => \Carbon\Carbon::createFromFormat('Y-m-d', $row[0]),
                        'description' => $row[1] ?? '',
                        'amount' => floatval($row[2]),
                        'type' => floatval($row[2]) >= 0 ? 'income' : 'expense',
                    ];
                }
            }
            
            fclose($handle);
        }
        
        return $data;
    }
}
