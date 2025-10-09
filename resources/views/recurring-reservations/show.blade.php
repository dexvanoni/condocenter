@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1"><i class="bi bi-calendar-week"></i> Detalhes da Reserva Recorrente</h2>
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
        <div class="col-lg-8">
            <!-- Informações Principais -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white py-2">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações Gerais</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted">Título</label>
                            <p class="mb-0">{{ $recurringReservation->title }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted">Espaço</label>
                            <p class="mb-0">{{ $recurringReservation->space->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted">Status</label>
                            <span class="badge {{ $recurringReservation->status === 'active' ? 'bg-success' : ($recurringReservation->status === 'inactive' ? 'bg-warning' : 'bg-danger') }}">
                                {{ ucfirst($recurringReservation->status) }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted">Criado por</label>
                            <p class="mb-0">{{ $recurringReservation->creator->name }}</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-muted">Descrição</label>
                            <p class="mb-0">{{ $recurringReservation->description ?: 'Sem descrição' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Horários e Período -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white py-2">
                    <h5 class="mb-0"><i class="bi bi-clock"></i> Horários e Período</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted">Dias da Semana</label>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($recurringReservation->getDaysOfWeekNames() as $day)
                                <span class="badge bg-primary">{{ $day }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted">Horário</label>
                            <p class="mb-0">
                                {{ \Carbon\Carbon::parse($recurringReservation->start_time)->format('H:i') }} às 
                                {{ \Carbon\Carbon::parse($recurringReservation->end_time)->format('H:i') }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted">Data de Início</label>
                            <p class="mb-0">{{ $recurringReservation->start_date->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted">Data de Fim</label>
                            <p class="mb-0">{{ $recurringReservation->end_date->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reservas Geradas -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white py-2">
                    <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Reservas Geradas</h5>
                </div>
                <div class="card-body p-4">
                    @if($recurringReservation->reservations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Horário</th>
                                        <th>Status</th>
                                        <th>Usuário</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recurringReservation->reservations->take(10) as $reservation)
                                    <tr>
                                        <td>{{ $reservation->reservation_date->format('d/m/Y') }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($reservation->start_time)->format('H:i') }} às 
                                            {{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}
                                        </td>
                                        <td>
                                            <span class="badge bg-success">Aprovada</span>
                                        </td>
                                        <td>{{ $reservation->user->name }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($recurringReservation->reservations->count() > 10)
                        <p class="text-muted small">
                            Mostrando 10 de {{ $recurringReservation->reservations->count() }} reservas
                        </p>
                        @endif
                    @else
                        <p class="text-muted">Nenhuma reserva foi gerada ainda.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Ações -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark py-2">
                    <h5 class="mb-0"><i class="bi bi-gear"></i> Ações</h5>
                </div>
                <div class="card-body p-3">
                    <div class="d-grid gap-2">
                        <a href="{{ route('recurring-reservations.edit', $recurringReservation) }}" 
                           class="btn btn-outline-warning">
                            <i class="bi bi-pencil"></i> Editar Reserva Recorrente
                        </a>
                        
                        @if($recurringReservation->status === 'active')
                        <button type="button" 
                                class="btn btn-outline-danger"
                                onclick="confirmCancel({{ $recurringReservation->id }}, '{{ $recurringReservation->title }}')">
                            <i class="bi bi-x-circle"></i> Cancelar Reserva Recorrente
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Estatísticas -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white py-2">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Estatísticas</h5>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2 text-center">
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <h4 class="text-primary mb-1">{{ $recurringReservation->reservations->count() }}</h4>
                                <small class="text-muted">Total de Reservas</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <h4 class="text-success mb-1">{{ $recurringReservation->reservations->where('status', 'approved')->count() }}</h4>
                                <small class="text-muted">Aprovadas</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações Técnicas -->
            <div class="card">
                <div class="card-header bg-dark text-white py-2">
                    <h5 class="mb-0"><i class="bi bi-info-square"></i> Informações Técnicas</h5>
                </div>
                <div class="card-body p-3">
                    <small class="text-muted">
                        <div class="mb-1"><strong>ID:</strong> {{ $recurringReservation->id }}</div>
                        <div class="mb-1"><strong>Criado em:</strong> {{ $recurringReservation->created_at->format('d/m/Y H:i') }}</div>
                        <div class="mb-1"><strong>Atualizado em:</strong> {{ $recurringReservation->updated_at->format('d/m/Y H:i') }}</div>
                        @if($recurringReservation->admin_notes)
                        <div class="mt-2">
                            <strong>Notas Administrativas:</strong><br>
                            {{ $recurringReservation->admin_notes }}
                        </div>
                        @endif
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Cancelamento -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Cancelar Reserva Recorrente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="cancelForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Você está prestes a cancelar a reserva recorrente: <strong id="cancelTitle"></strong></p>
                    <p class="text-danger"><strong>Atenção:</strong> Esta ação irá cancelar todas as reservas futuras e enviará notificações para os usuários afetados.</p>
                    
                    <div class="mb-3">
                        <label for="admin_reason" class="form-label">Motivo do cancelamento *</label>
                        <textarea class="form-control" id="admin_reason" name="admin_reason" rows="3" 
                                  placeholder="Descreva o motivo do cancelamento..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Cancelamento</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function confirmCancel(id, title) {
    document.getElementById('cancelTitle').textContent = title;
    document.getElementById('cancelForm').action = `/recurring-reservations/${id}`;
    
    const modal = new bootstrap.Modal(document.getElementById('cancelModal'));
    modal.show();
}
</script>
@endpush
