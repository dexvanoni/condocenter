@extends('layouts.app')

@section('title', 'Dashboard - Conselho Fiscal')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="row align-items-center mb-4">
            <div class="col-md-8">
                <h1 class="dashboard-title">
                    <i class="bi bi-clipboard-check text-gradient-primary"></i>
                    Conselho Fiscal
                </h1>
                <p class="dashboard-subtitle">
                    Fiscalização e Auditoria Financeira
                    <span class="text-muted">• {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}</span>
                </p>
            </div>
            <div class="col-md-4 text-end">
                <div class="dropdown d-inline-block">
                    <button class="btn btn-modern btn-gradient-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-download"></i> Exportar Relatórios
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-pdf text-danger"></i> Exportar PDF</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-excel text-success"></i> Exportar Excel</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas Importantes -->
    @if($semComprovante > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="widget-notification warning fade-in">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">Atenção! Transações sem comprovante</h6>
                        <p class="mb-0">
                            Existem {{ $semComprovante }} {{ Str::plural('transação', $semComprovante) }} de despesa sem comprovante anexado 
                            no valor total de <strong>R$ {{ number_format($totalSemComprovanteValor, 2, ',', '.') }}</strong>.
                            É recomendado solicitar ao síndico a inclusão dos comprovantes faltantes.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- KPIs Financeiros -->
    <div class="row g-4 mb-4">
        <!-- Receitas do Mês -->
        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-success stagger-1">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="stat-label mb-2">Receitas do Mês</p>
                            <h2 class="stat-value">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</h2>
                            @if($variacaoReceitas != 0)
                            <div class="stat-change">
                                <i class="bi bi-{{ $variacaoReceitas > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                {{ number_format(abs($variacaoReceitas), 1) }}% vs mês anterior
                            </div>
                            @endif
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-arrow-up-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Despesas do Mês -->
        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-danger stagger-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="stat-label mb-2">Despesas do Mês</p>
                            <h2 class="stat-value">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</h2>
                            @if($variacaoDespesas != 0)
                            <div class="stat-change">
                                <i class="bi bi-{{ $variacaoDespesas > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                {{ number_format(abs($variacaoDespesas), 1) }}%
                            </div>
                            @endif
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-arrow-down-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Saldo do Mês -->
        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-{{ $saldoMes >= 0 ? 'info' : 'warning' }} stagger-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="stat-label mb-2">Saldo do Mês</p>
                            <h2 class="stat-value">R$ {{ number_format(abs($saldoMes), 2, ',', '.') }}</h2>
                            <div class="stat-change">
                                {{ $saldoMes >= 0 ? 'Positivo' : 'Negativo' }}
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-{{ $saldoMes >= 0 ? 'check-circle' : 'exclamation-circle' }} fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inadimplência -->
        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-{{ $inadimplentes > 0 ? 'warning' : 'success' }} stagger-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="stat-label mb-2">Inadimplência</p>
                            <h2 class="stat-value">R$ {{ number_format($valorEmAtraso, 2, ',', '.') }}</h2>
                            <div class="stat-change">
                                {{ $inadimplentes }} {{ Str::plural('unidade', $inadimplentes) }}
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-exclamation-triangle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Saldo Anual e Informações -->
    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="dashboard-card hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="widget-icon bg-success bg-opacity-10 text-success me-3">
                            <i class="bi bi-graph-up-arrow fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Receitas no Ano</h6>
                            <h4 class="mb-0 text-success">R$ {{ number_format($receitasAno, 2, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="dashboard-card hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="widget-icon bg-danger bg-opacity-10 text-danger me-3">
                            <i class="bi bi-graph-down-arrow fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Despesas no Ano</h6>
                            <h4 class="mb-0 text-danger">R$ {{ number_format($despesasAno, 2, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="dashboard-card hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="widget-icon bg-{{ $saldoAno >= 0 ? 'primary' : 'warning' }} bg-opacity-10 text-{{ $saldoAno >= 0 ? 'primary' : 'warning' }} me-3">
                            <i class="bi bi-wallet2 fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Saldo Acumulado {{ now()->year }}</h6>
                            <h4 class="mb-0 text-{{ $saldoAno >= 0 ? 'success' : 'danger' }}">
                                R$ {{ number_format(abs($saldoAno), 2, ',', '.') }}
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabelas e Gráficos -->
    <div class="row g-4">
        <!-- Transações do Mês -->
        <div class="col-xl-8">
            <div class="dashboard-card">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="section-title mb-0">
                            <i class="bi bi-receipt text-primary"></i> Transações do Mês ({{ $totalTransacoes }})
                        </h5>
                        <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-primary">
                            Ver Todas <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Tipo</th>
                                    <th>Categoria</th>
                                    <th>Descrição</th>
                                    <th>Comprovante</th>
                                    <th class="text-end">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transacoesMes as $transacao)
                                <tr>
                                    <td>
                                        <strong>{{ $transacao->transaction_date->format('d/m/Y') }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge-modern bg-{{ $transacao->type === 'income' ? 'success' : 'danger' }}">
                                            {{ $transacao->type === 'income' ? 'Receita' : 'Despesa' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $transacao->category }}</span>
                                    </td>
                                    <td>{{ Str::limit($transacao->description, 50) }}</td>
                                    <td class="text-center">
                                        @if($transacao->receipts->count() > 0)
                                            <a href="#" class="text-success" title="Ver comprovantes">
                                                <i class="bi bi-file-check-fill"></i> {{ $transacao->receipts->count() }}
                                            </a>
                                        @else
                                            <span class="text-danger" title="Sem comprovante">
                                                <i class="bi bi-exclamation-triangle-fill"></i>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <strong class="{{ $transacao->type === 'income' ? 'text-success' : 'text-danger' }}">
                                            {{ $transacao->type === 'income' ? '+' : '-' }}
                                            R$ {{ number_format($transacao->amount, 2, ',', '.') }}
                                        </strong>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        Nenhuma transação neste mês
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($transacoesMes->count() > 0)
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="5" class="text-end">Saldo do Mês:</td>
                                    <td class="text-end {{ $saldoMes >= 0 ? 'text-success' : 'text-danger' }}">
                                        R$ {{ number_format($saldoMes, 2, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Despesas por Categoria e Resumo -->
        <div class="col-xl-4">
            <!-- Despesas por Categoria -->
            <div class="dashboard-card mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-pie-chart text-primary"></i> Despesas por Categoria
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($despesasPorCategoria as $despesa)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-medium">{{ $despesa->category }}</span>
                            <strong>R$ {{ number_format($despesa->total, 2, ',', '.') }}</strong>
                        </div>
                        <div class="progress-modern">
                            <div class="progress-bar" style="width: {{ $totalDespesas > 0 ? ($despesa->total / $totalDespesas) * 100 : 0 }}%"></div>
                        </div>
                        <small class="text-muted">
                            {{ $totalDespesas > 0 ? number_format(($despesa->total / $totalDespesas) * 100, 1) : 0 }}% do total
                        </small>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <p class="mb-0">Nenhuma despesa registrada</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Resumo de Auditoria -->
            <div class="dashboard-card">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-clipboard-data text-info"></i> Resumo de Auditoria
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-file-text text-primary me-2"></i>
                                    Total de Transações
                                </div>
                                <strong>{{ $totalTransacoes }}</strong>
                            </div>
                        </div>
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-{{ $semComprovante > 0 ? 'exclamation-triangle text-warning' : 'check-circle text-success' }} me-2"></i>
                                    Sem Comprovante
                                </div>
                                <strong class="{{ $semComprovante > 0 ? 'text-warning' : 'text-success' }}">
                                    {{ $semComprovante }}
                                </strong>
                            </div>
                        </div>
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-{{ $inadimplentes > 0 ? 'exclamation-circle text-danger' : 'check-circle text-success' }} me-2"></i>
                                    Inadimplentes
                                </div>
                                <strong class="{{ $inadimplentes > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $inadimplentes }}
                                </strong>
                            </div>
                        </div>
                        <div class="list-group-item px-0 border-bottom-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-calendar-check text-info me-2"></i>
                                    Período Analisado
                                </div>
                                <strong>{{ now()->format('m/Y') }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 mt-3 mb-0">
                        <small>
                            <i class="bi bi-info-circle me-1"></i>
                            Última atualização: {{ now()->format('d/m/Y H:i') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
