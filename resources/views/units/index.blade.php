@extends('layouts.app')

@section('title', 'Unidades')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-houses"></i> Unidades</h1>
    @can('create_units')
    <a href="{{ route('units.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nova Unidade
    </a>
    @endcan
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('units.index') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Buscar..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">Todos os tipos</option>
                    <option value="residential" {{ request('type') === 'residential' ? 'selected' : '' }}>Residencial</option>
                    <option value="commercial" {{ request('type') === 'commercial' ? 'selected' : '' }}>Comercial</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="situacao" class="form-select">
                    <option value="">Todas situações</option>
                    <option value="habitado" {{ request('situacao') === 'habitado' ? 'selected' : '' }}>Habitado</option>
                    <option value="fechado" {{ request('situacao') === 'fechado' ? 'selected' : '' }}>Fechado</option>
                    <option value="indisponivel" {{ request('situacao') === 'indisponivel' ? 'selected' : '' }}>Indisponível</option>
                    <option value="em_obra" {{ request('situacao') === 'em_obra' ? 'selected' : '' }}>Em Obra</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="possui_dividas" class="form-select">
                    <option value="">Com/Sem dívidas</option>
                    <option value="1" {{ request('possui_dividas') === '1' ? 'selected' : '' }}>Com dívidas</option>
                    <option value="0" {{ request('possui_dividas') === '0' ? 'selected' : '' }}>Sem dívidas</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="{{ route('units.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Limpar
                </a>
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
                        <th>ID</th>
                        <th>Número</th>
                        <th>Bloco</th>
                        <th>Tipo</th>
                        <th>Situação</th>
                        <th>Endereço</th>
                        <th>Moradores</th>
                        <th>Status</th>
                        <th width="150">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($units as $unit)
                    <tr>
                        <td>{{ $unit->id }}</td>
                        <td><strong>{{ $unit->number }}</strong></td>
                        <td>{{ $unit->block ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $unit->type === 'residential' ? 'info' : 'warning' }}">
                                {{ $unit->type === 'residential' ? 'Residencial' : 'Comercial' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $unit->situacao_label }}</span>
                        </td>
                        <td>
                            @if($unit->logradouro)
                                {{ $unit->logradouro }}, {{ $unit->numero }}
                                @if($unit->bairro) - {{ $unit->bairro }}@endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $unit->users->count() }}</span>
                        </td>
                        <td>
                            @if($unit->possui_dividas)
                                <span class="badge bg-danger">Com dívidas</span>
                            @else
                                <span class="badge bg-success">Em dia</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('units.show', $unit) }}" class="btn btn-info" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('edit_units')
                                <a href="{{ route('units.edit', $unit) }}" class="btn btn-warning" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan
                                @can('delete_units')
                                <form action="{{ route('units.destroy', $unit) }}" method="POST" class="d-inline" 
                                      onsubmit="return confirm('Tem certeza que deseja excluir esta unidade?')">
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
                        <td colspan="9" class="text-center py-4">
                            <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                            Nenhuma unidade encontrada.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $units->links() }}
        </div>
    </div>
</div>
@endsection

