@extends('layouts.app')

@section('title', 'Novo cliente direto')

@push('styles')
    @include('platform.clients.partials.form-page-styles')
@endpush

@section('content')
<div class="container-fluid px-4 platform-client-form-page">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <a href="{{ route('platform.organizations.index') }}" class="text-decoration-none small text-muted">
                <i class="bi bi-arrow-left"></i> Organizações
            </a>
            <h1 class="mt-2 mb-1 d-flex flex-wrap align-items-center gap-2">
                Novo cliente direto
                <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle fw-normal">Modelo A</span>
            </h1>
            <p class="text-muted mb-0">Organização, condomínio, síndico inicial e convite por e-mail para definir a senha.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('platform.clients.store-direct') }}">
        @csrf
        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card client-form-card">
                    <div class="card-body p-4 p-lg-5">
                        <div class="form-section-head">
                            <i class="bi bi-building"></i> Condomínio
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="condo_name">Nome <span class="text-danger">*</span></label>
                                <input type="text" id="condo_name" name="condo_name"
                                       class="form-control @error('condo_name') is-invalid @enderror"
                                       value="{{ old('condo_name') }}" required autofocus>
                                @error('condo_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="condo_cnpj">CNPJ</label>
                                <input type="text" id="condo_cnpj" name="condo_cnpj" class="form-control"
                                       value="{{ old('condo_cnpj') }}" placeholder="00.000.000/0000-00">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="directUnitsLimit">Limite de unidades</label>
                                <input type="number" min="1" max="50000" name="units_limit" id="directUnitsLimit"
                                       class="form-control @error('units_limit') is-invalid @enderror"
                                       value="{{ old('units_limit') }}" placeholder="Opcional">
                                @error('units_limit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="financial_mode">Modo financeiro</label>
                                <select name="financial_mode" id="financial_mode" class="form-select">
                                    <option value="simplified" @selected(old('financial_mode', 'simplified') === 'simplified')>Simplificado</option>
                                    <option value="full" @selected(old('financial_mode') === 'full')>Completo</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="condo_email">E-mail do condomínio</label>
                                <input type="email" id="condo_email" name="condo_email" class="form-control"
                                       value="{{ old('condo_email') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="condo_phone">Telefone</label>
                                <input type="text" id="condo_phone" name="condo_phone" class="form-control"
                                       value="{{ old('condo_phone') }}">
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-12">
                                <label class="form-label" for="condo_address">Endereço</label>
                                <input type="text" id="condo_address" name="condo_address" class="form-control"
                                       value="{{ old('condo_address') }}" placeholder="Rua, número">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label" for="condo_neighborhood">Bairro</label>
                                <input type="text" id="condo_neighborhood" name="condo_neighborhood" class="form-control"
                                       value="{{ old('condo_neighborhood') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="condo_city">Cidade</label>
                                <input type="text" id="condo_city" name="condo_city" class="form-control"
                                       value="{{ old('condo_city') }}">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label" for="condo_state">UF</label>
                                <input type="text" id="condo_state" name="condo_state" class="form-control text-uppercase"
                                       maxlength="2" value="{{ old('condo_state') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="condo_zip_code">CEP</label>
                                <input type="text" id="condo_zip_code" name="condo_zip_code" class="form-control"
                                       value="{{ old('condo_zip_code') }}">
                            </div>
                        </div>
                        <p class="form-text small text-muted mb-0 mt-2">
                            O limite de unidades é o teto que o síndico poderá cadastrar. Ao escolher um plano na lateral, o valor pode ser preenchido automaticamente.
                        </p>

                        <hr class="section-divider">

                        <div class="form-section-head">
                            <i class="bi bi-person-badge"></i> Síndico (primeiro acesso)
                        </div>
                        <p class="text-muted small mb-3">
                            Usuário com papel Síndico neste condomínio. Recebe e-mail com link seguro para criar a senha e acessar o painel.
                        </p>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="user_name">Nome <span class="text-danger">*</span></label>
                                <input type="text" id="user_name" name="user_name"
                                       class="form-control @error('user_name') is-invalid @enderror"
                                       value="{{ old('user_name') }}" required>
                                @error('user_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-7">
                                <label class="form-label" for="user_email">E-mail <span class="text-danger">*</span></label>
                                <input type="email" id="user_email" name="user_email"
                                       class="form-control @error('user_email') is-invalid @enderror"
                                       value="{{ old('user_email') }}" required>
                                @error('user_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-5">
                                <label class="form-label" for="user_phone">Telefone</label>
                                <input type="text" id="user_phone" name="user_phone" class="form-control"
                                       value="{{ old('user_phone') }}">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top d-flex flex-wrap gap-2 justify-content-between align-items-center py-3 px-4 px-lg-5">
                        <a href="{{ route('platform.organizations.index') }}" class="btn btn-link text-secondary text-decoration-none px-0">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2-circle me-1"></i> Cadastrar cliente direto
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="client-aside client-aside-sticky p-4">
                    <h6 class="fw-semibold mb-3">Plano de assinatura</h6>
                    <label class="form-label small text-muted mb-1" for="subscription_plan_id">Opcional no cadastro</label>
                    <select name="subscription_plan_id" id="subscription_plan_id" class="form-select form-select-sm mb-2">
                        <option value="">Definir depois (rascunho)</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" data-max-units="{{ $plan->max_units }}"
                                @selected((string) old('subscription_plan_id') === (string) $plan->id)>
                                {{ $plan->name }} — {{ $plan->priceSummary() }}@if($plan->max_units) (até {{ $plan->max_units }} un.)@endif
                            </option>
                        @endforeach
                    </select>
                    @if($plans->isEmpty())
                        <p class="small text-muted mb-0">
                            Nenhum plano com público <strong>Síndico / Condomínio</strong>.
                            <a href="{{ route('platform.plans.index') }}">Criar no catálogo</a>.
                        </p>
                    @else
                        <p class="small text-muted mb-4">
                            Planos do catálogo Modelo A. O contrato pode ser refinado depois em Assinatura do condomínio.
                        </p>
                    @endif

                    <h6 class="fw-semibold mb-3 mt-2">Depois do cadastro</h6>
                    <div class="d-flex flex-column gap-3">
                        <div class="step-item">
                            <span class="step-num">1</span>
                            <span>Organização e condomínio criados e vinculados.</span>
                        </div>
                        <div class="step-item">
                            <span class="step-num">2</span>
                            <span>E-mail enviado ao síndico para definir a senha.</span>
                        </div>
                        <div class="step-item">
                            <span class="step-num">3</span>
                            <span>Ele acessa o condomínio e cadastra unidades e moradores.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const planSelect = document.getElementById('subscription_plan_id');
    const unitsLimit = document.getElementById('directUnitsLimit');
    planSelect?.addEventListener('change', () => {
        const option = planSelect.selectedOptions[0];
        if (unitsLimit && option?.dataset.maxUnits) {
            unitsLimit.value = option.dataset.maxUnits;
        }
    });
});
</script>
@endpush
