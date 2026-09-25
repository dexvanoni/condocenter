@extends('organization.layout')

@section('title', 'Novo condomínio')

@section('org_content')
    <div class="org-hero">
        <a href="{{ route('organization.dashboard') }}" class="org-hero-back d-inline-block mb-2">
            <i class="bi bi-arrow-left"></i> Voltar ao painel
        </a>
        <h1 class="d-flex align-items-center gap-2">
            <i class="bi bi-building-add"></i> Novo condomínio
        </h1>
        <p class="org-hero-subtitle">
            Cadastre um condomínio na carteira de <strong>{{ $organization->displayName() }}</strong> e informe quem será o síndico.
        </p>
    </div>

    @include('organization.partials.quota-cards', ['quota' => $snapshot])

    @if(!$canCreate)
        <div class="alert alert-warning border-0 shadow-sm">
            <i class="bi bi-exclamation-triangle me-2"></i>
            O contrato não permite cadastrar outro condomínio. Revise o limite no menu <strong>Contrato</strong> ou com o suporte SindCON.
        </div>
    @else
        <form method="POST" action="{{ route('organization.condominiums.store') }}" class="card org-section-card">
            @csrf
            <div class="card-header">
                <i class="bi bi-card-checklist text-success me-2"></i>Dados do condomínio
            </div>
            <div class="card-body">
                @include('condominiums.partials.form')

                <hr class="my-4">

                <div class="org-form-section-title">
                    <i class="bi bi-sliders"></i> Cota de unidades
                </div>
                <p class="text-muted small mb-3">Define quantas unidades este condomínio poderá cadastrar, dentro do teto do seu contrato.</p>
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Limite de unidades deste condomínio *</label>
                        @php
                            $unitsRemaining = $snapshot['units_remaining_allocation'];
                            $unitsMax = $unitsRemaining !== null ? max(1, $unitsRemaining) : 50000;
                        @endphp
                        <input type="number" name="units_limit" class="form-control" min="1"
                               max="{{ $unitsMax }}"
                               value="{{ old('units_limit') }}" required
                               @if($unitsRemaining === 0) disabled @endif>
                        <div class="form-text">
                            @if($snapshot['max_units'] === null)
                                Informe quantas unidades este condomínio poderá cadastrar.
                            @elseif($unitsRemaining === 0)
                                <span class="text-danger">Não há cota disponível. Ajuste os limites dos outros condomínios ou o contrato.</span>
                            @else
                                Máximo neste cadastro: <strong>{{ number_format($unitsRemaining, 0, ',', '.') }}</strong> unidades.
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Modo financeiro *</label>
                        <select name="financial_mode" class="form-select" required>
                            <option value="full" @selected(old('financial_mode', 'full') === 'full')>Completo</option>
                            <option value="simplified" @selected(old('financial_mode') === 'simplified')>Simplificado</option>
                        </select>
                    </div>
                </div>

                <hr class="my-4">

                <div class="org-form-section-title">
                    <i class="bi bi-person-badge"></i> Síndico do condomínio
                </div>
                <p class="text-muted small mb-3">
                    A administradora não vira síndica. O síndico informado recebe e-mail para criar a senha e acessar o condomínio.
                </p>
                <div class="row g-3">
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
                        <input type="text" name="syndic_phone" class="form-control" value="{{ old('syndic_phone') }}" placeholder="Opcional">
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <a href="{{ route('organization.dashboard') }}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary" @if(($snapshot['units_remaining_allocation'] ?? 1) === 0) disabled @endif>
                    <i class="bi bi-check2-circle"></i> Cadastrar condomínio
                </button>
            </div>
        </form>
    @endif
@endsection
