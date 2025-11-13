@extends('layouts.app')

@section('title', 'Conciliação Bancária')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-1">Conciliação Bancária</h2>
        <p class="text-muted mb-0">
            Consolide as movimentações financeiras confirmadas em uma conta bancária e mantenha o saldo do sistema alinhado ao extrato do banco.
        </p>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif

@if (session('preview_errors'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        @foreach (session('preview_errors')->all() as $message)
            <div>{{ $message }}</div>
        @endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('bank-reconciliation.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="filterAccount" class="form-label">Conta bancária</label>
                <select class="form-select" id="filterAccount" name="account_id" required>
                    <option value="">Selecione...</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}" @selected($filters['account_id'] == $account->id)>
                            {{ $account->name }} — {{ $account->institution ?? 'Conta' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="filterStart" class="form-label">Início do período</label>
                <input type="date" class="form-control" id="filterStart" name="start_date" value="{{ $filters['start_date'] }}" required>
            </div>
            <div class="col-md-3">
                <label for="filterEnd" class="form-label">Fim do período</label>
                <input type="date" class="form-control" id="filterEnd" name="end_date" value="{{ $filters['end_date'] }}" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Pré-visualizar
                </button>
            </div>
        </form>
    </div>
</div>

@if ($selectedAccount && $preview)
    @php
        $currentBalance = $selectedAccount->current_balance ?? 0;
        $projectedBalance = $currentBalance + $preview['totals']['net'];
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <span class="text-muted d-block">Saldo atual (antes)</span>
                    <h4 class="mb-0 mt-2 text-primary">R$ {{ number_format($currentBalance, 2, ',', '.') }}</h4>
                    <small class="text-muted">
                        Atualizado em {{ optional($selectedAccount->balance_updated_at)->format('d/m/Y H:i') ?? '—' }}
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm border-success">
                <div class="card-body">
                    <span class="text-muted d-block">Entradas conciliáveis</span>
                    <h4 class="mb-0 mt-2 text-success">R$ {{ number_format($preview['totals']['income'], 2, ',', '.') }}</h4>
                    <small class="text-muted">{{ $preview['income_groups']->sum('count') }} lançamento(s)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm border-danger">
                <div class="card-body">
                    <span class="text-muted d-block">Saídas conciliáveis</span>
                    <h4 class="mb-0 mt-2 text-danger">R$ {{ number_format($preview['totals']['expense'], 2, ',', '.') }}</h4>
                    <small class="text-muted">{{ $preview['expense_groups']->sum('count') }} lançamento(s)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm {{ $preview['totals']['net'] >= 0 ? 'border-success' : 'border-danger' }}">
                <div class="card-body">
                    <span class="text-muted d-block">Saldo projetado</span>
                    <h4 class="mb-0 mt-2 {{ $preview['totals']['net'] >= 0 ? 'text-success' : 'text-danger' }}">
                        R$ {{ number_format($projectedBalance, 2, ',', '.') }}
                    </h4>
                    <small class="text-muted">
                        Resultado do período: {{ $preview['totals']['net'] >= 0 ? '+' : '-' }}R$ {{ number_format(abs($preview['totals']['net']), 2, ',', '.') }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-arrow-down-right text-success me-2"></i>Entradas do período</h5>
                </div>
                <div class="card-body">
                    @forelse ($preview['income_groups'] as $group)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold">{{ $group['label'] }}</span>
                                <span class="text-success">R$ {{ number_format($group['total'], 2, ',', '.') }}</span>
                            </div>
                            <small class="text-muted">{{ $group['count'] }} lançamento(s)</small>
                            <ul class="list-unstyled small bg-light rounded p-2 mt-2 mb-0">
                                @foreach ($group['items'] as $item)
                                    <li class="d-flex justify-content-between">
                                        <span>{{ $item['label'] }} • {{ \Carbon\Carbon::parse($item['reference_date'])->format('d/m/Y') }}</span>
                                        <span class="text-success">R$ {{ number_format($item['amount'], 2, ',', '.') }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Nenhuma entrada disponível para conciliação.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-arrow-up-right text-danger me-2"></i>Saídas do período</h5>
                </div>
                <div class="card-body">
                    @forelse ($preview['expense_groups'] as $group)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold">{{ $group['label'] }}</span>
                                <span class="text-danger">R$ {{ number_format($group['total'], 2, ',', '.') }}</span>
                            </div>
                            <small class="text-muted">{{ $group['count'] }} lançamento(s)</small>
                            <ul class="list-unstyled small bg-light rounded p-2 mt-2 mb-0">
                                @foreach ($group['items'] as $item)
                                    <li class="d-flex justify-content-between">
                                        <span>{{ $item['label'] }} • {{ \Carbon\Carbon::parse($item['reference_date'])->format('d/m/Y') }}</span>
                                        <span class="text-danger">R$ {{ number_format($item['amount'], 2, ',', '.') }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Nenhuma saída disponível para conciliação.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @can('manage_bank_statements')
        <div class="alert alert-warning mb-4">
            <strong>Atenção:</strong> a conciliação bancaria é definitiva. Após confirmar, os lançamentos consolidados não poderão ser utilizados novamente. Apenas a conciliação mais recente pode ser cancelada.
        </div>

        <div class="d-flex flex-wrap gap-2 mb-5">
            <form method="POST" action="{{ route('bank-reconciliation.store') }}">
                @csrf
                <input type="hidden" name="account_id" value="{{ $selectedAccount->id }}">
                <input type="hidden" name="start_date" value="{{ $filters['start_date'] }}">
                <input type="hidden" name="end_date" value="{{ $filters['end_date'] }}">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Confirmar conciliação
                </button>
            </form>
            @if($latestReconciliation && $latestReconciliation->bank_account_id === $selectedAccount->id)
                <form method="POST" action="{{ route('bank-reconciliation.cancel') }}" onsubmit="return confirm('Cancelar a última conciliação? Esta ação reverte o saldo da conta.');">
                    @csrf
                    <input type="hidden" name="account_id" value="{{ $selectedAccount->id }}">
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-arrow-counterclockwise"></i> Cancelar última conciliação
                    </button>
                </form>
            @endif
        </div>
    @endcan
@endif

<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0">Histórico de conciliações</h5>
    </div>
    <div class="card-body">
        @if($reconciliations->isEmpty())
            <p class="text-muted mb-0">Nenhuma conciliação registrada até o momento.</p>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Conta</th>
                            <th>Período</th>
                            <th class="text-end">Entradas</th>
                            <th class="text-end">Saídas</th>
                            <th class="text-end">Resultado</th>
                            <th class="text-end">Saldo pós-conciliação</th>
                            <th>Criada em</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reconciliations as $reconciliation)
                            @php
                                $incomeItems = $reconciliation->items->where('direction', 'income')->groupBy('label');
                                $expenseItems = $reconciliation->items->where('direction', 'expense')->groupBy('label');
                            @endphp
                            <tr>
                                <td>{{ $reconciliation->bankAccount->name }}</td>
                                <td>{{ $reconciliation->start_date->format('d/m/Y') }} – {{ $reconciliation->end_date->format('d/m/Y') }}</td>
                                <td class="text-end text-success">R$ {{ number_format($reconciliation->total_income, 2, ',', '.') }}</td>
                                <td class="text-end text-danger">R$ {{ number_format($reconciliation->total_expense, 2, ',', '.') }}</td>
                                <td class="text-end {{ $reconciliation->net_amount >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $reconciliation->net_amount >= 0 ? '+' : '-' }}R$ {{ number_format(abs($reconciliation->net_amount), 2, ',', '.') }}
                                </td>
                                <td class="text-end fw-semibold">R$ {{ number_format($reconciliation->resulting_balance, 2, ',', '.') }}</td>
                                <td>
                                    <span class="d-block">{{ $reconciliation->created_at->format('d/m/Y H:i') }}</span>
                                    <button class="btn btn-link btn-sm p-0" type="button" data-bs-toggle="collapse" data-bs-target="#reconciliationDetails{{ $reconciliation->id }}">
                                        Ver fontes
                                    </button>
                                </td>
                            </tr>
                            <tr class="collapse" id="reconciliationDetails{{ $reconciliation->id }}">
                                <td colspan="7">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="fw-semibold">Entradas</h6>
                                            <ul class="list-unstyled mb-0">
                                                @foreach ($incomeItems as $label => $items)
                                                    <li class="d-flex justify-content-between">
                                                        <span>{{ $label }} ({{ $items->count() }})</span>
                                                        <span class="text-success">R$ {{ number_format($items->sum('amount'), 2, ',', '.') }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="fw-semibold">Saídas</h6>
                                            <ul class="list-unstyled mb-0">
                                                @foreach ($expenseItems as $label => $items)
                                                    <li class="d-flex justify-content-between">
                                                        <span>{{ $label }} ({{ $items->count() }})</span>
                                                        <span class="text-danger">R$ {{ number_format($items->sum('amount'), 2, ',', '.') }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $reconciliations->links() }}
        @endif
    </div>
</div>
@endsection

