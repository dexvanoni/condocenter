@extends('layouts.app')

@section('title', 'Reservas de Espaços')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Sistema de Reservas</h2>
                <p class="text-muted mb-0">Reserva automática - 1 reserva por local por dia</p>
            </div>
            @can('make_reservations')
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#novaReservaModal">
                <i class="bi bi-calendar-plus"></i> Nova Reserva
            </button>
            @endcan
        </div>
    </div>
</div>

<!-- Minhas Reservas -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Minhas Reservas Confirmadas</h5>
            </div>
            <div class="card-body">
                <div id="myReservationsContainer">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2">Carregando suas reservas...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Espaços Disponíveis -->
<div class="row">
    <div class="col-12">
        <h4 class="mb-3">Espaços Disponíveis</h4>
    </div>
</div>

<div class="row g-4" id="spacesContainer">
    <div class="col-12 text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="text-muted mt-2">Carregando espaços...</p>
    </div>
</div>

<!-- Modal Nova Reserva -->
<div class="modal fade" id="novaReservaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nova Reserva</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="step1" style="display:block;">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Regras do Sistema:</strong>
                        <ul class="mb-0 mt-2">
                            <li>✅ Aprovação <strong>AUTOMÁTICA E IMEDIATA</strong></li>
                            <li>✅ Apenas <strong>1 reserva por local por dia</strong></li>
                            <li>💳 Taxa será cobrada via Asaas (PIX/Cartão) se houver</li>
                        </ul>
                    </div>

                    <form id="formNovaReserva">
                        <div class="mb-3">
                            <label class="form-label">Espaço *</label>
                            <select class="form-select" name="space_id" required id="spaceSelect" onchange="updateSpaceInfo()">
                                <option value="">Selecione um espaço...</option>
                            </select>
                        </div>

                        <!-- Informações do Espaço -->
                        <div id="spaceInfo" style="display:none;" class="card mb-3">
                            <div class="card-body">
                                <h6>Informações do Espaço:</h6>
                                <div id="spaceDetails"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Data da Reserva *</label>
                            <input type="date" class="form-control" name="reservation_date" required 
                                   min="{{ date('Y-m-d') }}" id="reservationDate" onchange="checkAvailability()">
                            <div id="availabilityMessage"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observações</label>
                            <textarea class="form-control" name="notes" rows="3" 
                                      placeholder="Informações adicionais sobre o uso..."></textarea>
                        </div>

                        <div id="chargeInfo" style="display:none;" class="alert alert-warning">
                            <i class="bi bi-credit-card"></i>
                            <strong>Cobrança:</strong> Será gerada uma cobrança de <strong id="chargeAmount">R$ 0,00</strong> 
                            via Asaas (PIX/Cartão) com vencimento 1 dia antes da reserva.
                        </div>
                    </form>
                </div>

                <div id="step2" style="display:none;">
                    <div class="alert alert-success text-center">
                        <i class="bi bi-check-circle display-4 d-block mb-3"></i>
                        <h4>Reserva Confirmada Automaticamente!</h4>
                        <p class="mb-0" id="confirmationMessage"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="resetReservationForm()">
                    Fechar
                </button>
                <button type="button" class="btn btn-primary" id="btnConfirm" onclick="criarReserva()">
                    <i class="bi bi-check-circle"></i> Confirmar Reserva
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let spaces = [];
    let reservations = [];
    let selectedSpace = null;

    // Carregar espaços ao iniciar
    async function loadSpaces() {
        try {
            const response = await fetch('/api/spaces', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            spaces = data;
            
            renderSpaces();
            populateSpaceSelect();
        } catch (error) {
            console.error('Erro ao carregar espaços:', error);
        }
    }

    function renderSpaces() {
        const container = document.getElementById('spacesContainer');
        
        if (spaces.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <p class="text-muted mt-3">Nenhum espaço disponível para reserva</p>
                </div>
            `;
            return;
        }
        
        let html = '';
        
        spaces.forEach(space => {
            const price = space.price_per_hour > 0 
                ? `R$ ${parseFloat(space.price_per_hour).toFixed(2).replace('.', ',')}` 
                : 'GRATUITO';
            
            html += `
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">${space.name}</h5>
                        </div>
                        <div class="card-body">
                            ${space.description ? `<p class="text-muted">${space.description}</p>` : ''}
                            
                            <div class="mb-2">
                                <i class="bi bi-cash"></i> <strong>Taxa:</strong> ${price}
                            </div>
                            
                            ${space.capacity ? `
                            <div class="mb-2">
                                <i class="bi bi-people"></i> <strong>Capacidade:</strong> ${space.capacity} pessoas
                            </div>
                            ` : ''}
                            
                            <div class="mb-2">
                                <i class="bi bi-calendar-check"></i> <strong>Limite:</strong> ${space.max_reservations_per_month_per_user}x/mês
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <button class="btn btn-primary w-100" onclick="openReservationModal(${space.id})">
                                <i class="bi bi-calendar-plus"></i> Reservar
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }

    function populateSpaceSelect() {
        const select = document.getElementById('spaceSelect');
        select.innerHTML = '<option value="">Selecione um espaço...</option>';
        
        spaces.forEach(space => {
            const price = space.price_per_hour > 0 
                ? ` - R$ ${parseFloat(space.price_per_hour).toFixed(2).replace('.', ',')}` 
                : ' - GRATUITO';
            select.innerHTML += `<option value="${space.id}">${space.name}${price}</option>`;
        });
    }

    function openReservationModal(spaceId) {
        document.getElementById('spaceSelect').value = spaceId;
        updateSpaceInfo();
        
        const modal = new bootstrap.Modal(document.getElementById('novaReservaModal'));
        modal.show();
    }

    function updateSpaceInfo() {
        const spaceId = document.getElementById('spaceSelect').value;
        
        if (!spaceId) {
            document.getElementById('spaceInfo').style.display = 'none';
            document.getElementById('chargeInfo').style.display = 'none';
            return;
        }
        
        selectedSpace = spaces.find(s => s.id == spaceId);
        
        if (selectedSpace) {
            const price = selectedSpace.price_per_hour > 0 
                ? `R$ ${parseFloat(selectedSpace.price_per_hour).toFixed(2).replace('.', ',')}` 
                : 'GRATUITO';
            
            document.getElementById('spaceDetails').innerHTML = `
                <p class="mb-1"><strong>Taxa:</strong> ${price}</p>
                ${selectedSpace.capacity ? `<p class="mb-1"><strong>Capacidade:</strong> ${selectedSpace.capacity} pessoas</p>` : ''}
                <p class="mb-1"><strong>Horário:</strong> ${selectedSpace.available_from} às ${selectedSpace.available_until}</p>
                <p class="mb-0"><strong>Limite:</strong> ${selectedSpace.max_reservations_per_month_per_user} reserva(s) por mês</p>
            `;
            
            document.getElementById('spaceInfo').style.display = 'block';
            
            // Mostrar info de cobrança se houver taxa
            if (selectedSpace.price_per_hour > 0) {
                document.getElementById('chargeAmount').textContent = price;
                document.getElementById('chargeInfo').style.display = 'block';
            } else {
                document.getElementById('chargeInfo').style.display = 'none';
            }
        }
    }

    async function checkAvailability() {
        const spaceId = document.getElementById('spaceSelect').value;
        const date = document.getElementById('reservationDate').value;
        const msgDiv = document.getElementById('availabilityMessage');
        
        if (!spaceId || !date) {
            msgDiv.innerHTML = '';
            return;
        }
        
        try {
            // Verificar se já existe reserva neste dia
            const response = await fetch(`/api/reservations?space_id=${spaceId}&date=${date}`, {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            const existingReservations = data.data || data;
            
            if (existingReservations.length > 0) {
                msgDiv.innerHTML = `
                    <div class="alert alert-danger mt-2">
                        <i class="bi bi-x-circle"></i>
                        <strong>Data indisponível!</strong> Este espaço já está reservado para esta data.
                        Escolha outra data.
                    </div>
                `;
                document.getElementById('btnConfirm').disabled = true;
            } else {
                msgDiv.innerHTML = `
                    <div class="alert alert-success mt-2">
                        <i class="bi bi-check-circle"></i>
                        <strong>Data disponível!</strong> Você pode confirmar a reserva.
                    </div>
                `;
                document.getElementById('btnConfirm').disabled = false;
            }
        } catch (error) {
            console.error('Erro ao verificar disponibilidade:', error);
        }
    }

    async function criarReserva() {
        const formData = new FormData(document.getElementById('formNovaReserva'));
        const data = {
            space_id: formData.get('space_id'),
            reservation_date: formData.get('reservation_date'),
            notes: formData.get('notes')
        };
        
        if (!data.space_id || !data.reservation_date) {
            alert('Por favor, preencha todos os campos obrigatórios');
            return;
        }
        
        try {
            const response = await fetch('/api/reservations', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (response.ok) {
                // Mostrar confirmação
                document.getElementById('step1').style.display = 'none';
                document.getElementById('step2').style.display = 'block';
                document.getElementById('btnConfirm').style.display = 'none';
                
                let msg = `Espaço: <strong>${selectedSpace.name}</strong><br>`;
                msg += `Data: <strong>${new Date(data.reservation_date).toLocaleDateString('pt-BR')}</strong><br>`;
                
                if (result.has_charge) {
                    msg += `<br><div class="alert alert-warning mt-3">
                        <i class="bi bi-credit-card"></i> 
                        Uma cobrança de <strong>R$ ${parseFloat(result.amount).toFixed(2).replace('.', ',')}</strong> 
                        será gerada via Asaas. Você receberá o link de pagamento (PIX/Cartão) em breve.
                    </div>`;
                }
                
                document.getElementById('confirmationMessage').innerHTML = msg;
                
                // Recarregar após 3 segundos
                setTimeout(() => {
                    location.reload();
                }, 3000);
            } else {
                if (result.error) {
                    alert(result.error);
                } else if (result.errors) {
                    alert('Erro de validação: ' + JSON.stringify(result.errors));
                }
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao criar reserva. Tente novamente.');
        }
    }

    function resetReservationForm() {
        document.getElementById('step1').style.display = 'block';
        document.getElementById('step2').style.display = 'none';
        document.getElementById('btnConfirm').style.display = 'inline-block';
        document.getElementById('formNovaReserva').reset();
        document.getElementById('spaceInfo').style.display = 'none';
        document.getElementById('chargeInfo').style.display = 'none';
        document.getElementById('availabilityMessage').innerHTML = '';
        document.getElementById('btnConfirm').disabled = false;
    }

    async function loadMyReservations() {
        try {
            const response = await fetch('/api/reservations', {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            const myReservations = (data.data || data).filter(r => r.status === 'approved' || r.status === 'pending');
            
            const container = document.getElementById('myReservationsContainer');
            
            if (myReservations.length === 0) {
                container.innerHTML = `
                    <p class="text-muted text-center mb-0">Você não tem reservas agendadas</p>
                `;
                return;
            }
            
            let html = '<div class="row g-3">';
            
            myReservations.forEach(res => {
                const date = new Date(res.reservation_date).toLocaleDateString('pt-BR');
                const badge = res.status === 'approved' ? 'success' : 'warning';
                
                html += `
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="mb-1">${res.space.name}</h5>
                                        <p class="mb-1">
                                            <i class="bi bi-calendar"></i> ${date}<br>
                                            <i class="bi bi-clock"></i> Dia inteiro
                                        </p>
                                        ${res.notes ? `<small class="text-muted">${res.notes}</small>` : ''}
                                    </div>
                                    <span class="badge bg-${badge}">
                                        ${res.status === 'approved' ? '✓ Confirmada' : 'Pendente'}
                                    </span>
                                </div>
                                <div class="mt-2 pt-2 border-top">
                                    <button class="btn btn-sm btn-outline-danger w-100" onclick="cancelReservation(${res.id})">
                                        <i class="bi bi-x-circle"></i> Cancelar Reserva
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
            
        } catch (error) {
            console.error('Erro ao carregar reservas:', error);
        }
    }

    async function cancelReservation(id) {
        if (!confirm('Tem certeza que deseja cancelar esta reserva?')) {
            return;
        }
        
        try {
            await fetch(`/api/reservations/${id}`, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            alert('Reserva cancelada com sucesso!');
            location.reload();
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao cancelar reserva');
        }
    }

    // Carregar dados ao iniciar
    document.addEventListener('DOMContentLoaded', () => {
        loadSpaces();
        loadMyReservations();
    });
</script>
@endpush
@endsection
