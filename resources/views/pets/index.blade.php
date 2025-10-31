@extends('layouts.app')

@section('title', 'Pets')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-hearts"></i> Cadastro de Pets</h2>
                <div class="d-flex gap-2">
                    <a href="{{ route('pets.verify') }}" class="btn btn-success">
                        <i class="bi bi-qr-code-scan"></i> Verificar QR Code
                    </a>
                    @can('create', App\Models\Pet::class)
                    <a href="{{ route('pets.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Cadastrar Pet
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('pets.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Nome, raça, cor ou dono...">
                </div>
                <div class="col-md-3">
                    <label for="type" class="form-label">Tipo</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">Todos</option>
                        <option value="dog" {{ request('type') == 'dog' ? 'selected' : '' }}>Cachorro</option>
                        <option value="cat" {{ request('type') == 'cat' ? 'selected' : '' }}>Gato</option>
                        <option value="bird" {{ request('type') == 'bird' ? 'selected' : '' }}>Pássaro</option>
                        <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Outro</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="size" class="form-label">Porte</label>
                    <select class="form-select" id="size" name="size">
                        <option value="">Todos</option>
                        <option value="small" {{ request('size') == 'small' ? 'selected' : '' }}>Pequeno</option>
                        <option value="medium" {{ request('size') == 'medium' ? 'selected' : '' }}>Médio</option>
                        <option value="large" {{ request('size') == 'large' ? 'selected' : '' }}>Grande</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">
        @forelse($pets as $pet)
        <div class="col-md-4 col-lg-3">
            <div class="card h-100 shadow-sm pet-card">
                <div class="position-relative">
                    <img src="{{ $pet->photo_url }}" class="card-img-top" alt="{{ $pet->name }}" 
                         style="height: 250px; object-fit: cover;">
                    <span class="badge bg-primary position-absolute top-0 end-0 m-2">
                        {{ $pet->type_label }}
                    </span>
                </div>
                <div class="card-body">
                    <h5 class="card-title">{{ $pet->name }}</h5>
                    <p class="card-text small">
                        @if($pet->breed)
                        <strong>Raça:</strong> {{ $pet->breed }}<br>
                        @endif
                        @if($pet->color)
                        <strong>Cor:</strong> {{ $pet->color }}<br>
                        @endif
                        <strong>Porte:</strong> {{ $pet->size_label }}<br>
                        <strong>Dono:</strong> {{ $pet->owner->name }}<br>
                        <strong>Unidade:</strong> {{ $pet->unit->full_identifier }}
                    </p>
                    @if($pet->observations)
                    <p class="card-text small text-muted">
                        {{ Str::limit($pet->observations, 80) }}
                    </p>
                    @endif
                </div>
                <div class="card-footer bg-transparent">
                    <div class="d-flex gap-2 flex-wrap mb-2">
                        <!-- Botão Ver Detalhes -->
                        <a href="{{ route('pets.show', $pet) }}" 
                           class="btn btn-primary btn-sm flex-grow-1">
                            <i class="bi bi-eye"></i> Ver Detalhes
                        </a>

                        <!-- Botão Chamar o Dono -->
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $pet->owner->phone) }}?text=Olá! Encontrei seu pet {{ $pet->name }}!" 
                           class="btn btn-success btn-sm flex-grow-1" target="_blank">
                            <i class="bi bi-whatsapp"></i> Chamar Dono
                        </a>
                    </div>
                    
                    <div class="d-flex gap-2 flex-wrap">
                        <!-- Botão QR Code -->
                        <a href="{{ route('pets.download-qr', $pet) }}" 
                           class="btn btn-info btn-sm" title="Baixar QR Code">
                            <i class="bi bi-qr-code"></i>
                        </a>

                        <!-- Botões de Editar/Excluir (apenas para dono ou admin) -->
                        @can('update', $pet)
                        <a href="{{ route('pets.edit', $pet) }}" 
                           class="btn btn-warning btn-sm" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @endcan

                        @can('delete', $pet)
                        <form action="{{ route('pets.destroy', $pet) }}" method="POST" 
                              class="d-inline" onsubmit="return confirm('Tem certeza que deseja remover este pet?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" title="Excluir">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle"></i>
                <p class="mb-0">Nenhum pet cadastrado ainda.</p>
                @can('create', App\Models\Pet::class)
                <a href="{{ route('pets.create') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-plus-circle"></i> Cadastrar Primeiro Pet
                </a>
                @endcan
            </div>
        </div>
        @endforelse
    </div>
</div>

@push('styles')
<style>
    .pet-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .pet-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.2) !important;
    }
</style>
@endpush
@endsection
