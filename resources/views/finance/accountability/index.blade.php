@extends('layouts.app')

@section('title', 'Prestação de Contas')

@section('content')
@if(session('error'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h2 class="mb-1"><i class="bi bi-file-earmark-bar-graph text-primary"></i> Prestação de Contas</h2>
                <p class="text-muted mb-2">
                    Relatório oficial de receitas e despesas.
                    Lançamentos em <a href="{{ route('financial.accounts.index') }}">Caixa do Condomínio</a>
                    @if(Route::has('financial.employees.index'))
                        e <a href="{{ route('financial.employees.index') }}">Quadro de Funcionários</a>.
                    @else
                        .
                    @endif
                </p>
                <span class="badge bg-light text-dark border">
                    <i class="bi bi-calendar3"></i>
                    {{ $startDate->format('d/m/Y') }} — {{ $endDate->format('d/m/Y') }}
                </span>
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
                        <i class="bi bi-search"></i> Gerar
                    </button>
                </div>
                @if($canExport)
                    <div class="col-auto">
                        <label class="form-label mb-1 d-none d-md-block">&nbsp;</label>
                        <div class="btn-group">
                            <a href="{{ route('accountability-reports.export.pdf', request()->query()) }}" class="btn btn-outline-primary" title="PDF completo">
                                <i class="bi bi-file-earmark-pdf"></i> PDF
                            </a>
                            <a href="{{ route('accountability-reports.export.excel', request()->query()) }}" class="btn btn-outline-success" title="Planilha Excel">
                                <i class="bi bi-file-earmark-excel"></i> Excel
                            </a>
                            <a href="{{ route('accountability-reports.print', request()->query()) }}" target="_blank" class="btn btn-outline-secondary" title="Imprimir">
                                <i class="bi bi-printer"></i>
                            </a>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm h-100 border-0 border-start border-4 border-secondary">
            <div class="card-body">
                <small class="text-muted text-uppercase">Saldo inicial</small>
                <h4 class="mb-0 mt-1">R$ {{ number_format($data['totals']['opening_balance'], 2, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm h-100 border-0 border-start border-4 border-success">
            <div class="card-body">
                <small class="text-muted text-uppercase">Entradas (taxas)</small>
                <h4 class="mb-0 mt-1 text-success">R$ {{ number_format($data['totals']['charges_income'], 2, ',', '.') }}</h4>
                <small class="text-muted">{{ $data['totals']['charges_received_count'] }} cobranças</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm h-100 border-0 border-start border-4 border-success">
            <div class="card-body">
                <small class="text-muted text-uppercase">Entradas (avulsas)</small>
                <h4 class="mb-0 mt-1 text-success">R$ {{ number_format($data['totals']['manual_income'], 2, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm h-100 border-0 border-start border-4 border-danger">
            <div class="card-body">
                <small class="text-muted text-uppercase">Saídas (total)</small>
                <h4 class="mb-0 mt-1 text-danger">R$ {{ number_format($data['totals']['total_expense'], 2, ',', '.') }}</h4>
                <small class="text-muted">
                    Avulsas: R$ {{ number_format($data['totals']['manual_expense'], 2, ',', '.') }}
                    · Pessoal: R$ {{ number_format($data['totals']['employee_payroll'], 2, ',', '.') }}
                </small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm h-100 border-0 border-start border-4 border-warning">
            <div class="card-body">
                <small class="text-muted text-uppercase">Despesas com pessoal</small>
                <h4 class="mb-0 mt-1 text-danger">R$ {{ number_format($data['totals']['employee_payroll'], 2, ',', '.') }}</h4>
                @if(($data['totals']['employee_payroll_gross'] ?? 0) > 0)
                    <small class="text-muted">Bruto: R$ {{ number_format($data['totals']['employee_payroll_gross'], 2, ',', '.') }}</small>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 bg-light">
            <div class="card-body d-flex flex-wrap justify-content-between gap-3 align-items-center">
                <div>
                    <small class="text-muted">Resultado do período</small>
                    <h3 class="mb-1 {{ $data['totals']['balance_period'] >= 0 ? 'text-success' : 'text-danger' }}">
                        R$ {{ number_format($data['totals']['balance_period'], 2, ',', '.') }}
                    </h3>
                    <small class="text-muted">Saldo final: R$ {{ number_format($data['totals']['closing_balance'], 2, ',', '.') }}</small>
                </div>
                @if($canExport)
                    <div class="btn-group flex-wrap">
                        <a href="{{ route('accountability-reports.export.pdf', request()->query()) }}" class="btn btn-primary">
                            <i class="bi bi-file-earmark-pdf"></i> PDF Detalhado
                        </a>
                        <a href="{{ route('accountability-reports.download-receipts', request()->query()) }}" class="btn btn-outline-info">
                            <i class="bi bi-download"></i> Comprovantes
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Taxas por dia --}}
    <div class="col-xl-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Taxas Recebidas — por dia</h5>
                        <small class="text-muted">Visão resumida do período</small>
                    </div>
                    <span class="badge bg-success">{{ $data['charge_daily_summary']->count() }} dias</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th class="text-end">Cobranças</th>
                                <th class="text-end">Total do dia</th>
                            </tr>
                        </thead>
                        <tbody>
                            @include('finance.accountability.partials.daily-charge-rows', [
                                'groups' => $data['charge_daily_summary'],
                                'emptyMessage' => 'Nenhuma taxa recebida no período.',
                            ])
                        </tbody>
                        @if($data['charge_daily_summary']->isNotEmpty())
                            <tfoot class="table-success">
                                <tr>
                                    <th class="text-end" colspan="2">Total ({{ $data['totals']['charges_received_count'] }} cobranças):</th>
                                    <th class="text-end">R$ {{ number_format($data['totals']['charges_income'], 2, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
            @if($data['charge_summary']->isNotEmpty())
                <div class="card-footer bg-white">
                    <button class="btn btn-sm btn-outline-secondary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#chargeTypeBreakdown">
                        <i class="bi bi-list-ul"></i> Ver detalhamento por tipo de taxa
                    </button>
                    <div class="collapse mt-3" id="chargeTypeBreakdown">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Taxa</th>
                                    <th class="text-end">Qtd</th>
                                    <th class="text-end">Unit.</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @include('finance.accountability._charge-summary-rows', [
                                    'summaries' => $data['charge_summary'],
                                    'highlight' => false,
                                ])
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Entradas avulsas por dia --}}
    <div class="col-xl-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Entradas Avulsas — por dia</h5>
                        <small class="text-muted">Recebimentos fora de taxas</small>
                    </div>
                    <span class="badge bg-success">{{ $data['manual_income_daily']->count() }} dias</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                @if($canViewDetails)
                                    <th>Data</th>
                                    <th>Resumo</th>
                                    <th class="text-end">Total do dia</th>
                                @else
                                    <th>Categoria</th>
                                    <th class="text-end">Valor</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @if($canViewDetails)
                                @forelse($data['manual_income_daily'] as $group)
                                    <tr>
                                        <td class="fw-semibold">{{ $group['date']->format('d/m/Y') }}</td>
                                        <td>
                                            {{ $group['count'] }} {{ $group['count'] === 1 ? 'recebimento' : 'recebimentos' }}
                                        </td>
                                        <td class="text-end text-success fw-semibold">
                                            R$ {{ number_format($group['total'], 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">Nenhum recebimento avulso.</td>
                                    </tr>
                                @endforelse
                            @else
                                @php
                                    $groupedManualIncomes = $data['manual_incomes']->groupBy(fn ($income) => strtoupper($income->payment_method ?? 'OUTROS'));
                                @endphp
                                @forelse($groupedManualIncomes as $method => $group)
                                    <tr>
                                        <td>{{ $method === 'OUTROS' ? 'Outros métodos' : $method }}</td>
                                        <td class="text-end text-success fw-semibold">
                                            R$ {{ number_format($group->sum('amount'), 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-4">Nenhum recebimento avulso.</td>
                                    </tr>
                                @endforelse
                            @endif
                        </tbody>
                        @if($canViewDetails && $data['manual_income_daily']->isNotEmpty())
                            <tfoot class="table-success">
                                <tr>
                                    <th colspan="2" class="text-end">Total:</th>
                                    <th class="text-end">R$ {{ number_format($data['totals']['manual_income'], 2, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    {{-- Saídas por dia --}}
    <div class="col-xl-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Saídas — por dia</h5>
                        <small class="text-muted">Pagamentos e despesas registradas</small>
                    </div>
                    <span class="badge bg-danger">{{ $data['manual_expense_daily']->count() }} dias</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Resumo</th>
                                <th class="text-end">Total computado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['manual_expense_daily'] as $group)
                                <tr @class(['table-secondary text-muted' => $group['active_total'] <= 0 && $group['cancelled_count'] > 0])>
                                    <td class="fw-semibold">{{ $group['date']->format('d/m/Y') }}</td>
                                    <td>
                                        {{ $group['count'] }} {{ $group['count'] === 1 ? 'pagamento' : 'pagamentos' }}
                                        @if($group['cancelled_count'] > 0)
                                            <span class="badge bg-secondary ms-1">{{ $group['cancelled_count'] }} cancelado(s)</span>
                                        @endif
                                    </td>
                                    <td class="text-end text-danger fw-semibold">
                                        R$ {{ number_format($group['active_total'], 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">Nenhum pagamento registrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($data['manual_expense_daily']->isNotEmpty())
                            <tfoot class="table-danger">
                                <tr>
                                    <th colspan="2" class="text-end">Total computado:</th>
                                    <th class="text-end">R$ {{ number_format($data['totals']['manual_expense'], 2, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
            @if($canViewDetails && ($data['totals']['manual_expense_cancelled_count'] ?? 0) > 0)
                <div class="card-footer bg-white">
                    <button class="btn btn-sm btn-outline-secondary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#cancelledExpensesDetail">
                        <i class="bi bi-x-circle"></i> Ver pagamentos cancelados ({{ $data['totals']['manual_expense_cancelled_count'] }})
                    </button>
                    <div class="collapse mt-3" id="cancelledExpensesDetail">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Descrição</th>
                                    <th>Motivo</th>
                                    <th class="text-end">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['manual_expenses']->filter(fn ($e) => $e->isCancelled()) as $expense)
                                    <tr class="text-muted">
                                        <td>{{ optional($expense->transaction_date)->format('d/m/Y') }}</td>
                                        <td>{{ $expense->description }}</td>
                                        <td><small>{{ $expense->cancellation_reason }}</small></td>
                                        <td class="text-end text-decoration-line-through">
                                            R$ {{ number_format($expense->amount, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Pagamentos por método --}}
    <div class="col-xl-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Pagamentos Recebidos — por método</h5>
                        <small class="text-muted">Consolidado sem identificação de unidades</small>
                    </div>
                    <span class="badge bg-primary">{{ $data['payments_summary']->sum('transactions') }}</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Método</th>
                                <th class="text-end">Quantidade</th>
                                <th class="text-end">Valor total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['payments_summary'] as $paymentSummary)
                                <tr>
                                    <td>{{ $paymentSummary['method'] }}</td>
                                    <td class="text-end">{{ $paymentSummary['transactions'] }}</td>
                                    <td class="text-end text-primary fw-semibold">
                                        R$ {{ number_format($paymentSummary['total'], 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">Nenhum pagamento registrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('finance.accountability.partials.employee-payroll-section', [
    'employeePayroll' => $data['employee_payroll'],
])

<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Contas bancárias</h5>
                    <small class="text-muted">Saldos atuais por conta</small>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Conta</th>
                                <th>Instituição</th>
                                <th>Titular</th>
                                <th>Última atualização</th>
                                <th class="text-end">Saldo atual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['bank_accounts'] as $account)
                                <tr>
                                    <td class="fw-semibold">{{ $account['name'] }}</td>
                                    <td>{{ $account['institution'] ?? '—' }}</td>
                                    <td>{{ $account['holder'] ?? '—' }}</td>
                                    <td>{{ optional($account['balance_updated_at'])->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td class="text-end fw-semibold {{ $account['current_balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        R$ {{ number_format($account['current_balance'], 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Nenhuma conta bancária cadastrada.</td>
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
