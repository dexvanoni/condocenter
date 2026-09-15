@extends('layouts.app')

@section('title', 'Movimentações de Encomendas')

@php
    $fromDate = $filters['from'];
    $toDate = $filters['to'];
@endphp

@push('styles')
<style>
    .pkg-report-page { min-width: 0; max-width: 100%; }
    .pkg-report-header h2 { font-size: 1.45rem; font-weight: 700; letter-spacing: -0.02em; }
    .pkg-report-filters {
        background: #fff;
        border: 1px solid #e8ecf1;
        border-radius: 12px;
        padding: 1rem 1.1rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }
    .pkg-report-filters .form-label {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        margin-bottom: 0.25rem;
    }
    .pkg-report-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    @media (max-width: 991.98px) {
        .pkg-report-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    .pkg-report-stat {
        background: #fff;
        border: 1px solid #e8ecf1;
        border-radius: 10px;
        padding: 0.75rem 0.9rem;
    }
    .pkg-report-stat__label {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
    }
    .pkg-report-stat__value {
        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.2;
        margin-top: 0.15rem;
    }
    .pkg-report-card {
        background: #fff;
        border: 1px solid #e8ecf1;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }
    .pkg-report-card__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
        border-bottom: 1px solid #eef2f7;
        background: #f8fafc;
    }
    .pkg-report-card__head h3 { font-size: 0.95rem; font-weight: 600; margin: 0; color: #334155; }
    .pkg-report-table-wrap { width: 100%; overflow-x: auto; }
    .pkg-report-table { width: 100%; table-layout: fixed; margin: 0; }
    .pkg-report-table th {
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
    .pkg-report-table td {
        padding: 0.8rem 0.85rem;
        vertical-align: top;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.875rem;
        color: #334155;
        word-wrap: break-word;
        overflow-wrap: anywhere;
    }
    .pkg-report-table tbody tr:hover { background: #fafbfd; }
    .pkg-report-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.28rem 0.55rem;
        border-radius: 999px;
        white-space: nowrap;
    }
    .pkg-report-badge--pending { background: #fef3c7; color: #92400e; }
    .pkg-report-badge--collected { background: #dcfce7; color: #166534; }
    .pkg-report-datetime { font-weight: 600; white-space: nowrap; }
    .pkg-report-datetime small { display: block; font-weight: 500; color: #94a3b8; font-size: 0.75rem; }
    .pkg-report-mobile { display: none; }
    @media (max-width: 767.98px) {
        .pkg-report-table-wrap { display: none; }
        .pkg-report-mobile { display: block; }
        .pkg-report-item {
            padding: 0.95rem 1rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .pkg-report-item:last-child { border-bottom: 0; }
        .pkg-report-item__top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 0.55rem;
        }
        .pkg-report-item__title { font-weight: 700; font-size: 0.95rem; margin: 0; }
        .pkg-report-item__grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.55rem 0.75rem;
            font-size: 0.82rem;
        }
        .pkg-report-item__grid span {
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
<div class="container-fluid pkg-report-page px-3 px-lg-4">
    <div class="pkg-report-header d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-box-seam text-primary"></i> Movimentações de Encomendas</h2>
            <p class="text-muted mb-0">Histórico completo de chegadas, retiradas e auditoria da portaria.</p>
        </div>
        @can('export_packages_reports')
        <div class="d-flex flex-wrap gap-2 flex-shrink-0">
            <a href="{{ route('packages.reports.excel', request()->only(['from', 'to', 'status', 'unit_id', 'search'])) }}"
               class="btn btn-outline-success btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </a>
            <a href="{{ route('packages.reports.pdf', request()->only(['from', 'to', 'status', 'unit_id', 'search'])) }}"
               class="btn btn-outline-danger btn-sm">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
        </div>
        @endcan
    </div>

    <form method="GET" class="pkg-report-filters mb-3">
        <div class="row g-2 g-md-3 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label" for="filterFrom">De</label>
                <input type="date" id="filterFrom" name="from" class="form-control form-control-sm" value="{{ $fromDate }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="filterTo">Até</label>
                <input type="date" id="filterTo" name="to" class="form-control form-control-sm" value="{{ $toDate }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="filterStatus">Status</label>
                <select id="filterStatus" name="status" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="pending" @selected($filters['status'] === 'pending')>Pendente</option>
                    <option value="collected" @selected($filters['status'] === 'collected')>Retirada</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="filterUnit">Unidade</label>
                <select id="filterUnit" name="unit_id" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" @selected($filters['unit_id'] === $unit->id)>
                            {{ $unit->full_identifier }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label" for="filterSearch">Buscar</label>
                <input type="search" id="filterSearch" name="search" class="form-control form-control-sm"
                       value="{{ $filters['search'] }}" placeholder="Morador, remetente, rastreio...">
            </div>
            <div class="col-12 col-md-auto">
                <button type="submit" class="btn btn-primary btn-sm px-4">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
            </div>
        </div>
    </form>

    <div class="pkg-report-stats">
        <div class="pkg-report-stat">
            <div class="pkg-report-stat__label">Total no período</div>
            <div class="pkg-report-stat__value text-dark">{{ $stats['total'] }}</div>
        </div>
        <div class="pkg-report-stat">
            <div class="pkg-report-stat__label">Pendentes</div>
            <div class="pkg-report-stat__value text-warning">{{ $stats['pending'] }}</div>
        </div>
        <div class="pkg-report-stat">
            <div class="pkg-report-stat__label">Retiradas</div>
            <div class="pkg-report-stat__value text-success">{{ $stats['collected'] }}</div>
        </div>
        <div class="pkg-report-stat">
            <div class="pkg-report-stat__label">Tempo médio retirada</div>
            <div class="pkg-report-stat__value text-primary">
                {{ $stats['avg_hours_to_collect'] !== null ? $stats['avg_hours_to_collect'] . ' h' : '—' }}
            </div>
        </div>
    </div>

    <div class="pkg-report-card">
        <div class="pkg-report-card__head">
            <h3><i class="bi bi-list-ul me-1"></i> Registros do período</h3>
            @if($movements->total() > 0)
                <span class="badge rounded-pill text-bg-light border">{{ $movements->total() }} registros</span>
            @endif
        </div>

        @if($movements->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                Nenhuma movimentação no período selecionado.
            </div>
        @else
            <div class="pkg-report-table-wrap">
                <table class="table pkg-report-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:11%">Recebida</th>
                            <th style="width:9%">Unidade</th>
                            <th style="width:8%">Tipo</th>
                            <th style="width:8%">Status</th>
                            <th style="width:12%">Remetente</th>
                            <th style="width:11%">Rastreamento</th>
                            <th style="width:11%">Registrado por</th>
                            <th style="width:11%">Retirada</th>
                            <th style="width:10%">Retirado por</th>
                            <th style="width:9%">Identificação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movements as $package)
                        <tr>
                            <td>
                                <span class="pkg-report-datetime">
                                    {{ $package->received_at?->format('d/m/Y') }}
                                    <small>{{ $package->received_at?->format('H:i') }}</small>
                                </span>
                            </td>
                            <td><strong>{{ $package->unit?->full_identifier ?? '—' }}</strong></td>
                            <td>{{ $package->type_label }}</td>
                            <td>
                                <span class="pkg-report-badge pkg-report-badge--{{ $package->status === 'pending' ? 'pending' : 'collected' }}">
                                    {{ $package->status_label }}
                                </span>
                            </td>
                            <td>{{ $package->sender ?? '—' }}</td>
                            <td>{{ $package->tracking_code ?? '—' }}</td>
                            <td>{{ $package->registeredBy?->name ?? '—' }}</td>
                            <td>
                                @if($package->collected_at)
                                    <span class="pkg-report-datetime">
                                        {{ $package->collected_at->format('d/m/Y') }}
                                        <small>{{ $package->collected_at->format('H:i') }}</small>
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                {{ $package->collectedBy?->name ?? '—' }}
                                @if($package->picked_up_by_name)
                                    <br><small class="text-muted">({{ $package->picked_up_by_name }})</small>
                                @endif
                            </td>
                            <td>{{ $package->identification_method_label }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pkg-report-mobile">
                @foreach($movements as $package)
                <div class="pkg-report-item">
                    <div class="pkg-report-item__top">
                        <h4 class="pkg-report-item__title">{{ $package->unit?->full_identifier ?? '—' }}</h4>
                        <span class="pkg-report-badge pkg-report-badge--{{ $package->status === 'pending' ? 'pending' : 'collected' }}">
                            {{ $package->status_label }}
                        </span>
                    </div>
                    <div class="pkg-report-item__grid">
                        <div>
                            <span>Recebida</span>
                            {{ $package->received_at?->format('d/m/Y H:i') ?? '—' }}
                        </div>
                        <div>
                            <span>Tipo</span>
                            {{ $package->type_label }}
                        </div>
                        <div>
                            <span>Remetente</span>
                            {{ $package->sender ?? '—' }}
                        </div>
                        <div>
                            <span>Registrado por</span>
                            {{ $package->registeredBy?->name ?? '—' }}
                        </div>
                        <div>
                            <span>Retirada</span>
                            {{ $package->collected_at?->format('d/m/Y H:i') ?? '—' }}
                        </div>
                        <div>
                            <span>Retirado por</span>
                            {{ $package->collectedBy?->name ?? '—' }}
                            @if($package->picked_up_by_name)
                                ({{ $package->picked_up_by_name }})
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="p-3 border-top">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
