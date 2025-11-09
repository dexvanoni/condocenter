@extends('layouts.app')

@section('title', 'Nova conta bancária')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0">Cadastrar conta bancária</h2>
                <p class="text-muted mb-0">Informe os dados da conta que será usada pelo condomínio.</p>
            </div>
            <a href="{{ route('financial.bank-accounts.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>

<form action="{{ route('financial.bank-accounts.store') }}" method="POST">
    @csrf
    <div class="card shadow-sm">
        <div class="card-body">
            @include('finance.bank-accounts._form')
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('financial.bank-accounts.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar conta</button>
        </div>
    </div>
</form>
@endsection

