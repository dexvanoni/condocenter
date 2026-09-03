@extends('layouts.app')

@section('title', 'Multas')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">Multas</h2>
        <p class="text-muted mb-0">Aplicação, acompanhamento e exportação de multas do condomínio.</p>
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
                        <th>Destinatários</th>
                        <th>Situação</th>
                        <th width="160">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fines as $fine)
                        <tr>
                            <td class="fw-semibold">{{ $fine->reference }}</td>
                            <td>{{ $fine->enquadramento }}</td>
                            <td>
                                <div>{{ $fine->applied_at->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $fine->applied_at->format('H:i') }}</small>
                            </td>
                            <td>{{ $fine->due_date->format('d/m/Y') }}</td>
                            <td class="text-end fw-semibold">R$ {{ number_format($fine->amount, 2, ',', '.') }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $fine->recipients->count() }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $fine->isCancelled() ? 'secondary' : 'danger' }}">
                                    {{ $fine->status_label }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('fines.show', $fine) }}" class="btn btn-outline-secondary" title="Detalhes">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('export', $fine)
                                        <a href="{{ route('fines.export-pdf', $fine) }}" class="btn btn-outline-primary" title="Exportar PDF">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
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
@endsection
