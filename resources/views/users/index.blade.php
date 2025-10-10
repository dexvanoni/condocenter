@extends('layouts.app')

@section('title', 'Usuários')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-people-fill"></i> Usuários</h1>
    @can('manage_users')
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Novo Usuário
    </a>
    @endcan
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('users.index') }}" class="row g-3">
            <!-- Primeira linha -->
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Buscar (nome, email, CPF)..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select">
                    <option value="">Todos os perfis</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="unit_id" class="form-select">
                    <option value="">Todas unidades</option>
                    @foreach($units as $unit)
                    <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->full_identifier }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
            
            <!-- Segunda linha -->
            <div class="col-md-3">
                <select name="is_active" class="form-select">
                    <option value="">Todos status</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Ativos</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inativos</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="possui_dividas" class="form-select">
                    <option value="">Todas dívidas</option>
                    <option value="1" {{ request('possui_dividas') === '1' ? 'selected' : '' }}>Com dívidas</option>
                    <option value="0" {{ request('possui_dividas') === '0' ? 'selected' : '' }}>Sem dívidas</option>
                </select>
            </div>
            <div class="col-md-3">
                <a href="{{ route('users.index') }}" class="btn btn-secondary w-100">
                    <i class="bi bi-x-circle"></i> Limpar Filtros
                </a>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-flex align-items-center h-100">
                    <i class="bi bi-info-circle me-1"></i>
                    {{ $users->total() }} usuário(s) encontrado(s)
                </small>
            </div>
        </form>
    </div>
</div>

<!-- Listagem -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>CPF</th>
                        <th>Unidade</th>
                        <th>Perfil(s)</th>
                        <th>Status</th>
                        <th width="150">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            @if($user->photo)
                            <img src="{{ Storage::url($user->photo) }}" alt="{{ $user->name }}" class="rounded-circle" width="40" height="40">
                            @else
                            <i class="bi bi-person-circle fs-3 text-muted"></i>
                            @endif
                        </td>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->cpf }}</td>
                        <td>{{ $user->unit?->full_identifier ?? '-' }}</td>
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
                            @if($user->possui_dividas)
                                <span class="badge bg-danger ms-1">Dívidas</span>
                            @endif
                            @if($user->senha_temporaria)
                                <span class="badge bg-warning ms-1">Senha Temp.</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('users.show', $user) }}" class="btn btn-info" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('update', $user)
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-warning" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan
                                @can('viewHistory', $user)
                                <a href="{{ route('users.history', $user) }}" class="btn btn-secondary" title="Histórico">
                                    <i class="bi bi-clock-history"></i>
                                </a>
                                @endcan
                                @can('delete', $user)
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" 
                                      onsubmit="return confirm('Tem certeza que deseja excluir este usuário?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" title="Excluir">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                            Nenhum usuário encontrado.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection

