@extends('layouts.app')

@section('title', 'Painel da administradora')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-briefcase"></i> {{ $organization->displayName() }}</h1>
            <p class="text-muted mb-0">
                Painel da administradora — cadastre e selecione um condomínio para operar.
                @if(!empty($quota))
                    Contrato:
                    {{ $quota['condominiums_used'] }}@if($quota['max_condominiums']) / {{ $quota['max_condominiums'] }}@endif condomínios,
                    {{ $quota['units_used'] }}@if($quota['max_units']) / {{ $quota['max_units'] }}@endif unidades.
                @endif
            </p>
        </div>
        <span class="badge bg-{{ $organization->isActive() ? 'success' : 'secondary' }} fs-6 align-self-center">
            {{ $organization->statusLabel() }}
        </span>
        @if(auth()->user()?->canUseManagementCompanyProfile())
            <a href="{{ route('organization.contract.show') }}" class="btn btn-outline-primary">Contrato SindCON</a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card shadow-sm h-100"><div class="card-body">
                <small class="text-muted">Condomínios</small>
                <h3 class="mb-0">{{ $metrics['condominiums'] }}</h3>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card shadow-sm h-100"><div class="card-body">
                <small class="text-muted">Unidades</small>
                <h3 class="mb-0">{{ $metrics['units'] }}</h3>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card shadow-sm h-100"><div class="card-body">
                <small class="text-muted">Usuários</small>
                <h3 class="mb-0">{{ $metrics['users'] }}</h3>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card shadow-sm h-100"><div class="card-body">
                <small class="text-muted">Ocorrências</small>
                <h3 class="mb-0">{{ $metrics['occurrences'] }}</h3>
            </div></div>
        </div>
        <div class="col-md-4 col-6">
            <div class="card"><div class="card-body py-2">
                <small class="text-muted">Encomendas</small>
                <strong class="d-block">{{ $metrics['packages'] }}</strong>
            </div></div>
        </div>
        <div class="col-md-4 col-6">
            <div class="card"><div class="card-body py-2">
                <small class="text-muted">Reservas</small>
                <strong class="d-block">{{ $metrics['reservations'] }}</strong>
            </div></div>
        </div>
        <div class="col-md-4 col-6">
            <div class="card"><div class="card-body py-2">
                <small class="text-muted">Assembléias</small>
                <strong class="d-block">{{ $metrics['assemblies'] }}</strong>
            </div></div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Condomínios geridos</h5>
            @can('manageCondominiums', $organization)
                <a href="{{ route('organization.condominiums.create') }}" class="btn btn-sm btn-primary">Novo condomínio</a>
            @endcan
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>Cidade/UF</th>
                            <th class="text-center">Unidades</th>
                            <th class="text-center">Usuários</th>
                            <th>Síndico</th>
                            <th>Status</th>
                            <th width="220">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($condominiums as $condo)
                        <tr>
                            <td class="fw-semibold">{{ $condo->name }}</td>
                            <td>{{ $condo->city }}{{ $condo->city && $condo->state ? ' / ' : '' }}{{ $condo->state }}</td>
                            <td class="text-center">{{ $condo->units_count }}</td>
                            <td class="text-center">{{ $condo->users_count }}</td>
                            <td>
                                @php $syndic = $condo->syndics->first(); @endphp
                                @if($syndic)
                                    <span class="fw-semibold">{{ $syndic->name }}</span>
                                    <small class="d-block text-muted">{{ $syndic->email }}</small>
                                @else
                                    <form method="POST" action="{{ route('organization.condominiums.syndic', $condo) }}" class="d-flex flex-column gap-1">
                                        @csrf
                                        <input type="text" name="syndic_name" class="form-control form-control-sm" placeholder="Nome do síndico" required>
                                        <input type="email" name="syndic_email" class="form-control form-control-sm" placeholder="E-mail" required>
                                        <button class="btn btn-sm btn-outline-primary">Vincular síndico</button>
                                    </form>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $condo->is_active ? 'success' : 'secondary' }}">
                                    {{ $condo->is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td>
                                @can('enterCondominium', $organization)
                                <form method="POST" action="{{ route('organization.condominiums.enter') }}">
                                    @csrf
                                    <input type="hidden" name="condominium_id" value="{{ $condo->id }}">
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-box-arrow-in-right"></i> Entrar
                                    </button>
                                </form>
                                @else
                                    <span class="text-muted small">Sem permissão</span>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-buildings fs-1 d-block mb-2"></i>
                                Nenhum condomínio vinculado a esta administradora.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
