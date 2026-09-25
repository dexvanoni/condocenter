@extends('layouts.app')

@section('title', 'Editar '.$organization->displayName())

@section('content')
<div class="container-fluid px-4">
    <div class="mb-4">
        <a href="{{ route('platform.organizations.show', $organization) }}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> {{ $organization->displayName() }}</a>
        <h1 class="mt-2 mb-1"><i class="bi bi-pencil"></i> Editar organização</h1>
        <p class="text-muted mb-0">
            <span class="badge bg-{{ $organization->isManagementCompany() ? 'primary' : 'secondary' }}">{{ $organization->typeLabel() }}</span>
            Dados cadastrais da organização. O status continua na ficha.
        </p>
    </div>

    <form method="POST" action="{{ route('platform.organizations.update', $organization) }}" class="row g-4">
        @csrf
        @method('PUT')

        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light"><h5 class="mb-0">Dados cadastrais</h5></div>
                <div class="card-body row g-3">
                    <div class="col-12">
                        <label class="form-label" for="legal_name">Razão social *</label>
                        <input type="text" id="legal_name" name="legal_name" class="form-control @error('legal_name') is-invalid @enderror" value="{{ old('legal_name', $organization->legal_name) }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="trade_name">Nome fantasia</label>
                        <input type="text" id="trade_name" name="trade_name" class="form-control" value="{{ old('trade_name', $organization->trade_name) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="document">Documento</label>
                        <input type="text" id="document" name="document" class="form-control" value="{{ old('document', $organization->document) }}" maxlength="18">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="phone">Telefone</label>
                        <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $organization->phone) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="email">E-mail</label>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $organization->email) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="address">Endereço</label>
                        <input type="text" id="address" name="address" class="form-control" value="{{ old('address', $organization->address) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="neighborhood">Bairro</label>
                        <input type="text" id="neighborhood" name="neighborhood" class="form-control" value="{{ old('neighborhood', $organization->neighborhood) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="city">Cidade</label>
                        <input type="text" id="city" name="city" class="form-control" value="{{ old('city', $organization->city) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="state">UF</label>
                        <input type="text" id="state" name="state" class="form-control" maxlength="2" value="{{ old('state', $organization->state) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="zip_code">CEP</label>
                        <input type="text" id="zip_code" name="zip_code" class="form-control" value="{{ old('zip_code', $organization->zip_code) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="notes">Observações</label>
                        <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes', $organization->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2-circle"></i> Salvar alterações
            </button>
            <a href="{{ route('platform.organizations.show', $organization) }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
