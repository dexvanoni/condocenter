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

    <!-- Estatísticas Principais -->
    <div class="row g-4 mb-4">
        <!-- Total de Condomínios -->
        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-primary stagger-1">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="stat-label mb-2">Total de Condomínios</p>
                            <h2 class="stat-value">{{ $totalCondominios }}</h2>
                            <div class="stat-change">
                                {{ $condominiosAtivos }} ativo(s)
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-building fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total de Usuários -->
        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-success stagger-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="stat-label mb-2">Total de Usuários</p>
                            <h2 class="stat-value">{{ $totalUsuarios }}</h2>
                            <div class="stat-change">
                                @if($crescimentoUsuarios != 0)
                                <i class="bi bi-{{ $crescimentoUsuarios > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                {{ number_format(abs($crescimentoUsuarios), 1) }}% este mês
                                @else
                                {{ $usuariosAtivos }} ativo(s)
                                @endif
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-people fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transações no Mês -->
        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-info stagger-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="stat-label mb-2">Transações no Mês</p>
                            <h2 class="stat-value">{{ $totalTransacoesMes }}</h2>
                            <div class="stat-change">
                                R$ {{ number_format($volumeFinanceiroMes, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-graph-up fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reservas no Mês -->
        <div class="col-xl-3 col-lg-6">
            <div class="card-stat card-gradient-warning stagger-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="stat-label mb-2">Reservas no Mês</p>
                            <h2 class="stat-value">{{ $totalReservasMes }}</h2>
                            <div class="stat-change">
                                Na plataforma
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-calendar-check fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Métricas Adicionais -->
    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="dashboard-card hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="widget-icon bg-success bg-opacity-10 text-success me-3">
                            <i class="bi bi-check-circle fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Usuários Ativos</h6>
                            <h4 class="mb-0">{{ $usuariosAtivos }}</h4>
                            <small class="text-muted">{{ $usuariosInativos }} inativos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="dashboard-card hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="widget-icon bg-primary bg-opacity-10 text-primary me-3">
                            <i class="bi bi-buildings fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Condomínios Ativos</h6>
                            <h4 class="mb-0">{{ $condominiosAtivos }}</h4>
                            <small class="text-muted">{{ $condominiosInativos }} inativos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="dashboard-card hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="widget-icon bg-info bg-opacity-10 text-info me-3">
                            <i class="bi bi-cash-stack fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Volume Financeiro</h6>
                            <h4 class="mb-0">R$ {{ number_format($volumeFinanceiroMes / 1000, 1) }}K</h4>
                            <small class="text-muted">Este mês</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabelas e Gráficos -->
    <div class="row g-4">
        <!-- Condomínios Recentes -->
        <div class="col-xl-8">
            <div class="dashboard-card">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="section-title mb-0">
                            <i class="bi bi-building text-primary"></i> Condomínios na Plataforma
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

        <!-- Sidebar com Informações -->
        <div class="col-xl-4">
            <!-- Top Condomínios -->
            <div class="dashboard-card mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-trophy text-warning"></i> Top Condomínios
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

            <!-- Usuários por Perfil -->
            <div class="dashboard-card">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-person-badge text-info"></i> Usuários por Perfil
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($usuariosPorPerfil as $perfil => $quantidade)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="flex-grow-1">
                            <strong>{{ $perfil }}</strong>
                            <div class="progress-modern mt-2">
                                <div class="progress-bar" style="width: {{ $totalUsuarios > 0 ? ($quantidade / $totalUsuarios) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                        <div class="ms-3">
                            <span class="badge bg-light text-dark">{{ $quantidade }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <p class="mb-0">Nenhum usuário</p>
                    </div>
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

    <!-- Ações Rápidas -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="dashboard-card">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-lightning-charge text-warning"></i> Ações Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @can('view_users')
                        <div class="col-md-4 col-sm-6">
                            <a href="{{ route('users.index') }}" class="widget-quick-action">
                                <div class="widget-icon bg-primary bg-opacity-10 text-primary">
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
                                <div class="widget-icon bg-success bg-opacity-10 text-success">
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
                                <div class="widget-icon bg-info bg-opacity-10 text-info">
                                    <i class="bi bi-graph-up-arrow"></i>
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
@endsection
