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

    <!-- KPIs Principais com Cards Gradientes -->
    <div class="row g-4 mb-4">
        <!-- Saldo do Mês -->
        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-{{ $saldo >= 0 ? 'success' : 'danger' }} stagger-1">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="stat-icon" style="width: 64px; height: 64px;">
                            <i class="bi bi-wallet2" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <p class="stat-label mb-2 text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px; font-weight: 600;">Saldo do Mês</p>
                    <h2 class="stat-value mb-2" style="font-size: 2rem; font-weight: 700;">R$ {{ number_format(abs($saldo), 2, ',', '.') }}</h2>
                    @if($variacaoReceitas != 0)
                    <div class="stat-change" style="font-size: 0.8rem;">
                        <i class="bi bi-{{ $variacaoReceitas > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ number_format(abs($variacaoReceitas), 1) }}% vs mês anterior
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Receitas -->
        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-success stagger-2">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="stat-icon" style="width: 64px; height: 64px;">
                            <i class="bi bi-arrow-up-circle" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <p class="stat-label mb-2 text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px; font-weight: 600;">Receitas do Mês</p>
                    <h2 class="stat-value mb-2" style="font-size: 2rem; font-weight: 700;">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</h2>
                    @if($variacaoReceitas != 0)
                    <div class="stat-change" style="font-size: 0.8rem;">
                        <i class="bi bi-{{ $variacaoReceitas > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ number_format(abs($variacaoReceitas), 1) }}% vs mês anterior
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Despesas -->
        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-danger stagger-3">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="stat-icon" style="width: 64px; height: 64px;">
                            <i class="bi bi-arrow-down-circle" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <p class="stat-label mb-2 text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px; font-weight: 600;">Despesas do Mês</p>
                    <h2 class="stat-value mb-2" style="font-size: 2rem; font-weight: 700;">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</h2>
                    @if($variacaoDespesas != 0)
                    <div class="stat-change" style="font-size: 0.8rem;">
                        <i class="bi bi-{{ $variacaoDespesas > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ number_format(abs($variacaoDespesas), 1) }}% vs mês anterior
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Taxa de Adimplência -->
        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-{{ $taxaAdimplencia >= 90 ? 'info' : 'warning' }} stagger-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="stat-icon" style="width: 64px; height: 64px;">
                            <i class="bi bi-graph-up-arrow" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <p class="stat-label mb-2 text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px; font-weight: 600;">Taxa de Adimplência</p>
                    <h2 class="stat-value mb-2" style="font-size: 2rem; font-weight: 700;">{{ number_format($taxaAdimplencia, 1) }}%</h2>
                    <div class="stat-change" style="font-size: 0.8rem;">
                        {{ $inadimplentes }} {{ Str::plural('inadimplente', $inadimplentes) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Métricas Secundárias -->
    <div class="row g-4 mb-4">
        <!-- A Receber -->
        <div class="col-lg-3 col-md-6">
            <div class="dashboard-card hover-lift stagger-1">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="widget-icon bg-warning bg-opacity-10 text-warning" style="width: 56px; height: 56px;">
                            <i class="bi bi-clock-history fs-3"></i>
                        </div>
                    </div>
                    <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px;">A Receber</h6>
                    <h3 class="mb-0 fw-bold">R$ {{ number_format($totalAReceber, 2, ',', '.') }}</h3>
                </div>
            </div>
        </div>

        <!-- Em Atraso -->
        <div class="col-lg-3 col-md-6">
            <div class="dashboard-card hover-lift stagger-2">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="widget-icon bg-danger bg-opacity-10 text-danger" style="width: 56px; height: 56px;">
                            <i class="bi bi-exclamation-triangle fs-3"></i>
                        </div>
                    </div>
                    <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px;">Em Atraso</h6>
                    <h3 class="mb-1 fw-bold text-danger">R$ {{ number_format($totalEmAtraso, 2, ',', '.') }}</h3>
                    <small class="text-muted">{{ $inadimplentes }} {{ Str::plural('unidade', $inadimplentes) }}</small>
                </div>
            </div>
        </div>

        <!-- Encomendas -->
        <div class="col-lg-3 col-md-6">
            <div class="dashboard-card hover-lift stagger-3">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="widget-icon bg-success bg-opacity-10 text-success" style="width: 56px; height: 56px;">
                            <i class="bi bi-box-seam fs-3"></i>
                        </div>
                    </div>
                    <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px;">Encomendas</h6>
                    <h3 class="mb-1 fw-bold">{{ $encombendasPendentes }}</h3>
                    <small class="text-muted">{{ $encombendasHoje }} {{ Str::plural('recebida', $encombendasHoje) }} hoje</small>
                </div>
            </div>
        </div>

        <!-- Reservas Pendentes -->
        <div class="col-lg-3 col-md-6">
            <div class="dashboard-card hover-lift stagger-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="widget-icon bg-info bg-opacity-10 text-info" style="width: 56px; height: 56px;">
                            <i class="bi bi-calendar-check fs-3"></i>
                        </div>
                    </div>
                    <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px;">Reservas Pendentes</h6>
                    <h3 class="mb-1 fw-bold">{{ $reservasPendentes }}</h3>
                    <small class="text-muted">Aguardando aprovação</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico Financeiro e Ações Rápidas -->
    <div class="row g-4 mb-4">
        <!-- Gráfico de Evolução Financeira -->
        <div class="col-xl-8">
            <div class="dashboard-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="section-title mb-0">
                            <i class="bi bi-bar-chart-line text-primary"></i> Evolução Financeira (6 meses)
                        </h5>
                        <div class="chart-legend">
                            <div class="chart-legend-item">
                                <div class="chart-legend-color" style="background: #11998e;"></div>
                                <span>Receitas</span>
                            </div>
                            <div class="chart-legend-item">
                                <div class="chart-legend-color" style="background: #eb3349;"></div>
                                <span>Despesas</span>
                            </div>
                        </div>
                    </div>
                    <canvas id="graficoFinanceiro" height="80"></canvas>
                </div>
            </div>
        </div>

        <!-- Ações Rápidas -->
        <div class="col-xl-4">
            <div class="dashboard-card">
                <div class="card-body">
                    <h5 class="section-title mb-4">
                        <i class="bi bi-lightning-charge text-warning"></i> Ações Rápidas
                    </h5>
                    <div class="d-grid gap-3">
                        @can('view_transactions')
                        <a href="{{ route('transactions.index') }}" class="widget-quick-action">
                            <div class="widget-icon bg-success bg-opacity-10 text-success">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                            <h6 class="mt-2 mb-0">Transações</h6>
                            <small class="text-muted">Ver todas as transações</small>
                        </a>
                        @endcan

                        @can('manage_reservations')
                        <a href="{{ route('reservations.manage') }}" class="widget-quick-action">
                            <div class="widget-icon bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                            <h6 class="mt-2 mb-0">Gerenciar Reservas</h6>
                            <small class="text-muted">{{ $reservasPendentes }} pendente(s)</small>
                        </a>
                        @endcan

                        @can('view_users')
                        <a href="{{ route('users.index') }}" class="widget-quick-action">
                            <div class="widget-icon bg-info bg-opacity-10 text-info">
                                <i class="bi bi-people"></i>
                            </div>
                            <h6 class="mt-2 mb-0">Moradores</h6>
                            <small class="text-muted">{{ $moradoresAtivos }} ativo(s)</small>
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabelas de Dados -->
    <div class="row g-4">
        <!-- Últimas Transações -->
        <div class="col-xl-8">
            <div class="dashboard-card">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="section-title mb-0">
                            <i class="bi bi-receipt text-primary"></i> Últimas Transações
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
                                        <strong class="{{ $transacao->type === 'income' ? 'text-success' : 'text-danger' }}">
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
            <div class="dashboard-card">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-calendar-event text-primary"></i> Próximas Reservas
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($proximasReservas as $reserva)
                    <div class="list-item-hover border-bottom pb-3 mb-3">
                        <div class="d-flex">
                            <div class="me-3">
                                <div class="text-center p-2 rounded" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; min-width: 60px;">
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
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
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
});
</script>
@endpush
@endsection
