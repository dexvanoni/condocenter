@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1"><i class="bi bi-pencil-square"></i> Editar Reserva Recorrente</h2>
                    <p class="text-muted mb-0">{{ $recurringReservation->title }}</p>
                </div>
                <div>
                    <a href="{{ route('recurring-reservations.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header bg-warning text-dark py-2">
                    <h5 class="mb-0">Editar Informações da Reserva Recorrente</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('recurring-reservations.update', $recurringReservation) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Título -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Título *</label>
                            <input type="text" class="form-control" name="title" value="{{ old('title', $recurringReservation->title) }}" 
                                   placeholder="Ex: Vôlei, Academia, Ensaio da Banda" required>
                        </div>

                        <!-- Espaço -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Espaço *</label>
                            <select class="form-select" name="space_id" required>
                                <option value="">Selecione o espaço</option>
                                @foreach($spaces as $space)
                                <option value="{{ $space->id }}" {{ old('space_id', $recurringReservation->space_id) == $space->id ? 'selected' : '' }}>
                                    {{ $space->name }} - {{ $space->reservation_mode === 'full_day' ? 'Diária' : 'Por Horários' }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Descrição -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Descrição</label>
                            <textarea class="form-control" name="description" rows="3" 
                                      placeholder="Descreva detalhes sobre esta reserva recorrente...">{{ old('description', $recurringReservation->description) }}</textarea>
                        </div>

                        <!-- Dias da Semana -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Dias da Semana *</label>
                            <div class="row g-2">
                                @php
                                    $days = [
                                        ['value' => 0, 'name' => 'Domingo', 'short' => 'Dom'],
                                        ['value' => 1, 'name' => 'Segunda-feira', 'short' => 'Seg'],
                                        ['value' => 2, 'name' => 'Terça-feira', 'short' => 'Ter'],
                                        ['value' => 3, 'name' => 'Quarta-feira', 'short' => 'Qua'],
                                        ['value' => 4, 'name' => 'Quinta-feira', 'short' => 'Qui'],
                                        ['value' => 5, 'name' => 'Sexta-feira', 'short' => 'Sex'],
                                        ['value' => 6, 'name' => 'Sábado', 'short' => 'Sáb'],
                                    ];
                                    $selectedDays = old('days_of_week', $recurringReservation->days_of_week);
                                @endphp
                                @foreach($days as $day)
                                <div class="col-md-3 col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="days_of_week[]" 
                                               value="{{ $day['value'] }}" id="day{{ $day['value'] }}"
                                               {{ in_array($day['value'], $selectedDays) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="day{{ $day['value'] }}">
                                            {{ $day['name'] }}
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="form-text">Selecione todos os dias em que a reserva deve se repetir</div>
                        </div>

                        <!-- Horário -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Horário de Início *</label>
                                <input type="time" class="form-control" name="start_time" 
                                       value="{{ old('start_time', $recurringReservation->start_time) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Horário de Fim *</label>
                                <input type="time" class="form-control" name="end_time" 
                                       value="{{ old('end_time', $recurringReservation->end_time) }}" required>
                            </div>
                        </div>

                        <!-- Data de Início -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Data de Início *</label>
                            <input type="date" class="form-control" name="start_date" 
                                   value="{{ old('start_date', $recurringReservation->start_date->format('Y-m-d')) }}" required>
                        </div>

                        <!-- Data de Fim -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Data de Fim *</label>
                            <input type="date" class="form-control" name="end_date" 
                                   value="{{ old('end_date', $recurringReservation->end_date->format('Y-m-d')) }}" required>
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Status *</label>
                            <select class="form-select" name="status" required>
                                <option value="active" {{ old('status', $recurringReservation->status) === 'active' ? 'selected' : '' }}>
                                    Ativo
                                </option>
                                <option value="inactive" {{ old('status', $recurringReservation->status) === 'inactive' ? 'selected' : '' }}>
                                    Inativo
                                </option>
                            </select>
                            <div class="form-text">
                                <strong>Ativo:</strong> Gera reservas automaticamente<br>
                                <strong>Inativo:</strong> Para de gerar novas reservas
                            </div>
                        </div>

                        <!-- Notas Administrativas -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Notas Administrativas</label>
                            <textarea class="form-control" name="admin_notes" rows="3" 
                                      placeholder="Observações internas sobre esta reserva recorrente...">{{ old('admin_notes', $recurringReservation->admin_notes) }}</textarea>
                        </div>

                        <!-- Botões -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning flex-grow-1">
                                <i class="bi bi-check-circle"></i> Salvar Alterações
                            </button>
                            <a href="{{ route('recurring-reservations.index') }}" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validação do formulário
    document.querySelector('form').addEventListener('submit', function(e) {
        const selectedDays = document.querySelectorAll('input[name="days_of_week[]"]:checked').length;
        
        if (selectedDays === 0) {
            e.preventDefault();
            alert('Por favor, selecione pelo menos um dia da semana.');
            return;
        }
        
        const startTime = document.querySelector('input[name="start_time"]').value;
        const endTime = document.querySelector('input[name="end_time"]').value;
        
        if (startTime >= endTime) {
            e.preventDefault();
            alert('O horário de fim deve ser posterior ao horário de início.');
            return;
        }
        
        const startDate = document.querySelector('input[name="start_date"]').value;
        const endDate = document.querySelector('input[name="end_date"]').value;
        
        if (endDate <= startDate) {
            e.preventDefault();
            alert('A data de fim deve ser posterior à data de início.');
            return;
        }
    });
});
</script>
@endpush
