@extends('layouts.app')

@section('title', 'Dashboard - Síndico')

@section('content')
<div class="container-fluid px-4">
    <!-- Header com Animação -->
    <div class="dashboard-header">
        <div class="row align-items-center mb-4">
            <div class="col-md-8">
                <h1 class="dashboard-title">
                    <i class="bi bi-speedometer2 text-gradient-primary"></i>
                    Dashboard do Síndico
                </h1>
                <p class="dashboard-subtitle">
                    Bem-vindo, <strong>{{ Auth::user()->name }}</strong>! 
                    <span class="text-muted">• {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}</span>
                </p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('transactions.index') }}" class="btn btn-modern btn-gradient-primary me-2">
                    <i class="bi bi-graph-up"></i> Financeiro Completo
                </a>
            </div>
        </div>
    </div>

    <!-- KPIs Principais -->
    <div class="row g-3 mb-4">
        <div class="col-xxl-2 col-lg-4 col-sm-6">
            <div class="dashboard-card kpi-card h-100" style="padding: 1.5rem !important;">
                <div class="card-body">
                    <span class="kpi-label">Saldo do mês</span>
                    <h3 class="kpi-value {{ $saldo >= 0 ? 'text-success' : 'text-danger' }}">
                        R$ {{ number_format($saldo, 2, ',', '.') }}
                    </h3>
                    <span class="kpi-subtitle text-muted">
                        {{ $saldo >= 0 ? 'Superávit' : 'Déficit' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="col-xxl-2 col-lg-4 col-sm-6">
            <div class="dashboard-card kpi-card h-100" style="padding: 1.5rem !important;">
                <div class="card-body">
                    <span class="kpi-label">Receitas do mês</span>
                    <h3 class="kpi-value">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</h3>
                    <span class="kpi-subtitle {{ $variacaoReceitas >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($variacaoReceitas, 1) }}% vs mês anterior
                    </span>
                </div>
            </div>
        </div>

        <div class="col-xxl-2 col-lg-4 col-sm-6">
            <div class="dashboard-card kpi-card h-100" style="padding: 1.5rem !important;">
                <div class="card-body">
                    <span class="kpi-label">Despesas do mês</span>
                    <h3 class="kpi-value">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</h3>
                    <span class="kpi-subtitle {{ $variacaoDespesas <= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($variacaoDespesas, 1) }}% vs mês anterior
                    </span>
                </div>
            </div>
        </div>

        <div class="col-xxl-2 col-lg-4 col-sm-6">
            <div class="dashboard-card kpi-card h-100" style="padding: 1.5rem !important;">
                <div class="card-body">
                    <span class="kpi-label">Taxa de adimplência</span>
                    <h3 class="kpi-value {{ $taxaAdimplencia >= 90 ? 'text-success' : 'text-warning' }}">
                        {{ number_format($taxaAdimplencia, 1) }}%
                    </h3>
                    <span class="kpi-subtitle text-muted">
                        {{ $inadimplentes }} {{ Str::plural('inadimplente', $inadimplentes) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="col-xxl-2 col-lg-4 col-sm-6">
            <div class="dashboard-card kpi-card h-100" style="padding: 1.5rem !important;">
                <div class="card-body">
                    <span class="kpi-label">A receber</span>
                    <h3 class="kpi-value">R$ {{ number_format($totalAReceber, 2, ',', '.') }}</h3>
                    <span class="kpi-subtitle text-muted">Cobranças pendentes</span>
                </div>
            </div>
        </div>

        <div class="col-xxl-2 col-lg-4 col-sm-6">
            <div class="dashboard-card kpi-card h-100" style="padding: 1.5rem !important;">
                <div class="card-body">
                    <span class="kpi-label">Em atraso</span>
                    <h3 class="kpi-value text-danger">R$ {{ number_format($totalEmAtraso, 2, ',', '.') }}</h3>
                    <span class="kpi-subtitle text-muted">{{ $inadimplentes }} unidade(s)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs de Conciliação -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-lg-4 col-sm-6">
            <div class="dashboard-card kpi-card h-100" style="padding: 1.5rem !important;">
                <div class="card-body">
                    <span class="kpi-label">Saldo consolidado</span>
                    <h3 class="kpi-value text-primary">
                        R$ {{ number_format($saldoConsolidado, 2, ',', '.') }}
                    </h3>
                    <span class="kpi-subtitle text-muted">Soma das contas bancárias</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-sm-6">
            <div class="dashboard-card kpi-card h-100" style="padding: 1.5rem !important;">
                <div class="card-body">
                    <span class="kpi-label">Entradas a conciliar</span>
                    <h3 class="kpi-value text-success">
                        R$ {{ number_format($entradasNaoConciliadas, 2, ',', '.') }}
                    </h3>
                    <span class="kpi-subtitle text-muted">Receitas confirmadas aguardando conciliação</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-sm-6">
            <div class="dashboard-card kpi-card h-100" style="padding: 1.5rem !important;">
                <div class="card-body">
                    <span class="kpi-label">Saídas a conciliar</span>
                    <h3 class="kpi-value text-danger">
                        R$ {{ number_format($saidasNaoConciliadas, 2, ',', '.') }}
                    </h3>
                    <span class="kpi-subtitle text-muted">Pagamentos efetuados aguardando conciliação</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-sm-6">
            <div class="dashboard-card kpi-card h-100" style="padding: 1.5rem !important;">
                <div class="card-body">
                    <span class="kpi-label">Última conciliação</span>
                    @if($ultimaConsolidacao)
                        <h3 class="kpi-value text-secondary">
                            {{ $ultimaConsolidacao->created_at->format('d/m/Y H:i') }}
                        </h3>
                        <span class="kpi-subtitle text-muted d-block">
                            Conta: {{ $ultimaConsolidacao->bankAccount->name }}
                        </span>
                        <span class="kpi-subtitle text-muted">
                            Saldo: R$ {{ number_format($ultimaConsolidacao->resulting_balance, 2, ',', '.') }}
                        </span>
                    @else
                        <h3 class="kpi-value text-secondary">—</h3>
                        <span class="kpi-subtitle text-muted">Nenhuma conciliação registrada</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Resumo Operacional -->
    <div class="row g-4 mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="dashboard-card h-100" style="padding: 1.5rem !important;">
                <div class="card-header bg-white border-0 dashboard-card-header">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-cash-stack text-brand"></i> Saúde Financeira
                    </h5>
                </div>
                <div class="card-body dashboard-card-body">
                    <div class="insight-item">
                        <div>
                            <span class="insight-label">Total a receber</span>
                            <p class="insight-value mb-0">R$ {{ number_format($totalAReceber, 2, ',', '.') }}</p>
                        </div>
                        <span class="badge bg-light text-dark">Mês atual</span>
                    </div>
                    <div class="insight-item">
                        <div>
                            <span class="insight-label">Valor em atraso</span>
                            <p class="insight-value mb-0 text-danger">R$ {{ number_format($totalEmAtraso, 2, ',', '.') }}</p>
                        </div>
                        <span class="badge bg-light text-danger">{{ $inadimplentes }} unidade(s)</span>
                    </div>
                    <div class="insight-item">
                        <div>
                            <span class="insight-label">Reservas aprovadas</span>
                            <p class="insight-value mb-0">{{ $reservasMes }} no mês</p>
                        </div>
                        <span class="badge bg-light text-muted">Agenda</span>
                    </div>
                    <div class="insight-item">
                        <div>
                            <span class="insight-label">Saldo atual</span>
                            <p class="insight-value mb-0 {{ $saldo >= 0 ? 'text-success' : 'text-danger' }}">
                                R$ {{ number_format($saldo, 2, ',', '.') }}
                            </p>
                        </div>
                        <span class="badge bg-light text-muted">Receitas - despesas</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="dashboard-card h-100" style="padding: 1.5rem !important;">
                <div class="card-header bg-white border-0 dashboard-card-header">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-clipboard-data text-brand"></i> Operações do Dia
                    </h5>
                </div>
                <div class="card-body dashboard-card-body">
                    <div class="insight-item">
                        <div>
                            <span class="insight-label">Reservas pendentes</span>
                            <p class="insight-value mb-0">{{ $reservasPendentes }}</p>
                        </div>
                        <span class="badge bg-light text-warning">Aprovar</span>
                    </div>
                    <div class="insight-item">
                        <div>
                            <span class="insight-label">Encomendas aguardando</span>
                            <p class="insight-value mb-0">{{ $encombendasPendentes }}</p>
                        </div>
                        <span class="badge bg-light text-muted">{{ $encombendasHoje }} hoje</span>
                    </div>
                    <div class="insight-item">
                        <div>
                            <span class="insight-label">Entradas registradas hoje</span>
                            <p class="insight-value mb-0">{{ $entradasHoje }}</p>
                        </div>
                        <span class="badge bg-light text-muted">Portaria</span>
                    </div>
                    <div class="insight-item">
                        <div>
                            <span class="insight-label">Moradores ativos</span>
                            <p class="insight-value mb-0">{{ $moradoresAtivos }}</p>
                        </div>
                        <span class="badge bg-light text-success">Engajamento</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="dashboard-card h-100" style="padding: 1.5rem !important;">
                <div class="card-header bg-white border-0 dashboard-card-header">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-buildings text-brand"></i> Ocupação do Condomínio
                    </h5>
                </div>
                <div class="card-body dashboard-card-body">
                    <div class="insight-item">
                        <div>
                            <span class="insight-label">Unidades cadastradas</span>
                            <p class="insight-value mb-0">{{ $totalUnidades }}</p>
                        </div>
                        <span class="badge bg-light text-muted">Total</span>
                    </div>
                    <div class="insight-item">
                        <div>
                            <span class="insight-label">Taxa de ocupação</span>
                            <p class="insight-value mb-0">{{ number_format($ocupacaoPercentual, 1) }}%</p>
                        </div>
                        <span class="badge bg-light text-success">Moradores</span>
                    </div>
                    <div class="insight-item">
                        <div>
                            <span class="insight-label">Reservas confirmadas</span>
                            <p class="insight-value mb-0">{{ $proximasReservas->count() }}</p>
                        </div>
                        <span class="badge bg-light text-muted">Próximos 5</span>
                    </div>
                    <div class="insight-item">
                        <div>
                            <span class="insight-label">Reservas pendentes</span>
                            <p class="insight-value mb-0">{{ $reservasPendentes }}</p>
                        </div>
                        <span class="badge bg-light text-warning">Aguardando</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="dashboard-card h-100" style="padding: 1.5rem !important;">
                <div class="card-header bg-white border-0 dashboard-card-header">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-bar-chart-line text-brand"></i> Evolução Financeira (6 meses)
                    </h5>
                </div>
                <div class="card-body dashboard-card-body">
                    <canvas id="graficoFinanceiro" height="90"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="dashboard-card h-100" style="padding: 1.5rem !important;">
                <div class="card-header bg-white border-0 dashboard-card-header">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-pie-chart text-brand"></i> Adimplência x Inadimplência
                    </h5>
                </div>
                <div class="card-body dashboard-card-body">
                    <canvas id="graficoAdimplencia" height="220"></canvas>
                    <div class="mt-4">
                        <div class="insight-item">
                            <div>
                                <span class="insight-label">Unidades adimplentes</span>
                                <p class="insight-value mb-0">{{ max($totalUnidades - $inadimplentes, 0) }}</p>
                            </div>
                            <span class="badge bg-light text-success">{{ number_format($taxaAdimplencia, 1) }}%</span>
                        </div>
                        <div class="insight-item">
                            <div>
                                <span class="insight-label">Unidades inadimplentes</span>
                                <p class="insight-value mb-0 text-danger">{{ $inadimplentes }}</p>
                            </div>
                            <span class="badge bg-light text-danger">
                                R$ {{ number_format($totalEmAtraso, 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="dashboard-card h-100" style="padding: 1.5rem !important;">
                <div class="card-header bg-white border-0 dashboard-card-header">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-bar-chart text-brand"></i> Categorias Financeiras (ano)
                    </h5>
                </div>
                <div class="card-body dashboard-card-body">
                    <div class="chart-container position-relative" style="height: 320px;">
                        <canvas id="categoriasFinanceirasChart"></canvas>
                    </div>
                    <div class="mt-4">
                        @forelse($categoriasFinanceiras as $categoria)
                        <div class="insight-item">
                            <div>
                                <span class="insight-label">{{ $categoria->category }}</span>
                                <p class="insight-value mb-0">
                                    R$ {{ number_format($categoria->total_movimentado, 2, ',', '.') }}
                                </p>
                            </div>
                            <span class="badge bg-light text-success">
                                Receitas: R$ {{ number_format($categoria->total_receitas, 2, ',', '.') }}
                            </span>
                        </div>
                        @empty
                        <p class="text-muted mb-0">Sem movimentações registradas.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="dashboard-card h-100" style="padding: 1.5rem !important;">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-lightning-charge text-brand"></i> Ações Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        @can('view_transactions')
                        <a href="{{ route('transactions.index') }}" class="widget-quick-action">
                            <div class="widget-icon bg-brand-soft">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                            <h6 class="mt-2 mb-0">Transações</h6>
                            <small class="text-muted">Consultar lançamentos financeiros</small>
                        </a>
                        @endcan

                        @can('manage_reservations')
                        <a href="{{ route('reservations.manage') }}" class="widget-quick-action">
                            <div class="widget-icon bg-brand-soft">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                            <h6 class="mt-2 mb-0">Reservas pendentes</h6>
                            <small class="text-muted">{{ $reservasPendentes }} aguardando aprovação</small>
                        </a>
                        @endcan

                        @can('view_users')
                        <a href="{{ route('users.index') }}" class="widget-quick-action">
                            <div class="widget-icon bg-brand-soft">
                                <i class="bi bi-people"></i>
                            </div>
                            <h6 class="mt-2 mb-0">Moradores ativos</h6>
                            <small class="text-muted">{{ $moradoresAtivos }} usuário(s)</small>
                        </a>
                        @endcan

                        <a href="{{ route('packages.index') }}" class="widget-quick-action">
                            <div class="widget-icon bg-brand-soft">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <h6 class="mt-2 mb-0">Encomendas pendentes</h6>
                            <small class="text-muted">{{ $encombendasPendentes }} aguardando retirada</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabelas de Dados -->
    <div class="row g-4">
        <!-- Últimas Transações -->
        <div class="col-xl-8">
            <div class="dashboard-card" style="padding: 1.5rem !important;">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="section-title mb-0">
                            <i class="bi bi-receipt text-brand"></i> Últimas Transações
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
                                    <th>Descrição</th>
                                    <th>Tipo</th>
                                    <th>Categoria</th>
                                    <th class="text-end">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ultimasTransacoes as $transacao)
                                <tr>
                                    <td>
                                        <strong>{{ $transacao->transaction_date->format('d/m/Y') }}</strong>
                                    </td>
                                    <td>{{ Str::limit($transacao->description, 40) }}</td>
                                    <td>
                                        <span class="badge-modern bg-{{ $transacao->type === 'income' ? 'success' : 'danger' }}">
                                            {{ $transacao->type === 'income' ? 'Receita' : 'Despesa' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $transacao->category }}</span>
                                    </td>
                                    <td class="text-end">
                                        <strong class="{{ $transacao->type === 'income' ? 'text-brand' : 'text-brand-dark' }}">
                                            {{ $transacao->type === 'income' ? '+' : '-' }}
                                            R$ {{ number_format($transacao->amount, 2, ',', '.') }}
                                        </strong>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        Nenhuma transação encontrada
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Próximas Reservas -->
        <div class="col-xl-4">
            <div class="dashboard-card" style="padding: 1.5rem !important;">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-calendar-event text-brand"></i> Próximas Reservas
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($proximasReservas as $reserva)
                    <div class="list-item-hover border-bottom pb-3 mb-3">
                        <div class="d-flex">
                            <div class="me-3">
                                <div class="text-center p-2 rounded" style="background: linear-gradient(135deg, #0a1b67 0%, #3866d2 100%); color: white; min-width: 60px;">
                                    <div class="fw-bold fs-4">{{ $reserva->reservation_date->format('d') }}</div>
                                    <small>{{ $reserva->reservation_date->format('M') }}</small>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $reserva->space->name }}</h6>
                                <p class="mb-1 small text-muted">
                                    <i class="bi bi-person"></i> {{ $reserva->user->name }}
                                    @if($reserva->unit)
                                    <span class="badge bg-light text-dark ms-1">{{ $reserva->unit->full_identifier }}</span>
                                    @endif
                                </p>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> {{ $reserva->start_time }} - {{ $reserva->end_time }}
                                </small>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                        <p class="mb-0">Nenhuma reserva próxima</p>
                    </div>
                    @endforelse

                    @if($proximasReservas->count() > 0)
                    <div class="text-center mt-3">
                        <a href="{{ route('reservations.index') }}" class="btn btn-sm btn-outline-primary">
                            Ver Calendário Completo
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de Evolução Financeira
    const ctx = document.getElementById('graficoFinanceiro');
    if (ctx) {
        const graficoData = @json($graficoFinanceiro);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: graficoData.map(d => d.mes),
                datasets: [
                    {
                        label: 'Receitas',
                        data: graficoData.map(d => d.receitas),
                        borderColor: '#11998e',
                        backgroundColor: 'rgba(17, 153, 142, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Despesas',
                        data: graficoData.map(d => d.despesas),
                        borderColor: '#eb3349',
                        backgroundColor: 'rgba(235, 51, 73, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Saldo',
                        data: graficoData.map(d => d.saldo),
                        borderColor: '#f4a261',
                        backgroundColor: 'rgba(244, 162, 97, 0.15)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        borderDash: [6, 6]
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += 'R$ ' + context.parsed.y.toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Gráfico de Adimplência
    const adimplenciaCanvas = document.getElementById('graficoAdimplencia');
    if (adimplenciaCanvas) {
        const dadosAdimplencia = @json($graficoAdimplencia);
        new Chart(adimplenciaCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Adimplentes', 'Inadimplentes'],
                datasets: [{
                    data: [dadosAdimplencia.adimplentes, dadosAdimplencia.inadimplentes],
                    backgroundColor: ['#11998e', '#eb3349'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    // Categorias Financeiras
    const categoriasCanvas = document.getElementById('categoriasFinanceirasChart');
    if (categoriasCanvas) {
        const categorias = @json($categoriasFinanceiras);
        categoriasCanvas.style.height = '100%';
        categoriasCanvas.style.width = '100%';
        if (categorias.length) {
            new Chart(categoriasCanvas, {
                type: 'bar',
                data: {
                    labels: categorias.map(c => c.category),
                    datasets: [
                        {
                            label: 'Receitas',
                            data: categorias.map(c => Number(c.total_receitas)),
                            backgroundColor: 'rgba(17, 153, 142, 0.7)'
                        },
                        {
                            label: 'Despesas',
                            data: categorias.map(c => Number(c.total_despesas)),
                            backgroundColor: 'rgba(235, 51, 73, 0.7)'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.parsed.y || 0;
                                    return `${context.dataset.label}: R$ ${value.toLocaleString('pt-BR', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    })}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'R$ ' + value.toLocaleString('pt-BR');
                                }
                            }
                        }
                    }
                }
            });
        }
    }
});
</script>
@endpush
@endsection
