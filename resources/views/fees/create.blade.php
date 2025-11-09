@extends('layouts.app')

@section('title', 'Nova Taxa')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0">Cadastrar Nova Taxa</h2>
                <p class="text-muted mb-0">Defina os parâmetros da taxa e selecione as unidades participantes.</p>
            </div>
            <a href="{{ route('fees.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>

<form action="{{ route('fees.store') }}" method="POST" id="fee-form">
    @csrf
    <div class="card shadow-sm">
        <div class="card-body">
            @include('fees._form')
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('fees.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar Taxa</button>
        </div>
    </div>
</form>
@endsection

