@extends('layouts.app')

@section('title', $member ? 'Editar usuário' : 'Novo usuário')

@section('content')
<div class="container-fluid px-4">
    <div class="mb-4">
        <a href="{{ route('platform.organizations.show', $organization) }}" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> {{ $organization->displayName() }}
        </a>
        <h1 class="mt-2 mb-1">{{ $member ? 'Editar usuário' : 'Novo usuário da administradora' }}</h1>
        <p class="text-muted mb-0">Este usuário não fica vinculado a um condomínio. O acesso é o da administradora.</p>
    </div>

    <form method="POST" action="{{ $member ? route('platform.organizations.members.update', [$organization, $member]) : route('platform.organizations.members.store', $organization) }}" class="card shadow-sm">
        @csrf
        @if($member) @method('PUT') @endif
        <div class="card-body row g-3">
            <div class="col-md-6">
                <label class="form-label">Nome *</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name', $member?->name) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">E-mail *</label>
                <input type="email" name="email" class="form-control" required value="{{ old('email', $member?->email) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Telefone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $member?->phone) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Papel na administradora *</label>
                <select name="role" class="form-select" required>
                    @foreach(\App\Models\Organization::organizationRoles() as $role)
                        <option value="{{ $role }}" @selected(old('role', $member?->organizationRoleFor($organization->id)) === $role)>
                            {{ \App\Models\Organization::organizationRoleLabel($role) }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if($member)
            <div class="col-md-4 d-flex align-items-end">
                <input type="hidden" name="is_active" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="memberActive" @checked(filter_var(old('is_active', $member->is_active), FILTER_VALIDATE_BOOLEAN))>
                    <label class="form-check-label" for="memberActive">Usuário ativo</label>
                </div>
            </div>
            @endif
        </div>
        <div class="card-footer bg-white">
            <button class="btn btn-primary">{{ $member ? 'Salvar' : 'Criar e enviar acesso' }}</button>
        </div>
    </form>
</div>
@endsection
