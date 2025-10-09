@extends('layouts.app')

@section('title', 'Reservas - Calendário')

@section('content')
<!-- Header Compacto -->
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-1"><i class="bi bi-calendar-event"></i> Sistema de Reservas</h3>
                <p class="text-muted mb-0 small">Selecione o espaço e a data desejada</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Saldo de Créditos Compacto -->
                <div class="alert alert-success py-2 mb-0" id="creditsAlert" style="display: none;">
                    <i class="bi bi-wallet2"></i>
                    <strong>Créditos:</strong>
                    <span id="totalCredits" class="fw-bold">R$ 0,00</span>
                </div>
                
                <!-- Badge de Reservas -->
                <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReservations">
                    <i class="bi bi-bookmark-check"></i> Minhas Reservas
                    <span class="badge bg-warning text-dark ms-1" id="reservationsCount">0</span>
                </button>
                
                @if(auth()->user()->isAdmin() || auth()->user()->isSindico())
                <a href="{{ route('recurring-reservations.index') }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-calendar-week"></i> Reservas Recorrentes
                </a>
                <a href="{{ route('admin.reservations.index') }}" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-gear"></i> Administrar Reservas
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Minhas Reservas (Colapsável) -->
<div class="row mb-3">
    <div class="col-12">
        <div class="collapse" id="collapseReservations">
            <div class="card border-primary">
                <div class="card-body p-3">
                    <div id="myReservationsList">
                        <div class="text-center py-2">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            <p class="text-muted mt-2 small">Carregando reservas...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Layout Principal: Espaços + Calendário -->
<div class="row">
    <!-- Coluna Esquerda: Seleção de Espaços -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white py-2">
                <h6 class="mb-0"><i class="bi bi-building"></i> Escolha o Espaço</h6>
            </div>
            <div class="card-body p-3">
                <!-- Barra de Progresso -->
                <div id="loadingProgress" class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted">Carregando informações...</small>
                        <small class="text-muted" id="progressText">0%</small>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                             role="progressbar" 
                             id="progressBar" 
                             style="width: 0%" 
                             aria-valuenow="0" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>
                </div>

                <!-- Tabs de Espaços Compactas -->
                <div class="mb-3" id="spaceTabsContainer" style="display: none;">
                    <ul class="nav nav-pills nav-fill" id="spaceTabs" role="tablist">
                        <!-- Tabs serão carregadas via JavaScript -->
                    </ul>
                </div>

                <!-- Informações do Espaço Selecionado -->
                <div id="spaceInfoCard" style="display: none;">
                    <div class="card bg-light">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-2" id="spaceName"></h6>
                            <p class="card-text small text-muted mb-3" id="spaceDescription"></p>
                            
                            <!-- Informações Básicas -->
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">💰 Taxa</small>
                                    <span class="fw-bold text-success" id="spacePrice">-</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">👥 Capacidade</small>
                                    <span class="fw-bold" id="spaceCapacity">-</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">🕐 Horário</small>
                                    <span class="fw-bold small" id="spaceHours">-</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">📅 Limite/Mês</small>
                                    <span class="fw-bold" id="spaceLimit">-</span>
                                </div>
                            </div>

                            <!-- Modo de Reserva -->
                            <div class="mb-3">
                                <small class="text-muted d-block">📋 Modo de Reserva</small>
                                <span class="fw-bold" id="spaceReservationMode">-</span>
                            </div>

                            <!-- Configurações de Horário (para espaços hourly) -->
                            <div id="hourlyConfig" style="display: none;" class="mb-3">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="text-muted d-block">⏱️ Duração Mín.</small>
                                        <span class="fw-bold small" id="spaceMinHours">-</span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">⏰ Duração Máx.</small>
                                        <span class="fw-bold small" id="spaceMaxHours">-</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Regras de Uso -->
                            <div id="spaceRulesContainer" style="display: none;" class="mb-3">
                                <small class="text-muted d-block mb-1">📜 Regras de Uso</small>
                                <div class="bg-white p-2 rounded border">
                                    <small class="text-muted" id="spaceRules">-</small>
                                </div>
                            </div>

                            <!-- Status do Espaço -->
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Status:</small>
                                <span id="spaceStatus" class="badge">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Coluna Direita: Calendário -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-light py-2">
                <h6 class="mb-0"><i class="bi bi-calendar3"></i> Selecione a Data</h6>
            </div>
            <div class="card-body p-3">
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
    /* Calendário Compacto */
    .fc {
        font-size: 0.85rem;
    }
    .fc-toolbar {
        margin-bottom: 0.5rem !important;
    }
    .fc-toolbar-title {
        font-size: 1.1rem !important;
        font-weight: 600;
    }
    .fc-button {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.8rem !important;
    }
    .fc-daygrid-day-number {
        padding: 0.25rem !important;
        font-size: 0.8rem;
    }
    .fc-daygrid-day-events {
        margin-top: 0.25rem !important;
    }
    .fc-event {
        cursor: pointer;
        font-size: 0.7rem !important;
        padding: 1px 3px !important;
        margin: 1px 0 !important;
        border-radius: 2px !important;
    }
    .fc-event-title {
        font-size: 0.7rem !important;
        line-height: 1.2;
    }
    .fc-daygrid-event-harness {
        margin-bottom: 1px !important;
    }
    
    /* Eventos do Calendário */
    .fc-event-unavailable {
        background-color: #dc3545 !important;
        border-color: #dc3545 !important;
        opacity: 0.8;
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
        cursor: help;
    }
    .fc-event-hourly-occupied .fc-event-title {
        color: #000 !important;
        font-size: 0.65rem !important;
    }
    
    /* Navegação de Espaços */
    .nav-pills .nav-link {
        padding: 0.4rem 0.8rem;
        font-size: 0.85rem;
        margin-bottom: 0.25rem;
        border-radius: 0.375rem;
    }
    .nav-pills .nav-link.active {
        background-color: #0d6efd;
        font-weight: 600;
    }
    .nav-pills .nav-link:hover {
        background-color: rgba(13, 110, 253, 0.1);
    }
    
    /* Cards Compactos */
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }
    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    /* Informações do Espaço */
    .card.bg-light {
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6;
    }
    
    /* QR Code */
    #pixQRCode img {
        max-width: 250px;
        border: 2px solid #ddd;
        padding: 8px;
        border-radius: 6px;
    }
    
    /* Responsividade */
    @media (max-width: 991.98px) {
        .col-lg-4 {
            margin-bottom: 1rem;
        }
        .fc-toolbar {
            flex-direction: column;
            gap: 0.5rem;
        }
        .fc-toolbar-chunk {
            display: flex;
            justify-content: center;
        }
    }
    
    /* Badge de Reservas */
    #reservationsCount {
        font-size: 0.75rem;
        padding: 0.2rem 0.4rem;
    }
    
    /* Alertas Compactos */
    .alert {
        padding: 0.5rem 0.75rem;
        margin-bottom: 0.5rem;
    }
    
    /* Minhas Reservas */
    .collapse.show .card-body {
        padding: 0.75rem;
    }
    
    /* Barra de Progresso */
    .progress {
        background-color: #e9ecef;
        border-radius: 0.375rem;
        overflow: hidden;
    }
    .progress-bar {
        background: linear-gradient(90deg, #0d6efd, #0b5ed7);
        transition: width 0.3s ease;
    }
    .progress-bar-animated {
        background-image: linear-gradient(
            45deg,
            rgba(255, 255, 255, 0.15) 25%,
            transparent 25%,
            transparent 50%,
            rgba(255, 255, 255, 0.15) 50%,
            rgba(255, 255, 255, 0.15) 75%,
            transparent 75%,
            transparent
        );
        background-size: 1rem 1rem;
        animation: progress-bar-stripes 1s linear infinite;
    }
    
    @keyframes progress-bar-stripes {
        0% {
            background-position: 1rem 0;
        }
        100% {
            background-position: 0 0;
        }
    }
    
    /* Container das tabs */
    #spaceTabsContainer {
        transition: opacity 0.3s ease;
    }
    
    /* Regras de Uso */
    #spaceRulesContainer .bg-white {
        background-color: #ffffff !important;
        border: 1px solid #dee2e6 !important;
        max-height: 120px;
        overflow-y: auto;
        font-size: 0.8rem;
        line-height: 1.4;
    }
    
    /* Status Badge */
    .badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }
    
    /* Informações do Espaço */
    .card.bg-light {
        border-left: 4px solid #0d6efd;
    }
    
    /* Emojis nos labels */
    small.text-muted {
        font-weight: 500;
    }
    
    /* Reservas Recorrentes */
    .fc-event-recurring {
        background: linear-gradient(45deg, #28a745, #20c997) !important;
        border: 2px solid #1e7e34 !important;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
    }
    
    .fc-event-recurring:hover {
        background: linear-gradient(45deg, #1e7e34, #17a2b8) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(40, 167, 69, 0.4);
    }
    
    /* Indicador visual para reservas recorrentes */
    .fc-event-recurring::before {
        content: "🔄";
        margin-right: 4px;
        font-size: 0.8em;
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
    let loadingSteps = 0;
    let totalSteps = 4; // espaços, reservas, créditos, calendário

    // Função para atualizar progresso
    function updateProgress(step, message) {
        loadingSteps = step;
        const percentage = Math.round((loadingSteps / totalSteps) * 100);
        
        document.getElementById('progressBar').style.width = percentage + '%';
        document.getElementById('progressBar').setAttribute('aria-valuenow', percentage);
        document.getElementById('progressText').textContent = percentage + '%';
        
        // Atualizar mensagem se fornecida
        if (message) {
            document.querySelector('#loadingProgress small:first-child').textContent = message;
        }
        
        // Esconder barra quando completa
        if (percentage >= 100) {
            setTimeout(() => {
                document.getElementById('loadingProgress').style.display = 'none';
                document.getElementById('spaceTabsContainer').style.display = 'block';
            }, 500);
        }
    }

    // Carregar espaços ao iniciar
    async function loadSpaces() {
        try {
            updateProgress(1, 'Carregando espaços disponíveis...');
            
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
                await selectSpace(spaces[0].id);
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
            
            // Ícones por tipo de espaço
            const typeIcons = {
                'party_hall': '🎉',
                'bbq': '🍖',
                'pool': '🏊',
                'sports_court': '⚽',
                'gym': '💪',
                'meeting_room': '🏢',
                'other': '📍'
            };
            
            const icon = typeIcons[space.type] || '📍';
            button.innerHTML = `${icon} ${space.name}`;
            button.onclick = () => selectSpace(space.id);
            
            li.appendChild(button);
            tabsContainer.appendChild(li);
        });
    }

    // Selecionar espaço
    async function selectSpace(spaceId) {
        selectedSpace = spaces.find(s => s.id == spaceId);
        
        if (!selectedSpace) return;
        
        updateProgress(2, 'Carregando informações do espaço...');
        
        // Atualizar informações do espaço
        document.getElementById('spaceName').textContent = selectedSpace.name;
        document.getElementById('spaceDescription').textContent = selectedSpace.description || '';
        document.getElementById('spacePrice').textContent = selectedSpace.price_per_hour > 0 
            ? `R$ ${parseFloat(selectedSpace.price_per_hour).toFixed(2).replace('.', ',')}` 
            : 'GRATUITO';
        document.getElementById('spaceCapacity').textContent = selectedSpace.capacity ? `${selectedSpace.capacity} pessoas` : 'Não informado';
        
        // Formatar horários para pt-BR
        const formatTime = (timeStr) => {
            if (!timeStr) return 'Não informado';
            // Se for datetime, extrair apenas a parte do tempo
            if (timeStr.includes('T')) {
                return timeStr.split('T')[1].substring(0, 5);
            }
            // Se já for apenas hora:minuto, usar como está
            return timeStr.substring(0, 5);
        };
        
        document.getElementById('spaceHours').textContent = `${formatTime(selectedSpace.available_from)} às ${formatTime(selectedSpace.available_until)}`;
        document.getElementById('spaceLimit').textContent = `${selectedSpace.max_reservations_per_month_per_unit}x por mês`;
        
        // Modo de Reserva
        const reservationModeText = selectedSpace.reservation_mode === 'full_day' 
            ? '📅 Dia Inteiro (1 reserva por dia)'
            : '⏰ Por Horários (múltiplas por dia)';
        document.getElementById('spaceReservationMode').textContent = reservationModeText;
        
        // Configurações de horário (para espaços hourly)
        const hourlyConfig = document.getElementById('hourlyConfig');
        if (selectedSpace.reservation_mode === 'hourly') {
            hourlyConfig.style.display = 'block';
            document.getElementById('spaceMinHours').textContent = `${selectedSpace.min_hours_per_reservation || 1}h`;
            document.getElementById('spaceMaxHours').textContent = `${selectedSpace.max_hours_per_reservation || 4}h`;
        } else {
            hourlyConfig.style.display = 'none';
        }
        
        // Regras de Uso
        const rulesContainer = document.getElementById('spaceRulesContainer');
        if (selectedSpace.rules && selectedSpace.rules.trim()) {
            rulesContainer.style.display = 'block';
            document.getElementById('spaceRules').textContent = selectedSpace.rules;
        } else {
            rulesContainer.style.display = 'none';
        }
        
        // Status do Espaço
        const statusBadge = document.getElementById('spaceStatus');
        if (selectedSpace.is_active) {
            statusBadge.className = 'badge bg-success';
            statusBadge.textContent = '✅ Ativo';
        } else {
            statusBadge.className = 'badge bg-secondary';
            statusBadge.textContent = '❌ Inativo';
        }
        
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
            updateProgress(3, 'Carregando disponibilidade...');
            
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
        updateProgress(4, 'Preparando calendário...');
        
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
            dayMaxEvents: 3, // Mostrar até 3 eventos por dia
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
                
                // Agrupar reservas por data para evitar duplicação
                const reservationsByDate = {};
                
                reservations.forEach(reservation => {
                    const eventDate = reservation.reservation_date.split('T')[0];
                    const isRecurring = reservation.is_recurring === true || reservation.is_recurring === 1 || reservation.is_recurring === '1';
                    
                    if (!reservationsByDate[eventDate]) {
                        reservationsByDate[eventDate] = {
                            recurring: [],
                            normal: []
                        };
                    }
                    
                    if (isRecurring) {
                        reservationsByDate[eventDate].recurring.push(reservation);
                    } else {
                        reservationsByDate[eventDate].normal.push(reservation);
                    }
                });
                
                // Processar cada data
                Object.keys(reservationsByDate).forEach(dateStr => {
                    const dateReservations = reservationsByDate[dateStr];
                    
                    if (selectedSpace?.reservation_mode === 'hourly') {
                        // MODO HORÁRIO: Priorizar reservas recorrentes
                        
                        // Primeiro: adicionar reservas recorrentes (máximo 1 por data)
                        if (dateReservations.recurring.length > 0) {
                            const recurringReservation = dateReservations.recurring[0]; // Pegar apenas a primeira
                            const startTime = recurringReservation.start_time.substring(0, 5);
                            const endTime = recurringReservation.end_time.substring(0, 5);
                            
                            events.push({
                                title: `${recurringReservation.title || 'Recorrente'} (${startTime}-${endTime})`,
                                start: dateStr,
                                allDay: true,
                                backgroundColor: '#28a745',
                                borderColor: '#28a745',
                                textColor: '#fff',
                                classNames: ['fc-event-recurring'],
                                extendedProps: {
                                    reservation: recurringReservation,
                                    isReserved: true,
                                    isRecurring: true,
                                    startTime: startTime,
                                    endTime: endTime
                                }
                            });
                        }
                        
                        // Segundo: adicionar reservas normais (máximo 2 por data)
                        dateReservations.normal.slice(0, 2).forEach(reservation => {
                            const startTime = reservation.start_time.substring(0, 5);
                            const endTime = reservation.end_time.substring(0, 5);
                            
                            events.push({
                                title: `${startTime} às ${endTime}`,
                                start: dateStr,
                                allDay: true,
                                backgroundColor: '#ffc107',
                                borderColor: '#ffc107',
                                textColor: '#000',
                                classNames: ['fc-event-hourly-occupied'],
                                extendedProps: {
                                    reservation: reservation,
                                    isReserved: true,
                                    isRecurring: false,
                                    startTime: startTime,
                                    endTime: endTime
                                }
                            });
                        });
                        
                    } else {
                        // MODO DIA INTEIRO: Mostrar apenas reserva recorrente se existir, senão "Indisponível"
                        if (dateReservations.recurring.length > 0) {
                            const recurringReservation = dateReservations.recurring[0];
                            events.push({
                                title: recurringReservation.title || 'Reserva Recorrente',
                                start: dateStr,
                                allDay: true,
                                display: 'background',
                                backgroundColor: '#28a745',
                                borderColor: '#28a745',
                                textColor: '#fff',
                                classNames: ['fc-event-recurring'],
                                extendedProps: {
                                    reservation: recurringReservation,
                                    isReserved: true,
                                    isRecurring: true
                                }
                            });
                        } else if (dateReservations.normal.length > 0) {
                            events.push({
                                title: 'Indisponível',
                                start: dateStr,
                                allDay: true,
                                display: 'background',
                                backgroundColor: '#dc3545',
                                borderColor: '#dc3545',
                                classNames: ['fc-event-unavailable'],
                                extendedProps: {
                                    reservation: dateReservations.normal[0],
                                    isReserved: true,
                                    isRecurring: false
                                }
                            });
                        }
                    }
                });
                
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
        
        // Formatar horários para pt-BR
        const formatTime = (timeStr) => {
            if (!timeStr) return 'Não informado';
            if (timeStr.includes('T')) {
                return timeStr.split('T')[1].substring(0, 5);
            }
            return timeStr.substring(0, 5);
        };
        
        document.getElementById('confirmSpaceName').textContent = selectedSpace.name;
        document.getElementById('confirmDate').textContent = formattedDate;
        document.getElementById('confirmHours').textContent = `${formatTime(selectedSpace.available_from)} às ${formatTime(selectedSpace.available_until)}`;
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
                <div class="text-center py-3">
                    <i class="bi bi-calendar-x text-muted"></i>
                    <p class="text-muted mt-2 small">Você não tem reservas futuras</p>
                </div>
            `;
            return;
        }
        
        countBadge.classList.remove('bg-secondary');
        countBadge.classList.add('bg-warning');
        
        let html = '<div class="row g-2">';
        
        reservations.forEach(reservation => {
            // Evitar problema de timezone: usar a data como string YYYY-MM-DD
            const dateStr = reservation.reservation_date.split('T')[0]; // "2025-10-07"
            const [year, month, day] = dateStr.split('-');
            
            // Criar data local sem conversão de timezone
            const date = new Date(parseInt(year), parseInt(month) - 1, parseInt(day));
            const formattedDate = date.toLocaleDateString('pt-BR', { 
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            
            const statusBadge = reservation.status === 'approved' 
                ? '<span class="badge bg-success small">✓</span>' 
                : '<span class="badge bg-warning small">⏳</span>';
            
            const escapedSpaceName = reservation.space.name.replace(/'/g, "\\'");
            const escapedDate = formattedDate.replace(/'/g, "\\'");
            
            // Ícones por tipo de espaço
            const typeIcons = {
                'party_hall': '🎉',
                'bbq': '🍖',
                'pool': '🏊',
                'sports_court': '⚽',
                'gym': '💪',
                'meeting_room': '🏢',
                'other': '📍'
            };
            
            const icon = typeIcons[reservation.space.type] || '📍';
            
            html += `
                <div class="col-md-6">
                    <div class="card border-primary border-2">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center">
                                    <span class="me-2">${icon}</span>
                                    <h6 class="card-title mb-0">${reservation.space.name}</h6>
                                </div>
                                ${statusBadge}
                            </div>
                            <div class="small text-muted mb-2">
                                <i class="bi bi-calendar-event"></i> ${formattedDate}<br>
                                <i class="bi bi-clock"></i> ${reservation.start_time} às ${reservation.end_time}
                            </div>
                            ${reservation.notes ? `<p class="text-muted small mb-2"><i class="bi bi-chat-left-text"></i> ${reservation.notes}</p>` : ''}
                            <button class="btn btn-danger btn-sm w-100" onclick="deleteReservation(${reservation.id}, '${escapedSpaceName}', '${escapedDate}')">
                                <i class="bi bi-trash"></i> Cancelar
                            </button>
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
        
        // Formatar horários para extrair apenas HH:MM
        const formatTime = (timeStr) => {
            if (!timeStr) return '08:00';
            if (timeStr.includes('T')) {
                return timeStr.split('T')[1].substring(0, 5);
            }
            return timeStr.substring(0, 5);
        };
        
        const availableFrom = formatTime(selectedSpace.available_from);
        const availableUntil = formatTime(selectedSpace.available_until);
        
        const [startHour, startMin] = availableFrom.split(':').map(Number);
        const [endHour, endMin] = availableUntil.split(':').map(Number);
        
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
        const endTimeStr = availableUntil;
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
        
        // Filtrar apenas reservas normais (não recorrentes) deste dia
        const dayReservations = reservations.filter(r => {
            const isSameDate = r.reservation_date.split('T')[0] === dateOnly;
            const isNotRecurring = !r.is_recurring; // Excluir reservas recorrentes
            return isSameDate && isNotRecurring;
        });
        
        // Filtrar reservas recorrentes deste dia para mostrar separadamente
        const recurringReservations = reservations.filter(r => {
            const isSameDate = r.reservation_date.split('T')[0] === dateOnly;
            const isRecurring = r.is_recurring === true || r.is_recurring === 1 || r.is_recurring === '1';
            return isSameDate && isRecurring;
        });
        
        if (dayReservations.length === 0 && recurringReservations.length === 0) {
            timeline.innerHTML = '<p class="text-muted"><i class="bi bi-check-circle"></i> Nenhuma reserva neste dia - Todos os horários disponíveis!</p>';
            return;
        }
        
        timeline.innerHTML = '';
        
        // Mostrar reservas recorrentes primeiro (se houver)
        if (recurringReservations.length > 0) {
            timeline.innerHTML += '<p class="mb-2"><strong>Reservas Recorrentes:</strong></p>';
            let html = '<div class="list-group mb-3">';
            
            recurringReservations.forEach(r => {
                html += `
                    <div class="list-group-item list-group-item-success d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-arrow-repeat"></i> ${r.title || 'Reserva Recorrente'} (${r.start_time} - ${r.end_time})</span>
                        <span class="badge bg-success">Recorrente</span>
                    </div>
                `;
            });
            
            html += '</div>';
            timeline.innerHTML += html;
        }
        
        // Mostrar reservas normais (se houver)
        if (dayReservations.length > 0) {
            timeline.innerHTML += '<p class="mb-2"><strong>Reservas Individuais:</strong></p>';
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
        try {
            await loadSpaces();
            await loadUserCredits();
            await loadMyReservations();
            initCalendar();
        } catch (error) {
            console.error('Erro na inicialização:', error);
            // Em caso de erro, esconder a barra de progresso
            document.getElementById('loadingProgress').style.display = 'none';
            document.getElementById('spaceTabsContainer').style.display = 'block';
        }
    });
</script>
@endpush
@endsection


