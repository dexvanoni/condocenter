@extends('layouts.app')

@section('title', $employee->name)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <a href="{{ route('financial.employees.index') }}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Quadro de Funcionários</a>
        <h2 class="mb-1 mt-2">{{ $employee->name }}</h2>
        <p class="text-muted mb-0">{{ $employee->position }} · Admissão {{ $employee->admission_date->format('d/m/Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-{{ $employee->status === 'active' ? 'success' : ($employee->status === 'vacation' ? 'info' : 'secondary') }} fs-6">
            {{ $employee->statusLabel() }}
        </span>
        @if($canManage)
            <a href="{{ route('financial.employees.edit', $employee) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil"></i> Editar dados</a>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalLancamento"><i class="bi bi-plus-circle"></i> Lançamento</button>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card shadow-sm h-100"><div class="card-body"><small class="text-muted">Salário base</small><h5 class="mb-0">R$ {{ number_format($employee->base_salary, 2, ',', '.') }}</h5></div></div></div>
    <div class="col-md-3"><div class="card shadow-sm h-100"><div class="card-body"><small class="text-muted">CPF</small><h5 class="mb-0">{{ $employee->cpf ?: '—' }}</h5></div></div></div>
    <div class="col-md-3"><div class="card shadow-sm h-100"><div class="card-body"><small class="text-muted">Contato</small><h5 class="mb-0">{{ $employee->phone ?: '—' }}</h5></div></div></div>
    <div class="col-md-3"><div class="card shadow-sm h-100"><div class="card-body"><small class="text-muted">Total lançado (ativos)</small><h5 class="mb-0 text-danger">R$ {{ number_format($employee->financialEntries->where('status','active')->sum('amount'), 2, ',', '.') }}</h5></div></div></div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-light"><h5 class="mb-0">Histórico de lançamentos</h5></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Data</th>
                        <th>Tipo</th>
                        <th>Detalhes</th>
                        <th class="text-end">Valor</th>
                        @if($canManage)<th></th>@endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($employee->financialEntries as $entry)
                        <tr @class(['table-secondary text-muted' => $entry->isCancelled()])>
                            <td>{{ $entry->reference_date->format('d/m/Y') }}</td>
                            <td>{{ $entryTypes[$entry->type] ?? $entry->type }}</td>
                            <td>
                                @if($entry->description){{ $entry->description }}@endif
                                @if($entry->hours)
                                    <div class="small">{{ $entry->hours }}h × R$ {{ number_format($entry->hourly_rate ?? 0, 2, ',', '.') }}</div>
                                @endif
                                @if($entry->vacation_start)
                                    <div class="small">Férias: {{ $entry->vacation_start->format('d/m/Y') }} a {{ $entry->vacation_end?->format('d/m/Y') }}</div>
                                @endif
                                @if($entry->tax_breakdown)
                                    <div class="small text-muted">
                                        @foreach($entry->tax_breakdown as $label => $value)
                                            {{ $label }}: R$ {{ number_format($value, 2, ',', '.') }}@if(!$loop->last); @endif
                                        @endforeach
                                    </div>
                                @endif
                                @if($entry->isCancelled())
                                    <span class="badge bg-secondary">Cancelado</span>
                                    <div class="small">{{ $entry->cancellation_reason }}</div>
                                @endif
                            </td>
                            <td @class(['text-end fw-semibold', $entry->isCancelled() ? 'text-muted text-decoration-line-through' : 'text-danger'])>
                                R$ {{ number_format($entry->amount, 2, ',', '.') }}
                            </td>
                            @if($canManage)
                            <td class="text-end">
                                @if($entry->isActive())
                                    <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#cancelEntry{{ $entry->id }}">Cancelar</button>
                                @endif
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="{{ $canManage ? 5 : 4 }}" class="text-center text-muted py-4">Nenhum lançamento registrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($canManage)
@include('finance.employees.partials.entry-modal')
@foreach($employee->financialEntries->where('status', 'active') as $entry)
<div class="modal fade" id="cancelEntry{{ $entry->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('financial.employees.entries.cancel', $entry) }}">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Cancelar lançamento</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <p class="small text-muted">O valor deixará de ser computado, mas permanecerá visível na prestação de contas.</p>
                    <label class="form-label">Motivo *</label>
                    <textarea name="cancellation_reason" class="form-control" rows="3" required minlength="10"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Voltar</button>
                    <button type="submit" class="btn btn-warning">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endif
@endsection
