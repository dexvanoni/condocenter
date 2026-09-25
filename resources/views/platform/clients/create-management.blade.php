@extends('layouts.app')

@section('title', 'Nova administradora')

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
                Nova administradora
                <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle fw-normal">Modelo B</span>
            </h1>
            <p class="text-muted mb-0">Organização profissional, proprietário inicial e convite por e-mail para definir a senha.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('platform.clients.store-management') }}">
        @csrf
        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card client-form-card">
                    <div class="card-body p-4 p-lg-5">
                        <div class="form-section-head">
                            <i class="bi bi-building"></i> Dados da organização
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="legal_name">Razão social <span class="text-danger">*</span></label>
                                <input type="text" id="legal_name" name="legal_name"
                                       class="form-control @error('legal_name') is-invalid @enderror"
                                       value="{{ old('legal_name') }}" required autofocus>
                                @error('legal_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="trade_name">Nome fantasia</label>
                                <input type="text" id="trade_name" name="trade_name" class="form-control"
                                       value="{{ old('trade_name') }}" placeholder="Como aparece no dia a dia">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="document">CNPJ</label>
                                <input type="text" id="document" name="document" class="form-control @error('document') is-invalid @enderror"
                                       value="{{ old('document') }}" placeholder="00.000.000/0000-00">
                                @error('document')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="email">E-mail da organização</label>
                                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="phone">Telefone</label>
                                <input type="text" id="phone" name="phone" class="form-control"
                                       value="{{ old('phone') }}">
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-12">
                                <label class="form-label" for="address">Endereço</label>
                                <input type="text" id="address" name="address" class="form-control"
                                       value="{{ old('address') }}" placeholder="Rua, número">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label" for="neighborhood">Bairro</label>
                                <input type="text" id="neighborhood" name="neighborhood" class="form-control"
                                       value="{{ old('neighborhood') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="city">Cidade</label>
                                <input type="text" id="city" name="city" class="form-control"
                                       value="{{ old('city') }}">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label" for="state">UF</label>
                                <input type="text" id="state" name="state" class="form-control text-uppercase"
                                       maxlength="2" value="{{ old('state') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="zip_code">CEP</label>
                                <input type="text" id="zip_code" name="zip_code" class="form-control"
                                       value="{{ old('zip_code') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="notes">Observações internas</label>
                                <textarea id="notes" name="notes" class="form-control" rows="2"
                                          placeholder="Opcional — anotações visíveis só na plataforma">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <hr class="section-divider">

                        <div class="form-section-head">
                            <i class="bi bi-person-badge"></i> Proprietário (primeiro acesso)
                        </div>
                        <p class="text-muted small mb-3">
                            Responsável pela conta da administradora no painel <strong>/organizacao</strong>. Recebe e-mail com link seguro para criar a senha.
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
                            <i class="bi bi-check2-circle me-1"></i> Cadastrar administradora
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
                        @foreach($plans ?? [] as $plan)
                            <option value="{{ $plan->id }}" @selected((string) old('subscription_plan_id') === (string) $plan->id)>
                                {{ $plan->name }} — {{ $plan->priceSummary() }}
                            </option>
                        @endforeach
                    </select>
                    @if(($plans ?? collect())->isEmpty())
                        <p class="small text-muted mb-0">
                            Nenhum plano com público <strong>Administradora</strong>.
                            <a href="{{ route('platform.plans.index') }}">Criar no catálogo</a>.
                        </p>
                    @else
                        <p class="small text-muted mb-4">
                            Planos do catálogo com público Administradora (Modelo B). Você pode ajustar o contrato depois na ficha da organização.
                        </p>
                    @endif

                    <h6 class="fw-semibold mb-3 mt-2">Depois do cadastro</h6>
                    <div class="d-flex flex-column gap-3">
                        <div class="step-item">
                            <span class="step-num">1</span>
                            <span>Organização criada e vinculada ao Modelo B.</span>
                        </div>
                        <div class="step-item">
                            <span class="step-num">2</span>
                            <span>E-mail enviado ao proprietário para definir a senha.</span>
                        </div>
                        <div class="step-item">
                            <span class="step-num">3</span>
                            <span>Ele acessa o painel e cadastra condomínios na carteira.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
