@extends('layouts.app')

@section('title', 'Editar Regimento Interno')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold text-primary mb-1">
                        <i class="bi bi-pencil me-2"></i>Editar Regimento Interno
                    </h2>
                    <p class="text-muted mb-0">Versão atual: {{ $regulation->version }}</p>
                </div>
                <a href="{{ route('internal-regulations.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> <strong>Atenção:</strong> Ao salvar, uma nova versão será criada automaticamente e a versão anterior será arquivada no histórico.
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('internal-regulations.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="assembly_date" class="form-label">Data da Assembleia de Aprovação</label>
                                <input type="date" class="form-control @error('assembly_date') is-invalid @enderror" 
                                       id="assembly_date" name="assembly_date" 
                                       value="{{ old('assembly_date', $regulation->assembly_date?->format('Y-m-d')) }}">
                                @error('assembly_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="assembly_details" class="form-label">Detalhes da Assembleia</label>
                                <input type="text" class="form-control @error('assembly_details') is-invalid @enderror" 
                                       id="assembly_details" name="assembly_details" 
                                       value="{{ old('assembly_details', $regulation->assembly_details) }}"
                                       placeholder="Ex: Assembleia Geral Ordinária 2024">
                                @error('assembly_details')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="changes_summary" class="form-label">Resumo das Alterações</label>
                            <input type="text" class="form-control @error('changes_summary') is-invalid @enderror" 
                                   id="changes_summary" name="changes_summary" value="{{ old('changes_summary') }}"
                                   placeholder="Ex: Atualização das regras de uso do salão de festas">
                            @error('changes_summary')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Descreva brevemente as mudanças realizadas para facilitar o rastreamento</div>
                        </div>

                        <div class="mb-4">
                            <label for="content" class="form-label">Conteúdo do Regimento Interno <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" name="content" rows="25" required>{{ old('content', $regulation->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('internal-regulations.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning text-white">
                                <i class="bi bi-save"></i> Salvar Alterações (Nova Versão: {{ $regulation->version + 1 }})
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
textarea#content {
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
    line-height: 1.6;
}
</style>
@endsection

