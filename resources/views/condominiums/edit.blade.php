@extends('layouts.app')

@section('title', 'Editar Condomínio')

@section('content')
<div class="mb-4">
    <a href="{{ route('condominiums.show', $condominium) }}" class="text-decoration-none">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
    <h1 class="mt-2 mb-1"><i class="bi bi-pencil-square"></i> Editar Condomínio</h1>
    <p class="text-muted mb-0">{{ $condominium->name }}</p>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('condominiums.update', $condominium) }}">
            @csrf
            @method('PUT')
            @include('condominiums.partials.form', ['condominium' => $condominium])
            <div class="mt-4 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Salvar alterações
                </button>
                <a href="{{ route('condominiums.show', $condominium) }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
