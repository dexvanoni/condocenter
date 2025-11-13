<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\BankAccountBalance;
use App\Models\BankAccountReconciliation;
use App\Models\BankAccountReconciliationItem;
use App\Models\CondominiumAccount;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class BankReconciliationService
{
    public function __construct(
        private readonly DatabaseManager $database,
    ) {
    }

    public function preview(int $condominiumId, BankAccount $account, Carbon $startDate, Carbon $endDate): array
    {
        $transactionsIncome = Transaction::withTrashed()
            ->where('condominium_id', $condominiumId)
            ->whereNull('reconciliation_id')
            ->where('status', 'paid')
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get();

        $transactionsExpense = Transaction::withTrashed()
            ->where('condominium_id', $condominiumId)
            ->whereNull('reconciliation_id')
            ->where('status', 'paid')
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get();

        $accountIncomes = CondominiumAccount::where('condominium_id', $condominiumId)
            ->whereNull('reconciliation_id')
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get();

        $accountExpenses = CondominiumAccount::where('condominium_id', $condominiumId)
            ->whereNull('reconciliation_id')
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get();

        $chargeIncomes = $accountIncomes->where('source_type', 'charge')->values();
        $manualIncomes = $accountIncomes->reject(fn (CondominiumAccount $entry) => $entry->source_type === 'charge')->values();

        $incomeGroups = collect([
            $this->buildGroup(
                label: 'Transações (Receitas)',
                direction: 'income',
                sourceType: 'transaction',
                items: $transactionsIncome->map(fn (Transaction $transaction) => [
                    'source_id' => $transaction->id,
                    'reference_date' => $transaction->transaction_date,
                    'amount' => $transaction->amount,
                    'label' => 'Receita registrada',
                ])
            ),
            $this->buildGroup(
                label: 'Recebimentos de Taxas',
                direction: 'income',
                sourceType: 'condominium_account',
                items: $chargeIncomes->map(fn (CondominiumAccount $entry) => [
                    'source_id' => $entry->id,
                    'reference_date' => $entry->transaction_date,
                    'amount' => $entry->amount,
                    'label' => 'Recebimento de taxa',
                ])
            ),
            $this->buildGroup(
                label: 'Recebimentos Avulsos',
                direction: 'income',
                sourceType: 'condominium_account',
                items: $manualIncomes->map(fn (CondominiumAccount $entry) => [
                    'source_id' => $entry->id,
                    'reference_date' => $entry->transaction_date,
                    'amount' => $entry->amount,
                    'label' => 'Recebimento avulso',
                ])
            ),
        ])->filter(fn ($group) => $group['count'] > 0)->values();

        $expenseGroups = collect([
            $this->buildGroup(
                label: 'Transações (Despesas)',
                direction: 'expense',
                sourceType: 'transaction',
                items: $transactionsExpense->map(fn (Transaction $transaction) => [
                    'source_id' => $transaction->id,
                    'reference_date' => $transaction->transaction_date,
                    'amount' => $transaction->amount,
                    'label' => 'Despesa registrada',
                ])
            ),
            $this->buildGroup(
                label: 'Pagamentos Registrados',
                direction: 'expense',
                sourceType: 'condominium_account',
                items: $accountExpenses->map(fn (CondominiumAccount $entry) => [
                    'source_id' => $entry->id,
                    'reference_date' => $entry->transaction_date,
                    'amount' => $entry->amount,
                    'label' => 'Pagamento registrado',
                ])
            ),
        ])->filter(fn ($group) => $group['count'] > 0)->values();

        $totalIncome = $incomeGroups->sum('total');
        $totalExpense = $expenseGroups->sum('total');
        $netAmount = $totalIncome - $totalExpense;

        return [
            'account' => $account,
            'income_groups' => $incomeGroups,
            'expense_groups' => $expenseGroups,
            'totals' => [
                'income' => $totalIncome,
                'expense' => $totalExpense,
                'net' => $netAmount,
                'count_entries' => $incomeGroups->sum('count') + $expenseGroups->sum('count'),
            ],
        ];
    }

    public function reconcile(User $user, BankAccount $account, Carbon $startDate, Carbon $endDate): BankAccountReconciliation
    {
        $condominiumId = $user->condominium_id;
        $preview = $this->preview($condominiumId, $account, $startDate, $endDate);

        if ($preview['totals']['count_entries'] === 0) {
            throw ValidationException::withMessages([
                'period' => 'Não há movimentações elegíveis para conciliação no período informado.',
            ]);
        }

        return $this->database->transaction(function () use ($preview, $user, $account, $startDate, $endDate) {
            $previousBalance = $account->current_balance ?? 0;
            $previousBalanceUpdatedAt = $account->balance_updated_at;

            $reconciliation = BankAccountReconciliation::create([
                'condominium_id' => $user->condominium_id,
                'bank_account_id' => $account->id,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'total_income' => $preview['totals']['income'],
                'total_expense' => $preview['totals']['expense'],
                'net_amount' => $preview['totals']['net'],
                'previous_balance' => $previousBalance,
                'resulting_balance' => $previousBalance + $preview['totals']['net'],
                'previous_balance_updated_at' => $previousBalanceUpdatedAt,
                'created_by' => $user->id,
            ]);

            $flattenItems = collect($preview['income_groups'])
                ->merge($preview['expense_groups'])
                ->flatMap(fn ($group) => $group['items']->map(function ($item) use ($group, $reconciliation) {
                    return [
                        'reconciliation_id' => $reconciliation->id,
                        'source_type' => $group['source_type'],
                        'source_id' => $item['source_id'],
                        'direction' => $group['direction'],
                        'reference_date' => $item['reference_date'],
                        'amount' => $item['amount'],
                        'label' => $item['label'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }));

            if ($flattenItems->isNotEmpty()) {
                BankAccountReconciliationItem::insert($flattenItems->toArray());
            }

            $transactionIds = $flattenItems
                ->where('source_type', 'transaction')
                ->pluck('source_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($transactionIds)) {
                Transaction::whereIn('id', $transactionIds)->update([
                    'reconciliation_id' => $reconciliation->id,
                ]);
            }

            $accountEntryIds = $flattenItems
                ->where('source_type', 'condominium_account')
                ->pluck('source_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($accountEntryIds)) {
                CondominiumAccount::whereIn('id', $accountEntryIds)->update([
                    'reconciliation_id' => $reconciliation->id,
                ]);
            }

            $balanceRecord = BankAccountBalance::create([
                'bank_account_id' => $account->id,
                'balance' => $reconciliation->resulting_balance,
                'recorded_at' => $endDate->toDateString(),
                'reference' => sprintf(
                    'Conciliação %s a %s',
                    $startDate->format('d/m/Y'),
                    $endDate->format('d/m/Y')
                ),
            ]);

            $reconciliation->update([
                'bank_account_balance_id' => $balanceRecord->id,
            ]);

            $account->forceFill([
                'current_balance' => $reconciliation->resulting_balance,
                'balance_updated_at' => $endDate->toDateTimeString(),
            ])->save();

            return $reconciliation->load('items');
        });
    }

    public function cancelLast(User $user, BankAccount $account): BankAccountReconciliation
    {
        $reconciliation = BankAccountReconciliation::where('bank_account_id', $account->id)
            ->where('condominium_id', $user->condominium_id)
            ->latest('created_at')
            ->first();

        if (!$reconciliation) {
            throw ValidationException::withMessages([
                'reconciliation' => 'Não há conciliações para cancelar.',
            ]);
        }

        return $this->database->transaction(function () use ($reconciliation, $account) {
            $items = $reconciliation->items;

            $transactionIds = $items
                ->where('source_type', 'transaction')
                ->pluck('source_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($transactionIds)) {
                Transaction::whereIn('id', $transactionIds)->update([
                    'reconciliation_id' => null,
                ]);
            }

            $accountEntryIds = $items
                ->where('source_type', 'condominium_account')
                ->pluck('source_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($accountEntryIds)) {
                CondominiumAccount::whereIn('id', $accountEntryIds)->update([
                    'reconciliation_id' => null,
                ]);
            }

            if ($reconciliation->bank_account_balance_id) {
                BankAccountBalance::where('id', $reconciliation->bank_account_balance_id)->delete();
            }

            $account->forceFill([
                'current_balance' => $reconciliation->previous_balance,
                'balance_updated_at' => optional($reconciliation->previous_balance_updated_at)->toDateTimeString(),
            ])->save();

            $reconciliation->items()->delete();
            $reconciliation->delete();

            return $reconciliation;
        });
    }

    private function buildGroup(string $label, string $direction, string $sourceType, Collection $items): array
    {
        $items = $items->map(fn (array $item) => array_merge($item, [
            'direction' => $direction,
            'source_type' => $sourceType,
        ]));

        return [
            'label' => $label,
            'direction' => $direction,
            'source_type' => $sourceType,
            'total' => $items->sum('amount'),
            'count' => $items->count(),
            'items' => $items,
        ];
    }
}

