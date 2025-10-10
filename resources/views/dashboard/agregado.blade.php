@extends('layouts.app')

@section('title', 'Dashboard - Agregado')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">
                <i class="bi bi-house-heart text-primary"></i> 
                Dashboard - Agregado
            </h1>
            <p class="text-muted mb-0">
                Olá, <strong>{{ Auth::user()->name }}</strong>! 
                @if($moradorResponsavel)
                    Vinculado ao morador <strong>{{ $moradorResponsavel->name }}</strong>
                @endif
            </p>
        </div>
        <div class="text-end">
            <small class="text-muted">
                <i class="bi bi-calendar3"></i> {{ now()->format('d/m/Y') }}
            </small>
        </div>
    </div>

    <!-- Informações do Perfil -->
    <div class="alert alert-info border-0 mb-4" style="background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle-fill fs-4 me-3"></i>
            <div>
                <h6 class="alert-heading mb-1">Perfil Agregado</h6>
                <p class="mb-0">
                    Como agregado, você tem acesso limitado ao sistema. 
                    Suas informações estão vinculadas ao morador responsável.
                </p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Morador Responsável -->
        @if($moradorResponsavel)
        <div class="col-lg-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge"></i> Morador Responsável
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        @if($moradorResponsavel->photo)
                            <img src="{{ Storage::url($moradorResponsavel->photo) }}" 
                                 alt="{{ $moradorResponsavel->name }}" 
                                 class="rounded-circle me-3" width="60" height="60">
                        @else
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 60px; height: 60px;">
                                <i class="bi bi-person fs-3 text-muted"></i>
                            </div>
                        @endif
                        <div>
                            <h6 class="mb-0">{{ $moradorResponsavel->name }}</h6>
                            <small class="text-muted">{{ $moradorResponsavel->email }}</small>
                        </div>
                    </div>
                    
                    @if($moradorResponsavel->unit)
                    <div class="row g-2">
                        <div class="col-6">
                            <small class="text-muted d-block">Unidade</small>
                            <strong>{{ $moradorResponsavel->unit->full_identifier }}</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Telefone</small>
                            <strong>{{ $moradorResponsavel->phone ?? '-' }}</strong>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Encomendas -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-box-seam"></i> Encomendas Pendentes
                        @if($encomendas->count() > 0)
                            <span class="badge bg-light text-success ms-2">{{ $encomendas->count() }}</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if($encomendas->count() > 0)
                        @foreach($encomendas as $encomenda)
                        <div class="d-flex align-items-center mb-3 p-2 rounded" style="background: #f8f9fa;">
                            <i class="bi bi-box text-success me-3"></i>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $encomenda->description ?? 'Encomenda' }}</h6>
                                <small class="text-muted">
                                    @if($encomenda->sender)
                                        De: {{ $encomenda->sender }}
                                    @endif
                                    • {{ $encomenda->received_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                            <p class="text-muted mb-0">Nenhuma encomenda pendente</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Notificações -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-bell"></i> Notificações
                        @if($notificacoes->count() > 0)
                            <span class="badge bg-light text-warning ms-2">{{ $notificacoes->count() }}</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if($notificacoes->count() > 0)
                        @foreach($notificacoes as $notificacao)
                        <div class="d-flex align-items-start mb-3 p-2 rounded" style="background: #fff3cd;">
                            <i class="bi bi-bell-fill text-warning me-3 mt-1"></i>
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
    </div>

    <!-- Funcionalidades Disponíveis -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-grid-3x3-gap"></i> Funcionalidades Disponíveis
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Espaços/Reservas -->
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <i class="bi bi-calendar-event text-primary fs-2 mb-2 d-block"></i>
                                <h6>Espaços</h6>
                                <small class="text-muted">Acesso completo</small>
                            </div>
                        </div>
                        
                        <!-- Marketplace -->
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <i class="bi bi-shop text-success fs-2 mb-2 d-block"></i>
                                <h6>Marketplace</h6>
                                <small class="text-muted">Acesso completo</small>
                            </div>
                        </div>
                        
                        <!-- Pets -->
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <i class="bi bi-heart-pulse text-danger fs-2 mb-2 d-block"></i>
                                <h6>Pets</h6>
                                <small class="text-muted">Acesso completo</small>
                            </div>
                        </div>
                        
                        <!-- Encomendas -->
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <i class="bi bi-box-seam text-warning fs-2 mb-2 d-block"></i>
                                <h6>Encomendas</h6>
                                <small class="text-muted">Apenas visualização</small>
                            </div>
                        </div>
                        
                        <!-- Mensagens -->
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <i class="bi bi-chat-dots text-info fs-2 mb-2 d-block"></i>
                                <h6>Mensagens</h6>
                                <small class="text-muted">Apenas visualização</small>
                            </div>
                        </div>
                        
                        <!-- Financeiro -->
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <i class="bi bi-cash-coin text-success fs-2 mb-2 d-block"></i>
                                <h6>Financeiro</h6>
                                <small class="text-muted">Apenas visualização</small>
                            </div>
                        </div>
                        
                        <!-- Notificações -->
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <i class="bi bi-bell text-warning fs-2 mb-2 d-block"></i>
                                <h6>Notificações</h6>
                                <small class="text-muted">Apenas visualização</small>
                            </div>
                        </div>
                        
                        <!-- Assembleias (Sem Acesso) -->
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded opacity-50">
                                <i class="bi bi-x-circle text-danger fs-2 mb-2 d-block"></i>
                                <h6>Assembleias</h6>
                                <small class="text-muted">Sem acesso</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Aviso sobre Limitações -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-warning border-0">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                    <div>
                        <h6 class="alert-heading mb-1">Acesso Limitado</h6>
                        <p class="mb-0">
                            Como agregado, você tem acesso limitado ao sistema. Você pode fazer reservas, 
                            criar anúncios no marketplace e gerenciar pets com acesso completo. 
                            Você pode visualizar mensagens, financeiro, notificações e encomendas, 
                            mas não pode participar de assembleias.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

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
