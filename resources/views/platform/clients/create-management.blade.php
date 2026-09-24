@extends('layouts.app')

@section('title', 'Nova administradora')

@section('content')
<div class="container-fluid px-4">
    <div class="mb-4">
        <a href="{{ route('platform.organizations.index') }}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Organizações</a>
        <h1 class="mt-2 mb-1"><i class="bi bi-briefcase"></i> Administradora (Modelo B)</h1>
        <p class="text-muted mb-0">Cria organização + proprietário e envia e-mail para definir a senha.</p>
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

    <form method="POST" action="{{ route('platform.clients.store-management') }}" class="row g-4">
        @csrf

        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light"><h5 class="mb-0">Organização</h5></div>
                <div class="card-body row g-3">
                    <div class="col-12">
                        <label class="form-label">Razão social *</label>
                        <input type="text" name="legal_name" class="form-control" value="{{ old('legal_name') }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Nome fantasia</label>
                        <input type="text" name="trade_name" class="form-control" value="{{ old('trade_name') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">CNPJ</label>
                        <input type="text" name="document" class="form-control" value="{{ old('document') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Telefone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Endereço</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Bairro</label>
                        <input type="text" name="neighborhood" class="form-control" value="{{ old('neighborhood') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cidade</label>
                        <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">UF</label>
                        <input type="text" name="state" class="form-control" maxlength="2" value="{{ old('state') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">CEP</label>
                        <input type="text" name="zip_code" class="form-control" value="{{ old('zip_code') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Observações</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light"><h5 class="mb-0">Proprietário (usuário inicial)</h5></div>
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
                        @foreach($plans ?? [] as $plan)
                            <option value="{{ $plan->id }}" @selected((string) old('subscription_plan_id') === (string) $plan->id)>
                                {{ $plan->name }} — {{ $plan->priceSummary() }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">
                        Lista os planos de <a href="{{ route('platform.plans.index') }}">Planos de assinatura</a> com público Administradora.
                        @if(($plans ?? collect())->isEmpty())
                            Nenhum plano desse público ainda. Crie um no catálogo e marque Administradora (Modelo B).
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2-circle"></i> Cadastrar administradora
            </button>
            <a href="{{ route('platform.organizations.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
