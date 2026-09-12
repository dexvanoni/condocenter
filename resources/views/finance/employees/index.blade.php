@extends('layouts.app')

@section('title', 'Quadro de Funcionários')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="mb-1"><i class="bi bi-people-fill text-primary"></i> Quadro de Funcionários</h2>
        <p class="text-muted mb-2">
            Folha de pagamento, encargos e histórico do quadro de pessoal.
            Todos os valores entram automaticamente na <a href="{{ route('accountability-reports.index') }}">Prestação de Contas</a>.
        </p>
        <span class="badge bg-light text-dark border">
            {{ $startDate->format('d/m/Y') }} — {{ $endDate->format('d/m/Y') }}
        </span>
    </div>
    @if($canManage)
        <a href="{{ route('financial.employees.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Novo funcionário
        </a>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-4 border-primary h-100">
            <div class="card-body">
                <small class="text-muted text-uppercase">Funcionários ativos</small>
                <h4 class="mb-0 mt-1">{{ $employees->where('status', 'active')->count() }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-4 border-danger h-100">
            <div class="card-body">
                <small class="text-muted text-uppercase">Custo líquido no período</small>
                <h4 class="mb-0 mt-1 text-danger">R$ {{ number_format($summary['totals']['net'], 2, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-4 border-warning h-100">
            <div class="card-body">
                <small class="text-muted text-uppercase">Encargos / impostos</small>
                <h4 class="mb-0 mt-1">R$ {{ number_format($summary['by_type']->where('type', 'employer_tax')->sum('total'), 2, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-4 border-secondary h-100">
            <div class="card-body">
                <small class="text-muted text-uppercase">Lançamentos</small>
                <h4 class="mb-0 mt-1">{{ $summary['active_entries']->count() }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Buscar</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nome, cargo ou CPF">
            </div>
            <div class="col-md-2">
                <label class="form-label">Situação</label>
                <select name="status" class="form-select">
                    <option value="">Todas</option>
                    @foreach(\App\Models\Employee::statusLabels() as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Início</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Fim</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card shadow-sm">
            <div class="card-header bg-light"><h5 class="mb-0">Funcionários</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th>Cargo</th>
                                <th>Admissão</th>
                                <th class="text-end">Salário base</th>
                                <th class="text-end">Período</th>
                                <th>Situação</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $employee)
                                <tr>
                                    <td class="fw-semibold">{{ $employee->name }}</td>
                                    <td>{{ $employee->position }}</td>
                                    <td>{{ $employee->admission_date->format('d/m/Y') }}</td>
                                    <td class="text-end">R$ {{ number_format($employee->base_salary, 2, ',', '.') }}</td>
                                    <td class="text-end text-danger fw-semibold">
                                        R$ {{ number_format($employee->period_total ?? 0, 2, ',', '.') }}
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $employee->status === 'active' ? 'success' : ($employee->status === 'vacation' ? 'info' : 'secondary') }}">
                                            {{ $employee->statusLabel() }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('financial.employees.show', $employee) }}" class="btn btn-sm btn-outline-primary">Abrir</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">Nenhum funcionário cadastrado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-light"><h6 class="mb-0">Resumo por tipo</h6></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($summary['by_type'] as $row)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $row['label'] }} <small class="text-muted">({{ $row['count'] }})</small></span>
                            <strong>R$ {{ number_format($row['total'], 2, ',', '.') }}</strong>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Sem lançamentos no período.</li>
                    @endforelse
                </ul>
            </div>
        </div>
        @if($summary['employer_taxes']->isNotEmpty())
        <div class="card shadow-sm">
            <div class="card-header bg-light"><h6 class="mb-0">Encargos detalhados</h6></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($summary['employer_taxes'] as $tax)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $tax['label'] }}</span>
                            <strong>R$ {{ number_format($tax['total'], 2, ',', '.') }}</strong>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
