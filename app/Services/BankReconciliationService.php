<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\BankAccountBalance;
use App\Models\BankAccountReconciliation;
use App\Models\BankAccountReconciliationItem;
use App\Models\BankStatement;
use App\Models\BankStatementLine;
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
        private readonly BankAccountRoutingService $bankAccountRoutingService,
    ) {
    }

    public function preview(
        int $condominiumId,
        BankAccount $account,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        bool $onlyLinkedToStatement = false,
    ): array {
        $linkedPairs = $onlyLinkedToStatement
            ? $this->linkedSourceLookup($account->id, $startDate, $endDate)
            : null;

        $transactionsIncome = $this->pendingTransactionsQuery($condominiumId, 'income', $startDate, $endDate)
            ->get()
            ->filter(fn (Transaction $transaction) => $this->matchesBankAccount($transaction, $account->id))
            ->when($linkedPairs !== null, fn (Collection $items) => $items->filter(
                fn (Transaction $transaction) => isset($linkedPairs['transaction:'.$transaction->id])
            ));

        $transactionsExpense = $this->pendingTransactionsQuery($condominiumId, 'expense', $startDate, $endDate)
            ->get()
            ->filter(fn (Transaction $transaction) => $this->matchesBankAccount($transaction, $account->id))
            ->when($linkedPairs !== null, fn (Collection $items) => $items->filter(
                fn (Transaction $transaction) => isset($linkedPairs['transaction:'.$transaction->id])
            ));

        $accountIncomes = $this->pendingCondominiumAccountsQuery($condominiumId, 'income', $startDate, $endDate)
            ->get()
            ->filter(fn (CondominiumAccount $entry) => $this->matchesBankAccount($entry, $account->id))
            ->when($linkedPairs !== null, fn (Collection $items) => $items->filter(
                fn (CondominiumAccount $entry) => isset($linkedPairs['condominium_account:'.$entry->id])
            ));

        $accountExpenses = $this->pendingCondominiumAccountsQuery($condominiumId, 'expense', $startDate, $endDate)
            ->get()
            ->filter(fn (CondominiumAccount $entry) => $this->matchesBankAccount($entry, $account->id))
            ->when($linkedPairs !== null, fn (Collection $items) => $items->filter(
                fn (CondominiumAccount $entry) => isset($linkedPairs['condominium_account:'.$entry->id])
            ));
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
                    'label' => $transaction->description ?: 'Receita registrada',
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
                    'label' => $entry->description ?: 'Recebimento de taxa',
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
                    'label' => $entry->description ?: 'Recebimento avulso',
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
                    'label' => $transaction->description ?: 'Despesa registrada',
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
                    'label' => $entry->description ?: 'Pagamento registrado',
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

    public function reconcile(
        User $user,
        BankAccount $account,
        Carbon $startDate,
        Carbon $endDate,
        ?array $preview = null,
        bool $acknowledgeBalanceDifference = false,
    ): BankAccountReconciliation {
        $condominiumId = $user->tenantCondominiumId();
        $activeStatement = $this->activeStatementCovering($account->id, $startDate, $endDate);
        $onlyLinked = $activeStatement !== null;

        $preview ??= $this->preview($condominiumId, $account, $startDate, $endDate, $onlyLinked);

        if ($preview['totals']['count_entries'] === 0) {
            throw ValidationException::withMessages([
                'period' => $onlyLinked
                    ? 'Não há lançamentos vinculados ao extrato para fechar neste período.'
                    : 'Não há movimentações elegíveis para conciliação no período informado.',
            ]);
        }

        $resultingBalance = ($account->current_balance ?? 0) + $preview['totals']['net'];

        if ($activeStatement && $activeStatement->closing_balance !== null) {
            $difference = round((float) $activeStatement->closing_balance - (float) $resultingBalance, 2);
            if (abs($difference) >= 0.01 && ! $acknowledgeBalanceDifference) {
                throw ValidationException::withMessages([
                    'acknowledge_balance_difference' => sprintf(
                        'O saldo do extrato (R$ %s) difere do saldo projetado (R$ %s). Confirme a ciência da diferença para continuar.',
                        number_format((float) $activeStatement->closing_balance, 2, ',', '.'),
                        number_format($resultingBalance, 2, ',', '.')
                    ),
                ]);
            }
        }

        return $this->database->transaction(function () use ($preview, $user, $account, $startDate, $endDate, $activeStatement, $resultingBalance) {
            $previousBalance = $account->current_balance ?? 0;
            $previousBalanceUpdatedAt = $account->balance_updated_at;

            $reconciliation = BankAccountReconciliation::create([
                'condominium_id' => $user->tenantCondominiumId(),
                'bank_account_id' => $account->id,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'total_income' => $preview['totals']['income'],
                'total_expense' => $preview['totals']['expense'],
                'net_amount' => $preview['totals']['net'],
                'previous_balance' => $previousBalance,
                'resulting_balance' => $resultingBalance,
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

            if ($activeStatement) {
                $activeStatement->update([
                    'status' => BankStatement::STATUS_RECONCILED,
                ]);
            }

            return $reconciliation->load('items');
        });
    }

    public function activeStatementCovering(int $bankAccountId, Carbon $startDate, Carbon $endDate): ?BankStatement
    {
        return BankStatement::query()
            ->readyForAccount($bankAccountId)
            ->whereNotNull('period_start')
            ->whereNotNull('period_end')
            ->where('period_start', '<=', $endDate->toDateString())
            ->where('period_end', '>=', $startDate->toDateString())
            ->latest('id')
            ->first();
    }

    /**
     * @return array<string, true>
     */
    protected function linkedSourceLookup(int $bankAccountId, ?Carbon $startDate, ?Carbon $endDate): array
    {
        $lines = BankStatementLine::query()
            ->where('bank_account_id', $bankAccountId)
            ->linked()
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('posted_at', [
                    $startDate->toDateString(),
                    $endDate->toDateString(),
                ]);
            })
            ->get(['matched_source_type', 'matched_source_id']);

        $lookup = [];
        foreach ($lines as $line) {
            $lookup[$line->matched_source_type.':'.$line->matched_source_id] = true;
        }

        return $lookup;
    }

    public function cancelLast(User $user, BankAccount $account): BankAccountReconciliation
    {
        $reconciliation = BankAccountReconciliation::where('bank_account_id', $account->id)
            ->where('condominium_id', $user->tenantCondominiumId())
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

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public function resolvePeriodFromPreview(array $preview): array
    {
        $dates = collect($preview['income_groups'])
            ->merge($preview['expense_groups'])
            ->flatMap(fn (array $group) => $group['items'])
            ->pluck('reference_date')
            ->filter()
            ->map(fn ($date) => Carbon::parse($date)->startOfDay());

        if ($dates->isEmpty()) {
            $today = now()->startOfDay();

            return [$today->copy(), $today->copy()->endOfDay()];
        }

        return [
            $dates->min()->copy()->startOfDay(),
            $dates->max()->copy()->endOfDay(),
        ];
    }

    private function pendingTransactionsQuery(int $condominiumId, string $type, ?Carbon $startDate, ?Carbon $endDate)
    {
        $query = Transaction::withTrashed()
            ->where('condominium_id', $condominiumId)
            ->whereNull('reconciliation_id')
            ->where('status', 'paid')
            ->where('type', $type);

        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }

        return $query;
    }

    private function pendingCondominiumAccountsQuery(int $condominiumId, string $type, ?Carbon $startDate, ?Carbon $endDate)
    {
        $query = CondominiumAccount::where('condominium_id', $condominiumId)
            ->whereNull('reconciliation_id')
            ->countsInBalance()
            ->where('type', $type);

        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }

        return $query;
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

    protected function matchesBankAccount(Transaction|CondominiumAccount $entry, int $bankAccountId): bool
    {
        $resolved = $entry instanceof Transaction
            ? $this->bankAccountRoutingService->resolveForTransaction($entry)
            : $this->bankAccountRoutingService->resolveForCondominiumAccount($entry);

        return (int) $resolved === $bankAccountId;
    }
}

