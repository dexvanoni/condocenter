@extends('layouts.app')

@section('title', 'Organizações')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <a href="{{ route('platform.dashboard') }}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Dashboard SaaS</a>
            <h1 class="mt-2 mb-1"><i class="bi bi-building-gear"></i> Organizações</h1>
            <p class="text-muted mb-0">Clientes diretos (Modelo A) e administradoras profissionais (Modelo B).</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('platform.clients.create-direct') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-person-plus"></i> Cliente direto
            </a>
            <a href="{{ route('platform.clients.create-management') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-briefcase"></i> Administradora
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('platform.organizations.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Buscar por nome, documento, e-mail ou cidade..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">Todos os tipos</option>
                        <option value="condominium" @selected(request('type') === 'condominium')>Síndico / Condomínio</option>
                        <option value="management_company" @selected(request('type') === 'management_company')>Administradora</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Todos os status</option>
                        <option value="active" @selected(request('status') === 'active')>Ativa</option>
                        <option value="suspended" @selected(request('status') === 'suspended')>Suspensa</option>
                        <option value="blocked" @selected(request('status') === 'blocked')>Bloqueada</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filtrar</button>
                    <a href="{{ route('platform.organizations.index') }}" class="btn btn-outline-secondary">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Organização</th>
                            <th>Tipo</th>
                            <th>Cidade/UF</th>
                            <th class="text-center">Condomínios</th>
                            <th class="text-center">Usuários</th>
                            <th>Status</th>
                            <th width="140">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($organizations as $organization)
                        <tr>
                            <td>
                                <a href="{{ route('platform.organizations.show', $organization) }}" class="fw-semibold text-decoration-none">
                                    {{ $organization->displayName() }}
                                </a>
                                @if($organization->document)
                                    <br><small class="text-muted">{{ $organization->document }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $organization->isManagementCompany() ? 'primary' : 'secondary' }}">
                                    {{ $organization->typeLabel() }}
                                </span>
                            </td>
                            <td>
                                @if($organization->city || $organization->state)
                                    {{ $organization->city }}{{ $organization->city && $organization->state ? ' / ' : '' }}{{ $organization->state }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-center">{{ $organization->condominiums_count }}</td>
                            <td class="text-center">{{ $organization->users_count }}</td>
                            <td>
                                @php
                                    $statusClass = match($organization->status) {
                                        'suspended' => 'warning text-dark',
                                        'blocked' => 'danger',
                                        default => 'success',
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusClass }}">{{ $organization->statusLabel() }}</span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('platform.organizations.show', $organization) }}" class="btn btn-sm btn-outline-primary" title="Ver">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('platform.organizations.edit', $organization) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-building fs-1 d-block mb-2"></i>
                                Nenhuma organização encontrada.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($organizations->hasPages())
        <div class="card-footer">
            {{ $organizations->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
