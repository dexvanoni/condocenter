@extends('layouts.app')

@section('title', 'Regimento Interno')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold text-primary mb-1">
                        <i class="bi bi-journal-text me-2"></i>Regimento Interno
                    </h2>
                    <p class="text-muted mb-0">
                        @if($regulation)
                            Versão {{ $regulation->version }} 
                            @if($regulation->assembly_date)
                                • Aprovado em {{ $regulation->formatted_assembly_date }}
                            @endif
                        @else
                            Nenhum regimento cadastrado
                        @endif
                    </p>
                </div>
                
                @if($regulation)
                <div class="btn-group" role="group">
                    <a href="{{ route('internal-regulations.export-pdf') }}" class="btn btn-danger" title="Exportar PDF">
                        <i class="bi bi-file-pdf me-1"></i>PDF
                    </a>
                    <a href="{{ route('internal-regulations.print') }}" class="btn btn-secondary" target="_blank" title="Imprimir">
                        <i class="bi bi-printer me-1"></i>Imprimir
                    </a>
                    <a href="{{ route('internal-regulations.history') }}" class="btn btn-info text-white" title="Ver histórico">
                        <i class="bi bi-clock-history me-1"></i>Histórico
                    </a>
                    @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Síndico'))
                    <a href="{{ route('internal-regulations.edit') }}" class="btn btn-warning text-white" title="Editar">
                        <i class="bi bi-pencil me-1"></i>Editar
                    </a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    @if($regulation)
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <!-- Metadados -->
                    @if($regulation->assembly_details || $regulation->updated_by)
                    <div class="alert alert-light border mb-4">
                        <div class="row">
                            @if($regulation->assembly_date)
                            <div class="col-md-4">
                                <strong><i class="bi bi-calendar-check text-primary"></i> Data de Aprovação:</strong><br>
                                {{ $regulation->formatted_assembly_date }}
                            </div>
                            @endif
                            
                            @if($regulation->assembly_details)
                            <div class="col-md-4">
                                <strong><i class="bi bi-info-circle text-primary"></i> Detalhes da Assembleia:</strong><br>
                                {{ $regulation->assembly_details }}
                            </div>
                            @endif
                            
                            @if($regulation->updated_by)
                            <div class="col-md-4">
                                <strong><i class="bi bi-person text-primary"></i> Última atualização por:</strong><br>
                                {{ $regulation->updatedBy->name ?? 'N/A' }} em {{ $regulation->updated_at->format('d/m/Y H:i') }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Conteúdo do regimento -->
                    <div class="regulation-content">
                        <div style="white-space: pre-wrap; line-height: 1.8; font-size: 1rem;">{{ $regulation->content }}</div>
                    </div>
                </div>
            </div>

            <!-- Histórico de alterações recentes -->
            @if($regulation->history->count() > 0)
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-clock-history text-primary"></i> Últimas Alterações</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Versão</th>
                                    <th>Data da Alteração</th>
                                    <th>Modificado por</th>
                                    <th>Resumo</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($regulation->history->take(5) as $history)
                                <tr>
                                    <td><span class="badge bg-secondary">v{{ $history->version }}</span></td>
                                    <td>{{ $history->formatted_changed_at }}</td>
                                    <td>{{ $history->updatedBy->name ?? 'N/A' }}</td>
                                    <td>{{ $history->changes_summary ?? 'Sem resumo' }}</td>
                                    <td>
                                        <a href="{{ route('internal-regulations.show-history', $history->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($regulation->history->count() > 5)
                    <div class="text-center mt-3">
                        <a href="{{ route('internal-regulations.history') }}" class="btn btn-outline-primary">
                            Ver todo o histórico ({{ $regulation->history->count() }} versões)
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
    @else
    <div class="row">
        <div class="col-12">
            <div class="alert alert-warning text-center">
                <i class="bi bi-exclamation-triangle fs-1 d-block mb-3"></i>
                <h4>Nenhum regimento interno cadastrado</h4>
                <p class="mb-0">O condomínio ainda não possui um regimento interno cadastrado no sistema.</p>
                @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Síndico'))
                <a href="{{ route('internal-regulations.create') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-plus-circle"></i> Cadastrar Regimento Interno
                </a>
                @endif
            </div>
        </div>
    </div>
    @endif
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
    color: #0d6efd;
    margin-top: 1.5rem;
    margin-bottom: 1rem;
}

.card {
    border-radius: 10px;
}

.btn-group .btn {
    border-radius: 0;
}

.btn-group .btn:first-child {
    border-top-left-radius: 5px;
    border-bottom-left-radius: 5px;
}

.btn-group .btn:last-child {
    border-top-right-radius: 5px;
    border-bottom-right-radius: 5px;
}
</style>
@endsection

