@extends('layouts.app')

@section('title', 'Novo Espaço')

@section('content')
<div class="row">
    <div class="col-lg-8 offset-lg-2">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Cadastrar Novo Espaço</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('spaces.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Nome do Espaço *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               name="name" value="{{ old('name') }}" required
                               placeholder="Ex: Churrasqueira 1, Salão de Festas">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  name="description" rows="3"
                                  placeholder="Descreva o espaço, comodidades, etc">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tipo *</label>
                                <select class="form-select @error('type') is-invalid @enderror" name="type" required>
                                    <option value="">Selecione...</option>
                                    <option value="bbq" {{ old('type') == 'bbq' ? 'selected' : '' }}>Churrasqueira</option>
                                    <option value="party_hall" {{ old('type') == 'party_hall' ? 'selected' : '' }}>Salão de Festas</option>
                                    <option value="pool" {{ old('type') == 'pool' ? 'selected' : '' }}>Piscina</option>
                                    <option value="sports_court" {{ old('type') == 'sports_court' ? 'selected' : '' }}>Quadra Poliesportiva</option>
                                    <option value="gym" {{ old('type') == 'gym' ? 'selected' : '' }}>Academia</option>
                                    <option value="meeting_room" {{ old('type') == 'meeting_room' ? 'selected' : '' }}>Sala de Reunião</option>
                                    <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Outro</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Capacidade (pessoas)</label>
                                <input type="number" class="form-control @error('capacity') is-invalid @enderror" 
                                       name="capacity" value="{{ old('capacity') }}" min="1"
                                       placeholder="Ex: 20">
                                @error('capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Modo de Reserva *</label>
                                <select class="form-select @error('reservation_mode') is-invalid @enderror" 
                                        name="reservation_mode" required id="reservationMode" onchange="toggleReservationMode()">
                                    <option value="full_day" {{ old('reservation_mode', 'full_day') == 'full_day' ? 'selected' : '' }}>
                                        📅 Dia Inteiro (1 reserva por dia)
                                    </option>
                                    <option value="hourly" {{ old('reservation_mode') == 'hourly' ? 'selected' : '' }}>
                                        ⏰ Por Horário (múltiplas reservas por dia)
                                    </option>
                                </select>
                                <small class="text-muted">
                                    Dia Inteiro: Uma pessoa reserva o dia todo | Por Horário: Várias pessoas podem reservar horários diferentes no mesmo dia
                                </small>
                                @error('reservation_mode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row" id="hourlySettings" style="display: none;">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Duração Mínima (horas)</label>
                                <input type="number" class="form-control" name="min_hours_per_reservation" 
                                       value="{{ old('min_hours_per_reservation', 1) }}" min="1" step="0.5">
                                <small class="text-muted">Ex: 1 hora mínima</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Duração Máxima (horas)</label>
                                <input type="number" class="form-control" name="max_hours_per_reservation" 
                                       value="{{ old('max_hours_per_reservation', 4) }}" min="1">
                                <small class="text-muted">Ex: 4 horas máximas</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Taxa de Reserva (R$) *</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" class="form-control @error('price_per_reservation') is-invalid @enderror" 
                                           name="price_per_reservation" value="{{ old('price_per_reservation', 0) }}" 
                                           step="0.01" min="0" required>
                                </div>
                                <small class="text-muted">Digite 0 se for gratuito</small>
                                @error('price_per_reservation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Limite de Reservas por Mês *</label>
                                <input type="number" class="form-control @error('max_reservations_per_month_per_unit') is-invalid @enderror" 
                                       name="max_reservations_per_month_per_unit" 
                                       value="{{ old('max_reservations_per_month_per_unit', 1) }}" 
                                       min="1" required>
                                <small class="text-muted">Por unidade</small>
                                @error('max_reservations_per_month_per_unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Disponível das *</label>
                                <input type="time" class="form-control @error('available_from') is-invalid @enderror" 
                                       name="available_from" value="{{ old('available_from', '08:00') }}" required>
                                @error('available_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Até *</label>
                                <input type="time" class="form-control @error('available_until') is-invalid @enderror" 
                                       name="available_until" value="{{ old('available_until', '22:00') }}" required>
                                @error('available_until')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Regras de Uso</label>
                        <textarea class="form-control @error('rules') is-invalid @enderror" 
                                  name="rules" rows="4"
                                  placeholder="Ex: Proibido som alto após 22h, limite de 50 pessoas, etc">{{ old('rules') }}</textarea>
                        @error('rules')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Importante:</strong>
                        <ul class="mb-0">
                            <li>As reservas serão aprovadas <strong>automaticamente</strong></li>
                            <li>Apenas <strong>1 reserva por local por dia</strong> será permitida</li>
                            <li>Se houver taxa, será gerada cobrança via <strong>Asaas (PIX/Cartão)</strong></li>
                        </ul>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Cadastrar Espaço
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

