@extends('layouts.app')

@section('title', 'Condomínios')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="mb-1"><i class="bi bi-buildings"></i> Condomínios</h1>
        <p class="text-muted mb-0">Gerencie todos os tenants da plataforma.</p>
    </div>
    @can('create', App\Models\Condominium::class)
    <a href="{{ route('condominiums.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Novo Condomínio
    </a>
    @endcan
</div>

@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show">
        <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('condominiums.index') }}" class="row g-3">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Buscar por nome, cidade, CNPJ ou código..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Todos os status</option>
                    <option value="active" @selected(request('status') === 'active')>Ativos</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inativos</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filtrar</button>
                <a href="{{ route('condominiums.index') }}" class="btn btn-outline-secondary">Limpar</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Cidade/UF</th>
                        <th>Código autocadastro</th>
                        <th class="text-center">Unidades</th>
                        <th class="text-center">Usuários</th>
                        <th>Financeiro</th>
                        <th>Assinatura SaaS</th>
                        <th>Status</th>
                        <th width="180">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($condominiums as $condominium)
                    <tr>
                        <td>
                            <a href="{{ route('condominiums.show', $condominium) }}" class="fw-semibold text-decoration-none">
                                {{ $condominium->name }}
                            </a>
                            @if($condominium->cnpj)
                                <br><small class="text-muted">{{ $condominium->cnpj }}</small>
                            @endif
                        </td>
                        <td>{{ $condominium->city }} / {{ $condominium->state }}</td>
                        <td><code>{{ $condominium->registration_code ?? '—' }}</code></td>
                        <td class="text-center">{{ $condominium->units_count }}</td>
                        <td class="text-center">{{ $condominium->users_count }}</td>
                        <td>
                            <span class="badge bg-{{ $condominium->financial_mode === 'simplified' ? 'info text-dark' : 'secondary' }}">
                                {{ $condominium->financial_mode_label }}
                            </span>
                        </td>
                        <td>
                            @if($condominium->subscription)
                                @php $ss = $condominium->subscription->status; @endphp
                                <span class="badge bg-{{ in_array($ss, ['active','trial']) ? 'success' : (in_array($ss, ['past_due']) ? 'warning text-dark' : 'secondary') }}">
                                    {{ $condominium->subscription->statusLabel() }}
                                </span>
                            @else
                                <span class="badge bg-light text-muted border">Sem contrato</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $condominium->is_active ? 'success' : 'secondary' }}">
                                {{ $condominium->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button"
                                        class="btn btn-success"
                                        title="Selecionar condomínio"
                                        data-condominium-id="{{ $condominium->id }}">
                                    <i class="bi bi-check2-circle"></i>
                                </button>
                                <a href="{{ route('condominiums.show', $condominium) }}" class="btn btn-outline-primary" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('update', $condominium)
                                <a href="{{ route('condominiums.edit', $condominium) }}" class="btn btn-outline-secondary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan
                                @if(auth()->user()?->isAdmin())
                                <a href="{{ route('condominiums.settings.whatsapp', $condominium) }}" class="btn btn-outline-success" title="WhatsApp">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                                <a href="{{ route('condominiums.settings.receiving', $condominium) }}" class="btn btn-outline-primary" title="Recebimentos Asaas">
                                    <i class="bi bi-wallet2"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-building fs-1 d-block mb-2"></i>
                            Nenhum condomínio encontrado.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($condominiums->hasPages())
    <div class="card-footer">
        {{ $condominiums->links() }}
    </div>
    @endif
</div>
@endsection
