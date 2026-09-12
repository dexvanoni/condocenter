@extends('layouts.app')

@section('title', 'Novo funcionário')

@section('content')
<div class="mb-4">
    <a href="{{ route('financial.employees.index') }}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Voltar ao quadro</a>
    <h2 class="mb-1 mt-2">Novo funcionário</h2>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('financial.employees.store') }}">
            @csrf
            @include('finance.employees.partials.form', ['statusLabels' => $statusLabels])
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="{{ route('financial.employees.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
