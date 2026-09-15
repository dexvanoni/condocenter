<?php

namespace App\Http\Controllers;

use App\Exports\PackageMovementsExport;
use App\Models\Unit;
use App\Services\PackageService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PackageWebController extends Controller
{
    public function __construct(private readonly PackageService $packageService) {}

    public function index()
    {
        $user = Auth::user();

        abort_unless($user->can('register_packages') || $user->can('view_packages'), 403);

        if ($user->can('register_packages')) {
            return view('packages.index');
        }

        return redirect()->route('packages.reports');
    }

    public function reports(Request $request)
    {
        $user = Auth::user();

        abort_unless($user->can('view_packages'), 403);

        $filters = $this->filtersFromRequest($request);
        $movements = $this->packageService->listMovements($user, $filters, paginate: true);
        $stats = $this->packageService->movementStatistics($user, $filters);
        $units = Unit::query()
            ->where('condominium_id', $user->tenantCondominiumId())
            ->orderBy('block')
            ->orderBy('number')
            ->get(['id', 'block', 'number']);

        return view('packages.reports', compact('movements', 'stats', 'units', 'filters'));
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();

        abort_unless($user->can('export_packages_reports'), 403);

        $filters = $this->filtersFromRequest($request);
        $movements = $this->packageService->listMovements($user, $filters);

        $pdf = Pdf::loadView('packages.export-pdf', [
            'movements' => $movements,
            'from' => $filters['from'],
            'to' => $filters['to'],
            'condominium' => $user->activeCondominium() ?? $user->condominium,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('movimentacoes-encomendas-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $user = Auth::user();

        abort_unless($user->can('export_packages_reports'), 403);

        $filters = $this->filtersFromRequest($request);
        $movements = $this->packageService->listMovements($user, $filters);

        $filename = 'movimentacoes-encomendas-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new PackageMovementsExport($movements), $filename);
    }

    /**
     * @return array{from: string, to: string, status: ?string, unit_id: ?int, search: ?string}
     */
    private function filtersFromRequest(Request $request): array
    {
        return [
            'from' => $request->input('from', now()->subDays(30)->toDateString()),
            'to' => $request->input('to', now()->toDateString()),
            'status' => $request->filled('status') ? $request->input('status') : null,
            'unit_id' => $request->filled('unit_id') ? (int) $request->input('unit_id') : null,
            'search' => $request->filled('search') ? trim((string) $request->input('search')) : null,
        ];
    }
}
