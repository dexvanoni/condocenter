<?php

namespace App\Http\Controllers;

use App\Services\AccessControlService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccessControlWebController extends Controller
{
    public function __construct(private AccessControlService $accessControl) {}

    public function porteiroPanel()
    {
        $user = Auth::user();

        abort_unless($user->can('process_access'), 403);

        return view('access-control.porteiro');
    }

    public function residentIndex()
    {
        $user = Auth::user();

        abort_unless(
            $user->can('create_access_authorizations')
                || $user->can('manage_access_lists')
                || $user->can('manage_service_providers'),
            403
        );

        return view('access-control.resident', [
            'isMorador' => $user->isMorador(),
            'isAgregado' => $user->isAgregado(),
            'canManageProviders' => $user->can('manage_service_providers'),
            'canManageCondoProviders' => $user->can('manage_condominium_service_providers'),
            'visitorPresets' => AccessControlService::INDIVIDUAL_VISITOR_PRESETS,
        ]);
    }

    public function reports(Request $request)
    {
        $user = Auth::user();

        abort_unless($user->can('view_access_movements'), 403);

        $condominiumId = $user->tenantCondominiumId();

        $movements = $this->accessControl->listMovements($condominiumId, [
            'from' => $request->input('from', now()->subDays(30)->toDateString()),
            'to' => $request->input('to', now()->toDateString()),
            'unit_id' => $request->input('unit_id'),
        ]);

        return view('access-control.reports', compact('movements'));
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();

        abort_unless($user->can('export_access_reports'), 403);

        $condominiumId = $user->tenantCondominiumId();
        $from = $request->input('from', now()->subDays(30)->toDateString());
        $to = $request->input('to', now()->toDateString());

        $movements = $this->accessControl->listMovements($condominiumId, [
            'from' => $from,
            'to' => $to,
            'unit_id' => $request->input('unit_id'),
        ]);

        $pdf = Pdf::loadView('access-control.export-pdf', [
            'movements' => $movements,
            'from' => $from,
            'to' => $to,
            'condominium' => $user->activeCondominium() ?? $user->condominium,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('movimentacoes-acesso-' . now()->format('Y-m-d') . '.pdf');
    }
}
