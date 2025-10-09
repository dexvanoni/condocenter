@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1"><i class="bi bi-plus-circle"></i> Nova Reserva Recorrente</h2>
                    <p class="text-muted mb-0">Crie um agendamento que se repete em múltiplas datas</p>
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
                <div class="card-header bg-primary text-white py-2">
                    <h5 class="mb-0">Informações da Reserva Recorrente</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('recurring-reservations.store') }}" method="POST">
                        @csrf

                        <!-- Título -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Título *</label>
                            <input type="text" class="form-control" name="title" value="{{ old('title') }}" 
                                   placeholder="Ex: Vôlei, Academia, Ensaio da Banda" required>
                            <div class="form-text">Este título aparecerá no calendário para todos os usuários</div>
                        </div>

                        <!-- Espaço -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Espaço *</label>
                            <select class="form-select" name="space_id" required>
                                <option value="">Selecione o espaço</option>
                                @foreach($spaces as $space)
                                <option value="{{ $space->id }}" {{ old('space_id') == $space->id ? 'selected' : '' }}>
                                    {{ $space->name }} - {{ $space->reservation_mode === 'full_day' ? 'Diária' : 'Por Horários' }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Descrição -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Descrição</label>
                            <textarea class="form-control" name="description" rows="3" 
                                      placeholder="Descreva detalhes sobre esta reserva recorrente...">{{ old('description') }}</textarea>
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
                                @endphp
                                @foreach($days as $day)
                                <div class="col-md-3 col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="days_of_week[]" 
                                               value="{{ $day['value'] }}" id="day{{ $day['value'] }}"
                                               {{ in_array($day['value'], old('days_of_week', [])) ? 'checked' : '' }}>
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
                                <input type="time" class="form-control" name="start_time" value="{{ old('start_time', '19:00') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Horário de Fim *</label>
                                <input type="time" class="form-control" name="end_time" value="{{ old('end_time', '21:00') }}" required>
                            </div>
                        </div>

                        <!-- Data de Início -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Data de Início *</label>
                            <input type="date" class="form-control" name="start_date" 
                                   value="{{ old('start_date', now()->toDateString()) }}" required>
                            <div class="form-text">A partir de quando as reservas devem começar</div>
                        </div>

                        <!-- Duração -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Duração *</label>
                            <div class="row g-2">
                                <div class="col-md-8">
                                    <select class="form-select" name="duration_months" required>
                                        <option value="">Selecione a duração</option>
                                        <option value="1" {{ old('duration_months') == '1' ? 'selected' : '' }}>1 mês</option>
                                        <option value="3" {{ old('duration_months') == '3' ? 'selected' : '' }}>3 meses</option>
                                        <option value="6" {{ old('duration_months') == '6' ? 'selected' : '' }}>6 meses</option>
                                        <option value="12" {{ old('duration_months') == '12' ? 'selected' : '' }}>1 ano</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <div class="alert alert-info py-2 mb-0">
                                        <small class="text-muted">
                                            <strong>Data de fim:</strong> 
                                            <span id="endDatePreview">Selecione a duração</span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview -->
                        <div class="mb-4" id="previewSection" style="display: none;">
                            <div class="alert alert-success">
                                <h6 class="alert-heading">Preview da Reserva Recorrente</h6>
                                <p class="mb-1"><strong>Título:</strong> <span id="previewTitle">-</span></p>
                                <p class="mb-1"><strong>Espaço:</strong> <span id="previewSpace">-</span></p>
                                <p class="mb-1"><strong>Dias:</strong> <span id="previewDays">-</span></p>
                                <p class="mb-1"><strong>Horário:</strong> <span id="previewTime">-</span></p>
                                <p class="mb-1"><strong>Período:</strong> <span id="previewPeriod">-</span></p>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bi bi-check-circle"></i> Criar Reserva Recorrente
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
    const startDateInput = document.querySelector('input[name="start_date"]');
    const durationSelect = document.querySelector('select[name="duration_months"]');
    const endDatePreview = document.getElementById('endDatePreview');
    const previewSection = document.getElementById('previewSection');

    function updatePreview() {
        const startDate = startDateInput.value;
        const duration = durationSelect.value;
        const title = document.querySelector('input[name="title"]').value;
        const spaceSelect = document.querySelector('select[name="space_id"]');
        const spaceText = spaceSelect.options[spaceSelect.selectedIndex].text;
        const startTime = document.querySelector('input[name="start_time"]').value;
        const endTime = document.querySelector('input[name="end_time"]').value;
        
        // Calcular data de fim
        if (startDate && duration) {
            const start = new Date(startDate);
            const end = new Date(start);
            end.setMonth(end.getMonth() + parseInt(duration));
            endDatePreview.textContent = end.toLocaleDateString('pt-BR');
        }

        // Atualizar preview
        if (title && spaceText && startTime && endTime && startDate && duration) {
            document.getElementById('previewTitle').textContent = title;
            document.getElementById('previewSpace').textContent = spaceText.split(' - ')[0];
            
            // Dias selecionados
            const selectedDays = Array.from(document.querySelectorAll('input[name="days_of_week[]"]:checked'))
                .map(checkbox => checkbox.nextElementSibling.textContent);
            document.getElementById('previewDays').textContent = selectedDays.join(', ') || 'Nenhum dia selecionado';
            
            document.getElementById('previewTime').textContent = `${startTime} às ${endTime}`;
            
            if (startDate && duration) {
                const start = new Date(startDate);
                const end = new Date(start);
                end.setMonth(end.getMonth() + parseInt(duration));
                document.getElementById('previewPeriod').textContent = 
                    `${start.toLocaleDateString('pt-BR')} até ${end.toLocaleDateString('pt-BR')}`;
            }
            
            previewSection.style.display = 'block';
        } else {
            previewSection.style.display = 'none';
        }
    }

    // Event listeners
    startDateInput.addEventListener('change', updatePreview);
    durationSelect.addEventListener('change', updatePreview);
    document.querySelector('input[name="title"]').addEventListener('input', updatePreview);
    document.querySelector('select[name="space_id"]').addEventListener('change', updatePreview);
    document.querySelector('input[name="start_time"]').addEventListener('change', updatePreview);
    document.querySelector('input[name="end_time"]').addEventListener('change', updatePreview);
    
    document.querySelectorAll('input[name="days_of_week[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', updatePreview);
    });

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
    });
});
</script>
@endpush
