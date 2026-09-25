@extends('layouts.app')

@section('title', $organization->displayName())

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <a href="{{ route('platform.organizations.index') }}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Organizações</a>
            <h1 class="mt-2 mb-1"><i class="bi bi-building"></i> {{ $organization->displayName() }}</h1>
            <p class="text-muted mb-0">
                <span class="badge bg-{{ $organization->isManagementCompany() ? 'primary' : 'secondary' }}">{{ $organization->typeLabel() }}</span>
                @php
                    $statusClass = match($organization->status) {
                        'suspended' => 'warning text-dark',
                        'blocked' => 'danger',
                        default => 'success',
                    };
                @endphp
                <span class="badge bg-{{ $statusClass }}">{{ $organization->statusLabel() }}</span>
            </p>
        </div>
        <div class="d-flex flex-wrap align-items-start gap-2">
            <a href="{{ route('platform.organizations.edit', $organization) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil"></i> Editar dados
            </a>
            <div class="card shadow-sm">
                <div class="card-body py-2">
                    <form method="POST" action="{{ route('platform.organizations.update-status', $organization) }}" class="d-flex flex-wrap align-items-center gap-2 mb-0">
                        @csrf
                        @method('PATCH')
                        <label class="form-label small text-muted mb-0">Status</label>
                        <select name="status" class="form-select form-select-sm" style="width: auto;">
                            <option value="active" @selected($organization->status === 'active')>Ativa</option>
                            <option value="suspended" @selected($organization->status === 'suspended')>Suspensa</option>
                            <option value="blocked" @selected($organization->status === 'blocked')>Bloqueada</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-outline-primary">Atualizar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Dados cadastrais</h5>
                    <a href="{{ route('platform.organizations.edit', $organization) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted">Razão social</dt>
                        <dd class="col-7">{{ $organization->legal_name }}</dd>
                        <dt class="col-5 text-muted">Nome fantasia</dt>
                        <dd class="col-7">{{ $organization->trade_name ?: '—' }}</dd>
                        <dt class="col-5 text-muted">Documento</dt>
                        <dd class="col-7">{{ $organization->document ?: '—' }}</dd>
                        <dt class="col-5 text-muted">E-mail</dt>
                        <dd class="col-7">{{ $organization->email ?: '—' }}</dd>
                        <dt class="col-5 text-muted">Telefone</dt>
                        <dd class="col-7">{{ $organization->phone ?: '—' }}</dd>
                        <dt class="col-5 text-muted">Endereço</dt>
                        <dd class="col-7">{{ $organization->address ?: '—' }}</dd>
                        <dt class="col-5 text-muted">Bairro</dt>
                        <dd class="col-7">{{ $organization->neighborhood ?: '—' }}</dd>
                        <dt class="col-5 text-muted">Cidade/UF</dt>
                        <dd class="col-7">
                            @if($organization->city || $organization->state)
                                {{ $organization->city }}{{ $organization->city && $organization->state ? ' / ' : '' }}{{ $organization->state }}
                            @else
                                —
                            @endif
                        </dd>
                        <dt class="col-5 text-muted">CEP</dt>
                        <dd class="col-7">{{ $organization->zip_code ?: '—' }}</dd>
                    </dl>
                    @if($organization->notes)
                        <hr>
                        <p class="small text-muted mb-0">{{ $organization->notes }}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light"><h5 class="mb-0">Contrato</h5></div>
                <div class="card-body">
                    @php
                        $directCondo = $organization->isDirectCondominium() ? $organization->condominiums->first() : null;
                        $contract = $organization->isManagementCompany()
                            ? $organization->subscription
                            : $directCondo?->subscription;
                    @endphp
                    @if($contract)
                        <p class="mb-1">
                            <strong>{{ $contract->plan?->name ?? 'Sem plano do catálogo' }}</strong>
                        </p>
                        <span class="badge bg-secondary mb-2">{{ $contract->statusLabel() }}</span>
                        <p class="small text-muted mb-2">
                            Recorrência: R$ {{ number_format((float) $contract->recurring_amount, 2, ',', '.') }}
                            @if($contract->asaas_subscription_id)
                                · Asaas vinculada
                            @endif
                        </p>
                    @else
                        <p class="text-muted mb-2">Nenhum plano do catálogo vinculado a este contrato.</p>
                    @endif
                    @if($directCondo)
                        <p class="small mb-2">
                            Limite de unidades:
                            <strong>{{ $directCondo->units_limit ? $directCondo->unitsQuotaSummary() : 'sem limite' }}</strong>
                        </p>
                    @endif
                    @if($organization->isManagementCompany())
                        <a href="{{ route('platform.organizations.subscription.edit', $organization) }}" class="btn btn-sm btn-primary">
                            Gerenciar contrato e cobranças
                        </a>
                    @elseif($directCondo)
                        <a href="{{ route('platform.subscriptions.edit', ['condominium' => $directCondo, 'from_organization' => $organization->id]) }}" class="btn btn-sm btn-primary">
                            Gerenciar contrato e cobranças
                        </a>
                    @else
                        <p class="small text-muted mb-0">Cadastre o condomínio desta organização para vincular o plano.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light"><h5 class="mb-0">Resumo</h5></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted">Condomínios</span>
                        <strong>{{ $organization->condominiums->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Usuários da org.</span>
                        <strong>{{ $organization->users->count() }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-light"><h5 class="mb-0">Condomínios</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nome</th>
                                    <th class="text-center">Unidades</th>
                                    <th class="text-center">Usuários</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($organization->condominiums as $condo)
                                <tr>
                                    <td class="fw-semibold">{{ $condo->name }}</td>
                                    <td class="text-center">{{ $condo->units_count }}</td>
                                    <td class="text-center">{{ $condo->users_count }}</td>
                                    <td>
                                        <span class="badge bg-{{ $condo->is_active ? 'success' : 'secondary' }}">
                                            {{ $condo->is_active ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('condominiums.show', $condo) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Nenhum condomínio vinculado.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Usuários</h5>
                    @if($organization->isManagementCompany())
                        <a href="{{ route('platform.organizations.members.create', $organization) }}" class="btn btn-sm btn-primary">Novo usuário</a>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Usuário</th>
                                    <th>Papel</th>
                                    <th>Acesso</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($organization->users as $member)
                                <tr>
                                    <td>
                                        <strong>{{ $member->name }}</strong>
                                        <small class="d-block text-muted">{{ $member->email }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ \App\Models\Organization::organizationRoleLabel($member->pivot->role) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $member->is_active ? 'success' : 'secondary' }}">
                                            {{ $member->is_active ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        @if($organization->isManagementCompany())
                                            <a href="{{ route('platform.organizations.members.edit', [$organization, $member]) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                            @if($member->is_active)
                                                <form method="POST" action="{{ route('platform.organizations.members.deactivate', [$organization, $member]) }}" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-warning">Desativar</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('platform.organizations.members.activate', [$organization, $member]) }}" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-success">Ativar</button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('platform.organizations.members.reset-password', [$organization, $member]) }}" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-secondary">Senha</button>
                                            </form>
                                            <form method="POST" action="{{ route('platform.organizations.members.destroy', [$organization, $member]) }}" class="d-inline" onsubmit="return confirm('Remover este usuário da administradora?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Excluir</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Nenhum usuário vinculado.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
