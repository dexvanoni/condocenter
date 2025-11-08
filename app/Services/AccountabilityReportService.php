<?php

namespace App\Services;

use App\Models\Charge;
use App\Models\CondominiumAccount;
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
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date')
            ->get();

        $manualExpenses = CondominiumAccount::with('creator')
            ->where('condominium_id', $condominiumId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date')
            ->get();

        $chargesPaid = Charge::with(['unit', 'fee'])
            ->where('condominium_id', $condominiumId)
            ->where('status', 'paid')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->orderBy('due_date')
            ->get();

        $payments = Payment::with(['charge.unit'])
            ->whereHas('charge', function ($query) use ($condominiumId, $startDate, $endDate) {
                $query->where('condominium_id', $condominiumId)
                    ->whereBetween('due_date', [$startDate, $endDate]);
            })
            ->orderBy('payment_date')
            ->get();

        $openingBalance = $this->calculateBalanceUntil($condominiumId, $startDate->copy()->subDay());

        $totals = [
            'manual_income' => $manualIncomes->sum('amount'),
            'manual_expense' => $manualExpenses->sum('amount'),
            'charges_income' => $chargesPaid->sum('amount'),
        ];

        $totals['total_income'] = $totals['manual_income'] + $totals['charges_income'];
        $totals['total_expense'] = $totals['manual_expense'];
        $totals['balance_period'] = $totals['total_income'] - $totals['total_expense'];
        $totals['opening_balance'] = $openingBalance;
        $totals['closing_balance'] = $openingBalance + $totals['balance_period'];

        return [
            'manual_incomes' => $manualIncomes,
            'manual_expenses' => $manualExpenses,
            'charges_paid' => $chargesPaid,
            'payments' => $payments,
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

