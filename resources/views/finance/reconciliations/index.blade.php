@extends('layouts.app')

@section('title', 'Conciliação Bancária')

@push('styles')
<style>
    .recon-hero {
        background: linear-gradient(135deg, #0a1b67 0%, #3866d2 100%);
        border-radius: 18px;
        color: #fff;
        padding: 1.5rem 1.75rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 12px 32px rgba(10, 27, 103, 0.2);
    }
    .recon-steps {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }
    .recon-step {
        flex: 1;
        min-width: 140px;
        padding: 0.65rem 0.85rem;
        border-radius: 10px;
        background: #f1f5f9;
        color: #64748b;
        font-size: 0.78rem;
        font-weight: 700;
        text-align: center;
        border: 2px solid transparent;
    }
    .recon-step.active {
        background: #eef2ff;
        color: #0a1b67;
        border-color: #3866d2;
    }
    .recon-step.done {
        background: #ecfdf5;
        color: #047857;
        border-color: #10b981;
    }
    .recon-account-card {
        border: 2px solid #e8ecf1;
        border-radius: 14px;
        padding: 1rem;
        text-decoration: none;
        color: inherit;
        display: block;
        transition: all 0.15s ease;
        height: 100%;
    }
    .recon-account-card:hover, .recon-account-card.selected {
        border-color: #3866d2;
        box-shadow: 0 8px 24px rgba(56, 102, 210, 0.12);
        color: inherit;
    }
    .recon-kpi {
        border-radius: 14px;
        border: 1px solid #e8ecf1;
        padding: 1rem;
        height: 100%;
        background: #fff;
    }
</style>
@endpush

@section('content')
<div class="recon-hero">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h2 class="mb-1 h3 fw-bold"><i class="bi bi-bank2 me-2"></i>Conciliação Bancária</h2>
            <p class="mb-0 opacity-75 small">
                Consolide os lançamentos por conta bancária e mantenha o saldo do sistema alinhado ao extrato.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('financial.settings.index') }}" class="btn btn-light btn-sm">
                <i class="bi bi-sliders"></i> Regras de destino
            </a>
            <a href="{{ route('financial.bank-accounts.index') }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-wallet2"></i> Contas
            </a>
        </div>
    </div>
</div>

@php
    $step = 1;
    if ($selectedAccount) {
        $step = 2;
    }
    if ($preview) {
        $step = 3;
    }
@endphp

<div class="recon-steps">
    <div class="recon-step {{ $step >= 1 ? ($step > 1 ? 'done' : 'active') : '' }}">1. Conta</div>
    <div class="recon-step {{ $step >= 2 ? ($step > 2 ? 'done' : 'active') : '' }}">2. Período</div>
    <div class="recon-step {{ $step >= 3 ? 'active' : '' }}">3. Pré-visualizar e confirmar</div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Atenção!</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif

@if (session('preview_errors'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>Atenção!</strong>
        <ul class="mb-0 mt-2">
            @foreach (session('preview_errors')->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        @if($accounts->isEmpty())
            <div class="text-center py-4">
                <i class="bi bi-bank display-4 text-muted"></i>
                <p class="mt-3 mb-2">Nenhuma conta bancária cadastrada.</p>
                <a href="{{ route('financial.bank-accounts.create') }}" class="btn btn-primary">Cadastrar conta</a>
            </div>
        @else
        <form method="GET" action="{{ route('bank-reconciliation.index') }}" class="row g-3 align-items-end">
            <div class="col-12">
                <label class="form-label fw-semibold">Conta bancária</label>
                <div class="row g-2">
                    @foreach($accounts as $account)
                    @php
                        $accountDefaults = $accountPeriodDefaults[$account->id] ?? [];
                    @endphp
                    <div class="col-md-4 col-sm-6">
                        <a href="{{ route('bank-reconciliation.index', array_merge(['account_id' => $account->id], $accountDefaults)) }}"
                           class="recon-account-card {{ ($filters['account_id'] ?? null) == $account->id ? 'selected' : '' }}">
                            <strong>{{ $account->name }}</strong>
                            <small class="d-block text-muted">{{ $account->institution ?? 'Conta' }}</small>
                            <span class="d-block mt-2 fw-bold text-primary">R$ {{ number_format($account->current_balance ?? 0, 2, ',', '.') }}</span>
                            @if($account->is_primary)<span class="badge bg-secondary mt-1">Principal</span>@endif
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @if($selectedAccount)
            <div class="col-md-4">
                <label for="filterStart" class="form-label">Início do período</label>
                <input type="date" class="form-control" id="filterStart" name="start_date" value="{{ $filters['start_date'] }}" required>
                @if($latestReconciliation)
                <small class="text-muted d-block mt-1">Última conciliação: {{ $latestReconciliation->end_date->format('d/m/Y') }}</small>
                @endif
            </div>
            <div class="col-md-4">
                <label for="filterEnd" class="form-label">Fim do período</label>
                <input type="date" class="form-control" id="filterEnd" name="end_date" value="{{ $filters['end_date'] }}" min="{{ $filters['start_date'] }}" required>
            </div>
            <div class="col-md-4">
                <input type="hidden" name="account_id" value="{{ $selectedAccount->id }}">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Pré-visualizar período
                    </button>
                    @if(($pendingForAccount['count_entries'] ?? 0) > 0)
                        <a href="{{ route('bank-reconciliation.index', ['account_id' => $selectedAccount->id, 'pending_only' => 1]) }}"
                           class="btn btn-outline-primary">
                            <i class="bi bi-clock-history"></i> Conciliar todas as pendências
                        </a>
                    @endif
                </div>
            </div>
            @if($pendingForAccount && ($pendingForAccount['count_entries'] ?? 0) > 0)
            <div class="col-12">
                <div class="alert alert-info mb-0 py-2 small">
                    <i class="bi bi-info-circle"></i>
                    Pendente de conciliar nesta conta:
                    <strong class="text-success">+R$ {{ number_format($pendingForAccount['income'] ?? 0, 2, ',', '.') }}</strong>
                    ·
                    <strong class="text-danger">-R$ {{ number_format($pendingForAccount['expense'] ?? 0, 2, ',', '.') }}</strong>
                    · {{ $pendingForAccount['count_entries'] ?? 0 }} lançamento(s)
                    @if($latestReconciliation)
                        <span class="d-block mt-1">
                            Alguns lançamentos podem ter datas anteriores à última conciliação ({{ $latestReconciliation->end_date->format('d/m/Y') }}).
                            Use <strong>Conciliar todas as pendências</strong> para incluí-los sem alterar o período já fechado item a item.
                        </span>
                    @endif
                </div>
            </div>
            @endif
            @endif
        </form>
        @endif
    </div>
</div>

@if ($selectedAccount && $preview)
    @php
        $currentBalance = $selectedAccount->current_balance ?? 0;
        $projectedBalance = $currentBalance + $preview['totals']['net'];
        $pendingEntries = collect($preview['income_groups'])
            ->flatMap(fn ($group) => $group['items']->map(fn ($item) => array_merge($item, [
                'group_label' => $group['label'],
                'direction' => 'income',
            ])))
            ->merge(collect($preview['expense_groups'])->flatMap(fn ($group) => $group['items']->map(fn ($item) => array_merge($item, [
                'group_label' => $group['label'],
                'direction' => 'expense',
            ]))))
            ->sortBy('reference_date')
            ->values();
        $hasPendingEntries = $pendingEntries->isNotEmpty();
    @endphp

    <div class="card border-0 shadow-sm mb-4" id="reconciliationPreview">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-0">
                    @if($pendingOnly ?? false)
                        Pré-visualização das pendências acumuladas
                    @else
                        Pré-visualização do período
                    @endif
                </h5>
                <small class="text-muted">
                    {{ \Carbon\Carbon::parse($filters['start_date'])->format('d/m/Y') }}
                    –
                    {{ \Carbon\Carbon::parse($filters['end_date'])->format('d/m/Y') }}
                    · {{ $selectedAccount->name }}
                </small>
                @if($pendingOnly ?? false)
                    <small class="d-block text-primary mt-1">
                        Período calculado automaticamente com base nas datas dos lançamentos pendentes.
                    </small>
                @endif
            </div>
            @if($hasPendingEntries)
                <span class="badge bg-primary">{{ $pendingEntries->count() }} lançamento(s) pendente(s)</span>
            @endif
        </div>
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="recon-kpi">
                        <span class="text-muted d-block">Saldo atual (antes)</span>
                        <h4 class="mb-0 mt-2 text-primary">R$ {{ number_format($currentBalance, 2, ',', '.') }}</h4>
                        <small class="text-muted">
                            Atualizado em {{ optional($selectedAccount->balance_updated_at)->format('d/m/Y H:i') ?? '—' }}
                        </small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="recon-kpi border-success">
                        <span class="text-muted d-block">Entradas conciliáveis</span>
                        <h4 class="mb-0 mt-2 text-success">R$ {{ number_format($preview['totals']['income'], 2, ',', '.') }}</h4>
                        <small class="text-muted">{{ $preview['income_groups']->sum('count') }} lançamento(s)</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="recon-kpi border-danger">
                        <span class="text-muted d-block">Saídas conciliáveis</span>
                        <h4 class="mb-0 mt-2 text-danger">R$ {{ number_format($preview['totals']['expense'], 2, ',', '.') }}</h4>
                        <small class="text-muted">{{ $preview['expense_groups']->sum('count') }} lançamento(s)</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="recon-kpi {{ $preview['totals']['net'] >= 0 ? 'border-success' : 'border-danger' }}">
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

            <h6 class="fw-semibold mb-3">Lançamentos pendentes de conciliação</h6>

            @if($hasPendingEntries)
                <div class="table-responsive mb-4">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Descrição</th>
                                <th>Origem</th>
                                <th>Tipo</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingEntries as $entry)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($entry['reference_date'])->format('d/m/Y') }}</td>
                                    <td>{{ $entry['label'] }}</td>
                                    <td>{{ $entry['group_label'] }}</td>
                                    <td>
                                        @if($entry['direction'] === 'income')
                                            <span class="badge bg-success-subtle text-success">Entrada</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">Saída</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-semibold {{ $entry['direction'] === 'income' ? 'text-success' : 'text-danger' }}">
                                        {{ $entry['direction'] === 'income' ? '+' : '-' }}R$ {{ number_format($entry['amount'], 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="4" class="text-end">Resultado do período</th>
                                <th class="text-end {{ $preview['totals']['net'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $preview['totals']['net'] >= 0 ? '+' : '-' }}R$ {{ number_format(abs($preview['totals']['net']), 2, ',', '.') }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="alert alert-light border mb-4">
                    <i class="bi bi-inbox me-1"></i>
                    Nenhum lançamento elegível para conciliação neste período. Ajuste as datas ou aguarde novos movimentos.
                </div>
            @endif

            @can('manage_bank_statements')
                <div class="alert alert-warning mb-4">
                    <strong>Atenção:</strong> a conciliação bancária é definitiva. Após confirmar, os lançamentos consolidados não poderão ser utilizados novamente. Apenas a conciliação mais recente pode ser cancelada.
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('bank-reconciliation.store') }}">
                        @csrf
                        <input type="hidden" name="account_id" value="{{ $selectedAccount->id }}">
                        @if($pendingOnly ?? false)
                            <input type="hidden" name="pending_only" value="1">
                        @else
                            <input type="hidden" name="start_date" value="{{ $filters['start_date'] }}">
                            <input type="hidden" name="end_date" value="{{ $filters['end_date'] }}">
                        @endif
                        <button type="submit" class="btn btn-success" @disabled(!$hasPendingEntries)>
                            <i class="bi bi-check-circle"></i>
                            {{ ($pendingOnly ?? false) ? 'Confirmar conciliação das pendências' : 'Confirmar conciliação' }}
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
        </div>
    </div>
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
                            <th class="text-end">Saldo anterior</th>
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
                                <td class="text-end text-muted">R$ {{ number_format($reconciliation->previous_balance ?? 0, 2, ',', '.') }}</td>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const startInput = document.getElementById('filterStart');
    const endInput = document.getElementById('filterEnd');

    if (!startInput || !endInput) {
        return;
    }

    const syncEndMinimum = () => {
        if (!startInput.value) {
            return;
        }

        endInput.min = startInput.value;

        if (endInput.value && endInput.value < startInput.value) {
            endInput.value = startInput.value;
        }
    };

    startInput.addEventListener('change', syncEndMinimum);
    syncEndMinimum();

    @if($preview ?? null)
    document.getElementById('reconciliationPreview')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    @endif
});
</script>
@endpush

