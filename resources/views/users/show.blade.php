@extends('layouts.app')

@section('title', 'Detalhes do Usuário')

@section('content')
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-person-circle"></i> {{ $user->name }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Usuários</a></li>
                    <li class="breadcrumb-item active">{{ $user->name }}</li>
                </ol>
            </nav>
        </div>
        <div>
            @can('viewHistory', $user)
            <a href="{{ route('users.history', $user) }}" class="btn btn-info">
                <i class="bi bi-clock-history"></i> Histórico
            </a>
            @endcan
            @can('update', $user)
            <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Editar
            </a>
            @endcan
            @can('delete', $user)
            <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" 
                  onsubmit="return confirm('Tem certeza que deseja excluir este usuário?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Excluir
                </button>
            </form>
            @endcan
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Informações Pessoais -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informações Pessoais</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Nome Completo:</strong><br>
                        {{ $user->name }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Email:</strong><br>
                        {{ $user->email }}
                    </div>
                    <div class="col-md-4 mb-3">
                        <strong>CPF:</strong><br>
                        {{ $user->cpf ?? '-' }}
                    </div>
                    <div class="col-md-4 mb-3">
                        <strong>CNH:</strong><br>
                        {{ $user->cnh ?? '-' }}
                    </div>
                    <div class="col-md-4 mb-3">
                        <strong>Data Nascimento:</strong><br>
                        {{ $user->data_nascimento?->format('d/m/Y') ?? '-' }}
                        @if($user->idade)
                            <small class="text-muted">({{ $user->idade }} anos)</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Telefones -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Telefones</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <strong>Principal:</strong><br>
                        {{ $user->phone ?? '-' }}
                    </div>
                    <div class="col-md-4 mb-2">
                        <strong>Residencial:</strong><br>
                        {{ $user->telefone_residencial ?? '-' }}
                    </div>
                    <div class="col-md-4 mb-2">
                        <strong>Celular:</strong><br>
                        {{ $user->telefone_celular ?? '-' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Vinculação -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Vinculação</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Condomínio:</strong><br>
                        {{ $user->condominium->name ?? '-' }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Unidade:</strong><br>
                        @if($user->unit)
                            @can('view_units')
                            <a href="{{ route('units.show', $user->unit) }}">{{ $user->unit->full_identifier }}</a>
                            @else
                            {{ $user->unit->full_identifier }}
                            @endcan
                        @else
                            -
                        @endif
                    </div>
                    @if($user->moradorVinculado)
                    <div class="col-md-12 mb-3">
                        <strong>Morador Vinculado (Agregado):</strong><br>
                        <a href="{{ route('users.show', $user->moradorVinculado) }}">
                            {{ $user->moradorVinculado->name }}
                        </a>
                    </div>
                    @endif
                    @if($user->agregados->count() > 0)
                    <div class="col-md-12">
                        <strong>Agregados Vinculados:</strong><br>
                        @foreach($user->agregados as $agregado)
                            <a href="{{ route('users.show', $agregado) }}" class="badge bg-secondary me-1">
                                {{ $agregado->name }}
                            </a>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Informações Profissionais -->
        @if($user->local_trabalho || $user->telefone_comercial || $user->contato_comercial)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informações Profissionais</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @if($user->local_trabalho)
                    <div class="col-md-8 mb-2">
                        <strong>Local de Trabalho:</strong><br>
                        {{ $user->local_trabalho }}
                    </div>
                    @endif
                    @if($user->telefone_comercial)
                    <div class="col-md-4 mb-2">
                        <strong>Tel. Comercial:</strong><br>
                        {{ $user->telefone_comercial }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Cuidados Especiais -->
        @if($user->necessita_cuidados_especiais)
        <div class="card mb-4">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Cuidados Especiais</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $user->descricao_cuidados_especiais }}</p>
            </div>
        </div>
        @endif

        <!-- Resumo de Atividades -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Resumo de Atividades</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <div class="border rounded p-3">
                            <i class="bi bi-calendar-check fs-2 text-primary"></i>
                            <h4 class="mt-2">{{ $user->reservations->count() }}</h4>
                            <small class="text-muted">Reservas</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="border rounded p-3">
                            <i class="bi bi-receipt fs-2 text-warning"></i>
                            <h4 class="mt-2">{{ $user->charges->count() }}</h4>
                            <small class="text-muted">Cobranças</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="border rounded p-3">
                            <i class="bi bi-heart fs-2 text-danger"></i>
                            <h4 class="mt-2">{{ $user->pets->count() }}</h4>
                            <small class="text-muted">Pets</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="border rounded p-3">
                            <i class="bi bi-chat-dots fs-2 text-info"></i>
                            <h4 class="mt-2">{{ $user->sentMessages->count() }}</h4>
                            <small class="text-muted">Mensagens</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Foto -->
        <div class="card mb-4">
            <div class="card-body text-center">
                @if($user->photo)
                <img src="{{ Storage::url($user->photo) }}" alt="{{ $user->name }}" class="img-fluid rounded-circle mb-3" style="max-width: 200px;">
                @else
                <i class="bi bi-person-circle" style="font-size: 200px; color: #ccc;"></i>
                @endif
                <h5>{{ $user->name }}</h5>
                <p class="text-muted mb-0">{{ $user->email }}</p>
            </div>
        </div>

        <!-- Perfis -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Perfis</h5>
            </div>
            <div class="card-body">
                @foreach($user->roles as $role)
                    <span class="badge bg-primary mb-1">{{ $role->name }}</span>
                @endforeach
            </div>
        </div>

        <!-- Status -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Status</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    @if($user->is_active)
                        <span class="badge bg-success">Ativo</span>
                    @else
                        <span class="badge bg-secondary">Inativo</span>
                    @endif
                </div>
                <div class="mb-2">
                    @if($user->possui_dividas)
                        <span class="badge bg-danger">Com Dívidas</span>
                    @else
                        <span class="badge bg-success">Sem Dívidas</span>
                    @endif
                </div>
                @if($user->senha_temporaria)
                <div class="mb-2">
                    <span class="badge bg-warning">Senha Temporária</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Datas -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Datas</h5>
            </div>
            <div class="card-body">
                @if($user->data_entrada)
                <p><strong>Entrada:</strong> {{ $user->data_entrada->format('d/m/Y') }}</p>
                @endif
                @if($user->data_saida)
                <p><strong>Saída:</strong> {{ $user->data_saida->format('d/m/Y') }}</p>
                @endif
                <p><strong>Cadastrado:</strong> {{ $user->created_at->format('d/m/Y H:i') }}</p>
                <p class="mb-0"><strong>Atualizado:</strong> {{ $user->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

