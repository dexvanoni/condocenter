@extends('layouts.app')

@section('title', 'Editar Espaço')

@section('content')
<div class="row">
    <div class="col-lg-8 offset-lg-2">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-white py-2">
                <h5 class="mb-0">Editar Espaço: {{ $space->name }}</h5>
            </div>
            <div class="card-body p-3">
                <form action="{{ route('spaces.update', $space) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-2">
                                <label class="form-label fw-semibold small">Nome do Espaço *</label>
                                <input type="text" class="form-control form-control-sm @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name', $space->name) }}" required
                                       placeholder="Ex: Churrasqueira 1, Salão de Festas">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-2">
                                <label class="form-label fw-semibold small">Status</label>
                                <select class="form-select form-select-sm" name="is_active">
                                    <option value="1" {{ old('is_active', $space->is_active) ? 'selected' : '' }}>✅ Ativo</option>
                                    <option value="0" {{ !old('is_active', $space->is_active) ? 'selected' : '' }}>❌ Inativo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Descrição</label>
                        <textarea class="form-control form-control-sm @error('description') is-invalid @enderror" 
                                  name="description" rows="2"
                                  placeholder="Descreva o espaço, comodidades, etc">{{ old('description', $space->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label class="form-label fw-semibold small">Tipo *</label>
                                <select class="form-select form-select-sm @error('type') is-invalid @enderror" name="type" required>
                                    <option value="">Selecione...</option>
                                    <option value="bbq" {{ old('type', $space->type) == 'bbq' ? 'selected' : '' }}>🍖 Churrasqueira</option>
                                    <option value="party_hall" {{ old('type', $space->type) == 'party_hall' ? 'selected' : '' }}>🎉 Salão de Festas</option>
                                    <option value="pool" {{ old('type', $space->type) == 'pool' ? 'selected' : '' }}>🏊 Piscina</option>
                                    <option value="sports_court" {{ old('type', $space->type) == 'sports_court' ? 'selected' : '' }}>⚽ Quadra</option>
                                    <option value="gym" {{ old('type', $space->type) == 'gym' ? 'selected' : '' }}>💪 Academia</option>
                                    <option value="meeting_room" {{ old('type', $space->type) == 'meeting_room' ? 'selected' : '' }}>🏢 Sala Reunião</option>
                                    <option value="other" {{ old('type', $space->type) == 'other' ? 'selected' : '' }}>📍 Outro</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-2">
                                <label class="form-label fw-semibold small">Capacidade</label>
                                <input type="number" class="form-control form-control-sm @error('capacity') is-invalid @enderror" 
                                       name="capacity" value="{{ old('capacity', $space->capacity) }}" min="1"
                                       placeholder="Ex: 20">
                                @error('capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-2">
                                <label class="form-label fw-semibold small">Limite/Mês *</label>
                                <input type="number" class="form-control form-control-sm @error('max_reservations_per_month_per_unit') is-invalid @enderror" 
                                       name="max_reservations_per_month_per_unit" 
                                       value="{{ old('max_reservations_per_month_per_unit', $space->max_reservations_per_month_per_unit ?? 1) }}" 
                                       min="1" required>
                                @error('max_reservations_per_month_per_unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-semibold small">Modo de Reserva *</label>
                                <select class="form-select form-select-sm @error('reservation_mode') is-invalid @enderror" 
                                        name="reservation_mode" required id="reservationMode" onchange="toggleReservationMode()">
                                    <option value="full_day" {{ old('reservation_mode', $space->reservation_mode) == 'full_day' ? 'selected' : '' }}>
                                        📅 Dia Inteiro
                                    </option>
                                    <option value="hourly" {{ old('reservation_mode', $space->reservation_mode) == 'hourly' ? 'selected' : '' }}>
                                        ⏰ Por Horário
                                    </option>
                                </select>
                                @error('reservation_mode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-semibold small">Taxa de Reserva *</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" class="form-control @error('price_per_reservation') is-invalid @enderror" 
                                           name="price_per_reservation" value="{{ old('price_per_reservation', $space->price_per_hour) }}" 
                                           step="0.01" min="0" required placeholder="0.00">
                                </div>
                                @error('price_per_reservation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row" id="hourlySettings" style="display: none;">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-semibold small">Duração Mínima (horas)</label>
                                <input type="number" class="form-control form-control-sm" name="min_hours_per_reservation" 
                                       value="{{ old('min_hours_per_reservation', $space->min_hours_per_reservation ?? 1) }}" min="1" step="0.5">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-semibold small">Duração Máxima (horas)</label>
                                <input type="number" class="form-control form-control-sm" name="max_hours_per_reservation" 
                                       value="{{ old('max_hours_per_reservation', $space->max_hours_per_reservation ?? 4) }}" min="1">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-semibold small">Disponível das *</label>
                                <input type="time" class="form-control form-control-sm @error('available_from') is-invalid @enderror" 
                                       name="available_from" value="{{ old('available_from', $space->available_from ?? '08:00') }}" required>
                                @error('available_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-semibold small">Até *</label>
                                <input type="time" class="form-control form-control-sm @error('available_until') is-invalid @enderror" 
                                       name="available_until" value="{{ old('available_until', $space->available_until ?? '22:00') }}" required>
                                @error('available_until')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Regras de Uso</label>
                        <textarea class="form-control form-control-sm @error('rules') is-invalid @enderror" 
                                  name="rules" rows="2"
                                  placeholder="Ex: Proibido som alto após 22h, limite de 50 pessoas, etc">{{ old('rules', $space->rules) }}</textarea>
                        @error('rules')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-warning py-2 mb-2 small">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Atenção:</strong> Reservas existentes mantêm os valores originais.
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning flex-grow-1">
                            <i class="bi bi-check-circle"></i> Salvar Alterações
                        </button>
                        <a href="{{ route('spaces.index') }}" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleReservationMode() {
        const mode = document.getElementById('reservationMode').value;
        const hourlySettings = document.getElementById('hourlySettings');
        
        if (mode === 'hourly') {
            hourlySettings.style.display = 'block';
        } else {
            hourlySettings.style.display = 'none';
        }
    }
    
    // Inicializar ao carregar
    document.addEventListener('DOMContentLoaded', () => {
        toggleReservationMode();
    });
</script>
@endpush
@endsection

