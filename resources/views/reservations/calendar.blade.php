@extends('layouts.app')

@section('title', 'Reservas - Calendário')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2><i class="bi bi-calendar-event"></i> Reservar Espaço</h2>
        <p class="text-muted">Selecione uma data no calendário e escolha o espaço desejado</p>
    </div>
</div>

<!-- Acordeão: Minhas Reservas -->
<div class="row mb-4">
    <div class="col-12">
        <div class="accordion" id="accordionReservations">
            <div class="accordion-item border-primary">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed bg-primary text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReservations" aria-expanded="false" aria-controls="collapseReservations">
                        <i class="bi bi-bookmark-check me-2"></i> <strong>Minhas Reservas</strong>
                        <span class="badge bg-warning text-dark ms-2" id="reservationsCount">0</span>
                    </button>
                </h2>
                <div id="collapseReservations" class="accordion-collapse collapse" data-bs-parent="#accordionReservations">
                    <div class="accordion-body">
                        <div id="myReservationsList">
                            <div class="text-center py-3">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="text-muted mt-2">Carregando reservas...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs de Espaços -->
<div class="row mb-4">
    <div class="col-12">
        <ul class="nav nav-pills justify-content-center" id="spaceTabs" role="tablist">
            <!-- Tabs serão carregadas via JavaScript -->
        </ul>
    </div>
</div>

<!-- Saldo de Créditos -->
<div class="row mb-3">
    <div class="col-12">
        <div class="alert alert-success" id="creditsAlert" style="display: none;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-wallet2"></i>
                    <strong>Sua Carteira de Créditos:</strong>
                    <span id="totalCredits" class="fs-5 fw-bold ms-2">R$ 0,00</span>
                </div>
                <small class="text-muted">Créditos serão aplicados automaticamente</small>
            </div>
        </div>
    </div>
</div>

<!-- Informações do Espaço Selecionado -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card" id="spaceInfoCard" style="display: none;">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4 id="spaceName"></h4>
                        <p id="spaceDescription" class="text-muted"></p>
                    </div>
                    <div class="col-md-4 text-end">
                        <h3 class="text-primary mb-0" id="spacePrice"></h3>
                        <small class="text-muted">por reserva</small>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-4">
                        <i class="bi bi-people"></i> <strong>Capacidade:</strong> <span id="spaceCapacity">-</span>
                    </div>
                    <div class="col-md-4">
                        <i class="bi bi-clock"></i> <strong>Horário:</strong> <span id="spaceHours">-</span>
                    </div>
                    <div class="col-md-4">
                        <i class="bi bi-calendar-check"></i> <strong>Limite:</strong> <span id="spaceLimit">-</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Calendário -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Seleção de Horário (para espaços hourly) -->
<div class="modal fade" id="hourlyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Selecione o Horário</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-clock"></i>
                    <strong>Escolha o horário desejado:</strong>
                    <p class="mb-0 mt-2">Máximo de <span id="maxHoursAllowed"></span> horas por reserva</p>
                </div>
                
                <p><strong>Espaço:</strong> <span id="hourlySpaceName"></span></p>
                <p><strong>Data:</strong> <span id="hourlyDate"></span></p>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Horário Início *</label>
                        <select class="form-select" id="startTime" onchange="checkHourlyConflict()">
                            <!-- Opções serão geradas via JS -->
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Horário Término *</label>
                        <select class="form-select" id="endTime" onchange="checkHourlyConflict()">
                            <!-- Opções serão geradas via JS -->
                        </select>
                    </div>
                </div>
                
                <div id="hourlyConflictMessage"></div>
                
                <div id="hourlyTimeline" class="mb-3">
                    <!-- Timeline visual dos horários -->
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Observações (opcional)</label>
                    <textarea class="form-control" id="hourlyNotes" rows="3"></textarea>
                </div>
                
                <div class="alert alert-light border">
                    <strong>Valor:</strong> <span id="hourlyPrice" class="text-success fw-bold"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmHourly" onclick="createHourlyReservation()">
                    <i class="bi bi-check-circle"></i> Confirmar Reserva
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação (para espaços full_day) -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Confirmar Reserva</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Detalhes da Reserva:</strong>
                </div>
                <p><strong>Espaço:</strong> <span id="confirmSpaceName"></span></p>
                <p><strong>Data:</strong> <span id="confirmDate"></span></p>
                <p><strong>Horário:</strong> <span id="confirmHours"></span></p>
                <p><strong>Valor:</strong> <span id="confirmPrice" class="text-success fw-bold"></span></p>
                
                <div class="mb-3">
                    <label class="form-label">Observações (opcional)</label>
                    <textarea class="form-control" id="reservationNotes" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="createReservation()">
                    <i class="bi bi-check-circle"></i> Confirmar Reserva
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Pagamento Asaas -->
<div class="modal fade" id="paymentModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle"></i> Reserva Confirmada!
                </h5>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <h5>✅ Sua reserva foi confirmada automaticamente!</h5>
                    <p class="mb-0">Para garantir sua reserva, efetue o pagamento abaixo:</p>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Espaço:</strong> <span id="paymentSpaceName"></span></p>
                        <p><strong>Data:</strong> <span id="paymentDate"></span></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <h3 class="text-success mb-0" id="paymentAmount"></h3>
                        <small class="text-muted">Vencimento: <span id="paymentDueDate"></span></small>
                    </div>
                </div>

                <!-- Tabs de Métodos de Pagamento -->
                <ul class="nav nav-tabs mb-3" id="paymentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pix-tab" data-bs-toggle="tab" data-bs-target="#pix" type="button">
                            <i class="bi bi-qr-code"></i> PIX
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="card-tab" data-bs-toggle="tab" data-bs-target="#card" type="button">
                            <i class="bi bi-credit-card"></i> Cartão de Crédito
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="boleto-tab" data-bs-toggle="tab" data-bs-target="#boleto" type="button">
                            <i class="bi bi-file-text"></i> Boleto
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="paymentTabContent">
                    <!-- PIX -->
                    <div class="tab-pane fade show active" id="pix" role="tabpanel">
                        <div class="text-center">
                            <h5 class="mb-3">Pague com PIX</h5>
                            <div id="pixQRCode" class="mb-3">
                                <!-- QR Code será inserido aqui -->
                            </div>
                            <div class="alert alert-warning">
                                <strong>Ou copie o código PIX:</strong>
                            </div>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" id="pixCopyPaste" readonly>
                                <button class="btn btn-primary" type="button" onclick="copyPixCode()">
                                    <i class="bi bi-clipboard"></i> Copiar
                                </button>
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> 
                                Após o pagamento, sua reserva será confirmada automaticamente em alguns minutos.
                            </small>
                        </div>
                    </div>

                    <!-- Cartão de Crédito -->
                    <div class="tab-pane fade" id="card" role="tabpanel">
                        <div class="alert alert-info">
                            <i class="bi bi-credit-card"></i>
                            <strong>Pagamento via Cartão de Crédito</strong>
                        </div>
                        <div id="cardPaymentLink">
                            <a href="#" id="cardPaymentUrl" target="_blank" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-credit-card"></i> Pagar com Cartão de Crédito
                            </a>
                            <p class="text-muted mt-2 text-center">
                                <small>Você será redirecionado para a página segura do Asaas</small>
                            </p>
                        </div>
                    </div>

                    <!-- Boleto -->
                    <div class="tab-pane fade" id="boleto" role="tabpanel">
                        <div class="alert alert-info">
                            <i class="bi bi-file-text"></i>
                            <strong>Pagamento via Boleto Bancário</strong>
                        </div>
                        <div id="boletoLink">
                            <a href="#" id="boletoUrl" target="_blank" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-download"></i> Baixar Boleto
                            </a>
                            <p class="text-muted mt-2 text-center">
                                <small>Vencimento: <span id="boletoDueDate"></span></small>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="alert alert-light border mt-3">
                    <strong>Código da Cobrança:</strong> <span id="chargeId" class="font-monospace"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closePaymentModal()">
                    Fechar
                </button>
                <a href="{{ route('charges.index') }}" class="btn btn-primary">
                    Ver Minhas Cobranças
                </a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<style>
    .fc-event {
        cursor: pointer;
    }
    .fc-event-unavailable {
        background-color: #dc3545 !important;
        border-color: #dc3545 !important;
        opacity: 0.7;
        cursor: not-allowed;
    }
    .fc-event-available {
        background-color: #28a745 !important;
        border-color: #28a745 !important;
    }
    .fc-event-hourly-occupied {
        background-color: #ffc107 !important;
        border-color: #ffc107 !important;
        color: #000 !important;
        font-weight: bold;
        padding: 2px 4px;
        border-radius: 3px;
        cursor: help;
    }
    .fc-event-hourly-occupied .fc-event-title {
        color: #000 !important;
        font-size: 0.85em;
    }
    .nav-pills .nav-link {
        margin: 0 5px;
    }
    .nav-pills .nav-link.active {
        background-color: #0d6efd;
    }
    #pixQRCode img {
        max-width: 300px;
        border: 2px solid #ddd;
        padding: 10px;
        border-radius: 8px;
    }
    
    /* Acordeão de Reservas */
    .accordion-button.bg-primary:not(.collapsed) {
        background-color: #0d6efd !important;
        color: white !important;
    }
    .accordion-button.bg-primary {
        background-color: #0d6efd !important;
        color: white !important;
    }
    .accordion-button.bg-primary:focus {
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .accordion-button.bg-primary::after {
        filter: brightness(0) invert(1);
    }
    #reservationsCount {
        font-size: 0.85rem;
        padding: 0.25rem 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/pt-br.global.min.js"></script>
<script>
    let calendar;
    let spaces = [];
    let selectedSpace = null;
    let selectedDate = null;
    let reservations = [];
    let currentReservation = null;
    let userCredits = 0;

    // Carregar espaços ao iniciar
    async function loadSpaces() {
        try {
            const response = await fetch('/api/spaces', {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) throw new Error('Erro ao carregar espaços');
            
            spaces = await response.json();
            renderSpaceTabs();
            
            if (spaces.length > 0) {
                selectSpace(spaces[0].id);
            }
        } catch (error) {
            console.error('Erro ao carregar espaços:', error);
            alert('Erro ao carregar espaços. Recarregue a página.');
        }
    }

    // Renderizar tabs de espaços
    function renderSpaceTabs() {
        const tabsContainer = document.getElementById('spaceTabs');
        tabsContainer.innerHTML = '';
        
        spaces.forEach((space, index) => {
            const li = document.createElement('li');
            li.className = 'nav-item';
            li.role = 'presentation';
            
            const button = document.createElement('button');
            button.className = `nav-link ${index === 0 ? 'active' : ''}`;
            button.type = 'button';
            button.innerHTML = `<i class="bi bi-building"></i> ${space.name}`;
            button.onclick = () => selectSpace(space.id);
            
            li.appendChild(button);
            tabsContainer.appendChild(li);
        });
    }

    // Selecionar espaço
    async function selectSpace(spaceId) {
        selectedSpace = spaces.find(s => s.id == spaceId);
        
        if (!selectedSpace) return;
        
        // Atualizar informações do espaço
        document.getElementById('spaceName').textContent = selectedSpace.name;
        document.getElementById('spaceDescription').textContent = selectedSpace.description || '';
        document.getElementById('spacePrice').textContent = selectedSpace.price_per_hour > 0 
            ? `R$ ${parseFloat(selectedSpace.price_per_hour).toFixed(2).replace('.', ',')}` 
            : 'GRATUITO';
        document.getElementById('spaceCapacity').textContent = selectedSpace.capacity ? `${selectedSpace.capacity} pessoas` : 'Não informado';
        document.getElementById('spaceHours').textContent = `${selectedSpace.available_from} às ${selectedSpace.available_until}`;
        document.getElementById('spaceLimit').textContent = `${selectedSpace.max_reservations_per_month_per_unit}x por mês`;
        
        document.getElementById('spaceInfoCard').style.display = 'block';
        
        // Atualizar tabs
        document.querySelectorAll('#spaceTabs .nav-link').forEach(btn => btn.classList.remove('active'));
        event?.target?.classList.add('active');
        
        // Carregar reservas deste espaço
        await loadReservations(spaceId);
        
        // Atualizar calendário
        if (calendar) {
            calendar.refetchEvents();
        }
    }

    // Carregar reservas (disponibilidade geral - TODAS as reservas do espaço)
    async function loadReservations(spaceId) {
        try {
            // Usar endpoint de disponibilidade que retorna TODAS as reservas (sem dados pessoais)
            const response = await fetch(`/api/reservations/availability/${spaceId}`, {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            // Armazenar slots ocupados (TODAS as reservas, não apenas as minhas)
            reservations = data.occupied_slots || [];
            
            console.log('Disponibilidade do espaço carregada:', spaceId, 'Slots ocupados:', reservations.length);
        } catch (error) {
            console.error('Erro ao carregar disponibilidade:', error);
            reservations = [];
        }
    }

    // Inicializar calendário
    function initCalendar() {
        const calendarEl = document.getElementById('calendar');
        
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'pt-br',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek'
            },
            buttonText: {
                today: 'Hoje',
                month: 'Mês',
                week: 'Semana'
            },
            selectable: true,
            selectMirror: true,
            dayMaxEvents: true,
            validRange: {
                start: new Date().toISOString().split('T')[0]
            },
            dateClick: function(info) {
                handleDateClick(info.dateStr);
            },
            events: function(fetchInfo, successCallback, failureCallback) {
                console.log('Carregando eventos para o calendário. Reservas:', reservations);
                console.log('Modo do espaço:', selectedSpace?.reservation_mode);
                
                const events = [];
                
                if (selectedSpace?.reservation_mode === 'hourly') {
                    // MODO HORÁRIO: Mostrar horários específicos em AMARELO
                    reservations.forEach(reservation => {
                        const eventDate = reservation.reservation_date.split('T')[0];
                        const startTime = reservation.start_time.substring(0, 5); // "HH:MM"
                        const endTime = reservation.end_time.substring(0, 5);     // "HH:MM"
                        
                        events.push({
                            title: `${startTime} às ${endTime}`,
                            start: eventDate,
                            allDay: true,
                            backgroundColor: '#ffc107', // Amarelo
                            borderColor: '#ffc107',
                            textColor: '#000',
                            classNames: ['fc-event-hourly-occupied'],
                            extendedProps: {
                                reservation: reservation,
                                isReserved: true,
                                startTime: startTime,
                                endTime: endTime
                            }
                        });
                    });
                } else {
                    // MODO DIA INTEIRO: Mostrar "Indisponível" em VERMELHO
                    reservations.forEach(reservation => {
                        const eventDate = reservation.reservation_date.split('T')[0];
                        
                        events.push({
                            title: 'Indisponível',
                            start: eventDate,
                            allDay: true,
                            display: 'background',
                            backgroundColor: '#dc3545', // Vermelho
                            borderColor: '#dc3545',
                            classNames: ['fc-event-unavailable'],
                            extendedProps: {
                                reservation: reservation,
                                isReserved: true
                            }
                        });
                    });
                }
                
                console.log('Total de eventos para calendário:', events.length, events);
                successCallback(events);
            }
        });
        
        calendar.render();
    }

    // Manipular clique em data
    function handleDateClick(dateStr) {
        if (!selectedSpace) {
            alert('Selecione um espaço primeiro');
            return;
        }
        
        const dateOnly = dateStr.split('T')[0];
        
        // Comportamento diferente por modo de reserva
        if (selectedSpace.reservation_mode === 'full_day') {
            // MODO DIA INTEIRO: Verificar se dia está ocupado
            const isDayOccupied = reservations.some(r => {
                const reservDateOnly = r.reservation_date.split('T')[0];
                return reservDateOnly === dateOnly;
            });
            
            if (isDayOccupied) {
                alert('❌ Esta data já está reservada para este espaço.\n\nEscolha outra data.');
                return; // NÃO ABRE MODAL
            }
            
            selectedDate = dateStr;
            showConfirmModal(dateStr);
            
        } else {
            // MODO HORÁRIO: Sempre abre modal (usuário escolhe horário disponível)
            selectedDate = dateStr;
            showHourlyModal(dateStr);
        }
    }

    // Mostrar modal de confirmação
    function showConfirmModal(dateStr) {
        // Evitar problema de timezone
        const [year, month, day] = dateStr.split('-');
        const date = new Date(parseInt(year), parseInt(month) - 1, parseInt(day));
        const formattedDate = date.toLocaleDateString('pt-BR');
        
        document.getElementById('confirmSpaceName').textContent = selectedSpace.name;
        document.getElementById('confirmDate').textContent = formattedDate;
        document.getElementById('confirmHours').textContent = `${selectedSpace.available_from} às ${selectedSpace.available_until}`;
        document.getElementById('confirmPrice').textContent = selectedSpace.price_per_hour > 0 
            ? `R$ ${parseFloat(selectedSpace.price_per_hour).toFixed(2).replace('.', ',')}` 
            : 'GRATUITO';
        
        // Usar Bootstrap via window ou criar manualmente
        const modalEl = document.getElementById('confirmModal');
        let modal = window.bootstrap?.Modal.getInstance(modalEl);
        if (!modal) {
            modal = new window.bootstrap.Modal(modalEl);
        }
        modal.show();
    }

    // Criar reserva
    async function createReservation() {
        const notes = document.getElementById('reservationNotes').value;
        
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
                body: JSON.stringify({
                    space_id: selectedSpace.id,
                    reservation_date: selectedDate,
                    notes: notes
                })
            });
            
            const result = await response.json();
            
            if (response.ok) {
                // Fechar modal de confirmação
                const confirmModalEl = document.getElementById('confirmModal');
                const confirmModal = window.bootstrap?.Modal.getInstance(confirmModalEl);
                if (confirmModal) {
                    confirmModal.hide();
                }
                
                currentReservation = result.reservation;
                
                // Atualizar créditos do usuário
                if (result.total_user_credits !== undefined) {
                    updateCreditsDisplay(result.total_user_credits);
                }
                
                // Mensagens personalizadas
                let successMsg = result.message;
                
                if (result.credit_used) {
                    successMsg += `\n\n💰 Créditos utilizados: R$ ${parseFloat(result.credit_amount).toFixed(2).replace('.', ',')}`;
                }
                
                // Se tem cobrança restante, mostrar modal de pagamento
                if (result.has_charge && result.payment_data) {
                    alert(successMsg);
                    showPaymentModal(result.payment_data, result.reservation);
                } else {
                    alert(successMsg);
                    location.reload();
                }
            } else {
                alert(result.error || 'Erro ao criar reserva');
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao criar reserva. Tente novamente.');
        }
    }

    // Mostrar modal de pagamento
    function showPaymentModal(paymentData, reservation) {
        // Evitar problema de timezone
        const dateStr = reservation.reservation_date.split('T')[0];
        const [year, month, day] = dateStr.split('-');
        const date = new Date(parseInt(year), parseInt(month) - 1, parseInt(day));
        
        const dueDateStr = paymentData.due_date.split('T')[0];
        const [dueYear, dueMonth, dueDay] = dueDateStr.split('-');
        const dueDate = new Date(parseInt(dueYear), parseInt(dueMonth) - 1, parseInt(dueDay));
        
        document.getElementById('paymentSpaceName').textContent = selectedSpace.name;
        document.getElementById('paymentDate').textContent = date.toLocaleDateString('pt-BR');
        document.getElementById('paymentAmount').textContent = `R$ ${parseFloat(paymentData.value).toFixed(2).replace('.', ',')}`;
        document.getElementById('paymentDueDate').textContent = dueDate.toLocaleDateString('pt-BR');
        
        // PIX
        if (paymentData.pix_qrcode) {
            document.getElementById('pixQRCode').innerHTML = `<img src="data:image/png;base64,${paymentData.pix_qrcode}" alt="QR Code PIX">`;
        }
        if (paymentData.pix_code) {
            document.getElementById('pixCopyPaste').value = paymentData.pix_code;
        }
        
        // Cartão
        if (paymentData.invoice_url) {
            document.getElementById('cardPaymentUrl').href = paymentData.invoice_url;
        }
        
        // Boleto
        if (paymentData.boleto_url) {
            document.getElementById('boletoUrl').href = paymentData.boleto_url;
            document.getElementById('boletoDueDate').textContent = dueDate.toLocaleDateString('pt-BR');
        }
        
        document.getElementById('chargeId').textContent = paymentData.id || '-';
        
        const modalEl = document.getElementById('paymentModal');
        let modal = window.bootstrap?.Modal.getInstance(modalEl);
        if (!modal) {
            modal = new window.bootstrap.Modal(modalEl);
        }
        modal.show();
    }

    // Copiar código PIX
    function copyPixCode() {
        const input = document.getElementById('pixCopyPaste');
        input.select();
        document.execCommand('copy');
        
        alert('Código PIX copiado para a área de transferência!');
    }

    // Fechar modal de pagamento
    function closePaymentModal() {
        const modalEl = document.getElementById('paymentModal');
        const modal = window.bootstrap?.Modal.getInstance(modalEl);
        if (modal) {
            modal.hide();
        }
        location.reload();
    }

    // Carregar minhas reservas
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
            console.log('Dados recebidos da API /reservations:', data);
            
            const allReservations = data.data || data;
            console.log('Total de reservas na API:', allReservations.length);
            
            // Comparar apenas strings de data (YYYY-MM-DD)
            const todayStr = new Date().toISOString().split('T')[0];
            console.log('Data de hoje (string):', todayStr);
            
            const myReservations = allReservations.filter(r => {
                // Extrair apenas a parte da data (YYYY-MM-DD)
                const reservDateStr = r.reservation_date.split('T')[0];
                const isFutureOrToday = reservDateStr >= todayStr;
                const isActiveStatus = ['approved', 'pending'].includes(r.status);
                
                console.log(`Reserva ${r.id}: Data=${reservDateStr}, Hoje=${todayStr}, IsFutureOrToday=${isFutureOrToday}, IsActive=${isActiveStatus}`);
                
                return isActiveStatus && isFutureOrToday;
            });
            
            console.log('Minhas reservas filtradas:', myReservations.length);
            renderMyReservations(myReservations);
        } catch (error) {
            console.error('Erro ao carregar reservas:', error);
            document.getElementById('myReservationsList').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> Erro ao carregar reservas
                </div>
            `;
        }
    }

    // Renderizar minhas reservas
    function renderMyReservations(reservations) {
        const container = document.getElementById('myReservationsList');
        const countBadge = document.getElementById('reservationsCount');
        
        console.log('Renderizando minhas reservas:', reservations.length, reservations);
        
        // Atualizar badge de contagem
        countBadge.textContent = reservations.length;
        
        if (reservations.length === 0) {
            countBadge.classList.remove('bg-warning');
            countBadge.classList.add('bg-secondary');
            
            container.innerHTML = `
                <div class="text-center py-4">
                    <i class="bi bi-calendar-x display-4 text-muted"></i>
                    <p class="text-muted mt-2">Você não tem reservas futuras</p>
                </div>
            `;
            return;
        }
        
        countBadge.classList.remove('bg-secondary');
        countBadge.classList.add('bg-warning');
        
        let html = '<div class="row g-3">';
        
        reservations.forEach(reservation => {
            // Evitar problema de timezone: usar a data como string YYYY-MM-DD
            const dateStr = reservation.reservation_date.split('T')[0]; // "2025-10-07"
            const [year, month, day] = dateStr.split('-');
            
            // Criar data local sem conversão de timezone
            const date = new Date(parseInt(year), parseInt(month) - 1, parseInt(day));
            const formattedDate = date.toLocaleDateString('pt-BR', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            
            const statusBadge = reservation.status === 'approved' 
                ? '<span class="badge bg-success">✓ Confirmada</span>' 
                : '<span class="badge bg-warning">⏳ Pendente</span>';
            
            const escapedSpaceName = reservation.space.name.replace(/'/g, "\\'");
            const escapedDate = formattedDate.replace(/'/g, "\\'");
            
            html += `
                <div class="col-md-6">
                    <div class="card h-100 border-start border-primary border-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-building"></i> ${reservation.space.name}
                                </h5>
                                ${statusBadge}
                            </div>
                            <p class="card-text">
                                <i class="bi bi-calendar-event"></i> ${formattedDate}<br>
                                <i class="bi bi-clock"></i> ${reservation.start_time} às ${reservation.end_time}
                            </p>
                            ${reservation.notes ? `<p class="text-muted small"><i class="bi bi-chat-left-text"></i> ${reservation.notes}</p>` : ''}
                            <div class="d-grid gap-2">
                                <button class="btn btn-danger btn-sm" onclick="deleteReservation(${reservation.id}, '${escapedSpaceName}', '${escapedDate}')">
                                    <i class="bi bi-trash"></i> Cancelar Reserva
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        container.innerHTML = html;
    }

    // Deletar reserva
    async function deleteReservation(reservationId, spaceName, dateStr) {
        if (!confirm(`Tem certeza que deseja cancelar a reserva?\n\nEspaço: ${spaceName}\nData: ${dateStr}\n\nEsta ação não pode ser desfeita.`)) {
            return;
        }
        
        try {
            const response = await fetch(`/api/reservations/${reservationId}`, {
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
                let cancelMsg = '✅ Reserva cancelada com sucesso!\n\nNotificações foram enviadas aos envolvidos.';
                
                if (result.credit_generated) {
                    cancelMsg += `\n\n💰 Como você já havia pago, geramos um crédito de R$ ${parseFloat(result.credit_amount).toFixed(2).replace('.', ',')} na sua carteira!`;
                    cancelMsg += '\n\n✨ Use este crédito em futuras reservas (válido por 12 meses).';
                    
                    // Recarregar créditos
                    await loadUserCredits();
                } else if (result.charge_deleted) {
                    cancelMsg += '\n\n📄 A cobrança pendente foi removida.';
                }
                
                alert(cancelMsg);
                
                // Recarregar reservas e calendário
                await loadMyReservations();
                if (selectedSpace) {
                    await loadReservations(selectedSpace.id);
                    calendar.refetchEvents();
                }
            } else {
                alert('❌ ' + (result.error || 'Erro ao cancelar reserva'));
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('❌ Erro ao cancelar reserva. Tente novamente.');
        }
    }

    // Mostrar modal de seleção de horário
    function showHourlyModal(dateStr) {
        const [year, month, day] = dateStr.split('-');
        const date = new Date(parseInt(year), parseInt(month) - 1, parseInt(day));
        const formattedDate = date.toLocaleDateString('pt-BR');
        
        document.getElementById('hourlySpaceName').textContent = selectedSpace.name;
        document.getElementById('hourlyDate').textContent = formattedDate;
        document.getElementById('maxHoursAllowed').textContent = selectedSpace.max_hours_per_reservation;
        document.getElementById('hourlyPrice').textContent = selectedSpace.price_per_hour > 0 
            ? `R$ ${parseFloat(selectedSpace.price_per_hour).toFixed(2).replace('.', ',')} por hora` 
            : 'GRATUITO';
        
        // Gerar opções de horário
        generateTimeOptions();
        
        // Renderizar timeline
        renderHourlyTimeline(dateStr);
        
        const modalEl = document.getElementById('hourlyModal');
        let modal = window.bootstrap?.Modal.getInstance(modalEl);
        if (!modal) {
            modal = new window.bootstrap.Modal(modalEl);
        }
        modal.show();
    }

    // Gerar opções de horário
    function generateTimeOptions() {
        const startSelect = document.getElementById('startTime');
        const endSelect = document.getElementById('endTime');
        
        startSelect.innerHTML = '<option value="">Selecione...</option>';
        endSelect.innerHTML = '<option value="">Selecione...</option>';
        
        const [startHour, startMin] = selectedSpace.available_from.split(':').map(Number);
        const [endHour, endMin] = selectedSpace.available_until.split(':').map(Number);
        
        // Gerar opções de 30 em 30 minutos
        for (let h = startHour; h <= endHour; h++) {
            for (let m = 0; m < 60; m += 30) {
                const totalMinutes = h * 60 + m;
                const limitMinutes = endHour * 60 + endMin;
                
                if (totalMinutes >= startHour * 60 + startMin && totalMinutes < limitMinutes) {
                    const timeStr = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
                    startSelect.innerHTML += `<option value="${timeStr}">${timeStr}</option>`;
                    endSelect.innerHTML += `<option value="${timeStr}">${timeStr}</option>`;
                }
            }
        }
        
        // Adicionar horário de fim também
        const endTimeStr = selectedSpace.available_until;
        endSelect.innerHTML += `<option value="${endTimeStr}">${endTimeStr}</option>`;
    }

    // Calcular horário de término baseado no máximo permitido
    function calculateEndTime() {
        const startTime = document.getElementById('startTime').value;
        if (!startTime) return;
        
        const [startHour, startMin] = startTime.split(':').map(Number);
        const maxHours = selectedSpace.max_hours_per_reservation;
        
        // Calcular horário máximo de término
        const maxEndMinutes = (startHour * 60 + startMin) + (maxHours * 60);
        const maxEndHour = Math.floor(maxEndMinutes / 60);
        const maxEndMin = maxEndMinutes % 60;
        const maxEndTime = `${String(maxEndHour).padStart(2, '0')}:${String(maxEndMin).padStart(2, '0')}`;
        
        // Verificar conflitos ao mudar horário de início
        checkHourlyConflict();
    }

    // Verificar conflito de horários
    function checkHourlyConflict() {
        const startTime = document.getElementById('startTime').value;
        const endTime = document.getElementById('endTime').value;
        const messageDiv = document.getElementById('hourlyConflictMessage');
        const btnConfirm = document.getElementById('btnConfirmHourly');
        
        if (!startTime || !endTime) {
            messageDiv.innerHTML = '';
            btnConfirm.disabled = true;
            return;
        }
        
        // Verificar se horário de fim é maior que início
        if (endTime <= startTime) {
            messageDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle"></i> Horário de término deve ser maior que o de início
                </div>
            `;
            btnConfirm.disabled = true;
            return;
        }
        
        // Verificar duração máxima
        const [startH, startM] = startTime.split(':').map(Number);
        const [endH, endM] = endTime.split(':').map(Number);
        const durationMinutes = (endH * 60 + endM) - (startH * 60 + startM);
        const durationHours = durationMinutes / 60;
        
        if (durationHours > selectedSpace.max_hours_per_reservation) {
            messageDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle"></i> Duração máxima permitida: ${selectedSpace.max_hours_per_reservation}h. Você selecionou: ${durationHours}h
                </div>
            `;
            btnConfirm.disabled = true;
            return;
        }
        
        if (durationHours < selectedSpace.min_hours_per_reservation) {
            messageDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle"></i> Duração mínima permitida: ${selectedSpace.min_hours_per_reservation}h
                </div>
            `;
            btnConfirm.disabled = true;
            return;
        }
        
        // Verificar conflito com outras reservas
        const dateOnly = selectedDate.split('T')[0];
        const conflicts = reservations.filter(r => {
            const reservDate = r.reservation_date.split('T')[0];
            if (reservDate !== dateOnly) return false;
            
            // Verificar sobreposição de horários
            const rStart = r.start_time;
            const rEnd = r.end_time;
            
            return (startTime >= rStart && startTime < rEnd) || 
                   (endTime > rStart && endTime <= rEnd) ||
                   (startTime <= rStart && endTime >= rEnd);
        });
        
        if (conflicts.length > 0) {
            let conflictHours = conflicts.map(c => `${c.start_time}-${c.end_time}`).join(', ');
            messageDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle"></i> <strong>Conflito de horário!</strong><br>
                    Horários já reservados: ${conflictHours}
                </div>
            `;
            btnConfirm.disabled = true;
            return;
        }
        
        // Sem conflitos!
        messageDiv.innerHTML = `
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i> Horário disponível! Duração: ${durationHours}h
            </div>
        `;
        btnConfirm.disabled = false;
    }

    // Renderizar timeline visual dos horários
    function renderHourlyTimeline(dateStr) {
        const timeline = document.getElementById('hourlyTimeline');
        const dateOnly = dateStr.split('T')[0];
        
        // Filtrar reservas deste dia
        const dayReservations = reservations.filter(r => r.reservation_date.split('T')[0] === dateOnly);
        
        if (dayReservations.length === 0) {
            timeline.innerHTML = '<p class="text-muted"><i class="bi bi-check-circle"></i> Nenhuma reserva neste dia - Todos os horários disponíveis!</p>';
            return;
        }
        
        timeline.innerHTML = '<p class="mb-2"><strong>Horários já reservados hoje:</strong></p>';
        let html = '<div class="list-group">';
        
        dayReservations.forEach(r => {
            html += `
                <div class="list-group-item list-group-item-danger d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-clock"></i> ${r.start_time} - ${r.end_time}</span>
                    <span class="badge bg-danger">Indisponível</span>
                </div>
            `;
        });
        
        html += '</div>';
        timeline.innerHTML += html;
    }

    // Criar reserva por horário
    async function createHourlyReservation() {
        const startTime = document.getElementById('startTime').value;
        const endTime = document.getElementById('endTime').value;
        const notes = document.getElementById('hourlyNotes').value;
        
        if (!startTime || !endTime) {
            alert('Por favor, selecione horário de início e término');
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
                body: JSON.stringify({
                    space_id: selectedSpace.id,
                    reservation_date: selectedDate,
                    start_time: startTime,
                    end_time: endTime,
                    notes: notes
                })
            });
            
            const result = await response.json();
            
            if (response.ok) {
                // Fechar modal de horários
                const hourlyModalEl = document.getElementById('hourlyModal');
                const hourlyModal = window.bootstrap?.Modal.getInstance(hourlyModalEl);
                if (hourlyModal) {
                    hourlyModal.hide();
                }
                
                currentReservation = result.reservation;
                
                // Atualizar créditos
                if (result.total_user_credits !== undefined) {
                    updateCreditsDisplay(result.total_user_credits);
                }
                
                // Mensagem personalizada
                let successMsg = result.message;
                
                if (result.credit_used) {
                    successMsg += `\n\n💰 Créditos utilizados: R$ ${parseFloat(result.credit_amount).toFixed(2).replace('.', ',')}`;
                }
                
                // Se tem cobrança, mostrar modal de pagamento
                if (result.has_charge && result.payment_data) {
                    alert(successMsg);
                    showPaymentModal(result.payment_data, result.reservation);
                } else {
                    alert(successMsg);
                    location.reload();
                }
            } else {
                alert(result.error || 'Erro ao criar reserva');
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao criar reserva. Tente novamente.');
        }
    }

    // Carregar créditos do usuário
    async function loadUserCredits() {
        try {
            const response = await fetch('/api/user/credits', {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            const totalCredits = data.total || 0;
            
            updateCreditsDisplay(totalCredits);
        } catch (error) {
            console.error('Erro ao carregar créditos:', error);
        }
    }

    // Atualizar display de créditos
    function updateCreditsDisplay(total) {
        userCredits = total;
        
        const alertEl = document.getElementById('creditsAlert');
        const totalEl = document.getElementById('totalCredits');
        
        if (total > 0) {
            totalEl.textContent = `R$ ${parseFloat(total).toFixed(2).replace('.', ',')}`;
            alertEl.style.display = 'block';
        } else {
            alertEl.style.display = 'none';
        }
    }

    // Inicializar ao carregar página
    document.addEventListener('DOMContentLoaded', async () => {
        await loadSpaces();
        await loadUserCredits();
        await loadMyReservations();
        initCalendar();
    });
</script>
@endpush
@endsection


