@extends('layouts.app')

@section('title', 'Criar Regimento Interno')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold text-primary mb-1">
                <i class="bi bi-plus-circle me-2"></i>Criar Regimento Interno
            </h2>
            <p class="text-muted mb-0">Cadastre o regimento interno do condomínio</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('internal-regulations.store') }}" method="POST">
                        @csrf

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="assembly_date" class="form-label">Data da Assembleia de Aprovação</label>
                                <input type="date" class="form-control @error('assembly_date') is-invalid @enderror" 
                                       id="assembly_date" name="assembly_date" value="{{ old('assembly_date') }}">
                                @error('assembly_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="assembly_details" class="form-label">Detalhes da Assembleia</label>
                                <input type="text" class="form-control @error('assembly_details') is-invalid @enderror" 
                                       id="assembly_details" name="assembly_details" value="{{ old('assembly_details') }}"
                                       placeholder="Ex: Assembleia Geral Ordinária 2024">
                                @error('assembly_details')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="content" class="form-label">Conteúdo do Regimento Interno <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" name="content" rows="25" required>{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Cole aqui o texto completo do regimento interno</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('internal-regulations.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Voltar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Salvar Regimento
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

