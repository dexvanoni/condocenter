@extends('layouts.app')

@section('title', 'Painel de Adimplência')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="mb-1">Painel de Adimplência</h2>
                <p class="text-muted mb-0">Visão consolidada das unidades adimplentes e inadimplentes.</p>
            </div>
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-1">Início</label>
                    <input type="date" class="form-control" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1">Fim</label>
                    <input type="date" class="form-control" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <span class="text-muted">Unidades</span>
                <h3 class="mb-0 mt-2">{{ $summary['total_units'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm h-100 border-success">
            <div class="card-body">
                <span class="text-muted">Adimplentes</span>
                <h3 class="mb-0 mt-2 text-success">{{ $summary['adimplentes'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm h-100 border-danger">
            <div class="card-body">
                <span class="text-muted">Inadimplentes</span>
                <h3 class="mb-0 mt-2 text-danger">{{ $summary['inadimplentes'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <span class="text-muted">Total em aberto</span>
                <h3 class="mb-0 mt-2 text-warning">R$ {{ number_format($summary['total_pending'] + $summary['total_overdue'], 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Unidades Adimplentes</h5>
                <span class="badge bg-light text-success">{{ $adimplentes->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Unidade</th>
                                <th>Responsável</th>
                                <th class="text-end">Total pago</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($adimplentes as $unit)
                                <tr>
                                    <td>{{ $unit->full_identifier }}</td>
                                    <td>{{ optional($unit->morador)->name ?? '—' }}</td>
                                    <td class="text-end text-success fw-semibold">
                                        R$ {{ number_format($unit->paid_total ?? 0, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">Nenhuma unidade adimplente no período.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Unidades Inadimplentes</h5>
                <span class="badge bg-light text-danger">{{ $inadimplentes->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Unidade</th>
                                <th>Responsável</th>
                                <th class="text-end">Pendentes</th>
                                <th class="text-end">Atrasadas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inadimplentes as $unit)
                                <tr>
                                    <td>{{ $unit->full_identifier }}</td>
                                    <td>{{ optional($unit->morador)->name ?? '—' }}</td>
                                    <td class="text-end text-warning fw-semibold">
                                        R$ {{ number_format($unit->pending_total ?? 0, 2, ',', '.') }}
                                    </td>
                                    <td class="text-end text-danger fw-semibold">
                                        R$ {{ number_format($unit->overdue_total ?? 0, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Nenhuma unidade inadimplente no período.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

