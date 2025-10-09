@extends('layouts.app')

@section('title', 'Detalhes da Unidade')

@section('content')
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-houses"></i> Unidade {{ $unit->full_identifier }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('units.index') }}">Unidades</a></li>
                    <li class="breadcrumb-item active">{{ $unit->full_identifier }}</li>
                </ol>
            </nav>
        </div>
        <div>
            @can('edit_units')
            <a href="{{ route('units.edit', $unit) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Editar
            </a>
            @endcan
            @can('delete_units')
            <form action="{{ route('units.destroy', $unit) }}" method="POST" class="d-inline" 
                  onsubmit="return confirm('Tem certeza que deseja excluir esta unidade?')">
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
        <!-- Informações Básicas -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informações Básicas</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Número:</strong><br>
                        {{ $unit->number }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Bloco:</strong><br>
                        {{ $unit->block ?? '-' }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Tipo:</strong><br>
                        <span class="badge bg-{{ $unit->type === 'residential' ? 'info' : 'warning' }}">
                            {{ $unit->type === 'residential' ? 'Residencial' : 'Comercial' }}
                        </span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Situação:</strong><br>
                        <span class="badge bg-secondary">{{ $unit->situacao_label }}</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Status Financeiro:</strong><br>
                        @if($unit->possui_dividas)
                            <span class="badge bg-danger">Com Dívidas</span>
                        @else
                            <span class="badge bg-success">Em Dia</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Status:</strong><br>
                        @if($unit->is_active)
                            <span class="badge bg-success">Ativo</span>
                        @else
                            <span class="badge bg-secondary">Inativo</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Endereço -->
        @if($unit->logradouro)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Endereço</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $unit->full_address }}</p>
            </div>
        </div>
        @endif

        <!-- Características -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Características</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Quartos:</strong><br>
                        {{ $unit->num_quartos ?? '-' }}
                    </div>
                    <div class="col-md-3">
                        <strong>Banheiros:</strong><br>
                        {{ $unit->num_banheiros ?? '-' }}
                    </div>
                    <div class="col-md-3">
                        <strong>Área:</strong><br>
                        {{ $unit->area ? $unit->area . ' m²' : '-' }}
                    </div>
                    <div class="col-md-3">
                        <strong>Andar:</strong><br>
                        {{ $unit->floor ?? '-' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Moradores -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Moradores ({{ $unit->users->count() }})</h5>
            </div>
            <div class="card-body">
                @if($unit->users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>CPF</th>
                                <th>Perfil</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($unit->users as $user)
                            <tr>
                                <td>
                                    @can('view_users')
                                    <a href="{{ route('users.show', $user) }}">{{ $user->name }}</a>
                                    @else
                                    {{ $user->name }}
                                    @endcan
                                </td>
                                <td>{{ $user->cpf }}</td>
                                <td>
                                    @foreach($user->roles as $role)
                                        <span class="badge bg-primary">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge bg-success">Ativo</span>
                                    @else
                                        <span class="badge bg-secondary">Inativo</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted mb-0">Nenhum morador vinculado a esta unidade.</p>
                @endif
            </div>
        </div>

        <!-- Observações -->
        @if($unit->notes)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Observações</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $unit->notes }}</p>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Foto -->
        @if($unit->foto)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Foto</h5>
            </div>
            <div class="card-body text-center">
                <img src="{{ Storage::url($unit->foto) }}" alt="Foto da Unidade" class="img-fluid rounded">
            </div>
        </div>
        @endif

        <!-- Estatísticas -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Estatísticas</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Moradores:</span>
                    <strong>{{ $unit->users->count() }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Cobranças:</span>
                    <strong>{{ $unit->charges->count() }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Reservas:</span>
                    <strong>{{ $unit->reservations->count() }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

