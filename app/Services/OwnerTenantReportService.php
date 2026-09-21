<?php

namespace App\Services;

use App\Models\Charge;
use App\Models\Fine;
use App\Models\Unit;
use App\Models\User;
use App\Support\CondominiumDocuments;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class OwnerTenantReportService
{
    public function __construct(
        private readonly UnitOccupancyService $occupancy,
    ) {
    }

    public function assertOwnerMayAccess(User $owner, Unit $unit): void
    {
        abort_unless($this->occupancy->userOwnsUnit($owner, $unit), 403, 'Você não é o proprietário desta unidade.');
        abort_unless(
            (int) $unit->condominium_id === (int) $owner->tenantCondominiumId(),
            403,
            'Unidade fora do condomínio ativo.'
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function buildReportData(User $owner, Unit $unit, Request $request): array
    {
        $unit->loadMissing(['morador', 'condominium']);
        $condominium = $unit->condominium;

        $chargesQuery = Charge::query()
            ->where('unit_id', $unit->id)
            ->where('condominium_id', $unit->condominium_id)
            ->orderByDesc('due_date')
            ->orderByDesc('id');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $chargesQuery->whereBetween('due_date', [
                $request->input('start_date'),
                $request->input('end_date'),
            ]);
        }

        $allCharges = $chargesQuery->get();
        $tenantCharges = $allCharges->filter(
            fn (Charge $charge) => $this->occupancy->isMoradorResponsibleCharge($charge)
        )->values();

        $fines = Fine::query()
            ->issued()
            ->byCondominium((int) $unit->condominium_id)
            ->whereHas('recipients', fn ($q) => $q->where('unit_id', $unit->id))
            ->orderByDesc('applied_at')
            ->get();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $from = Carbon::parse($request->input('start_date'))->startOfDay();
            $to = Carbon::parse($request->input('end_date'))->endOfDay();
            $fines = $fines->filter(function (Fine $fine) use ($from, $to) {
                $at = $fine->applied_at ?? $fine->due_date;

                return $at && $at->between($from, $to);
            })->values();
        }

        $openTenant = $tenantCharges->filter(
            fn (Charge $c) => in_array($c->effectiveStatus(), ['pending', 'overdue'], true)
        );
        $overdueTenant = $tenantCharges->filter(fn (Charge $c) => $c->effectiveStatus() === 'overdue');

        return [
            'condominium' => CondominiumDocuments::presentCondominium($condominium),
            'owner' => [
                'name' => $owner->name,
                'email' => $owner->email,
            ],
            'unit' => [
                'identifier' => $unit->full_identifier,
                'regime' => $unit->occupancy_regime_label,
                'lease_ends_at' => $unit->lease_contract_ends_at?->format('d/m/Y'),
            ],
            'tenant' => $unit->morador ? [
                'name' => $unit->morador->name,
                'email' => $unit->morador->email,
            ] : null,
            'period' => $this->describePeriod($request),
            'summary' => [
                'tenant_charges_total' => $tenantCharges->count(),
                'tenant_open' => $openTenant->count(),
                'tenant_overdue' => $overdueTenant->count(),
                'tenant_open_amount' => $this->money($openTenant->sum('amount')),
                'fines_count' => $fines->count(),
                'fines_amount' => $this->money($fines->sum('amount')),
            ],
            'charges' => $tenantCharges->map(fn (Charge $c) => [
                'title' => $c->title,
                'due_date' => $c->due_date?->format('d/m/Y') ?? '—',
                'paid_at' => $c->paid_at?->format('d/m/Y') ?? '—',
                'amount' => $this->money($c->amount),
                'status' => $this->statusLabel($c->effectiveStatus()),
            ])->all(),
            'fines' => $fines->map(fn (Fine $f) => [
                'reference' => $f->reference,
                'motivo' => $f->motivo,
                'enquadramento' => $f->enquadramento,
                'applied_at' => $f->applied_at?->format('d/m/Y') ?? '—',
                'amount' => $this->money($f->amount),
                'status' => $f->status_label,
            ])->all(),
            'generated_at' => now()->format('d/m/Y H:i:s'),
            'app_name' => config('app.name', 'SindCON'),
        ];
    }

    public function downloadPdf(User $owner, Unit $unit, Request $request): Response
    {
        $this->assertOwnerMayAccess($owner, $unit);

        $data = $this->buildReportData($owner, $unit, $request);

        $slug = str_replace(['/', '\\'], '-', $unit->full_identifier);
        $filename = sprintf(
            'relatorio_inquilino_%s_%s.pdf',
            $slug,
            now()->format('Y-m-d_His')
        );

        $pdf = Pdf::loadView('reports.pdf.owner-tenant-report', $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    protected function describePeriod(Request $request): ?string
    {
        if (!$request->filled('start_date') || !$request->filled('end_date')) {
            return 'Histórico completo disponível no sistema';
        }

        return 'Período: '
            . Carbon::parse($request->input('start_date'))->format('d/m/Y')
            . ' a '
            . Carbon::parse($request->input('end_date'))->format('d/m/Y');
    }

    protected function statusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Pendente',
            'overdue' => 'Em atraso',
            'paid' => 'Pago',
            'cancelled' => 'Cancelada',
            default => ucfirst($status),
        };
    }

    protected function money($value): string
    {
        return 'R$ ' . number_format((float) $value, 2, ',', '.');
    }
}
