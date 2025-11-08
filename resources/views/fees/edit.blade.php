@extends('layouts.app')

@section('title', 'Editar Taxa')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0">Editar Taxa: {{ $fee->name }}</h2>
                <p class="text-muted mb-0">Atualize valores, recorrência e unidades vinculadas.</p>
            </div>
            <a href="{{ route('fees.show', $fee) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>

<form action="{{ route('fees.update', $fee) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="card shadow-sm">
        <div class="card-body">
            @include('fees._form')
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('fees.show', $fee) }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        </div>
    </div>
</form>

<form action="{{ route('fees.destroy', $fee) }}" method="POST"
      class="mt-3"
      onsubmit="return confirm('Deseja realmente remover esta taxa? As cobranças existentes serão canceladas.');">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-outline-danger">
        <i class="bi bi-trash"></i> Remover Taxa
    </button>
</form>
@endsection

