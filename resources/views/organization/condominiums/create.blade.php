@extends('layouts.app')

@section('title', 'Novo condomínio')

@section('content')
<div class="container-fluid px-4">
    <div class="mb-4">
        <a href="{{ route('organization.dashboard') }}" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> {{ $organization->displayName() }}
        </a>
        <h1 class="mt-2 mb-1">Novo condomínio</h1>
        <p class="text-muted mb-0">
            Contrato: {{ $snapshot['condominiums_used'] }}
            @if($snapshot['max_condominiums']) / {{ $snapshot['max_condominiums'] }} @endif
            condomínios
            · {{ $snapshot['units_used'] }}
            @if($snapshot['max_units']) / {{ $snapshot['max_units'] }} @endif
            unidades.
        </p>
    </div>

    @if(!$canCreate)
        <div class="alert alert-warning">O contrato não permite cadastrar outro condomínio.</div>
    @else
        <form method="POST" action="{{ route('organization.condominiums.store') }}" class="card shadow-sm">
            @csrf
            <div class="card-body">
                @include('condominiums.partials.form')
                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Limite de unidades deste condomínio *</label>
                        <input type="number" name="units_limit" class="form-control" min="1"
                               max="{{ $snapshot['max_units'] ? max(1, $snapshot['max_units'] - $snapshot['units_reserved']) : 50000 }}"
                               value="{{ old('units_limit') }}" required>
                        <div class="form-text">A soma das cotas não pode passar do limite da administradora.</div>
                    </div>
                    <div class="col-12">
                        <h6 class="mb-0">Síndico do condomínio</h6>
                        <p class="text-muted small mb-0">A administradora não vira síndica. Informe quem será o síndico. Ele recebe o e-mail para criar a senha.</p>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Nome do síndico *</label>
                        <input type="text" name="syndic_name" class="form-control" value="{{ old('syndic_name') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">E-mail do síndico *</label>
                        <input type="email" name="syndic_email" class="form-control" value="{{ old('syndic_email') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Telefone</label>
                        <input type="text" name="syndic_phone" class="form-control" value="{{ old('syndic_phone') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Modo financeiro *</label>
                        <select name="financial_mode" class="form-select" required>
                            <option value="full" @selected(old('financial_mode', 'full') === 'full')>Completo</option>
                            <option value="simplified" @selected(old('financial_mode') === 'simplified')>Simplificado</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white">
                <button class="btn btn-primary">Cadastrar condomínio</button>
            </div>
        </form>
    @endif
</div>
@endsection
