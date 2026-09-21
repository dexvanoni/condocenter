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

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <strong>Corrija os campos abaixo:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white py-3">
        <h5 class="mb-0"><i class="bi bi-building-add"></i> Dados do condomínio</h5>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('condominiums.store') }}">
            @csrf
            @include('condominiums.partials.form')
            <div class="mt-4 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Cadastrar condomínio
                </button>
                <a href="{{ route('condominiums.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
