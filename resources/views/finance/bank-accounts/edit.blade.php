@extends('layouts.app')

@section('title', 'Editar conta bancária')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0">Editar conta: {{ $bankAccount->name }}</h2>
                <p class="text-muted mb-0">Atualize os dados cadastrais e o saldo de referência.</p>
            </div>
            <a href="{{ route('financial.bank-accounts.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>

<form action="{{ route('financial.bank-accounts.update', $bankAccount) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="card shadow-sm">
        <div class="card-body">
            @include('finance.bank-accounts._form', ['account' => $bankAccount])
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('financial.bank-accounts.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar alterações</button>
        </div>
    </div>
</form>
@endsection

