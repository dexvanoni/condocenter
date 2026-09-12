@extends('layouts.app')

@section('title', 'Editar funcionário')

@section('content')
<div class="mb-4">
    <a href="{{ route('financial.employees.show', $employee) }}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Voltar</a>
    <h2 class="mb-1 mt-2">Editar — {{ $employee->name }}</h2>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('financial.employees.update', $employee) }}">
            @csrf
            @method('PUT')
            @include('finance.employees.partials.form', ['employee' => $employee, 'statusLabels' => $statusLabels])
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Salvar alterações</button>
                <a href="{{ route('financial.employees.show', $employee) }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
