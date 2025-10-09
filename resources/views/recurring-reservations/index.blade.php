@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1"><i class="bi bi-calendar-week"></i> Reservas Recorrentes</h2>
                    <p class="text-muted mb-0">Gerencie agendamentos que se repetem em múltiplas datas</p>
                </div>
                <div>
                    <a href="{{ route('recurring-reservations.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nova Reserva Recorrente
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-2">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-3">
                            <select class="form-select form-select-sm" id="statusFilter">
                                <option value="">Todos os Status</option>
                                <option value="active">Ativas</option>
                                <option value="inactive">Inativas</option>
                                <option value="cancelled">Canceladas</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select form-select-sm" id="spaceFilter">
                                <option value="">Todos os Espaços</option>
                                @foreach($recurringReservations->pluck('space.name')->unique() as $spaceName)
                                <option value="{{ $spaceName }}">{{ $spaceName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-secondary" onclick="clearFilters()">
                                    <i class="bi bi-x-circle"></i> Limpar
                                </button>
                                <button class="btn btn-sm btn-primary" onclick="applyFilters()">
                                    <i class="bi bi-funnel"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Reservas Recorrentes -->
    <div class="row g-3">
        @forelse($recurringReservations as $reservation)
        <div class="col-lg-6 col-xl-4 recurring-card" 
             data-status="{{ $reservation->status }}" 
             data-space="{{ $reservation->space->name }}">
            <div class="card h-100 {{ $reservation->status === 'active' ? 'border-success' : ($reservation->status === 'inactive' ? 'border-warning' : 'border-danger') }}">
                <div class="card-header {{ $reservation->status === 'active' ? 'bg-success' : ($reservation->status === 'inactive' ? 'bg-warning' : 'bg-danger') }} text-white py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">{{ $reservation->title }}</h6>
                        <span class="badge bg-light text-dark">
                            {{ ucfirst($reservation->status) }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-3">
                    <!-- Espaço -->
                    <div class="mb-2">
                        <small class="text-muted d-block">Espaço</small>
                        <span class="fw-semibold">{{ $reservation->space->name }}</span>
                    </div>

                    <!-- Dias da Semana -->
                    <div class="mb-2">
                        <small class="text-muted d-block">Dias</small>
                        <span class="fw-semibold">{{ $reservation->getFormattedDays() }}</span>
                    </div>

                    <!-- Horário -->
                    <div class="mb-2">
                        <small class="text-muted d-block">Horário</small>
                        <span class="fw-semibold">
                            {{ \Carbon\Carbon::parse($reservation->start_time)->format('H:i') }} às 
                            {{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}
                        </span>
                    </div>

                    <!-- Período -->
                    <div class="mb-2">
                        <small class="text-muted d-block">Período</small>
                        <span class="fw-semibold">
                            {{ $reservation->start_date->format('d/m/Y') }} até {{ $reservation->end_date->format('d/m/Y') }}
                        </span>
                    </div>

                    <!-- Descrição -->
                    @if($reservation->description)
                    <div class="mb-2">
                        <small class="text-muted d-block">Descrição</small>
                        <small class="text-muted">{{ Str::limit($reservation->description, 60) }}</small>
                    </div>
                    @endif

                    <!-- Criado por -->
                    <div class="mb-2">
                        <small class="text-muted d-block">Criado por</small>
                        <span class="fw-semibold">{{ $reservation->creator->name }}</span>
                    </div>

                    <!-- Total de Reservas -->
                    <div class="border-top pt-2 mt-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Total de Reservas:</small>
                            <span class="badge bg-primary">{{ $reservation->reservations_count ?? 0 }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light py-2">
                    <div class="d-flex gap-1">
                        <a href="{{ route('recurring-reservations.show', $reservation) }}" 
                           class="btn btn-sm btn-outline-primary flex-fill">
                            <i class="bi bi-eye"></i> Ver
                        </a>
                        <a href="{{ route('recurring-reservations.edit', $reservation) }}" 
                           class="btn btn-sm btn-outline-warning flex-fill">
                            <i class="bi bi-pencil"></i> Editar
                        </a>
                        <button type="button" 
                                class="btn btn-sm btn-outline-danger flex-fill"
                                onclick="confirmCancel({{ $reservation->id }}, '{{ $reservation->title }}')">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-calendar-week display-1 text-muted"></i>
                    <h4 class="mt-3 text-muted">Nenhuma reserva recorrente</h4>
                    <p class="text-muted">Comece criando a primeira reserva recorrente</p>
                    <a href="{{ route('recurring-reservations.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Criar Primeira Reserva Recorrente
                    </a>
                </div>
            </div>
        </div>
        @endforelse
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
function applyFilters() {
    const statusFilter = document.getElementById('statusFilter').value;
    const spaceFilter = document.getElementById('spaceFilter').value;
    
    document.querySelectorAll('.recurring-card').forEach(card => {
        let show = true;
        
        if (statusFilter && card.dataset.status !== statusFilter) {
            show = false;
        }
        
        if (spaceFilter && card.dataset.space !== spaceFilter) {
            show = false;
        }
        
        card.style.display = show ? 'block' : 'none';
    });
}

function clearFilters() {
    document.getElementById('statusFilter').value = '';
    document.getElementById('spaceFilter').value = '';
    document.querySelectorAll('.recurring-card').forEach(card => {
        card.style.display = 'block';
    });
}

function confirmCancel(id, title) {
    document.getElementById('cancelTitle').textContent = title;
    document.getElementById('cancelForm').action = `/recurring-reservations/${id}`;
    
    const modal = new bootstrap.Modal(document.getElementById('cancelModal'));
    modal.show();
}

// Aplicar filtros automaticamente
document.getElementById('statusFilter').addEventListener('change', applyFilters);
document.getElementById('spaceFilter').addEventListener('change', applyFilters);
</script>
@endpush
