<?php

namespace App\Services;

use App\Models\Charge;
use App\Models\CondominiumAccount;
use App\Models\BankAccount;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AccountabilityReportService
{
    public function generate(int $condominiumId, Carbon $startDate, Carbon $endDate): array
    {
        $manualIncomes = CondominiumAccount::with('creator')
            ->where('condominium_id', $condominiumId)
            ->where('type', 'income')
            ->where(function ($query) {
                $query->whereNull('source_type')
                    ->orWhere('source_type', 'manual_income');
            })
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date')
            ->get();

        $manualExpenses = CondominiumAccount::with('creator')
            ->where('condominium_id', $condominiumId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date')
            ->get();

        $chargeIncomeEntries = CondominiumAccount::where('condominium_id', $condominiumId)
            ->where('type', 'income')
            ->where('source_type', 'charge')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date')
            ->get();

        $chargeIds = $chargeIncomeEntries->pluck('source_id')->filter()->unique();
        $chargesById = Charge::with('fee')
            ->whereIn('id', $chargeIds)
            ->get()
            ->keyBy('id');

        $chargeSummary = $chargeIncomeEntries
            ->groupBy(function (CondominiumAccount $entry) use ($chargesById) {
                $charge = $chargesById->get($entry->source_id);
                return $charge?->fee?->name ?? $charge?->title ?? $entry->description ?? 'Cobranças';
            })
            ->map(function (Collection $group, $name) {
                return [
                    'name' => $name,
                    'total' => $group->sum('amount'),
                ];
            })
            ->values();

        $payments = Payment::with(['charge'])
            ->whereHas('charge', function ($query) use ($condominiumId) {
                $query->where('condominium_id', $condominiumId);
            })
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->orderBy('payment_date')
            ->get();

        $paymentsSummary = $payments
            ->groupBy(function (Payment $payment) {
                return strtoupper($payment->payment_method ?? 'OUTROS');
            })
            ->map(function (Collection $group, string $method) {
                return [
                    'method' => $method === 'OUTROS' ? 'Outros métodos' : $method,
                    'transactions' => $group->count(),
                    'total' => $group->sum('amount_paid'),
                ];
            })
            ->values();

        $bankAccounts = BankAccount::where('condominium_id', $condominiumId)
            ->orderBy('name')
            ->get()
            ->map(function (BankAccount $account) {
                $latestHistory = $account->balances()
                    ->orderByDesc('recorded_at')
                    ->orderByDesc('created_at')
                    ->first();

                return [
                    'name' => $account->name,
                    'institution' => $account->institution,
                    'holder' => $account->holder_name,
                    'current_balance' => $account->current_balance,
                    'balance_updated_at' => $account->balance_updated_at,
                    'history' => $latestHistory,
                ];
            });

        $openingBalance = $this->calculateBalanceUntil($condominiumId, $startDate->copy()->subDay());

        $totals = [
            'manual_income' => $manualIncomes->sum('amount'),
            'manual_expense' => $manualExpenses->sum('amount'),
            'charges_income' => $chargeIncomeEntries->sum('amount'),
        ];

        $totals['total_income'] = $totals['manual_income'] + $totals['charges_income'];
        $totals['total_expense'] = $totals['manual_expense'];
        $totals['balance_period'] = $totals['total_income'] - $totals['total_expense'];
        $totals['opening_balance'] = $openingBalance;
        $totals['closing_balance'] = $openingBalance + $totals['balance_period'];

        return [
            'manual_incomes' => $manualIncomes,
            'manual_expenses' => $manualExpenses,
            'charge_summary' => $chargeSummary,
            'payments_summary' => $paymentsSummary,
            'bank_accounts' => $bankAccounts,
            'totals' => $totals,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    protected function calculateBalanceUntil(int $condominiumId, Carbon $date): float
    {
        if ($date->isPast() === false) {
            return 0.0;
        }

        $income = CondominiumAccount::where('condominium_id', $condominiumId)
            ->where('type', 'income')
            ->where('transaction_date', '<=', $date)
            ->sum('amount');

        $expenses = CondominiumAccount::where('condominium_id', $condominiumId)
            ->where('type', 'expense')
            ->where('transaction_date', '<=', $date)
            ->sum('amount');

        return $income - $expenses;
    }
}

