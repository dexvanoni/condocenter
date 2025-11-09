@extends('layouts.app')

@section('title', 'Prestação de Contas')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="mb-1">Prestação de Contas</h2>
                <p class="text-muted mb-0">Relatório detalhado de receitas e despesas do condomínio.</p>
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
            </form>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm h-100 border-success">
            <div class="card-body">
                <span class="text-muted">Saldo Inicial</span>
                <h3 class="mb-0 mt-2">R$ {{ number_format($data['totals']['opening_balance'], 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm h-100 border-success">
            <div class="card-body">
                <span class="text-muted">Entradas (Taxas)</span>
                <h3 class="mb-0 mt-2 text-success">R$ {{ number_format($data['totals']['charges_income'], 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm h-100 border-success">
            <div class="card-body">
                <span class="text-muted">Entradas (Avulsas)</span>
                <h3 class="mb-0 mt-2 text-success">R$ {{ number_format($data['totals']['manual_income'], 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm h-100 border-danger">
            <div class="card-body">
                <span class="text-muted">Saídas</span>
                <h3 class="mb-0 mt-2 text-danger">R$ {{ number_format($data['totals']['manual_expense'], 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body d-flex flex-wrap justify-content-between gap-3 align-items-center">
                <div>
                    <span class="text-muted">Resultado do Período</span>
                    <h3 class="mb-0 mt-1 {{ $data['totals']['balance_period'] >= 0 ? 'text-success' : 'text-danger' }}">
                        R$ {{ number_format($data['totals']['balance_period'], 2, ',', '.') }}
                    </h3>
                    <small class="text-muted">Saldo final: R$ {{ number_format($data['totals']['closing_balance'], 2, ',', '.') }}</small>
                </div>
                @if($canExport)
                    <div class="btn-group">
                        <a href="{{ route('accountability-reports.export.pdf', request()->query()) }}" class="btn btn-outline-primary">
                            <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
                        </a>
                        <a href="{{ route('accountability-reports.export.excel', request()->query()) }}" class="btn btn-outline-success">
                            <i class="bi bi-file-earmark-excel"></i> Exportar Planilha
                        </a>
                        <a href="{{ route('accountability-reports.print', request()->query()) }}" target="_blank" class="btn btn-outline-secondary">
                            <i class="bi bi-printer"></i> Imprimir
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Entradas - Taxas Recebidas</h5>
                <span class="badge bg-success">{{ $data['charge_summary']->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Taxa</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['charge_summary'] as $summary)
                                <tr>
                                    <td>{{ $summary['name'] }}</td>
                                    <td class="text-end text-success fw-semibold">
                                        R$ {{ number_format($summary['total'], 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">Nenhuma taxa recebida no período.</td>
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
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Entradas - Avulsas</h5>
                <span class="badge bg-success">{{ $data['manual_incomes']->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>{{ $canViewDetails ? 'Data' : 'Categoria' }}</th>
                                @if($canViewDetails)
                                    <th>Descrição</th>
                                    <th>Método</th>
                                @endif
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($canViewDetails)
                                @forelse($data['manual_incomes'] as $income)
                                    <tr>
                                        <td>{{ optional($income->transaction_date)->format('d/m/Y') }}</td>
                                        <td>{{ $income->description }}</td>
                                        <td>{{ strtoupper($income->payment_method ?? '—') }}</td>
                                        <td class="text-end text-success fw-semibold">
                                            R$ {{ number_format($income->amount, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Nenhum recebimento avulso registrado.</td>
                                    </tr>
                                @endforelse
                            @else
                                @php
                                    $groupedManualIncomes = $data['manual_incomes']->groupBy(function ($income) {
                                        return strtoupper($income->payment_method ?? 'OUTROS');
                                    });
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
                                        <td colspan="2" class="text-center text-muted py-4">Nenhum recebimento avulso registrado.</td>
                                    </tr>
                                @endforelse
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if($canViewDetails)
    @include('finance.accountability.shared-tables', ['data' => $data, 'skipCharges' => true])
@endif

<div class="row g-3 mt-3">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Contas bancárias</h5>
                <span class="text-muted small">Resumo dos saldos por conta</span>
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

<div class="row g-3 mt-3">
    <div class="col-xl-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Saídas Registradas</h5>
                <span class="badge bg-danger">{{ $data['manual_expenses']->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Descrição</th>
                                <th>Método</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['manual_expenses'] as $expense)
                                <tr>
                                    <td>{{ optional($expense->transaction_date)->format('d/m/Y') }}</td>
                                    <td>{{ $expense->description }}</td>
                                    <td>{{ strtoupper($expense->payment_method ?? '—') }}</td>
                                    <td class="text-end text-danger fw-semibold">
                                        R$ {{ number_format($expense->amount, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Nenhum pagamento registrado.</td>
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
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Pagamentos Recebidos (Resumo)</h5>
                <span class="badge bg-primary">{{ $data['payments_summary']->sum('transactions') }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Método</th>
                                <th class="text-end">Quantidade</th>
                                <th class="text-end">Valor Total</th>
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
@endsection

