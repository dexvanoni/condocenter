@extends('layouts.app')

@section('title', ($isMoradorView ?? false) ? 'Minhas Multas' : 'Multas')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">{{ ($isMoradorView ?? false) ? 'Minhas Multas' : 'Multas' }}</h2>
        <p class="text-muted mb-0">
            @if($isMoradorView ?? false)
                Acompanhe o status e efetue o pagamento das multas aplicadas à sua unidade.
            @else
                Aplicação, acompanhamento e exportação de multas do condomínio.
            @endif
        </p>
    </div>
    @can('manage_fines')
        <a href="{{ route('fines.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Aplicar multa
        </a>
    @endcan
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Buscar</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                       placeholder="Referência, motivo ou enquadramento">
            </div>
            <div class="col-md-3">
                <label class="form-label">Situação</label>
                <select name="status" class="form-select">
                    <option value="">Todas</option>
                    <option value="issued" {{ request('status') === 'issued' ? 'selected' : '' }}>Aplicadas</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Canceladas</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="{{ route('fines.index') }}" class="btn btn-outline-secondary">Limpar</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Referência</th>
                        <th>Enquadramento</th>
                        <th>Aplicação</th>
                        <th>Vencimento</th>
                        <th class="text-end">Valor</th>
                        @if($isMoradorView ?? false)
                            <th>Pagamento</th>
                        @else
                            <th>Destinatários</th>
                        @endif
                        <th>Situação</th>
                        <th width="180">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fines as $fine)
                        @php
                            $ctx = $fine->resident_context ?? null;
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $fine->reference }}</td>
                            <td>{{ $fine->enquadramento }}</td>
                            <td>
                                <div>{{ $fine->applied_at->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $fine->applied_at->format('H:i') }}</small>
                            </td>
                            <td>{{ $fine->due_date->format('d/m/Y') }}</td>
                            <td class="text-end fw-semibold">R$ {{ number_format($fine->amount, 2, ',', '.') }}</td>
                            @if($isMoradorView ?? false)
                                <td>
                                    @if($ctx)
                                        <span class="badge bg-{{ $ctx['payment_status_color'] }}">
                                            {{ $ctx['payment_status_label'] }}
                                        </span>
                                        @if(!empty($ctx['paid_at']))
                                            <div class="small text-muted mt-1">
                                                Pago em {{ $ctx['paid_at']->format('d/m/Y') }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            @else
                                <td>
                                    <span class="badge bg-secondary">{{ $fine->recipients->count() }}</span>
                                </td>
                            @endif
                            <td>
                                <span class="badge bg-{{ $fine->isCancelled() ? 'secondary' : 'danger' }}">
                                    {{ $fine->status_label }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <a href="{{ route('fines.show', $fine) }}" class="btn btn-outline-secondary btn-sm" title="Detalhes">
                                        <i class="bi bi-eye"></i> Ver
                                    </a>
                                    @if(($isMoradorView ?? false) && ($ctx['can_pay_online'] ?? false))
                                        <button type="button"
                                                class="btn btn-success btn-sm"
                                                onclick="openChargeCheckout({{ $ctx['charge_id'] }})">
                                            <i class="bi bi-wallet2"></i> Pagar
                                        </button>
                                    @endif
                                    @can('export', $fine)
                                        <a href="{{ route('fines.export-pdf', $fine) }}" class="btn btn-outline-primary btn-sm" title="Exportar PDF">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($isMoradorView ?? false) ? 8 : 8 }}" class="text-center text-muted py-5">
                                Nenhuma multa encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($fines->hasPages())
        <div class="card-footer">
            {{ $fines->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

@if(($isMoradorView ?? false) && ($onlinePaymentsEnabled ?? false))
    @include('charges.partials.payment-checkout')
@endif
@endsection
