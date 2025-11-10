@extends('layouts.app')

@section('title', 'Dashboard - Administrador da Plataforma')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="row align-items-center mb-4">
            <div class="col-md-8">
                <h1 class="dashboard-title">
                    <i class="bi bi-gear-fill text-gradient-primary"></i>
                    Administração da Plataforma
                </h1>
                <p class="dashboard-subtitle">
                    Painel de Controle Geral
                    <span class="text-muted">• {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}</span>
                </p>
            </div>
            <div class="col-md-4 text-end">
                @can('view_users')
                <a href="{{ route('users.index') }}" class="btn btn-modern btn-gradient-primary">
                    <i class="bi bi-people"></i> Gerenciar Usuários
                </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row g-3 mb-4">
        <div class="col-xxl-2 col-lg-4 col-sm-6">
            <div class="dashboard-card kpi-card h-100" style="padding: 1.5rem !important;">
                <div class="card-body">
                    <span class="kpi-label">Condomínios</span>
                    <h3 class="kpi-value">{{ $totalCondominios }}</h3>
                    <span class="kpi-subtitle text-success">
                        {{ number_format($condominiosAtivosPercentual, 1) }}% ativos
                    </span>
                </div>
            </div>
        </div>

        <div class="col-xxl-2 col-lg-4 col-sm-6">
            <div class="dashboard-card kpi-card h-100" style="padding: 1.5rem !important;">
                <div class="card-body">
                    <span class="kpi-label">Usuários</span>
                    <h3 class="kpi-value">{{ $totalUsuarios }}</h3>
                    <span class="kpi-subtitle text-success">
                        {{ number_format($usuariosAtivosPercentual, 1) }}% ativos
                    </span>
                </div>
            </div>
        </div>

        <div class="col-xxl-2 col-lg-4 col-sm-6">
            <div class="dashboard-card kpi-card h-100" style="padding: 1.5rem !important;">
                <div class="card-body">
                    <span class="kpi-label">Transações (mês)</span>
                    <h3 class="kpi-value">{{ $totalTransacoesMes }}</h3>
                    <span class="kpi-subtitle text-muted">
                        Volume R$ {{ number_format($volumeFinanceiroMes, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="col-xxl-2 col-lg-4 col-sm-6">
            <div class="dashboard-card kpi-card h-100" style="padding: 1.5rem !important;">
                <div class="card-body">
                    <span class="kpi-label">Reservas (mês)</span>
                    <h3 class="kpi-value">{{ $totalReservasMes }}</h3>
                    <span class="kpi-subtitle text-muted">Plataforma</span>
                </div>
            </div>
        </div>

        <div class="col-xxl-2 col-lg-4 col-sm-6">
            <div class="dashboard-card kpi-card h-100" style="padding: 1.5rem !important;">
                <div class="card-body">
                    <span class="kpi-label">Crescimento usuários</span>
                    <h3 class="kpi-value {{ $crescimentoUsuarios >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($crescimentoUsuarios, 1) }}%
                    </h3>
                    <span class="kpi-subtitle text-muted">vs mês anterior</span>
                </div>
            </div>
        </div>

        <div class="col-xxl-2 col-lg-4 col-sm-6">
            <div class="dashboard-card kpi-card h-100" style="padding: 1.5rem !important;">
                <div class="card-body">
                    <span class="kpi-label">Cobranças pendentes</span>
                    <h3 class="kpi-value">{{ $resumoOperacional['cobrancasPendentes'] }}</h3>
                    <span class="kpi-subtitle text-danger">
                        R$ {{ number_format($resumoOperacional['valorCobrancasPendentes'], 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="dashboard-card h-100" style="padding: 1.5rem !important;">
                <div class="card-header bg-white border-0 dashboard-card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="section-title mb-0">
                            <i class="bi bi-activity text-brand"></i> Crescimento da Plataforma (6 meses)
                        </h5>
                    </div>
                </div>
                <div class="card-body dashboard-card-body">
                    <canvas id="historicoPlataformaChart" height="90"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="dashboard-card h-100" style="padding: 1.5rem !important;">
                <div class="card-header bg-white border-0 dashboard-card-header">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-people text-brand"></i> Distribuição de Perfis
                    </h5>
                </div>
                <div class="card-body dashboard-card-body">
                    <canvas id="usuariosPerfilChart" height="220"></canvas>
                    <div class="mt-4">
                        @forelse($usuariosPorPerfil as $perfil => $quantidade)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold">{{ $perfil }}</span>
                            <span class="badge bg-light text-dark">{{ $quantidade }}</span>
                        </div>
                        @empty
                        <p class="text-muted mb-0 text-center">Nenhum usuário cadastrado</p>
                        @endforelse
                        @if(count($usuariosPorPerfil) > 0)
                        <div class="alert alert-info border-0 mt-3 mb-0">
                            <small>
                                <i class="bi bi-info-circle me-1"></i>
                                Total: {{ $totalUsuarios }} {{ Str::plural('usuário', $totalUsuarios) }} na plataforma
                            </small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-4">
            <div class="dashboard-card h-100" style="padding: 1.5rem !important;">
                <div class="card-header bg-white border-0 dashboard-card-header">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-speedometer2 text-brand"></i> Indicadores Operacionais
                    </h5>
                </div>
                <div class="card-body">
                    <div class="insight-item">
                        <div>
                            <span class="insight-label">Cobranças em aberto</span>
                            <p class="insight-value mb-0">
                                {{ $resumoOperacional['cobrancasPendentes'] }} ocorrência(s)
                            </p>
                        </div>
                        <span class="badge bg-light text-danger">
                            R$ {{ number_format($resumoOperacional['valorCobrancasPendentes'], 2, ',', '.') }}
                        </span>
                    </div>
                    <div class="insight-item">
                        <div>
                            <span class="insight-label">Valor em atraso</span>
                            <p class="insight-value mb-0">
                                R$ {{ number_format($resumoOperacional['valorCobrancasAtraso'], 2, ',', '.') }}
                            </p>
                        </div>
                        <span class="badge bg-light text-danger">Crítico</span>
                    </div>
                    <div class="insight-item">
                        <div>
                            <span class="insight-label">Reservas pendentes</span>
                            <p class="insight-value mb-0">
                                {{ $resumoOperacional['reservasPendentes'] }} aguardando ação
                            </p>
                        </div>
                        <span class="badge bg-light text-warning">Priorizar</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Condomínios ativos</span>
                        <span class="fw-semibold">{{ $condominiosAtivos }} / {{ $totalCondominios }}</span>
                    </div>
                    <div class="progress-modern mt-2">
                        <div class="progress-bar" style="width: {{ $condominiosAtivosPercentual }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <span class="text-muted">Usuários ativos</span>
                        <span class="fw-semibold">{{ $usuariosAtivos }} / {{ $totalUsuarios }}</span>
                    </div>
                    <div class="progress-modern mt-2">
                        <div class="progress-bar" style="width: {{ $usuariosAtivosPercentual }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="dashboard-card h-100" style="padding: 1.5rem !important;">
                <div class="card-header bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="section-title mb-0">
                            <i class="bi bi-building text-brand"></i> Condomínios na Plataforma
                        </h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Endereço</th>
                                    <th class="text-center">Unidades</th>
                                    <th class="text-center">Usuários</th>
                                    <th>Status</th>
                                    <th>Criado em</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($condominios as $condominio)
                                <tr>
                                    <td>
                                        <strong>{{ $condominio->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            {{ $condominio->address ?? '-' }}
                                            @if($condominio->city)
                                            <br><small>{{ $condominio->city }} - {{ $condominio->state }}</small>
                                            @endif
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark">
                                            {{ $condominio->units_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark">
                                            {{ $condominio->users_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-modern bg-{{ $condominio->is_active ? 'success' : 'secondary' }}">
                                            {{ $condominio->is_active ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $condominio->created_at->format('d/m/Y') }}</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        Nenhum condomínio cadastrado
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="dashboard-card h-100" style="padding: 1.5rem !important;">
                <div class="card-header bg-white border-0">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-trophy text-brand"></i> Top Condomínios
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($topCondominios as $index => $condo)
                    <div class="list-item-hover border-bottom pb-3 mb-3">
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <div class="rounded-circle bg-{{ $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : 'light') }} text-white d-flex align-items-center justify-content-center" 
                                     style="width: 40px; height: 40px; font-weight: bold;">
                                    {{ $index + 1 }}
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $condo->name }}</h6>
                                <small class="text-muted">
                                    <i class="bi bi-people"></i> {{ $condo->users_count }} {{ Str::plural('usuário', $condo->users_count) }}
                                </small>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <p class="mb-0">Nenhum condomínio</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="row g-4">
        <div class="col-12">
            <div class="dashboard-card" style="padding: 1.5rem !important;">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-lightning-charge text-brand"></i> Ações Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @can('view_users')
                        <div class="col-md-4 col-sm-6">
                            <a href="{{ route('users.index') }}" class="widget-quick-action">
                                <div class="widget-icon bg-brand-soft">
                                    <i class="bi bi-people"></i>
                                </div>
                                <h6 class="mt-3 mb-1">Gerenciar Usuários</h6>
                                <small class="text-muted">Ver todos os usuários</small>
                            </a>
                        </div>
                        @endcan

                        @can('view_units')
                        <div class="col-md-4 col-sm-6">
                            <a href="{{ route('units.index') }}" class="widget-quick-action">
                                <div class="widget-icon bg-brand-soft">
                                    <i class="bi bi-building"></i>
                                </div>
                                <h6 class="mt-3 mb-1">Gerenciar Unidades</h6>
                                <small class="text-muted">Ver todas as unidades</small>
                            </a>
                        </div>
                        @endcan

                        @can('view_transactions')
                        <div class="col-md-4 col-sm-6">
                            <a href="{{ route('transactions.index') }}" class="widget-quick-action">
                                <div class="widget-icon bg-brand-soft">
                                    <i class="bi bi-graph-up"></i>
                                </div>
                                <h6 class="mt-3 mb-1">Transações</h6>
                                <small class="text-muted">Análises financeiras</small>
                            </a>
                        </div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const historico = @json($historicoPlataforma);
    const usuariosPorPerfil = @json($usuariosPorPerfil);

    const historicoCanvas = document.getElementById('historicoPlataformaChart');
    if (historicoCanvas && historico.length) {
        const labels = historico.map(item => item.mes);
        const usuariosDataset = historico.map(item => item.usuarios);
        const condominiosDataset = historico.map(item => item.condominios);

        new Chart(historicoCanvas, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Novos usuários',
                        data: usuariosDataset,
                        borderColor: '#3866d2',
                        backgroundColor: 'rgba(56, 102, 210, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                    },
                    {
                        label: 'Novos condomínios',
                        data: condominiosDataset,
                        borderColor: '#11998e',
                        backgroundColor: 'rgba(17, 153, 142, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                const label = context.dataset.label || '';
                                return `${label}: ${context.parsed.y.toLocaleString('pt-BR')}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
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

    const perfilCanvas = document.getElementById('usuariosPerfilChart');
    const perfilEntries = Object.entries(usuariosPorPerfil);
    if (perfilCanvas && perfilEntries.length) {
        const labels = perfilEntries.map(([label]) => label);
        const data = perfilEntries.map(([, value]) => value);
        const colors = [
            '#3866d2',
            '#0a1b67',
            '#11998e',
            '#eb3349',
            '#f4a261',
            '#8338ec',
            '#06d6a0'
        ];

        new Chart(perfilCanvas, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: colors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '65%'
            }
        });
    }
});
</script>
@endpush
@endsection
