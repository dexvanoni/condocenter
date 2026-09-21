@extends('layouts.app')

@section('title', 'Cobranças SaaS — visão geral')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <a href="{{ route('platform.dashboard') }}" class="text-decoration-none">
                <i class="bi bi-arrow-left"></i> Dashboard SaaS
            </a>
            <h1 class="mt-2 mb-1"><i class="bi bi-cash-coin"></i> Cobranças da plataforma</h1>
            <p class="text-muted mb-0">
                Todas as cobranças de assinatura SindCON por condomínio (fonte Asaas).
                Escaneados {{ $billingReport['subscriptions_scanned'] ?? 0 }} contrato(s).
            </p>
        </div>
        <a href="{{ route('condominiums.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-buildings"></i> Condomínios
        </a>
    </div>

    @php
        $summary = $billingReport['summary'] ?? [];
        $charges = $billingReport['charges'] ?? [];
        $filters = $billingFilters ?? [];
    @endphp

    <div class="row g-2 mb-4">
        <div class="col-6 col-md-3">
            <div class="card shadow-sm h-100"><div class="card-body py-3">
                <small class="text-muted">Pendentes</small>
                <h4 class="mb-0 text-warning">{{ $summary['pending'] ?? 0 }}</h4>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm h-100"><div class="card-body py-3">
                <small class="text-muted">Pagas (lista)</small>
                <h4 class="mb-0 text-success">{{ $summary['paid'] ?? 0 }}</h4>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm h-100"><div class="card-body py-3">
                <small class="text-muted">Vencidas</small>
                <h4 class="mb-0 text-danger">{{ $summary['overdue'] ?? 0 }}</h4>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm h-100"><div class="card-body py-3">
                <small class="text-muted">Total exibido</small>
                <h4 class="mb-0">{{ $summary['total'] ?? 0 }}</h4>
            </div></div>
        </div>
    </div>

    <form method="GET" action="{{ route('platform.billing.index') }}" class="card shadow-sm mb-4">
        <div class="card-body row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Condomínio</label>
                <select name="condominium_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($billingReport['condominiums'] ?? [] as $condo)
                        <option value="{{ $condo->id }}" @selected(($filters['condominium_id'] ?? '') == $condo->id)>
                            {{ $condo->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Venc. de</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Venc. até</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select form-select-sm">
                    @foreach(['all' => 'Todos', 'pending' => 'Pendentes', 'paid' => 'Pagas', 'overdue' => 'Vencidas', 'other' => 'Outros'] as $val => $label)
                        <option value="{{ $val }}" @selected(($filters['status'] ?? 'all') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1"><i class="bi bi-funnel"></i> Filtrar</button>
                <a href="{{ route('platform.billing.index') }}" class="btn btn-sm btn-outline-secondary">Limpar</a>
            </div>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if(empty($charges))
                <p class="text-muted p-4 mb-0">Nenhuma cobrança encontrada. Verifique o Asaas em Plataforma → Financeiro.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Condomínio</th>
                                <th>Contrato</th>
                                <th>Vencimento</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th class="text-end">Gerenciar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($charges as $charge)
                                @php
                                    $badge = match($charge['status_group']) {
                                        'paid' => 'success',
                                        'pending' => 'warning text-dark',
                                        'overdue' => 'danger',
                                        default => 'secondary',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('platform.subscriptions.edit', $charge['condominium_id']) }}" class="fw-semibold text-decoration-none">
                                            {{ $charge['condominium_name'] }}
                                        </a>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">{{ $charge['subscription_status'] }}</span></td>
                                    <td>{{ $charge['due_date']?->format('d/m/Y') ?? '—' }}</td>
                                    <td>R$ {{ number_format($charge['value'], 2, ',', '.') }}</td>
                                    <td><span class="badge bg-{{ $badge }}">{{ $charge['status_label'] }}</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('platform.subscriptions.edit', $charge['condominium_id']) }}#cobrancas-saas" class="btn btn-sm btn-outline-primary">
                                            Abrir
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
