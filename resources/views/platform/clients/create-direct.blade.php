@extends('layouts.app')

@section('title', 'Novo cliente direto')

@section('content')
<div class="container-fluid px-4">
    <div class="mb-4">
        <a href="{{ route('platform.organizations.index') }}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Organizações</a>
        <h1 class="mt-2 mb-1"><i class="bi bi-person-plus"></i> Cliente direto (Modelo A)</h1>
        <p class="text-muted mb-0">Cria organização + condomínio + síndico e envia e-mail para definir a senha.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('platform.clients.store-direct') }}" class="row g-4">
        @csrf

        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light"><h5 class="mb-0">Condomínio</h5></div>
                <div class="card-body row g-3">
                    <div class="col-12">
                        <label class="form-label">Nome *</label>
                        <input type="text" name="condo_name" class="form-control" value="{{ old('condo_name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">CNPJ</label>
                        <input type="text" name="condo_cnpj" class="form-control" value="{{ old('condo_cnpj') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Limite de unidades</label>
                        <input type="number" min="1" max="50000" name="units_limit" id="directUnitsLimit" class="form-control" value="{{ old('units_limit') }}" placeholder="Sem limite">
                        <div class="form-text">Máximo que o síndico poderá cadastrar. Se escolher um plano com limite, ele entra aqui.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Modo financeiro</label>
                        <select name="financial_mode" class="form-select">
                            <option value="simplified" @selected(old('financial_mode', 'simplified') === 'simplified')>Simplificado</option>
                            <option value="full" @selected(old('financial_mode') === 'full')>Completo</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="condo_email" class="form-control" value="{{ old('condo_email') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Telefone</label>
                        <input type="text" name="condo_phone" class="form-control" value="{{ old('condo_phone') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Endereço</label>
                        <input type="text" name="condo_address" class="form-control" value="{{ old('condo_address') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Bairro</label>
                        <input type="text" name="condo_neighborhood" class="form-control" value="{{ old('condo_neighborhood') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cidade</label>
                        <input type="text" name="condo_city" class="form-control" value="{{ old('condo_city') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">UF</label>
                        <input type="text" name="condo_state" class="form-control" maxlength="2" value="{{ old('condo_state') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">CEP</label>
                        <input type="text" name="condo_zip_code" class="form-control" value="{{ old('condo_zip_code') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light"><h5 class="mb-0">Síndico (usuário inicial)</h5></div>
                <div class="card-body row g-3">
                    <div class="col-12">
                        <label class="form-label">Nome *</label>
                        <input type="text" name="user_name" class="form-control" value="{{ old('user_name') }}" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">E-mail *</label>
                        <input type="email" name="user_email" class="form-control" value="{{ old('user_email') }}" required>
                        <div class="form-text">Será enviado link para definir a senha. A senha temporária não é exibida.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Telefone</label>
                        <input type="text" name="user_phone" class="form-control" value="{{ old('user_phone') }}">
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-light"><h5 class="mb-0">Plano (opcional)</h5></div>
                <div class="card-body">
                    <label class="form-label">Plano de assinatura</label>
                    <select name="subscription_plan_id" class="form-select">
                        <option value="">Sem plano agora (rascunho depois)</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" data-max-units="{{ $plan->max_units }}" @selected((string) old('subscription_plan_id') === (string) $plan->id)>
                                {{ $plan->name }} — {{ $plan->priceSummary() }}@if($plan->max_units) — até {{ $plan->max_units }} un.@endif
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">
                        Lista os planos de <a href="{{ route('platform.plans.index') }}">Planos de assinatura</a> com público Síndico / Condomínio.
                        @if($plans->isEmpty())
                            Nenhum plano desse público ainda.
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2-circle"></i> Cadastrar cliente direto
            </button>
            <a href="{{ route('platform.organizations.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const planSelect = document.querySelector('[name="subscription_plan_id"]');
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
