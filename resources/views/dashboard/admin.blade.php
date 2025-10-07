@extends('layouts.app')

@section('title', 'Dashboard - Administrador')

@section('content')
<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Dashboard - Administrador da Plataforma</h2>
    </div>
</div>

<!-- KPIs -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total de Condomínios</h6>
                        <h3 class="mb-0">{{ $totalCondominios }}</h3>
                        <small class="text-muted">{{ $condominiosAtivos }} ativos</small>
                    </div>
                    <i class="bi bi-building fs-1 text-primary opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total de Usuários</h6>
                        <h3 class="mb-0">{{ $totalUsuarios }}</h3>
                        <small class="text-muted">na plataforma</small>
                    </div>
                    <i class="bi bi-people fs-1 text-success opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Condomínios Ativos</h6>
                        <h3 class="mb-0">{{ number_format(($condominiosAtivos / max($totalCondominios, 1)) * 100, 1) }}%</h3>
                        <small class="text-muted">{{ $condominiosAtivos }}/{{ $totalCondominios }}</small>
                    </div>
                    <i class="bi bi-graph-up fs-1 text-warning opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Condomínios -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Condomínios Cadastrados</h5>
                <button class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Novo Condomínio
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>CNPJ</th>
                                <th>Cidade/Estado</th>
                                <th class="text-center">Unidades</th>
                                <th class="text-center">Usuários</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($condominios as $condo)
                            <tr>
                                <td>
                                    <strong>{{ $condo->name }}</strong>
                                </td>
                                <td>{{ $condo->cnpj ?: 'N/A' }}</td>
                                <td>{{ $condo->city }}/{{ $condo->state }}</td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $condo->units_count }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success">{{ $condo->users_count }}</span>
                                </td>
                                <td class="text-center">
                                    @if($condo->is_active)
                                        <span class="badge bg-success">Ativo</span>
                                    @else
                                        <span class="badge bg-secondary">Inativo</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" title="Visualizar">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" title="Desativar">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
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
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Ações Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary">
                        <i class="bi bi-building-add"></i> Adicionar Novo Condomínio
                    </button>
                    <button class="btn btn-outline-success">
                        <i class="bi bi-person-plus"></i> Criar Usuário Administrador
                    </button>
                    <button class="btn btn-outline-info">
                        <i class="bi bi-file-earmark-text"></i> Relatório Geral
                    </button>
                    <button class="btn btn-outline-warning">
                        <i class="bi bi-gear"></i> Configurações da Plataforma
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Informações do Sistema</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i>
                        <strong>Versão:</strong> 1.0.0-alpha
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i>
                        <strong>Laravel:</strong> 12
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i>
                        <strong>PHP:</strong> {{ PHP_VERSION }}
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i>
                        <strong>Ambiente:</strong> {{ config('app.env') }}
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-shield-check text-success"></i>
                        <strong>Autenticação:</strong> Laravel Sanctum
                    </li>
                    <li class="mb-0">
                        <i class="bi bi-credit-card text-success"></i>
                        <strong>Gateway:</strong> Asaas ({{ config('services.asaas.sandbox') ? 'Sandbox' : 'Produção' }})
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info mt-4">
    <i class="bi bi-info-circle"></i>
    <strong>Bem-vindo, Administrador!</strong> Você tem acesso completo à plataforma. 
    Pode gerenciar todos os condomínios, usuários e configurações do sistema.
</div>
@endsection

