<?php

namespace App\Services;

use App\Models\Charge;
use App\Models\User;
use App\Support\CondominiumDocuments;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ChargeResidentReportService
{
    public function download(User $user, Request $request): Response
    {
        $charges = $this->buildQuery($user, $request)
            ->orderByDesc('due_date')
            ->orderByDesc('id')
            ->get();

        $condominiumId = (int) $user->tenantCondominiumId();
        $condominium = $user->condominium ?? \App\Models\Condominium::query()->find($condominiumId);

        $rows = $charges->map(fn (Charge $charge) => [
            'title' => $charge->title,
            'due_date' => optional($charge->due_date)->format('d/m/Y') ?? '—',
            'paid_at' => optional($charge->paid_at)->format('d/m/Y') ?? '—',
            'amount' => $this->formatMoney($charge->amount),
            'status_label' => $this->statusLabel($charge->status),
        ]);

        $filename = sprintf(
            'minhas_cobrancas_%s.pdf',
            now()->format('Y-m-d_His')
        );

        $pdf = Pdf::loadView('charges.pdf.resident-report', [
            'condominium' => CondominiumDocuments::presentCondominium($condominium),
            'resident' => [
                'name' => $user->name,
                'unit' => $user->unit?->full_identifier,
            ],
            'filters' => $this->describeFilters($request),
            'rows' => $rows,
            'summary' => [
                'total' => $charges->count(),
                'pending' => $charges->where('status', 'pending')->count(),
                'overdue' => $charges->where('status', 'overdue')->count(),
                'paid' => $charges->where('status', 'paid')->count(),
                'open_amount' => $this->formatMoney(
                    $charges->whereIn('status', ['pending', 'overdue'])->sum('amount')
                ),
                'paid_amount' => $this->formatMoney(
                    $charges->where('status', 'paid')->sum('amount')
                ),
            ],
            'generated_at' => now()->format('d/m/Y H:i:s'),
            'app_name' => config('app.name', 'SindCON'),
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    public function buildQuery(User $user, Request $request): Builder
    {
        $query = Charge::query()
            ->where('condominium_id', $user->tenantCondominiumId())
            ->where('unit_id', $user->unit_id);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('due_date', [
                $request->input('start_date'),
                $request->input('end_date'),
            ]);
        }

        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $query->where(function ($builder) use ($term) {
                $builder->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        return $query;
    }

    protected function describeFilters(Request $request): array
    {
        $parts = [];

        if ($request->filled('status')) {
            $parts[] = 'Status: ' . $this->statusLabel($request->input('status'));
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $parts[] = 'Vencimento: '
                . \Carbon\Carbon::parse($request->input('start_date'))->format('d/m/Y')
                . ' a '
                . \Carbon\Carbon::parse($request->input('end_date'))->format('d/m/Y');
        }

        if ($request->filled('search')) {
            $parts[] = 'Busca: ' . $request->input('search');
        }

        return $parts;
    }

    protected function statusLabel(?string $status): string
    {
        return match ($status) {
            'pending' => 'Pendente',
            'overdue' => 'Em atraso',
            'paid' => 'Pago',
            'cancelled' => 'Cancelada',
            default => ucfirst((string) $status),
        };
    }

    protected function formatMoney($value): string
    {
        return 'R$ ' . number_format((float) $value, 2, ',', '.');
    }
}
