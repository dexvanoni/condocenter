@extends('layouts.app')

@section('title', 'Gerenciar Reservas')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2><i class="bi bi-calendar-check"></i> Gerenciar Todas as Reservas</h2>
        <p class="text-muted">Visualize e gerencie todas as reservas do condomínio</p>
    </div>
</div>

<!-- Filtros -->
<div class="row mb-4">
    <div class="col-md-3">
        <select class="form-select" id="filterSpace" onchange="loadAllReservations()">
            <option value="">Todos os espaços</option>
        </select>
    </div>
    <div class="col-md-3">
        <select class="form-select" id="filterStatus" onchange="loadAllReservations()">
            <option value="">Todos os status</option>
            <option value="approved">Aprovadas</option>
            <option value="pending">Pendentes</option>
            <option value="cancelled">Canceladas</option>
        </select>
    </div>
    <div class="col-md-3">
        <input type="date" class="form-control" id="filterDate" onchange="loadAllReservations()">
    </div>
    <div class="col-md-3">
        <button class="btn btn-secondary w-100" onclick="clearFilters()">
            <i class="bi bi-x-circle"></i> Limpar Filtros
        </button>
    </div>
</div>

<!-- Lista de Reservas -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div id="allReservationsList">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2">Carregando reservas...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let allReservations = [];
    let spaces = [];

    async function loadSpaces() {
        try {
            const response = await fetch('/api/spaces?is_active=', {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            spaces = await response.json();
            
            // Preencher filtro
            const select = document.getElementById('filterSpace');
            spaces.forEach(space => {
                const option = document.createElement('option');
                option.value = space.id;
                option.textContent = space.name;
                select.appendChild(option);
            });
        } catch (error) {
            console.error('Erro ao carregar espaços:', error);
        }
    }

    async function loadAllReservations() {
        try {
            let url = '/api/reservations?';
            
            const spaceId = document.getElementById('filterSpace').value;
            const status = document.getElementById('filterStatus').value;
            const date = document.getElementById('filterDate').value;
            
            if (spaceId) url += `space_id=${spaceId}&`;
            if (status) url += `status=${status}&`;
            if (date) url += `date=${date}&`;
            
            const response = await fetch(url, {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            allReservations = data.data || data;
            
            renderAllReservations();
        } catch (error) {
            console.error('Erro ao carregar reservas:', error);
        }
    }

    function renderAllReservations() {
        const container = document.getElementById('allReservationsList');
        
        if (allReservations.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x display-4 text-muted"></i>
                    <p class="text-muted mt-3">Nenhuma reserva encontrada</p>
                </div>
            `;
            return;
        }
        
        let html = '<div class="table-responsive"><table class="table table-hover"><thead class="table-light"><tr>';
        html += '<th>Espaço</th><th>Morador</th><th>Unidade</th><th>Data</th><th>Horário</th><th>Status</th><th>Ações</th>';
        html += '</tr></thead><tbody>';
        
        allReservations.forEach(reservation => {
            const date = new Date(reservation.reservation_date).toLocaleDateString('pt-BR');
            
            let statusBadge = '';
            switch(reservation.status) {
                case 'approved':
                    statusBadge = '<span class="badge bg-success">✓ Confirmada</span>';
                    break;
                case 'pending':
                    statusBadge = '<span class="badge bg-warning">⏳ Pendente</span>';
                    break;
                case 'cancelled':
                    statusBadge = '<span class="badge bg-danger">✗ Cancelada</span>';
                    break;
            }
            
            const canDelete = reservation.status !== 'cancelled';
            
            html += `<tr>
                <td><strong>${reservation.space.name}</strong></td>
                <td>${reservation.user.name}</td>
                <td>${reservation.unit.number}${reservation.unit.block ? ' - Bloco ' + reservation.unit.block : ''}</td>
                <td>${date}</td>
                <td>${reservation.start_time} - ${reservation.end_time}</td>
                <td>${statusBadge}</td>
                <td>
                    ${canDelete ? `
                        <button class="btn btn-sm btn-danger" onclick="deleteReservationAdmin(${reservation.id}, '${reservation.user.name}', '${reservation.space.name}', '${date}')">
                            <i class="bi bi-trash"></i>
                        </button>
                    ` : '-'}
                </td>
            </tr>`;
        });
        
        html += '</tbody></table></div>';
        container.innerHTML = html;
    }

    async function deleteReservationAdmin(id, userName, spaceName, date) {
        if (!confirm(`ATENÇÃO: Cancelar reserva?\n\nMorador: ${userName}\nEspaço: ${spaceName}\nData: ${date}\n\nO morador será notificado por email e no sistema.`)) {
            return;
        }
        
        try {
            const response = await fetch(`/api/reservations/${id}`, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (response.ok) {
                alert('✅ Reserva cancelada!\n\nNotificações enviadas ao morador.');
                loadAllReservations();
            } else {
                alert('❌ ' + (result.error || 'Erro ao cancelar'));
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('❌ Erro ao cancelar reserva');
        }
    }

    function clearFilters() {
        document.getElementById('filterSpace').value = '';
        document.getElementById('filterStatus').value = '';
        document.getElementById('filterDate').value = '';
        loadAllReservations();
    }

    document.addEventListener('DOMContentLoaded', async () => {
        await loadSpaces();
        await loadAllReservations();
    });
</script>
@endpush
@endsection

