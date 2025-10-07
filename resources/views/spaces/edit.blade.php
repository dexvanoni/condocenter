@extends('layouts.app')

@section('title', 'Editar Espaço')

@section('content')
<div class="row">
    <div class="col-lg-8 offset-lg-2">
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h4 class="mb-0">Editar Espaço: {{ $space->name }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('spaces.update', $space) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Nome do Espaço *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               name="name" value="{{ old('name', $space->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea class="form-control" name="description" rows="3">{{ old('description', $space->description) }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Taxa de Reserva (R$) *</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" class="form-control" name="price_per_reservation" 
                                           value="{{ old('price_per_reservation', $space->price_per_hour) }}" 
                                           step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="is_active">
                                    <option value="1" {{ $space->is_active ? 'selected' : '' }}>Ativo</option>
                                    <option value="0" {{ !$space->is_active ? 'selected' : '' }}>Inativo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Atenção:</strong> Ao alterar a taxa, as novas reservas utilizarão o novo valor. 
                        Reservas já existentes mantêm os valores originais.
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning btn-lg">
                            <i class="bi bi-check-circle"></i> Salvar Alterações
                        </button>
                        <a href="{{ route('spaces.index') }}" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

