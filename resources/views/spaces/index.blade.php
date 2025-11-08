@extends('layouts.app')

@section('title', 'Gestão de Espaços')

@push('styles')
<style>
    .space-image-container {
        height: 200px;
        overflow: hidden;
        background: linear-gradient(135deg, #0a1b67 0%, #3866d2 100%);
        position: relative;
    }
    
    .space-image-container img {
        transition: transform 0.3s ease;
    }
    
    .card:hover .space-image-container img {
        transform: scale(1.05);
    }
    
    .space-image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.3));
        pointer-events: none;
    }

    .spaces-hero {
        background: linear-gradient(135deg, #0a1b67 0%, #3866d2 100%);
        color: white;
    }
</style>
@endpush

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
<div class="row g-3">
    @forelse($spaces as $space)
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm {{ $space->is_active ? '' : 'border-secondary opacity-75' }}">
            <!-- Imagem do Espaço -->
            <div class="space-image-container">
                <img src="{{ $space->getPhotoUrl() }}" 
                     alt="{{ $space->name }}" 
                     class="w-100 h-100"
                     style="object-fit: cover;">
                <div class="space-image-overlay"></div>
                @if(!$space->is_active)
                <div class="position-absolute top-0 end-0 m-2" style="z-index: 1;">
                    <span class="badge bg-secondary">Inativo</span>
                </div>
                @endif
            </div>
            
            <div class="card-header {{ $space->is_active ? 'bg-primary text-white' : 'bg-secondary text-white' }} py-2">
                <h6 class="mb-0">{{ $space->name }}</h6>
            </div>
            <div class="card-body p-3">
                <!-- Primeira linha: Tipo e Capacidade -->
                <div class="row mb-2">
                    <div class="col-6">
                        <small class="text-muted d-block">Tipo</small>
                        <span class="fw-semibold">
                            @php
                                $typeIcons = [
                                    'party_hall' => '🎉',
                                    'bbq' => '🍖',
                                    'pool' => '🏊',
                                    'sports_court' => '⚽',
                                    'gym' => '💪',
                                    'meeting_room' => '🏢',
                                    'other' => '📍'
                                ];
                                $typeNames = [
                                    'party_hall' => 'Salão',
                                    'bbq' => 'Churrasqueira',
                                    'pool' => 'Piscina',
                                    'sports_court' => 'Quadra',
                                    'gym' => 'Academia',
                                    'meeting_room' => 'Sala',
                                    'other' => 'Outro'
                                ];
                            @endphp
                            {{ ($typeIcons[$space->type] ?? '📍') . ' ' . ($typeNames[$space->type] ?? $space->type) }}
                        </span>
                    </div>
                    @if($space->capacity)
                    <div class="col-6">
                        <small class="text-muted d-block">Capacidade</small>
                        <span class="fw-semibold">👥 {{ $space->capacity }} pessoas</span>
                    </div>
                    @endif
                </div>

                <!-- Segunda linha: Taxa e Limite -->
                <div class="row mb-2">
                    <div class="col-6">
                        <small class="text-muted d-block">Taxa</small>
                        @if($space->price_per_hour > 0)
                            <span class="text-success fw-bold">💰 R$ {{ number_format($space->price_per_hour, 2, ',', '.') }}</span>
                        @else
                            <span class="text-success fw-bold">🆓 GRATUITO</span>
                        @endif
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Limite/Mês</small>
                        <span class="fw-semibold">📅 {{ $space->max_reservations_per_month_per_user }}</span>
                    </div>
                </div>

                <!-- Terceira linha: Modo de Reserva -->
                <div class="mb-2">
                    <small class="text-muted d-block">Modo de Reserva</small>
                    <span class="fw-semibold">
                        @if($space->reservation_mode == 'full_day')
                            📅 Diária (1 reserva por dia)
                        @else
                            ⏰ Por Horários (múltiplas por dia)
                        @endif
                    </span>
                </div>

                <!-- Quarta linha: Horário -->
                <div class="mb-2">
                    <small class="text-muted d-block">Horário de Funcionamento</small>
                    <span class="fw-semibold">
                        🕐 
                        @if($space->available_from && $space->available_until)
                            {{ \Carbon\Carbon::parse($space->available_from)->format('H:i') }} às {{ \Carbon\Carbon::parse($space->available_until)->format('H:i') }}
                        @else
                            Não definido
                        @endif
                    </span>
                </div>

                <!-- Descrição (se existir) -->
                @if($space->description)
                <div class="mb-2">
                    <small class="text-muted">{{ Str::limit($space->description, 80) }}</small>
                </div>
                @endif

                <!-- Total de Reservas -->
                <div class="border-top pt-2 mt-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Total de Reservas:</small>
                        <span class="badge bg-primary">{{ $space->reservations_count }}</span>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light py-2">
                <div class="d-flex gap-1">
                    <a href="{{ route('spaces.edit', $space) }}" class="btn btn-sm btn-outline-primary flex-fill">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                    <form action="{{ route('spaces.destroy', $space) }}" method="POST" 
                          onsubmit="return confirm('Tem certeza que deseja remover este espaço?')" class="flex-fill">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                            <i class="bi bi-trash"></i>
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

