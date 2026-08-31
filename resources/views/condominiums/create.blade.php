@extends('layouts.app')

@section('title', 'Novo Condomínio')

@section('content')
<div class="mb-4">
    <a href="{{ route('condominiums.index') }}" class="text-decoration-none">
        <i class="bi bi-arrow-left"></i> Voltar para condomínios
    </a>
    <h1 class="mt-2 mb-1"><i class="bi bi-building-add"></i> Novo Condomínio</h1>
    <p class="text-muted mb-0">Cadastre um novo tenant na plataforma. Um código de autocadastro será gerado automaticamente.</p>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('condominiums.store') }}">
            @csrf
            @include('condominiums.partials.form')
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Cadastrar condomínio
                </button>
                <a href="{{ route('condominiums.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
