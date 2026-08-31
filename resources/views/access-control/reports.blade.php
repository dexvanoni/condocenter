@extends('layouts.app')

@section('title', 'Relatório de Acesso')

@php
    $fromDate = request('from', now()->subDays(30)->toDateString());
    $toDate = request('to', now()->toDateString());
    $totalCount = $movements->count();
    $enteredCount = $movements->where('action', 'entered')->count();
    $deniedCount = $movements->where('action', 'denied')->filter(fn ($m) => !$m->isProhibitionAlert())->count();
    $prohibitionAlertCount = $movements->filter(fn ($m) => $m->isProhibitionAlert())->count();
    $earlyCount = $movements->filter(fn ($m) => $m->isEarlyEntry())->count();
    $sourceLabels = [
        'authorization' => 'Liberação individual',
        'list_item' => 'Lista / evento',
        'service_provider' => 'Prestador',
    ];
@endphp

@push('styles')
<style>
    .ac-report-page {
        min-width: 0;
        max-width: 100%;
    }

    .ac-report-header {
        margin-bottom: 1.25rem;
    }

    .ac-report-header h2 {
        font-size: 1.45rem;
        font-weight: 700;
        letter-spacing: -0.02em;
    }

    .ac-report-filters {
        background: #fff;
        border: 1px solid #e8ecf1;
        border-radius: 12px;
        padding: 1rem 1.1rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    .ac-report-filters .form-label {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        margin-bottom: 0.25rem;
    }

    .ac-report-stats {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    @media (max-width: 991.98px) {
        .ac-report-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .ac-report-stat {
        background: #fff;
        border: 1px solid #e8ecf1;
        border-radius: 10px;
        padding: 0.75rem 0.9rem;
    }

    .ac-report-stat__label {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
    }

    .ac-report-stat__value {
        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.2;
        margin-top: 0.15rem;
    }

    .ac-report-card {
        background: #fff;
        border: 1px solid #e8ecf1;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .ac-report-card__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
        border-bottom: 1px solid #eef2f7;
        background: #f8fafc;
    }

    .ac-report-card__head h3 {
        font-size: 0.95rem;
        font-weight: 600;
        margin: 0;
        color: #334155;
    }

    .ac-report-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .ac-report-table {
        width: 100%;
        table-layout: fixed;
        margin: 0;
    }

    .ac-report-table th {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        background: #f8fafc;
        border-bottom: 1px solid #eef2f7;
        padding: 0.7rem 0.85rem;
        white-space: nowrap;
    }

    .ac-report-table td {
        padding: 0.8rem 0.85rem;
        vertical-align: top;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.875rem;
        color: #334155;
        word-wrap: break-word;
        overflow-wrap: anywhere;
    }

    .ac-report-table tbody tr:hover {
        background: #fafbfd;
    }

    .ac-report-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .ac-report-table .col-when { width: 10%; }
    .ac-report-table .col-visitor { width: 19%; }
    .ac-report-table .col-unit { width: 9%; }
    .ac-report-table .col-action { width: 12%; }
    .ac-report-table .col-notes { width: 32%; }
    .ac-report-table .col-people { width: 18%; }

    .ac-report-datetime {
        font-weight: 600;
        white-space: nowrap;
    }

    .ac-report-datetime small {
        display: block;
        font-weight: 500;
        color: #94a3b8;
        font-size: 0.75rem;
        margin-top: 0.1rem;
    }

    .ac-report-visitor {
        font-weight: 600;
        line-height: 1.35;
    }

    .ac-report-meta {
        display: block;
        margin-top: 0.2rem;
        font-size: 0.75rem;
        color: #64748b;
        line-height: 1.35;
    }

    .ac-report-unit {
        font-weight: 600;
        white-space: nowrap;
    }

    .ac-report-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.28rem 0.55rem;
        border-radius: 999px;
        white-space: nowrap;
    }

    .ac-report-badge--success {
        background: #dcfce7;
        color: #166534;
    }

    .ac-report-badge--danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .ac-report-badge--critical {
        background: #450a0a;
        color: #fecaca;
    }

    .ac-report-note {
        display: flex;
        gap: 0.45rem;
        align-items: flex-start;
        font-size: 0.78rem;
        line-height: 1.45;
        color: #92400e;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 8px;
        padding: 0.45rem 0.55rem;
    }

    .ac-report-note i {
        flex-shrink: 0;
        margin-top: 0.1rem;
    }

    .ac-report-empty-note {
        color: #94a3b8;
        font-size: 0.82rem;
    }

    .ac-report-person {
        display: flex;
        align-items: flex-start;
        gap: 0.45rem;
        margin-bottom: 0.45rem;
    }

    .ac-report-person:last-child {
        margin-bottom: 0;
    }

    .ac-report-person__icon {
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.72rem;
        flex-shrink: 0;
        background: #eef2ff;
        color: #4338ca;
    }

    .ac-report-person__icon--staff {
        background: #ecfeff;
        color: #0e7490;
    }

    .ac-report-person__label {
        display: block;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #94a3b8;
        line-height: 1.2;
    }

    .ac-report-person__name {
        display: block;
        font-size: 0.82rem;
        font-weight: 600;
        color: #334155;
        line-height: 1.35;
        word-break: break-word;
    }

    .ac-report-mobile {
        display: none;
    }

    @media (max-width: 767.98px) {
        .ac-report-table-wrap {
            display: none;
        }

        .ac-report-mobile {
            display: block;
        }

        .ac-report-item {
            padding: 0.95rem 1rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .ac-report-item:last-child {
            border-bottom: 0;
        }

        .ac-report-item__top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 0.55rem;
        }

        .ac-report-item__title {
            font-weight: 700;
            font-size: 0.95rem;
            margin: 0;
        }

        .ac-report-item__grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.55rem 0.75rem;
            font-size: 0.82rem;
        }

        .ac-report-item__grid span {
            display: block;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #94a3b8;
            margin-bottom: 0.1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid ac-report-page px-3 px-lg-4">
    <div class="ac-report-header d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-shield-check text-primary"></i> Movimentações de Acesso</h2>
            <p class="text-muted mb-0">Histórico de entradas, negações e alertas de proibição registrados pela portaria.</p>
        </div>
        @can('export_access_reports')
        <a href="{{ route('access-control.reports.pdf', request()->only(['from', 'to', 'unit_id'])) }}"
           class="btn btn-outline-danger btn-sm flex-shrink-0">
            <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
        </a>
        @endcan
    </div>

    <form method="GET" class="ac-report-filters mb-3">
        <div class="row g-2 g-md-3 align-items-end">
            <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label" for="filterFrom">De</label>
                <input type="date" id="filterFrom" name="from" class="form-control form-control-sm" value="{{ $fromDate }}">
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label" for="filterTo">Até</label>
                <input type="date" id="filterTo" name="to" class="form-control form-control-sm" value="{{ $toDate }}">
            </div>
            <div class="col-12 col-md-auto">
                <button type="submit" class="btn btn-primary btn-sm px-4">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
            </div>
        </div>
    </form>

    <div class="ac-report-stats">
        <div class="ac-report-stat">
            <div class="ac-report-stat__label">Total</div>
            <div class="ac-report-stat__value text-dark">{{ $totalCount }}</div>
        </div>
        <div class="ac-report-stat">
            <div class="ac-report-stat__label">Entradas</div>
            <div class="ac-report-stat__value text-success">{{ $enteredCount }}</div>
        </div>
        <div class="ac-report-stat">
            <div class="ac-report-stat__label">Negadas</div>
            <div class="ac-report-stat__value text-danger">{{ $deniedCount }}</div>
        </div>
        <div class="ac-report-stat">
            <div class="ac-report-stat__label">Alertas proibição</div>
            <div class="ac-report-stat__value" style="color:#7f1d1d">{{ $prohibitionAlertCount }}</div>
        </div>
        <div class="ac-report-stat">
            <div class="ac-report-stat__label">Antecipadas</div>
            <div class="ac-report-stat__value text-warning">{{ $earlyCount }}</div>
        </div>
    </div>

    <div class="ac-report-card">
        <div class="ac-report-card__head">
            <h3><i class="bi bi-list-ul me-1"></i> Registros do período</h3>
            @if($totalCount > 0)
                <span class="badge rounded-pill text-bg-light border">{{ $totalCount }} {{ $totalCount === 1 ? 'registro' : 'registros' }}</span>
            @endif
        </div>

        @if($movements->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                Nenhuma movimentação no período selecionado.
            </div>
        @else
            <div class="ac-report-table-wrap">
                <table class="table ac-report-table mb-0">
                    <thead>
                        <tr>
                            <th class="col-when">Quando</th>
                            <th class="col-visitor">Visitante</th>
                            <th class="col-unit">Unidade</th>
                            <th class="col-action">Resultado</th>
                            <th class="col-notes">Observações</th>
                            <th class="col-people">Envolvidos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movements as $m)
                        @php
                            $isEntered = $m->action === 'entered';
                            $isProhibitionAlert = $m->isProhibitionAlert();
                            $badgeTone = $isEntered ? 'success' : ($isProhibitionAlert ? 'critical' : 'danger');
                            $badgeIcon = $isEntered ? 'check-circle-fill' : ($isProhibitionAlert ? 'exclamation-octagon-fill' : 'x-circle-fill');
                            $reference = $m->reference_label ?: ($sourceLabels[$m->source_type] ?? $m->source_type);
                        @endphp
                        <tr>
                            <td>
                                <span class="ac-report-datetime">
                                    {{ $m->occurred_at->format('d/m/Y') }}
                                    <small>{{ $m->occurred_at->format('H:i') }}</small>
                                </span>
                            </td>
                            <td>
                                <span class="ac-report-visitor">{{ $m->visitor_name }}</span>
                                <span class="ac-report-meta">{{ $reference }}</span>
                            </td>
                            <td>
                                <span class="ac-report-unit">{{ $m->unit?->full_identifier ?? '—' }}</span>
                            </td>
                            <td>
                                <span class="ac-report-badge ac-report-badge--{{ $badgeTone }}">
                                    <i class="bi bi-{{ $badgeIcon }}"></i>
                                    {{ $m->actionLabel() }}
                                </span>
                            </td>
                            <td>
                                @if($m->earlyEntryReportNote())
                                    <div class="ac-report-note">
                                        <i class="bi bi-clock-history"></i>
                                        <span>{{ $m->earlyEntryReportNote() }}</span>
                                    </div>
                                @else
                                    <span class="ac-report-empty-note">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="ac-report-person">
                                    <span class="ac-report-person__icon"><i class="bi bi-person-fill"></i></span>
                                    <span>
                                        <span class="ac-report-person__label">Morador</span>
                                        <span class="ac-report-person__name">{{ $m->notifyUser?->name ?? '—' }}</span>
                                    </span>
                                </div>
                                <div class="ac-report-person">
                                    <span class="ac-report-person__icon ac-report-person__icon--staff"><i class="bi bi-shield-fill"></i></span>
                                    <span>
                                        <span class="ac-report-person__label">Porteiro</span>
                                        <span class="ac-report-person__name">{{ $m->processedBy?->name ?? '—' }}</span>
                                    </span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="ac-report-mobile">
                @foreach($movements as $m)
                @php
                    $isEntered = $m->action === 'entered';
                    $isProhibitionAlert = $m->isProhibitionAlert();
                    $badgeTone = $isEntered ? 'success' : ($isProhibitionAlert ? 'critical' : 'danger');
                    $reference = $m->reference_label ?: ($sourceLabels[$m->source_type] ?? $m->source_type);
                @endphp
                <article class="ac-report-item">
                    <div class="ac-report-item__top">
                        <div>
                            <h4 class="ac-report-item__title">{{ $m->visitor_name }}</h4>
                            <small class="text-muted">{{ $reference }}</small>
                        </div>
                        <span class="ac-report-badge ac-report-badge--{{ $badgeTone }}">
                            {{ $m->actionLabel() }}
                        </span>
                    </div>
                    <div class="ac-report-item__grid">
                        <div><span>Quando</span>{{ $m->occurred_at->format('d/m/Y H:i') }}</div>
                        <div><span>Unidade</span>{{ $m->unit?->full_identifier ?? '—' }}</div>
                        <div><span>Morador</span>{{ $m->notifyUser?->name ?? '—' }}</div>
                        <div><span>Porteiro</span>{{ $m->processedBy?->name ?? '—' }}</div>
                    </div>
                    @if($m->earlyEntryReportNote())
                        <div class="ac-report-note mt-2">
                            <i class="bi bi-clock-history"></i>
                            <span>{{ $m->earlyEntryReportNote() }}</span>
                        </div>
                    @endif
                </article>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
