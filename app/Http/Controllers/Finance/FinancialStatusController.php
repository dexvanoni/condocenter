<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Charge;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

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
                $query->whereBetween('due_date', [$startDate, $endDate])
                    ->where('status', 'paid');
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

        $chargesQuery = Charge::where('condominium_id', $condominiumId)
            ->whereBetween('due_date', [$startDate, $endDate]);

        $summary = [
            'total_units' => $units->count(),
            'total_pending' => (clone $chargesQuery)->where('status', 'pending')->sum('amount'),
            'total_overdue' => (clone $chargesQuery)->where('status', 'overdue')->sum('amount'),
            'total_paid' => (clone $chargesQuery)->where('status', 'paid')->sum('amount'),
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

