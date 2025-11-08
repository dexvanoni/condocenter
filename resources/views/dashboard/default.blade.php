@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">
                <i class="bi bi-speedometer2 text-brand"></i> 
                Dashboard
            </h1>
            <p class="text-muted mb-0">
                Bem-vindo, <strong>{{ Auth::user()->name }}</strong>!
            </p>
        </div>
        <div class="text-end">
            <small class="text-muted">
                <i class="bi bi-calendar3"></i> {{ now()->format('d/m/Y') }}
            </small>
        </div>
    </div>

    <!-- Informações do Perfil -->
    <div class="alert alert-info border-0 mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle-fill fs-4 me-3"></i>
            <div>
                <h6 class="alert-heading mb-1">Perfil Ativo</h6>
                <p class="mb-0">
                    @foreach(Auth::user()->roles as $role)
                        <span class="badge badge-brand me-1">{{ $role->name }}</span>
                    @endforeach
                </p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Notificações -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-brand-gradient text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-bell"></i> Notificações
                        @if($notificacoes->count() > 0)
                            <span class="badge badge-brand ms-2">{{ $notificacoes->count() }}</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if($notificacoes->count() > 0)
                        @foreach($notificacoes as $notificacao)
                        <div class="d-flex align-items-start mb-3 p-2 rounded" style="background: #e3f2fd;">
                            <i class="bi bi-bell-fill text-brand me-3 mt-1"></i>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $notificacao->title ?? 'Notificação' }}</h6>
                                <p class="mb-1 small">{{ $notificacao->message ?? $notificacao->description }}</p>
                                <small class="text-muted">{{ $notificacao->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-bell-slash fs-1 text-muted mb-3 d-block"></i>
                            <p class="text-muted mb-0">Nenhuma notificação</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Informações do Usuário -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-brand-gradient text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-circle"></i> Suas Informações
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                @if(Auth::user()->photo)
                                    <img src="{{ Storage::url(Auth::user()->photo) }}" 
                                         alt="{{ Auth::user()->name }}" 
                                         class="rounded-circle me-3" width="60" height="60">
                                @else
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" 
                                         style="width: 60px; height: 60px;">
                                        <i class="bi bi-person fs-3 text-muted"></i>
                                    </div>
                                @endif
                                <div>
                                    <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                                    <small class="text-muted">{{ Auth::user()->email }}</small>
                                </div>
                            </div>
                        </div>
                        
                        @if(Auth::user()->unit)
                        <div class="col-6">
                            <small class="text-muted d-block">Unidade</small>
                            <strong>{{ Auth::user()->unit->full_identifier }}</strong>
                        </div>
                        @endif
                        
                        <div class="col-6">
                            <small class="text-muted d-block">Telefone</small>
                            <strong>{{ Auth::user()->phone ?? '-' }}</strong>
                        </div>
                        
                        @if(Auth::user()->data_entrada)
                        <div class="col-6">
                            <small class="text-muted d-block">Data de Entrada</small>
                            <strong>{{ Auth::user()->data_entrada->format('d/m/Y') }}</strong>
                        </div>
                        @endif
                        
                        <div class="col-6">
                            <small class="text-muted d-block">Status</small>
                            @if(Auth::user()->is_active)
                                <span class="badge badge-brand">Ativo</span>
                            @else
                                <span class="badge bg-secondary">Inativo</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leitor de QR Code de Pets -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-gradient" style="background: linear-gradient(135deg, #0a1b67 0%, #3866d2 100%);">
                <div class="card-body text-center py-4">
                    <h4 class="text-white mb-3">
                        <i class="bi bi-qr-code-scan"></i> Encontrou um Pet Perdido?
                    </h4>
                    <p class="text-white mb-3">Escaneie o QR Code da coleira para ver as informações e contatar o dono</p>
                    <button type="button" class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#petQrReaderModal">
                        <i class="bi bi-camera"></i> Escanear QR Code
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Acesso ao Sistema -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-grid-3x3-gap"></i> Módulos Disponíveis
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @can('view_spaces')
                        <div class="col-md-3">
                            <a href="{{ route('spaces.index') }}" class="text-decoration-none">
                                <div class="text-center p-3 border rounded h-100 hover-shadow">
                                    <i class="bi bi-building text-brand fs-2 mb-2 d-block"></i>
                                    <h6>Espaços</h6>
                                    <small class="text-muted">Áreas comuns</small>
                                </div>
                            </a>
                        </div>
                        @endcan
                        
                        @can('view_marketplace')
                        <div class="col-md-3">
                            <a href="{{ route('marketplace.index') }}" class="text-decoration-none">
                                <div class="text-center p-3 border rounded h-100 hover-shadow">
                                    <i class="bi bi-shop text-brand fs-2 mb-2 d-block"></i>
                                    <h6>Marketplace</h6>
                                    <small class="text-muted">Compras e vendas</small>
                                </div>
                            </a>
                        </div>
                        @endcan
                        
                        @can('view_pets')
                        <div class="col-md-3">
                            <a href="{{ route('pets.index') }}" class="text-decoration-none">
                                <div class="text-center p-3 border rounded h-100 hover-shadow">
                                    <i class="bi bi-heart text-brand fs-2 mb-2 d-block"></i>
                                    <h6>Pets</h6>
                                    <small class="text-muted">Animais de estimação</small>
                                </div>
                            </a>
                        </div>
                        @endcan
                        
                        @can('view_assemblies')
                        <div class="col-md-3">
                            <a href="{{ route('assemblies.index') }}" class="text-decoration-none">
                                <div class="text-center p-3 border rounded h-100 hover-shadow">
                                    <i class="bi bi-people text-brand fs-2 mb-2 d-block"></i>
                                    <h6>Assembleias</h6>
                                    <small class="text-muted">Reuniões e votações</small>
                                </div>
                            </a>
                        </div>
                        @endcan
                        
                        @can('view_notifications')
                        <div class="col-md-3">
                            <a href="{{ route('notifications.index') }}" class="text-decoration-none">
                                <div class="text-center p-3 border rounded h-100 hover-shadow">
                                    <i class="bi bi-bell text-brand fs-2 mb-2 d-block"></i>
                                    <h6>Notificações</h6>
                                    <small class="text-muted">Avisos e comunicados</small>
                                </div>
                            </a>
                        </div>
                        @endcan
                        
                        @can('manage_users')
                        <div class="col-md-3">
                            <a href="{{ route('users.index') }}" class="text-decoration-none">
                                <div class="text-center p-3 border rounded h-100 hover-shadow">
                                    <i class="bi bi-people-fill text-secondary fs-2 mb-2 d-block"></i>
                                    <h6>Usuários</h6>
                                    <small class="text-muted">Gestão de moradores</small>
                                </div>
                            </a>
                        </div>
                        @endcan
                        
                        @can('manage_units')
                        <div class="col-md-3">
                            <a href="{{ route('units.index') }}" class="text-decoration-none">
                                <div class="text-center p-3 border rounded h-100 hover-shadow">
                                    <i class="bi bi-house fs-2 mb-2 d-block" style="color: #6f42c1;"></i>
                                    <h6>Unidades</h6>
                                    <small class="text-muted">Apartamentos e casas</small>
                                </div>
                            </a>
                        </div>
                        @endcan
                        
                        @can('view_financial')
                        <div class="col-md-3">
                            <a href="{{ route('transactions.index') }}" class="text-decoration-none">
                                <div class="text-center p-3 border rounded h-100 hover-shadow">
                                    <i class="bi bi-cash-coin fs-2 mb-2 d-block" style="color: #20c997;"></i>
                                    <h6>Financeiro</h6>
                                    <small class="text-muted">Receitas e despesas</small>
                                </div>
                            </a>
                        </div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.hover-shadow:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
    transform: translateY(-2px);
    transition: all 0.3s ease;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh das notificações a cada 30 segundos
    setInterval(function() {
        // Implementar refresh via AJAX se necessário
    }, 30000);
});
</script>
@endpush

<!-- Incluir componente do leitor de QR Code -->
@include('components.pet-qr-reader')

@endsection
