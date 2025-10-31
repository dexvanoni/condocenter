@extends('layouts.app')

@section('title', 'Versão Anterior - Regimento Interno')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold text-warning mb-1">
                        <i class="bi bi-archive me-2"></i>Versão Anterior - v{{ $history->version }}
                    </h2>
                    <p class="text-muted mb-0">
                        Arquivada em {{ $history->formatted_changed_at }}
                        @if($history->assembly_date)
                            • Aprovada em {{ $history->formatted_assembly_date }}
                        @endif
                    </p>
                </div>
                
                <div>
                    <a href="{{ route('internal-regulations.history') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar ao Histórico
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> <strong>Atenção:</strong> Esta é uma versão anterior do regimento interno, mantida apenas para fins de histórico. 
                <a href="{{ route('internal-regulations.index') }}" class="alert-link">Clique aqui para ver a versão atual</a>.
            </div>

            <div class="card shadow-sm border-warning">
                <div class="card-body p-4">
                    <!-- Metadados -->
                    <div class="alert alert-light border mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <strong><i class="bi bi-bookmark text-warning"></i> Versão:</strong><br>
                                <span class="badge bg-secondary fs-6">v{{ $history->version }}</span>
                            </div>

                            @if($history->assembly_date)
                            <div class="col-md-3">
                                <strong><i class="bi bi-calendar-check text-warning"></i> Data de Aprovação:</strong><br>
                                {{ $history->formatted_assembly_date }}
                            </div>
                            @endif
                            
                            @if($history->assembly_details)
                            <div class="col-md-3">
                                <strong><i class="bi bi-info-circle text-warning"></i> Detalhes:</strong><br>
                                {{ $history->assembly_details }}
                            </div>
                            @endif
                            
                            <div class="col-md-3">
                                <strong><i class="bi bi-person text-warning"></i> Modificado por:</strong><br>
                                {{ $history->updatedBy->name ?? 'N/A' }}
                            </div>
                        </div>

                        @if($history->changes_summary)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <strong><i class="bi bi-pencil text-warning"></i> Resumo das Alterações:</strong><br>
                                {{ $history->changes_summary }}
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Conteúdo do regimento -->
                    <div class="regulation-content">
                        <div style="white-space: pre-wrap; line-height: 1.8; font-size: 1rem; opacity: 0.9;">{{ $history->content }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.regulation-content {
    font-family: 'Georgia', serif;
    color: #333;
    text-align: justify;
}

.regulation-content h1, 
.regulation-content h2, 
.regulation-content h3 {
    color: #856404;
    margin-top: 1.5rem;
    margin-bottom: 1rem;
}

.card {
    border-radius: 10px;
}
</style>
@endsection

