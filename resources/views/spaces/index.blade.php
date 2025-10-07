@extends('layouts.app')

@section('title', 'Gestão de Espaços')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Gestão de Espaços</h2>
                <p class="text-muted mb-0">Administre os espaços disponíveis para reserva</p>
            </div>
            <a href="{{ route('spaces.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Novo Espaço
            </a>
        </div>
    </div>
</div>

<!-- Cards de Espaços -->
<div class="row g-4">
    @forelse($spaces as $space)
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 {{ $space->is_active ? '' : 'border-secondary' }}">
            <div class="card-header {{ $space->is_active ? 'bg-primary text-white' : 'bg-secondary text-white' }}">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $space->name }}</h5>
                    @if(!$space->is_active)
                    <span class="badge bg-light text-dark">Inativo</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <i class="bi bi-tag-fill text-muted"></i>
                    <strong>Tipo:</strong> 
                    {{ [
                        'party_hall' => 'Salão de Festas',
                        'bbq' => 'Churrasqueira',
                        'pool' => 'Piscina',
                        'sports_court' => 'Quadra',
                        'gym' => 'Academia',
                        'meeting_room' => 'Sala de Reunião',
                        'other' => 'Outro'
                    ][$space->type] ?? $space->type }}
                </div>

                @if($space->capacity)
                <div class="mb-2">
                    <i class="bi bi-people-fill text-muted"></i>
                    <strong>Capacidade:</strong> {{ $space->capacity }} pessoas
                </div>
                @endif

                <div class="mb-2">
                    <i class="bi bi-cash text-muted"></i>
                    <strong>Taxa:</strong> 
                    @if($space->price_per_hour > 0)
                        <span class="text-success fw-bold">R$ {{ number_format($space->price_per_hour, 2, ',', '.') }}</span> por reserva
                    @else
                        <span class="text-success fw-bold">GRATUITO</span>
                    @endif
                </div>

                <div class="mb-2">
                    <i class="bi bi-clock text-muted"></i>
                    <strong>Horário:</strong> {{ $space->available_from }} às {{ $space->available_until }}
                </div>

                <div class="mb-3">
                    <i class="bi bi-calendar-check text-muted"></i>
                    <strong>Limite mensal:</strong> {{ $space->max_reservations_per_month_per_unit }} reserva(s) por unidade
                </div>

                @if($space->description)
                <p class="text-muted small mb-3">{{ Str::limit($space->description, 100) }}</p>
                @endif

                <div class="border-top pt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">Total de Reservas:</small>
                        <span class="badge bg-info">{{ $space->reservations_count }}</span>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-grid gap-2">
                    <a href="{{ route('spaces.edit', $space) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                    <form action="{{ route('spaces.destroy', $space) }}" method="POST" 
                          onsubmit="return confirm('Tem certeza que deseja remover este espaço?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                            <i class="bi bi-trash"></i> Remover
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h4 class="mt-3 text-muted">Nenhum espaço cadastrado</h4>
                <p class="text-muted">Comece criando o primeiro espaço para reservas</p>
                <a href="{{ route('spaces.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Criar Primeiro Espaço
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>
@endsection

