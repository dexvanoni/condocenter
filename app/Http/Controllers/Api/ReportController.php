<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Charge;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Relatório financeiro
     */
    public function financial(Request $request)
    {
        $user = Auth::user();
        $condominiumId = $user->condominium_id;

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        $transactions = Transaction::where('condominium_id', $condominiumId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date')
            ->get();

        $totalReceitas = $transactions->where('type', 'income')->sum('amount');
        $totalDespesas = $transactions->where('type', 'expense')->sum('amount');
        $saldo = $totalReceitas - $totalDespesas;

        // Agrupar por categoria
        $byCategory = $transactions->groupBy('category')->map(function ($items, $category) {
            return [
                'category' => $category,
                'receitas' => $items->where('type', 'income')->sum('amount'),
                'despesas' => $items->where('type', 'expense')->sum('amount'),
                'saldo' => $items->where('type', 'income')->sum('amount') - $items->where('type', 'expense')->sum('amount'),
            ];
        })->values();

        $data = [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'summary' => [
                'total_receitas' => $totalReceitas,
                'total_despesas' => $totalDespesas,
                'saldo' => $saldo,
            ],
            'by_category' => $byCategory,
            'transactions' => $transactions,
        ];

        // Se solicitado PDF
        if ($request->get('format') === 'pdf') {
            $pdf = Pdf::loadView('reports.monthly-financial', [
                'condominium' => $user->condominium,
                'transactions' => $transactions,
                'totalReceitas' => $totalReceitas,
                'totalDespesas' => $totalDespesas,
                'saldo' => $saldo,
                'period' => "$startDate a $endDate",
            ]);

            return $pdf->download('relatorio-financeiro.pdf');
        }

        return response()->json($data);
    }

    /**
     * Relatório de inadimplência
     */
    public function defaulters(Request $request)
    {
        $user = Auth::user();
        $condominiumId = $user->condominium_id;

        $overdueCharges = Charge::with(['unit'])
            ->where('condominium_id', $condominiumId)
            ->where('status', 'overdue')
            ->orderBy('due_date')
            ->get();

        $defaulterUnits = $overdueCharges->groupBy('unit_id')->map(function ($charges, $unitId) {
            $unit = Unit::find($unitId);
            $totalOverdue = $charges->sum(function ($charge) {
                return $charge->calculateTotal();
            });
            $oldestCharge = $charges->sortBy('due_date')->first();

            return [
                'unit' => $unit,
                'total_charges' => $charges->count(),
                'total_amount' => $totalOverdue,
                'oldest_charge_date' => $oldestCharge->due_date,
                'days_overdue' => now()->diffInDays($oldestCharge->due_date),
                'charges' => $charges,
            ];
        })->sortByDesc('total_amount')->values();

        $summary = [
            'total_defaulter_units' => $defaulterUnits->count(),
            'total_overdue_amount' => $overdueCharges->sum(function ($charge) {
                return $charge->calculateTotal();
            }),
            'total_overdue_charges' => $overdueCharges->count(),
        ];

        return response()->json([
            'summary' => $summary,
            'defaulters' => $defaulterUnits,
        ]);
    }

    /**
     * Balancete
     */
    public function balance(Request $request)
    {
        $user = Auth::user();
        $condominiumId = $user->condominium_id;

        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $transactions = Transaction::where('condominium_id', $condominiumId)
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->get();

        $balance = [
            'receitas' => $transactions->where('type', 'income')
                ->groupBy('category')
                ->map(fn($items) => $items->sum('amount'))
                ->toArray(),
            'despesas' => $transactions->where('type', 'expense')
                ->groupBy('category')
                ->map(fn($items) => $items->sum('amount'))
                ->toArray(),
            'total_receitas' => $transactions->where('type', 'income')->sum('amount'),
            'total_despesas' => $transactions->where('type', 'expense')->sum('amount'),
        ];

        $balance['saldo'] = $balance['total_receitas'] - $balance['total_despesas'];

        return response()->json($balance);
    }

    /**
     * Fluxo de caixa
     */
    public function cashFlow(Request $request)
    {
        $user = Auth::user();
        $condominiumId = $user->condominium_id;

        $months = $request->get('months', 6);
        $cashFlow = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            
            $receitas = Transaction::where('condominium_id', $condominiumId)
                ->where('type', 'income')
                ->whereYear('transaction_date', $date->year)
                ->whereMonth('transaction_date', $date->month)
                ->sum('amount');

            $despesas = Transaction::where('condominium_id', $condominiumId)
                ->where('type', 'expense')
                ->whereYear('transaction_date', $date->year)
                ->whereMonth('transaction_date', $date->month)
                ->sum('amount');

            $cashFlow[] = [
                'month' => $date->locale('pt_BR')->translatedFormat('M/Y'),
                'receitas' => $receitas,
                'despesas' => $despesas,
                'saldo' => $receitas - $despesas,
            ];
        }

        return response()->json($cashFlow);
    }
}
