<?php

namespace App\Http\Controllers\Finance;

use App\Exports\AccountabilityExport;
use App\Http\Controllers\Controller;
use App\Services\AccountabilityReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;

class AccountabilityReportController extends Controller
{
    public function __construct(
        private readonly AccountabilityReportService $service
    ) {
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if (! $user->can('view_accountability_reports') && ! $user->can('view_financial_reports')) {
            abort(403);
        }

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfMonth();

        $data = $this->service->generate($user->condominium_id, $startDate, $endDate);

        return view('finance.accountability.index', [
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'canExport' => $user->can('export_accountability_reports'),
        ]);
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();

        if (! $user->can('export_accountability_reports')) {
            abort(403);
        }

        [$startDate, $endDate] = $this->resolvePeriod($request);

        $data = $this->service->generate($user->condominium_id, $startDate, $endDate);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('finance.accountability.pdf', [
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'condominium' => $user->condominium,
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('prestacao_contas_' . $startDate->format('Ymd') . '_' . $endDate->format('Ymd') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $user = Auth::user();

        if (! $user->can('export_accountability_reports')) {
            abort(403);
        }

        [$startDate, $endDate] = $this->resolvePeriod($request);

        $data = $this->service->generate($user->condominium_id, $startDate, $endDate);

        return Excel::download(
            new AccountabilityExport($user->condominium, $data, $startDate, $endDate),
            'prestacao_contas_' . $startDate->format('Ymd') . '_' . $endDate->format('Ymd') . '.xlsx'
        );
    }

    public function print(Request $request)
    {
        $user = Auth::user();

        if (! $user->can('export_accountability_reports')) {
            abort(403);
        }

        [$startDate, $endDate] = $this->resolvePeriod($request);
        $data = $this->service->generate($user->condominium_id, $startDate, $endDate);

        return view('finance.accountability.print', [
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'condominium' => $user->condominium,
        ]);
    }

    protected function resolvePeriod(Request $request): array
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->startOfMonth();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfMonth();

        return [$startDate, $endDate];
    }
}

