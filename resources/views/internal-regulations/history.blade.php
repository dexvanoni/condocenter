@extends('layouts.app')

@section('title', 'Histórico de Alterações')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold text-primary mb-1">
                        <i class="bi bi-clock-history me-2"></i>Histórico de Alterações
                    </h2>
                    <p class="text-muted mb-0">Todas as versões do regimento interno</p>
                </div>
                <a href="{{ route('internal-regulations.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    @if($history->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="10%">Versão</th>
                                    <th width="15%">Data da Alteração</th>
                                    <th width="15%">Modificado por</th>
                                    <th width="15%">Data Assembleia</th>
                                    <th width="25%">Resumo das Alterações</th>
                                    <th width="20%">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Versão Atual -->
                                <tr class="table-success">
                                    <td>
                                        <span class="badge bg-success fs-6">
                                            <i class="bi bi-check-circle"></i> v{{ $regulation->version }}
                                        </span>
                                        <br><small class="text-muted">Atual</small>
                                    </td>
                                    <td>{{ $regulation->updated_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $regulation->updatedBy->name ?? 'N/A' }}</td>
                                    <td>{{ $regulation->formatted_assembly_date ?? 'N/A' }}</td>
                                    <td><em class="text-muted">Versão em vigor</em></td>
                                    <td>
                                        <a href="{{ route('internal-regulations.index') }}" class="btn btn-sm btn-success">
                                            <i class="bi bi-eye"></i> Ver Atual
                                        </a>
                                    </td>
                                </tr>

                                <!-- Versões Anteriores -->
                                @foreach($history as $item)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">v{{ $item->version }}</span>
                                    </td>
                                    <td>{{ $item->formatted_changed_at }}</td>
                                    <td>{{ $item->updatedBy->name ?? 'N/A' }}</td>
                                    <td>{{ $item->formatted_assembly_date ?? 'N/A' }}</td>
                                    <td>{{ $item->changes_summary ?? 'Sem resumo' }}</td>
                                    <td>
                                        <a href="{{ route('internal-regulations.show-history', $item->id) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Visualizar
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle fs-1 d-block mb-3"></i>
                        <h5>Nenhuma alteração registrada</h5>
                        <p class="mb-0">Este é o regimento original, ainda não foram realizadas alterações.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

