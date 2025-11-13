<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Charge;
use App\Models\CondominiumAccount;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialStatusController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();

        if (! $user->can('view_charges') && ! $user->can('view_own_financial')) {
            abort(403);
        }

        $condominiumId = $user->condominium_id;
        $startDate = $request->filled('start_date')
            ? \Carbon\Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? \Carbon\Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfMonth();

        $units = Unit::with(['morador'])
            ->where('condominium_id', $condominiumId)
            ->withSum(['charges as pending_total' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('due_date', [$startDate, $endDate])
                    ->where('status', 'pending');
            }], 'amount')
            ->withSum(['charges as overdue_total' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('due_date', [$startDate, $endDate])
                    ->where('status', 'overdue');
            }], 'amount')
            ->withSum(['charges as paid_total' => function ($query) use ($startDate, $endDate) {
                $query->where('status', 'paid')
                    ->whereNotNull('paid_at')
                    ->whereBetween('paid_at', [$startDate, $endDate]);
            }], 'amount')
            ->orderBy('block')
            ->orderBy('number')
            ->get();

        $inadimplentes = $units->filter(function ($unit) {
            return ($unit->pending_total ?? 0) > 0 || ($unit->overdue_total ?? 0) > 0;
        });

        $adimplentes = $units->filter(function ($unit) {
            return ($unit->pending_total ?? 0) == 0 && ($unit->overdue_total ?? 0) == 0;
        });

        $chargesByDueDate = Charge::where('condominium_id', $condominiumId)
            ->whereBetween('due_date', [$startDate, $endDate]);

        $paidChargesTotal = Charge::where('condominium_id', $condominiumId)
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->sum('amount');

        $manualIncomeTotal = CondominiumAccount::byCondominium($condominiumId)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where(function ($query) {
                $query->whereNull('source_type')
                    ->orWhere('source_type', '!=', 'charge');
            })
            ->sum('amount');

        $summary = [
            'total_units' => $units->count(),
            'total_pending' => (clone $chargesByDueDate)->where('status', 'pending')->sum('amount'),
            'total_overdue' => (clone $chargesByDueDate)->where('status', 'overdue')->sum('amount'),
            'total_paid' => $paidChargesTotal + $manualIncomeTotal,
            'inadimplentes' => $inadimplentes->count(),
            'adimplentes' => $adimplentes->count(),
        ];

        return view('finance.status', [
            'units' => $units,
            'adimplentes' => $adimplentes,
            'inadimplentes' => $inadimplentes,
            'summary' => $summary,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'canExport' => $user->can('export_financial_reports'),
        ]);
    }
}

