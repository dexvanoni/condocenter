<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Charge;
use App\Models\CondominiumAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class IncomeExpenseController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Acessível por todos que podem ver transações ou próprio financeiro
        if (!$user->can('view_transactions') && !$user->can('view_own_financial')) {
            abort(403);
        }

        $condominiumId = $user->condominium_id;
        
        // Período padrão: mês atual
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfMonth();

        // Entradas
        $incomeQuery = CondominiumAccount::with('creator')
            ->byCondominium($condominiumId)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at');

        // Saídas
        $expenseQuery = CondominiumAccount::with('creator')
            ->byCondominium($condominiumId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at');

        // Verifica se é morador (privacidade)
        $isMorador = $user->isMorador() && !$user->isAdmin() && !$user->isSindico();

        // Se for morador, filtrar apenas suas próprias entradas
        if ($isMorador) {
            $chargeIncomeIds = (clone $incomeQuery)
                ->where('source_type', 'charge')
                ->get()
                ->filter(function ($entry) use ($user) {
                    $charge = Charge::find($entry->source_id);
                    return $charge && $charge->unit_id === $user->unit_id;
                })
                ->pluck('id');

            $incomeQuery = (clone $incomeQuery)->where(function ($q) use ($user, $chargeIncomeIds) {
                $q->where('source_type', '!=', 'charge')
                    ->orWhereIn('id', $chargeIncomeIds);
            });
        }

        $incomes = $incomeQuery->get();
        $expenses = $expenseQuery->get();

        // Separar entradas de taxas (charges) e entradas manuais (avulsas)
        $chargeIncomes = $incomes->where('source_type', 'charge');
        $manualIncomes = $incomes->where('source_type', '!=', 'charge');

        // Obter todas as taxas (fees) relacionadas às cobranças para agrupar
        $chargeIds = $chargeIncomes->pluck('source_id')->unique();
        $chargesById = Charge::with(['fee'])
            ->whereIn('id', $chargeIds)
            ->get()
            ->keyBy('id');

        // Agrupar entradas de taxas por fee_id
        $groupedTaxIncomes = collect();
        $chargeIncomes->groupBy(function ($entry) use ($chargesById) {
            $charge = $chargesById->get($entry->source_id);
            return $charge?->fee_id ?? 'unknown';
        })->each(function ($entries, $feeId) use ($chargesById, &$groupedTaxIncomes) {
            $firstEntry = $entries->first();
            $firstCharge = $chargesById->get($firstEntry->source_id);
            
            // Calcular total e quantidade de cobranças
            $total = $entries->sum('amount');
            $count = $entries->count();
            
            // Data mais recente do grupo
            $latestDate = $entries->max('transaction_date');
            
            $groupedTaxIncomes->push([
                'id' => 'fee_' . $feeId,
                'date' => $latestDate,
                'description' => $firstCharge?->fee?->name ?? $firstCharge?->title ?? 'Taxa de Condomínio',
                'amount' => $total,
                'payment_method' => 'VARIADO',
                'source_type' => 'charge_grouped',
                'count' => $count,
                'fee_id' => $feeId,
            ]);
        });

        // Processar entradas manuais (avulsas) - uma linha por entrada
        $manualIncomeData = $manualIncomes->map(function (CondominiumAccount $entry) {
            return [
                'id' => $entry->id,
                'date' => $entry->transaction_date,
                'description' => $entry->description,
                'amount' => $entry->amount,
                'payment_method' => $entry->payment_method,
                'source_type' => 'manual',
                'created_by' => $entry->creator?->name,
                'notes' => $entry->notes,
            ];
        });

        // Combinar entradas agrupadas (taxas) + entradas manuais
        $incomeData = $groupedTaxIncomes->concat($manualIncomeData)
            ->sortByDesc('date')
            ->values();

        $expenseData = $expenses->map(function (CondominiumAccount $entry) {
            return [
                'id' => $entry->id,
                'date' => $entry->transaction_date,
                'description' => $entry->description,
                'amount' => $entry->amount,
                'payment_method' => $entry->payment_method,
                'installments' => $entry->installments_total 
                    ? ($entry->installment_number ?? 1) . '/' . $entry->installments_total 
                    : null,
                'created_by' => $entry->creator?->name,
                'notes' => $entry->notes,
            ];
        });

        // Converter para Collections
        $incomeCollection = collect($incomeData);
        $expenseCollection = collect($expenseData);

        // Resumo
        $incomeTotal = $incomeCollection->sum('amount');
        $expenseTotal = $expenseCollection->sum('amount');
        $balance = $incomeTotal - $expenseTotal;

        return view('finance.income-expense.index', compact(
            'incomeCollection',
            'expenseCollection',
            'incomeTotal',
            'expenseTotal',
            'balance',
            'startDate',
            'endDate',
            'isMorador',
            'user'
        ));
    }

    public function exportIncomePdf(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->can('view_transactions') && !$user->can('view_own_financial')) {
            abort(403);
        }

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfMonth();

        $data = $this->getIncomeData($user, $startDate, $endDate);

        $pdf = Pdf::loadView('finance.income-expense.export.income-pdf', [
            'data' => collect($data['data']),
            'total' => $data['total'],
            'startDate' => $data['startDate'],
            'endDate' => $data['endDate'],
            'condominium' => $data['condominium'],
        ]);
        $pdf->setPaper('a4', 'landscape');

        $filename = 'entradas_' . $startDate->format('Ymd') . '_' . $endDate->format('Ymd') . '.pdf';
        
        return $pdf->download($filename);
    }

    public function exportIncomeExcel(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->can('view_transactions') && !$user->can('view_own_financial')) {
            abort(403);
        }

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfMonth();

        $data = $this->getIncomeData($user, $startDate, $endDate);

        return Excel::download(
            new \App\Exports\IncomeExport([
                'data' => collect($data['data']),
                'total' => $data['total'],
                'startDate' => $data['startDate'],
                'endDate' => $data['endDate'],
                'condominium' => $data['condominium'],
            ]),
            'entradas_' . $startDate->format('Ymd') . '_' . $endDate->format('Ymd') . '.xlsx'
        );
    }

    public function exportExpensePdf(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->can('view_transactions') && !$user->can('view_own_financial')) {
            abort(403);
        }

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfMonth();

        $data = $this->getExpenseData($user, $startDate, $endDate);

        $pdf = Pdf::loadView('finance.income-expense.export.expense-pdf', [
            'data' => collect($data['data']),
            'total' => $data['total'],
            'startDate' => $data['startDate'],
            'endDate' => $data['endDate'],
            'condominium' => $data['condominium'],
        ]);
        $pdf->setPaper('a4', 'landscape');

        $filename = 'saidas_' . $startDate->format('Ymd') . '_' . $endDate->format('Ymd') . '.pdf';
        
        return $pdf->download($filename);
    }

    public function exportExpenseExcel(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->can('view_transactions') && !$user->can('view_own_financial')) {
            abort(403);
        }

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfMonth();

        $data = $this->getExpenseData($user, $startDate, $endDate);

        return Excel::download(
            new \App\Exports\ExpenseExport([
                'data' => collect($data['data']),
                'total' => $data['total'],
                'startDate' => $data['startDate'],
                'endDate' => $data['endDate'],
                'condominium' => $data['condominium'],
            ]),
            'saidas_' . $startDate->format('Ymd') . '_' . $endDate->format('Ymd') . '.xlsx'
        );
    }

    protected function getIncomeData($user, $startDate, $endDate)
    {
        $condominiumId = $user->condominium_id;
        $isMorador = $user->isMorador() && !$user->isAdmin() && !$user->isSindico();

        $incomeQuery = CondominiumAccount::with('creator')
            ->byCondominium($condominiumId)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at');

        if ($isMorador) {
            $chargeIncomeIds = (clone $incomeQuery)
                ->where('source_type', 'charge')
                ->get()
                ->filter(function ($entry) use ($user) {
                    $charge = Charge::find($entry->source_id);
                    return $charge && $charge->unit_id === $user->unit_id;
                })
                ->pluck('id');

            $incomeQuery = (clone $incomeQuery)->where(function ($q) use ($user, $chargeIncomeIds) {
                $q->where('source_type', '!=', 'charge')
                    ->orWhereIn('id', $chargeIncomeIds);
            });
        }

        $incomes = $incomeQuery->get();

        // Separar entradas de taxas (charges) e entradas manuais (avulsas)
        $chargeIncomes = $incomes->where('source_type', 'charge');
        $manualIncomes = $incomes->where('source_type', '!=', 'charge');

        // Obter todas as taxas (fees) relacionadas às cobranças para agrupar
        $chargeIds = $chargeIncomes->pluck('source_id')->unique();
        $chargesById = Charge::with(['fee'])
            ->whereIn('id', $chargeIds)
            ->get()
            ->keyBy('id');

        // Agrupar entradas de taxas por fee_id
        $groupedTaxIncomes = collect();
        $chargeIncomes->groupBy(function ($entry) use ($chargesById) {
            $charge = $chargesById->get($entry->source_id);
            return $charge?->fee_id ?? 'unknown';
        })->each(function ($entries, $feeId) use ($chargesById, &$groupedTaxIncomes) {
            $firstEntry = $entries->first();
            $firstCharge = $chargesById->get($firstEntry->source_id);
            
            // Calcular total e quantidade de cobranças
            $total = $entries->sum('amount');
            $count = $entries->count();
            
            // Data mais recente do grupo
            $latestDate = $entries->max('transaction_date');
            
            $groupedTaxIncomes->push([
                'id' => 'fee_' . $feeId,
                'date' => $latestDate,
                'description' => $firstCharge?->fee?->name ?? $firstCharge?->title ?? 'Taxa de Condomínio',
                'amount' => $total,
                'payment_method' => 'VARIADO',
                'source_type' => 'charge_grouped',
                'count' => $count,
                'fee_id' => $feeId,
            ]);
        });

        // Processar entradas manuais (avulsas) - uma linha por entrada
        $manualIncomeData = $manualIncomes->map(function (CondominiumAccount $entry) {
            return [
                'id' => $entry->id,
                'date' => $entry->transaction_date,
                'description' => $entry->description,
                'amount' => $entry->amount,
                'payment_method' => $entry->payment_method,
                'source_type' => 'manual',
                'created_by' => $entry->creator?->name,
                'notes' => $entry->notes,
            ];
        });

        // Combinar entradas agrupadas (taxas) + entradas manuais
        $incomeData = $groupedTaxIncomes->concat($manualIncomeData)
            ->sortByDesc('date')
            ->values();

        $total = collect($incomeData)->sum('amount');

        return [
            'data' => $incomeData,
            'total' => $total,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'condominium' => $user->condominium,
            'user' => $user,
        ];
    }

    protected function getExpenseData($user, $startDate, $endDate)
    {
        $condominiumId = $user->condominium_id;

        $expenses = CondominiumAccount::with('creator')
            ->byCondominium($condominiumId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->get();

        $expenseData = $expenses->map(function (CondominiumAccount $entry) {
            return [
                'id' => $entry->id,
                'date' => $entry->transaction_date,
                'description' => $entry->description,
                'amount' => $entry->amount,
                'payment_method' => $entry->payment_method,
                'installments' => $entry->installments_total 
                    ? ($entry->installment_number ?? 1) . '/' . $entry->installments_total 
                    : null,
                'created_by' => $entry->creator?->name,
                'notes' => $entry->notes,
            ];
        });

        $total = $expenseData->sum('amount');

        return [
            'data' => $expenseData,
            'total' => $total,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'condominium' => $user->condominium,
            'user' => $user,
        ];
    }
}
