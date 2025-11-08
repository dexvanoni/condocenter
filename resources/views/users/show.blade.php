@extends('layouts.app')

@section('title', 'Detalhes do Usuário')

@push('styles')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #0a1b67 0%, #3866d2 100%);
        --success-gradient: linear-gradient(135deg, rgba(10, 27, 103, 0.9) 0%, rgba(56, 102, 210, 0.9) 100%);
        --warning-gradient: linear-gradient(135deg, rgba(10, 27, 103, 0.75) 0%, rgba(56, 102, 210, 0.75) 100%);
        --info-gradient: linear-gradient(135deg, rgba(56, 102, 210, 0.7) 0%, rgba(10, 27, 103, 0.7) 100%);
    }

    .user-profile-header {
        background: var(--primary-gradient);
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 40px rgba(10, 27, 103, 0.3);
        position: relative;
        overflow: hidden;
    }

    .user-profile-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.1;
    }

    .user-avatar-container {
        position: relative;
        width: 150px;
        height: 150px;
        margin: 0 auto 1rem;
    }

    .user-avatar {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        border: 5px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        object-fit: cover;
    }

    .user-avatar-placeholder {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        border: 5px solid rgba(255, 255, 255, 0.3);
        background: rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    }

    .premium-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        margin-bottom: 1.5rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }

    .premium-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    .premium-card-header {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        padding: 1.25rem 1.5rem;
        border-bottom: none;
        position: relative;
    }

    .premium-card-header::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--primary-gradient);
    }

    .premium-card-header h5 {
        margin: 0;
        font-weight: 700;
        color: #2d3748;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .info-item {
        padding: 1rem;
        border-radius: 12px;
        background: #f8f9fa;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .info-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }

    .info-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .info-value {
        font-size: 1rem;
        color: #2d3748;
        font-weight: 500;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        height: 100%;
    }

    .stat-card:hover {
        border-color: var(--brand-light);
        transform: scale(1.05);
        box-shadow: 0 8px 25px rgba(10, 27, 103, 0.2);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #718096;
        font-weight: 500;
    }

    .badge-role {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        border-radius: 20px;
        font-weight: 600;
        margin: 0.25rem;
        display: inline-block;
    }

    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 0.5rem;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }

    .permission-item {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
    }

    .permission-item.crud {
        border-left-color: var(--brand-light);
        background: linear-gradient(90deg, rgba(10, 27, 103, 0.08) 0%, white 100%);
    }

    .permission-item.view {
        border-left-color: var(--brand-dark);
        background: linear-gradient(90deg, rgba(56, 102, 210, 0.08) 0%, white 100%);
    }

    .permission-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .alert-special-care {
        background: linear-gradient(135deg, #0a1b67 0%, #3866d2 100%);
        border: none;
        border-radius: 12px;
        color: white;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(10, 27, 103, 0.25);
    }

    .btn-action {
        border-radius: 10px;
        padding: 0.625rem 1.25rem;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .timeline-item {
        padding-left: 2rem;
        position: relative;
        margin-bottom: 1rem;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: -1rem;
        width: 2px;
        background: #e5e7eb;
    }

    .timeline-item::after {
        content: '';
        position: absolute;
        left: -4px;
        top: 8px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--brand-light);
        border: 2px solid white;
        box-shadow: 0 2px 8px rgba(10, 27, 103, 0.4);
    }

    .linked-user-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: white;
        border-radius: 20px;
        text-decoration: none;
        color: #2d3748;
        font-weight: 500;
        transition: all 0.3s ease;
        border: 2px solid #e5e7eb;
        margin: 0.25rem;
    }

    .linked-user-badge:hover {
        border-color: var(--brand-light);
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(10, 27, 103, 0.2);
        color: var(--brand-light);
    }
</style>
@endpush

@section('content')
<!-- Header Profile -->
<div class="user-profile-header">
    <div class="row align-items-center position-relative" style="z-index: 1;">
        <div class="col-md-3 text-center">
            <div class="user-avatar-container">
                @if($user->photo)
                    <img src="{{ Storage::url($user->photo) }}" alt="{{ $user->name }}" class="user-avatar">
                @else
                    <div class="user-avatar-placeholder">
                        <i class="bi bi-person-circle" style="font-size: 80px; color: rgba(255,255,255,0.7);"></i>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-6 text-center text-md-start">
            <h1 class="text-white mb-2" style="font-weight: 700;">{{ $user->name }}</h1>
            <p class="text-white mb-3 opacity-75">
                <i class="bi bi-envelope me-2"></i>{{ $user->email }}
            </p>
            <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start">
                @foreach($user->roles as $role)
                    <span class="badge-role bg-white text-dark">
                        <i class="bi bi-shield-check"></i> {{ $role->name }}
                    </span>
                @endforeach
            </div>
        </div>
        <div class="col-md-3 text-center text-md-end mt-3 mt-md-0">
            <div class="d-flex flex-column gap-2">
                @can('viewHistory', $user)
                <a href="{{ route('users.history', $user) }}" class="btn btn-light btn-action">
                    <i class="bi bi-clock-history"></i> Histórico
                </a>
                @endcan
                @can('update', $user)
                <a href="{{ route('users.edit', $user) }}" class="btn btn-warning btn-action">
                    <i class="bi bi-pencil"></i> Editar
                </a>
                @endcan
                @can('delete', $user)
                <form action="{{ route('users.destroy', $user) }}" method="POST" 
                      onsubmit="return confirm('Tem certeza que deseja excluir este usuário?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-action w-100">
                        <i class="bi bi-trash"></i> Excluir
                    </button>
                </form>
                @endcan
            </div>
        </div>
    </div>
</div>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bi bi-house-door"></i> Início</a></li>
        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Usuários</a></li>
        <li class="breadcrumb-item active">{{ $user->name }}</li>
    </ol>
</nav>

<div class="row">
    <!-- Coluna Principal -->
    <div class="col-lg-8">
        <!-- Informações Pessoais -->
        <div class="premium-card">
            <div class="premium-card-header">
                <h5><i class="bi bi-person-vcard"></i> Informações Pessoais</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label"><i class="bi bi-person"></i> Nome Completo</div>
                            <div class="info-value">{{ $user->name }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label"><i class="bi bi-envelope"></i> Email</div>
                            <div class="info-value">{{ $user->email }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label"><i class="bi bi-card-text"></i> CPF</div>
                            <div class="info-value">{{ $user->cpf ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label"><i class="bi bi-credit-card"></i> CNH</div>
                            <div class="info-value">{{ $user->cnh ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label"><i class="bi bi-calendar-event"></i> Nascimento</div>
                            <div class="info-value">
                                {{ $user->data_nascimento?->format('d/m/Y') ?? '-' }}
                                @if($user->idade)
                                    <small class="text-muted">({{ $user->idade }} anos)</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contatos -->
        <div class="premium-card">
            <div class="premium-card-header">
                <h5><i class="bi bi-telephone"></i> Telefones de Contato</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label"><i class="bi bi-phone"></i> Principal</div>
                            <div class="info-value">{{ $user->phone ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label"><i class="bi bi-house-door"></i> Residencial</div>
                            <div class="info-value">{{ $user->telefone_residencial ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label"><i class="bi bi-phone-vibrate"></i> Celular</div>
                            <div class="info-value">{{ $user->telefone_celular ?? '-' }}</div>
                        </div>
                    </div>
                    @if($user->telefone_comercial)
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label"><i class="bi bi-briefcase"></i> Comercial</div>
                            <div class="info-value">{{ $user->telefone_comercial }}</div>
                        </div>
                    </div>
                    @endif
                    @if($user->local_trabalho)
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label"><i class="bi bi-building"></i> Local de Trabalho</div>
                            <div class="info-value">{{ $user->local_trabalho }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Vinculações -->
        <div class="premium-card">
            <div class="premium-card-header">
                <h5><i class="bi bi-link-45deg"></i> Vinculações</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label"><i class="bi bi-building"></i> Condomínio</div>
                            <div class="info-value">{{ $user->condominium->name ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label"><i class="bi bi-door-closed"></i> Unidade</div>
                            <div class="info-value">
                                @if($user->unit)
                                    @can('view_units')
                                    <a href="{{ route('units.show', $user->unit) }}" class="text-decoration-none">
                                        {{ $user->unit->full_identifier }} <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    @else
                                    {{ $user->unit->full_identifier }}
                                    @endcan
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    @if($user->moradorVinculado)
                    <div class="col-12">
                        <div class="info-item">
                            <div class="info-label"><i class="bi bi-person-check"></i> Morador Responsável</div>
                            <div class="info-value">
                                <a href="{{ route('users.show', $user->moradorVinculado) }}" class="linked-user-badge">
                                    <i class="bi bi-person-circle"></i>
                                    {{ $user->moradorVinculado->name }}
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($user->agregados->count() > 0)
                    <div class="col-12">
                        <div class="info-item">
                            <div class="info-label"><i class="bi bi-people"></i> Agregados Vinculados</div>
                            <div class="info-value">
                                @foreach($user->agregados as $agregado)
                                    <a href="{{ route('users.show', $agregado) }}" class="linked-user-badge">
                                        <i class="bi bi-person-circle"></i>
                                        {{ $agregado->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Cuidados Especiais -->
        @if($user->necessita_cuidados_especiais)
        <div class="alert-special-care">
            <h5 class="mb-3"><i class="bi bi-exclamation-triangle-fill"></i> Cuidados Especiais Necessários</h5>
            <p class="mb-0">{{ $user->descricao_cuidados_especiais }}</p>
        </div>
        @endif

        <!-- Estatísticas de Atividades -->
        <div class="premium-card">
            <div class="premium-card-header">
                <h5><i class="bi bi-graph-up"></i> Resumo de Atividades</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon" style="background: linear-gradient(135deg, #0a1b67 0%, #3866d2 100%);">
                                <i class="bi bi-calendar-check text-white"></i>
                            </div>
                            <div class="stat-value">{{ $user->reservations->count() }}</div>
                            <div class="stat-label">Reservas</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                <i class="bi bi-receipt text-white"></i>
                            </div>
                            <div class="stat-value">{{ $user->unit?->charges->count() ?? 0 }}</div>
                            <div class="stat-label">Cobranças</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                <i class="bi bi-heart text-white"></i>
                            </div>
                            <div class="stat-value">{{ $user->pets->count() }}</div>
                            <div class="stat-label">Pets</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                                <i class="bi bi-chat-dots text-white"></i>
                            </div>
                            <div class="stat-value">{{ $user->sentMessages->count() }}</div>
                            <div class="stat-label">Mensagens</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Status -->
        <div class="premium-card">
            <div class="premium-card-header">
                <h5><i class="bi bi-activity"></i> Status Atual</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3 p-3" style="background: #f8f9fa; border-radius: 10px;">
                    <span class="status-indicator" style="background: {{ $user->is_active ? '#10b981' : '#6b7280' }};"></span>
                    <strong>{{ $user->is_active ? 'Ativo' : 'Inativo' }}</strong>
                </div>
                <div class="d-flex align-items-center mb-3 p-3" style="background: #f8f9fa; border-radius: 10px;">
                    <span class="status-indicator" style="background: {{ $user->possui_dividas ? '#ef4444' : '#10b981' }};"></span>
                    <strong>{{ $user->possui_dividas ? 'Com Dívidas' : 'Sem Dívidas' }}</strong>
                </div>
                @if($user->senha_temporaria)
                <div class="d-flex align-items-center p-3" style="background: #fef3c7; border-radius: 10px;">
                    <i class="bi bi-shield-exclamation text-warning me-2"></i>
                    <strong>Senha Temporária</strong>
                </div>
                @endif
            </div>
        </div>

        <!-- Permissões Especiais (Agregados) -->
        @if($user->isAgregado())
        <div class="premium-card">
            <div class="premium-card-header" style="background: linear-gradient(135deg, #0a1b67 0%, #3866d2 100%);">
                <h5 class="text-white"><i class="bi bi-gear-fill"></i> Permissões Especiais</h5>
            </div>
            <div class="card-body p-4">
                @php
                    $agregadoPermissions = $user->getAgregadoPermissions();
                    $availablePermissions = \App\Models\AgregadoPermission::getAvailablePermissions();
                @endphp
                
                @if(count($agregadoPermissions) > 0)
                    @foreach($agregadoPermissions as $permissionKey)
                        @php
                            $userPermission = $user->agregadoPermissions()->where('permission_key', $permissionKey)->where('is_granted', true)->first();
                            $permissionLevel = $userPermission ? $userPermission->permission_level : 'view';
                        @endphp
                        @if(isset($availablePermissions[$permissionKey]))
                            <div class="permission-item {{ $permissionLevel }}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        @if($permissionLevel === 'crud')
                                            <i class="bi bi-gear-fill text-success fs-5"></i>
                                        @else
                                            <i class="bi bi-eye text-info fs-5"></i>
                                        @endif
                                        <strong>{{ $availablePermissions[$permissionKey]['name'] }}</strong>
                                    </div>
                                    @if($permissionLevel === 'crud')
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Completo
                                        </span>
                                    @else
                                        <span class="badge bg-info">
                                            <i class="bi bi-eye"></i> Visualização
                                        </span>
                                    @endif
                                </div>
                                <small class="text-muted">{{ $availablePermissions[$permissionKey]['description'] }}</small>
                            </div>
                        @endif
                    @endforeach
                    
                    @if($user->agregadoPermissions->count() > 0)
                        <div class="mt-3 pt-3 border-top">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i>
                                Concedidas por: 
                                @foreach($user->agregadoPermissions->unique('granted_by') as $permission)
                                    <strong>{{ $permission->grantedBy->name }}</strong>@if(!$loop->last), @endif
                                @endforeach
                            </small>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-shield-x" style="font-size: 3rem; color: #cbd5e1;"></i>
                        <p class="text-muted mb-0 mt-2">Nenhuma permissão especial</p>
                        <small class="text-muted">Apenas permissões básicas do perfil</small>
                    </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Linha do Tempo -->
        <div class="premium-card">
            <div class="premium-card-header">
                <h5><i class="bi bi-clock-history"></i> Linha do Tempo</h5>
            </div>
            <div class="card-body p-4">
                @if($user->data_entrada)
                <div class="timeline-item">
                    <small class="text-muted d-block mb-1">Entrada no Condomínio</small>
                    <strong>{{ $user->data_entrada->format('d/m/Y') }}</strong>
                </div>
                @endif
                
                <div class="timeline-item">
                    <small class="text-muted d-block mb-1">Cadastro no Sistema</small>
                    <strong>{{ $user->created_at->format('d/m/Y H:i') }}</strong>
                </div>
                
                <div class="timeline-item">
                    <small class="text-muted d-block mb-1">Última Atualização</small>
                    <strong>{{ $user->updated_at->format('d/m/Y H:i') }}</strong>
                </div>
                
                @if($user->data_saida)
                <div class="timeline-item">
                    <small class="text-muted d-block mb-1">Saída do Condomínio</small>
                    <strong>{{ $user->data_saida->format('d/m/Y') }}</strong>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
