@extends('layouts.app')

@section('title', 'Reservas - Calendário')

@section('content')
@php
    use App\Helpers\SidebarHelper;
    $user = Auth::user();
    $canMakeReservations = SidebarHelper::canMakeReservations($user);
    $canViewReservations = SidebarHelper::canViewReservations($user);
    $initialUserCredits = (float) ($initialUserCredits ?? 0);
@endphp

<!-- Variáveis JavaScript para permissões -->
<script>
    window.userPermissions = {
        canMakeReservations: @json($canMakeReservations),
        canViewReservations: @json($canViewReservations),
        isAgregado: @json($user->isAgregado()),
        userName: @json($user->name),
        onlinePaymentsEnabled: @json($onlinePaymentsEnabled ?? false),
    };
</script>

@if(!($onlinePaymentsEnabled ?? false))
<div class="alert alert-info mb-3">
    <i class="bi bi-info-circle"></i>
    O condomínio recebe pagamentos <strong>manualmente</strong>. Taxas de reserva geram cobrança no sistema, mas o pagamento deve ser feito conforme orientação da administração (sem checkout Asaas).
</div>
@endif

<!-- Header Compacto -->
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-1"><i class="bi bi-calendar-event"></i> Sistema de Reservas</h3>
                <p class="text-muted mb-0 small">Selecione o espaço e a data desejada</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Saldo de Créditos -->
                <div class="alert alert-{{ $initialUserCredits > 0 ? 'success' : 'light border' }} py-2 mb-0 credits-wallet-card" id="creditsAlert">
                    <i class="bi bi-wallet2"></i>
                    <strong>Créditos:</strong>
                    <span id="totalCredits" class="fw-bold">R$ {{ number_format($initialUserCredits, 2, ',', '.') }}</span>
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
                            <!-- Foto do Espaço -->
                            <div class="text-center mb-3">
                                <img id="spacePhoto" src="" alt="Foto do Espaço" class="img-fluid rounded shadow-sm" style="max-height: 200px; width: 100%; object-fit: cover;">
                            </div>
                            
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

                            <!-- Informações de Pré-reserva -->
                            <div id="prereservationInfo" style="display: none;" class="mb-3">
                                <small class="text-muted d-block">💳 Sistema de Pré-reserva</small>
                                <div class="bg-warning bg-opacity-10 p-2 rounded border border-warning">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <small class="text-muted d-block">⏰ Prazo para Pagamento</small>
                                            <span class="fw-bold small text-warning" id="spacePaymentDeadline">-</span>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">🔄 Cancelamento</small>
                                            <span class="fw-bold small" id="spaceAutoCancel">-</span>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted d-block">📝 Instruções de Pagamento</small>
                                        <div class="bg-white p-2 rounded border mt-1">
                                            <small class="text-dark" id="spacePaymentInstructions">-</small>
                                        </div>
                                    </div>
                                </div>
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
                
                <div class="alert alert-light border mb-3">
                    <strong>Valor:</strong> <span id="hourlyPrice" class="text-success fw-bold"></span>
                </div>

                <div id="hourlyCreditPaymentSection" class="credit-payment-section d-none mb-3"></div>
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

                <div id="confirmCreditPaymentSection" class="credit-payment-section d-none mb-3"></div>
                
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

@if($onlinePaymentsEnabled ?? false)
    @include('charges.partials.payment-checkout')
@endif

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
    
    /* Estilos para informações de pré-reserva */
    #prereservationInfo {
        animation: fadeIn 0.3s ease-in;
    }
    
    #prereservationInfo .bg-warning {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }
    
    #prereservationInfo .border-warning {
        border-color: rgba(255, 193, 7, 0.3) !important;
    }
    
    #prereservationInfo .text-warning {
        color: #ff8c00 !important;
        font-weight: 600;
    }
    
    #spacePaymentInstructions {
        line-height: 1.4;
        max-height: 60px;
        overflow-y: auto;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Estilos para pré-reservas no calendário */
    .fc-event-prereservation {
        cursor: pointer !important;
        border: 2px solid #ff8c00 !important;
        box-shadow: 0 2px 4px rgba(255, 140, 0, 0.3) !important;
        transition: all 0.2s ease !important;
    }
    
    .fc-event-prereservation:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 8px rgba(255, 140, 0, 0.4) !important;
        border-color: #e67e22 !important;
    }
    
    .fc-event-prereservation::after {
        content: " ℹ️";
        font-size: 12px;
    }
</style>
@endpush

<!-- Modal Informação de Pré-reserva -->
<div class="modal fade" id="prereservationInfoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-hourglass-split"></i> Pré-reserva Pendente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3">
                    <h6 class="alert-heading">
                        <i class="bi bi-exclamation-triangle"></i> Este horário está temporariamente reservado
                    </h6>
                    <p class="mb-0">
                        Alguém fez uma pré-reserva para este horário, mas ainda não confirmou o pagamento.
                    </p>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-muted mb-3">
                                    <i class="bi bi-clock-history"></i> Informações da Pré-reserva
                                </h6>
                                
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="text-muted d-block">📅 Data</small>
                                        <strong id="prereservationDate">-</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">🕐 Horário</small>
                                        <strong id="prereservationTime">-</strong>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <hr class="my-2">
                                        <small class="text-muted d-block">⏱️ Tempo restante para pagamento</small>
                                        <div class="d-flex align-items-center gap-2 mt-2">
                                            <div class="spinner-border spinner-border-sm text-warning" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <span class="fw-bold text-warning fs-5" id="prereservationExpiration">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i>
                            <strong>O que isso significa?</strong>
                            <p class="mb-2 mt-2">
                                Se o pagamento não for confirmado dentro do prazo, esta pré-reserva será 
                                <strong>cancelada automaticamente</strong> e o horário ficará disponível para você.
                            </p>
                            <small class="text-muted">
                                💡 <em>Sugestão: Se você deseja este horário, aguarde a expiração da pré-reserva 
                                e tente novamente em algumas horas.</em>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Fechar
                </button>
            </div>
        </div>
    </div>
</div>
<!-- FIM Modal Informação de Pré-reserva -->

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
    let userCredits = @json($initialUserCredits);
    let loadingSteps = 0;
    let totalSteps = 4; // espaços, reservas, créditos, calendário

    function formatCurrency(value) {
        return `R$ ${parseFloat(value || 0).toFixed(2).replace('.', ',')}`;
    }

    function renderCreditPaymentSection(containerId, totalPrice) {
        const container = document.getElementById(containerId);
        if (!container) {
            return;
        }

        if (!totalPrice || totalPrice <= 0 || userCredits <= 0) {
            container.classList.add('d-none');
            container.innerHTML = '';
            return;
        }

        const prefix = containerId.replace('CreditPaymentSection', '');
        const maxCredit = Math.min(userCredits, totalPrice);

        container.classList.remove('d-none');
        container.innerHTML = `
            <div class="alert alert-success py-2 mb-0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="${prefix}UseCredits">
                    <label class="form-check-label" for="${prefix}UseCredits">
                        Usar créditos disponíveis (${formatCurrency(userCredits)})
                    </label>
                </div>
                <div id="${prefix}PartialCreditWrap" class="mt-2 d-none">
                    <label class="form-label small mb-1" for="${prefix}CreditAmount">Valor em créditos (opcional)</label>
                    <input type="number" class="form-control form-control-sm" id="${prefix}CreditAmount" min="0.01" max="${maxCredit}" step="0.01" placeholder="Máx. ${formatCurrency(maxCredit)}">
                    <small class="text-muted">Deixe em branco para usar o máximo possível. O restante pode ser pago via Asaas.</small>
                </div>
                <div id="${prefix}CreditSummary" class="small mt-2 text-muted"></div>
            </div>
        `;

        const checkbox = document.getElementById(`${prefix}UseCredits`);
        const partialWrap = document.getElementById(`${prefix}PartialCreditWrap`);
        const amountInput = document.getElementById(`${prefix}CreditAmount`);
        const summary = document.getElementById(`${prefix}CreditSummary`);

        const updateSummary = () => {
            if (!checkbox.checked) {
                summary.textContent = '';
                return;
            }

            let creditToUse = maxCredit;
            if (amountInput.value) {
                creditToUse = Math.min(maxCredit, Math.max(0, parseFloat(amountInput.value) || 0));
            }

            const remaining = Math.max(0, totalPrice - creditToUse);
            summary.innerHTML = `Crédito: <strong>${formatCurrency(creditToUse)}</strong> · Restante: <strong>${formatCurrency(remaining)}</strong>`;
        };

        checkbox.addEventListener('change', () => {
            partialWrap.classList.toggle('d-none', !checkbox.checked);
            updateSummary();
        });
        amountInput.addEventListener('input', updateSummary);
    }

    function getCreditPaymentPayload(sectionPrefix) {
        const checkbox = document.getElementById(`${sectionPrefix}UseCredits`);
        if (!checkbox || !checkbox.checked) {
            return { use_credits: false };
        }

        const payload = { use_credits: true };
        const amountInput = document.getElementById(`${sectionPrefix}CreditAmount`);

        if (amountInput && amountInput.value) {
            payload.credit_amount = parseFloat(amountInput.value);
        }

        return payload;
    }

    function normalizeTime(timeStr) {
        if (!timeStr) {
            return '';
        }

        const raw = String(timeStr).trim();

        if (raw.includes('T')) {
            return raw.split('T')[1].substring(0, 5);
        }

        if (raw.includes(' ')) {
            const parts = raw.split(' ');
            const timePart = parts.length > 1 ? parts[1] : parts[0];
            return timePart.substring(0, 5);
        }

        return raw.substring(0, 5);
    }

    function timeToMinutes(timeStr) {
        const normalized = normalizeTime(timeStr);
        const [hours, minutes] = normalized.split(':').map(Number);

        if (Number.isNaN(hours) || Number.isNaN(minutes)) {
            return 0;
        }

        return (hours * 60) + minutes;
    }

    function timesOverlap(startA, endA, startB, endB) {
        const aStart = timeToMinutes(startA);
        const aEnd = timeToMinutes(endA);
        const bStart = timeToMinutes(startB);
        const bEnd = timeToMinutes(endB);

        return aStart < bEnd && aEnd > bStart;
    }

    function getOccupiedSlotsForDate(dateOnly) {
        return reservations.filter(r => r.reservation_date.split('T')[0] === dateOnly);
    }

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
        
        // Atualizar informações do espaço com verificações de segurança
        const spaceName = document.getElementById('spaceName');
        const spaceDescription = document.getElementById('spaceDescription');
        
        if (spaceName) spaceName.textContent = selectedSpace.name;
        if (spaceDescription) spaceDescription.textContent = selectedSpace.description || '';
        
        // Atualizar foto do espaço
        const photoElement = document.getElementById('spacePhoto');
        if (selectedSpace.photo_path) {
            photoElement.src = `/storage/${selectedSpace.photo_path}`;
            photoElement.style.display = 'block';
        } else {
            // Foto padrão baseada no tipo do espaço
            const defaultPhotos = {
                'party_hall': '/images/defaults/party_hall.jpg',
                'bbq': '/images/defaults/bbq.jpg',
                'pool': '/images/defaults/pool.jpg',
                'sports_court': '/images/defaults/sports_court.jpg',
                'gym': '/images/defaults/gym.jpg',
                'meeting_room': '/images/defaults/meeting_room.jpg',
                'other': '/images/defaults/space.jpg',
            };
            photoElement.src = defaultPhotos[selectedSpace.type] || '/images/defaults/space.jpg';
            photoElement.style.display = 'block';
        }
        const spacePrice = document.getElementById('spacePrice');
        const spaceCapacity = document.getElementById('spaceCapacity');
        
        if (spacePrice) {
            spacePrice.textContent = selectedSpace.price_per_hour > 0 
                ? `R$ ${parseFloat(selectedSpace.price_per_hour).toFixed(2).replace('.', ',')}` 
                : 'GRATUITO';
        }
        if (spaceCapacity) {
            spaceCapacity.textContent = selectedSpace.capacity ? `${selectedSpace.capacity} pessoas` : 'Não informado';
        }
        
        // Formatar horários para pt-BR
        const formatTime = (timeStr) => {
            const normalized = normalizeTime(timeStr);
            return normalized || 'Não informado';
        };
        
        const spaceHours = document.getElementById('spaceHours');
        const spaceLimit = document.getElementById('spaceLimit');
        
        if (spaceHours) spaceHours.textContent = `${formatTime(selectedSpace.available_from)} às ${formatTime(selectedSpace.available_until)}`;
        if (spaceLimit) spaceLimit.textContent = `${selectedSpace.max_reservations_per_month_per_user}x por mês`;
        
        // Modo de Reserva
        const reservationModeText = selectedSpace.reservation_mode === 'full_day' 
            ? '📅 Dia Inteiro (1 reserva por dia)'
            : '⏰ Por Horários (múltiplas por dia)';
        const spaceReservationMode = document.getElementById('spaceReservationMode');
        if (spaceReservationMode) spaceReservationMode.textContent = reservationModeText;
        
        // Configurações de horário (para espaços hourly)
        const hourlyConfig = document.getElementById('hourlyConfig');
        if (selectedSpace.reservation_mode === 'hourly') {
            hourlyConfig.style.display = 'block';
            const spaceMinHours = document.getElementById('spaceMinHours');
            const spaceMaxHours = document.getElementById('spaceMaxHours');
            
            if (spaceMinHours) spaceMinHours.textContent = `${selectedSpace.min_hours_per_reservation || 1}h`;
            if (spaceMaxHours) spaceMaxHours.textContent = `${selectedSpace.max_hours_per_reservation || 4}h`;
        } else {
            hourlyConfig.style.display = 'none';
        }
        
        // Informações de Pré-reserva
        const prereservationInfo = document.getElementById('prereservationInfo');
        if (selectedSpace.approval_type === 'prereservation') {
            prereservationInfo.style.display = 'block';
            
            // Prazo para pagamento
            const paymentHours = selectedSpace.prereservation_payment_hours || 24;
            const spacePaymentDeadline = document.getElementById('spacePaymentDeadline');
            if (spacePaymentDeadline) spacePaymentDeadline.textContent = `${paymentHours} horas`;
            
            // Cancelamento automático
            const autoCancel = selectedSpace.prereservation_auto_cancel 
                ? 'Automático' 
                : 'Manual';
            const spaceAutoCancel = document.getElementById('spaceAutoCancel');
            if (spaceAutoCancel) spaceAutoCancel.textContent = autoCancel;
            
            // Instruções de pagamento
            const instructions = selectedSpace.prereservation_instructions || 'Consulte o síndico para informações de pagamento.';
            const spacePaymentInstructions = document.getElementById('spacePaymentInstructions');
            if (spacePaymentInstructions) spacePaymentInstructions.textContent = instructions;
        } else {
            prereservationInfo.style.display = 'none';
        }
        
        // Regras de Uso
        const rulesContainer = document.getElementById('spaceRulesContainer');
        if (selectedSpace.rules && selectedSpace.rules.trim()) {
            rulesContainer.style.display = 'block';
            const spaceRules = document.getElementById('spaceRules');
            if (spaceRules) spaceRules.textContent = selectedSpace.rules;
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
            
            // console.log('=== RESPOSTA DA API ===');
            // console.log('Status da resposta:', response.status);
            // console.log('Data recebida:', data);
            
            // Armazenar slots ocupados (TODAS as reservas, não apenas as minhas)
            reservations = data.occupied_slots || [];
            
            // console.log('Disponibilidade do espaço carregada:', spaceId, 'Slots ocupados:', reservations.length);
            // console.log('Reservas detalhadas:', reservations);
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
            eventClick: function(info) {
                const event = info.event;
                const extendedProps = event.extendedProps;
                
                // console.log('Evento clicado:', event.title);
                // console.log('ExtendedProps:', extendedProps);
                // console.log('isPrereservation:', extendedProps.isPrereservation);
                // console.log('reservation:', extendedProps.reservation);
                
                // Verificar se é uma pré-reserva clicável
                if (extendedProps.isPrereservation && extendedProps.reservation) {
                    // console.log('Chamando showPrereservationInfo...');
                    showPrereservationInfo(extendedProps.reservation);
                    info.jsEvent.preventDefault();
                    return false; // Prevenir propagação
                } else {
                    // console.log('Não é uma pré-reserva clicável');
                    // Se não for pré-reserva, permitir comportamento padrão (modal de erro)
                }
            },
            events: function(fetchInfo, successCallback, failureCallback) {
                // console.log('=== CARREGANDO EVENTOS ===');
                // console.log('fetchInfo:', fetchInfo);
                // console.log('selectedSpace:', selectedSpace);
                // console.log('Reservas carregadas:', reservations);
                // console.log('Modo do espaço:', selectedSpace?.reservation_mode);
                
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
                            const startTime = normalizeTime(recurringReservation.start_time);
                            const endTime = normalizeTime(recurringReservation.end_time);
                            
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
                            const startTime = normalizeTime(reservation.start_time);
                            const endTime = normalizeTime(reservation.end_time);
                            
                            // Verificar se é pré-reserva
                            const isPrereservation = reservation.is_prereservation === true || 
                                                   reservation.is_prereservation === 1 || 
                                                   reservation.is_prereservation === '1' ||
                                                   reservation.prereservation_status === 'pending_payment';
                            
                            console.log('Modo Por Horário - Reserva:', reservation.id, 'isPrereservation:', isPrereservation);
                            
                            const backgroundColor = isPrereservation ? '#ffc107' : '#ffc107';
                            const borderColor = isPrereservation ? '#ff8c00' : '#ffc107';
                            const classNames = isPrereservation ? ['fc-event-hourly-occupied', 'fc-event-prereservation', 'fc-event-clickable'] : ['fc-event-hourly-occupied'];
                            
                            events.push({
                                title: `${startTime} às ${endTime}${isPrereservation ? ' ℹ️' : ''}`,
                                start: dateStr,
                                allDay: true,
                                backgroundColor: backgroundColor,
                                borderColor: borderColor,
                                textColor: '#000',
                                classNames: classNames,
                                extendedProps: {
                                    reservation: reservation,
                                    isReserved: true,
                                    isRecurring: false,
                                    isPrereservation: isPrereservation,
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
                            const normalReservation = dateReservations.normal[0];
                            
                            // Verificar se é pré-reserva
                            const isPrereservation = normalReservation.is_prereservation === true || 
                                                   normalReservation.is_prereservation === 1 || 
                                                   normalReservation.is_prereservation === '1' ||
                                                   normalReservation.prereservation_status === 'pending_payment';
                            
                            console.log('Modo Dia Inteiro - Reserva:', normalReservation.id, 'isPrereservation:', isPrereservation);
                            
                            if (isPrereservation) {
                                // Pré-reserva: Badge amarelo clicável
                                events.push({
                                    title: 'Pré-reserva',
                                    start: dateStr,
                                    allDay: true,
                                    display: 'background',
                                    backgroundColor: '#ffc107',
                                    borderColor: '#ffc107',
                                    textColor: '#000',
                                    classNames: ['fc-event-prereservation', 'fc-event-clickable'],
                                    extendedProps: {
                                        reservation: normalReservation,
                                        isReserved: true,
                                        isRecurring: false,
                                        isPrereservation: true
                                    }
                                });
                            } else {
                                // Reserva normal: Badge vermelho
                                events.push({
                                    title: 'Indisponível',
                                    start: dateStr,
                                    allDay: true,
                                    display: 'background',
                                    backgroundColor: '#dc3545',
                                    borderColor: '#dc3545',
                                    classNames: ['fc-event-unavailable'],
                                    extendedProps: {
                                        reservation: normalReservation,
                                        isReserved: true,
                                        isRecurring: false,
                                        isPrereservation: false
                                    }
                                });
                            }
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
        // Verificar se o usuário tem permissão para fazer reservas
        if (!window.userPermissions.canMakeReservations) {
            alert('❌ Você não tem permissão para fazer reservas.\n\nApenas visualização permitida.');
            return;
        }

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
            const normalized = normalizeTime(timeStr);
            return normalized || 'Não informado';
        };
        
        document.getElementById('confirmSpaceName').textContent = selectedSpace.name;
        document.getElementById('confirmDate').textContent = formattedDate;
        document.getElementById('confirmHours').textContent = `${formatTime(selectedSpace.available_from)} às ${formatTime(selectedSpace.available_until)}`;
        document.getElementById('confirmPrice').textContent = selectedSpace.price_per_hour > 0 
            ? `R$ ${parseFloat(selectedSpace.price_per_hour).toFixed(2).replace('.', ',')}` 
            : 'GRATUITO';

        const totalPrice = selectedSpace.price_per_hour > 0 ? parseFloat(selectedSpace.price_per_hour) : 0;
        renderCreditPaymentSection('confirmCreditPaymentSection', totalPrice);
        
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
                    notes: notes,
                    ...getCreditPaymentPayload('confirm'),
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
                handleReservationChargeResult(result, successMsg);
            } else {
                console.error('Erro na resposta:', result);
                alert(result.error || 'Erro ao criar reserva. Verifique o console para mais detalhes.');
            }
        } catch (error) {
            console.error('Erro na requisição:', error);
            alert('Erro ao criar reserva. Tente novamente. Verifique o console para mais detalhes.');
        }
    }

    function handleReservationChargeResult(result, successMsg) {
        const onlineEnabled = result.online_payments_enabled ?? window.userPermissions.onlinePaymentsEnabled;
        const chargeId = result.payment_data?.charge_id || result.reservation_charge?.id;
        const myChargesUrl = @json(route('my-charges.index'));

        if (result.credit_used) {
            successMsg += `\n\n💰 Créditos utilizados: R$ ${parseFloat(result.credit_amount).toFixed(2).replace('.', ',')}`;
        }

        if (result.has_charge && onlineEnabled && chargeId) {
            alert(successMsg);

            if (typeof window.openChargeCheckout === 'function') {
                window.openChargeCheckout(chargeId);
            } else {
                window.location.href = `${myChargesUrl}?pay=${chargeId}`;
            }

            return;
        }

        if (result.has_charge && !onlineEnabled) {
            successMsg += '\n\n📋 Uma cobrança foi registrada. Procure a administração do condomínio para efetuar o pagamento ou acesse Minhas Cobranças para acompanhar.';
        }

        alert(successMsg);
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
                } else if (result.charge_deleted) {
                    cancelMsg += '\n\n📄 A cobrança pendente foi removida.';
                }

                if (result.total_user_credits !== undefined) {
                    updateCreditsDisplay(result.total_user_credits);
                } else if (result.credit_generated) {
                    const nextTotal = userCredits + (parseFloat(result.credit_amount) || 0);
                    updateCreditsDisplay(nextTotal);
                    await loadUserCredits();
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
        
        // Preencher modal com verificações de segurança
        const hourlySpaceName = document.getElementById('hourlySpaceName');
        const hourlyDate = document.getElementById('hourlyDate');
        const maxHoursAllowed = document.getElementById('maxHoursAllowed');
        const hourlyPrice = document.getElementById('hourlyPrice');
        
        if (hourlySpaceName) hourlySpaceName.textContent = selectedSpace.name;
        if (hourlyDate) hourlyDate.textContent = formattedDate;
        if (maxHoursAllowed) maxHoursAllowed.textContent = selectedSpace.max_hours_per_reservation;
        if (hourlyPrice) {
            hourlyPrice.textContent = selectedSpace.price_per_hour > 0 
                ? `R$ ${parseFloat(selectedSpace.price_per_hour).toFixed(2).replace('.', ',')} por hora` 
                : 'GRATUITO';
        }

        renderCreditPaymentSection('hourlyCreditPaymentSection', 0);
        
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
        const messageDiv = document.getElementById('hourlyConflictMessage');

        if (!startSelect || !endSelect || !selectedSpace) {
            return;
        }

        startSelect.innerHTML = '<option value="">Selecione...</option>';
        endSelect.innerHTML = '<option value="">Selecione...</option>';

        if (messageDiv) {
            messageDiv.innerHTML = '';
        }

        const availableFrom = normalizeTime(selectedSpace.available_from);
        const availableUntil = normalizeTime(selectedSpace.available_until);
        const openMinutes = timeToMinutes(availableFrom);
        const closeMinutes = timeToMinutes(availableUntil);

        if (!availableFrom || !availableUntil || closeMinutes <= openMinutes) {
            if (messageDiv) {
                messageDiv.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        Horário de funcionamento inválido para este espaço (${availableFrom || '?'} às ${availableUntil || '?'}).
                    </div>
                `;
            }
            return;
        }

        const dateOnly = selectedDate.split('T')[0];
        const occupiedSlots = getOccupiedSlotsForDate(dateOnly);

        for (let minutes = openMinutes; minutes < closeMinutes; minutes += 30) {
            const h = Math.floor(minutes / 60);
            const m = minutes % 60;
            const timeStr = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;

            const timeMin = timeToMinutes(timeStr);
            const isStartOccupied = occupiedSlots.some(slot => {
                const slotStart = timeToMinutes(slot.start_time);
                const slotEnd = timeToMinutes(slot.end_time);
                return timeMin >= slotStart && timeMin < slotEnd;
            });

            if (!isStartOccupied) {
                startSelect.innerHTML += `<option value="${timeStr}">${timeStr}</option>`;
            }

            endSelect.innerHTML += `<option value="${timeStr}">${timeStr}</option>`;
        }

        endSelect.innerHTML += `<option value="${availableUntil}">${availableUntil}</option>`;

        if (startSelect.options.length <= 1 && messageDiv) {
            messageDiv.innerHTML = `
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    Não há horários livres neste dia. Verifique as reservas recorrentes ou individuais abaixo.
                </div>
            `;
        }
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
        
        // Verificar conflito com outras reservas (incluindo recorrentes)
        const dateOnly = selectedDate.split('T')[0];
        const conflicts = getOccupiedSlotsForDate(dateOnly).filter(slot =>
            timesOverlap(startTime, endTime, slot.start_time, slot.end_time)
        );
        
        if (conflicts.length > 0) {
            let conflictHours = conflicts.map(c => `${normalizeTime(c.start_time)}-${normalizeTime(c.end_time)}`).join(', ');
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

        if (selectedSpace.price_per_hour > 0) {
            const totalPrice = Math.max(1, durationHours) * parseFloat(selectedSpace.price_per_hour);
            const hourlyPrice = document.getElementById('hourlyPrice');
            if (hourlyPrice) {
                hourlyPrice.textContent = `${formatCurrency(totalPrice)} (${durationHours}h)`;
            }
            renderCreditPaymentSection('hourlyCreditPaymentSection', totalPrice);
        }
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
                        <span><i class="bi bi-arrow-repeat"></i> ${r.title || 'Reserva Recorrente'} (${normalizeTime(r.start_time)} - ${normalizeTime(r.end_time)})</span>
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
                // Verificar se é pré-reserva - múltiplas verificações para garantir robustez
                const isPrereservation = r.is_prereservation === true || 
                                       r.is_prereservation === 1 || 
                                       r.is_prereservation === '1' ||
                                       r.prereservation_status === 'pending_payment';
                
                console.log('Reserva ID:', r.id, 'is_prereservation:', r.is_prereservation, 'prereservation_status:', r.prereservation_status, 'Resultado:', isPrereservation);
                
                const badgeClass = isPrereservation ? 'bg-warning text-dark' : 'bg-danger';
                const badgeText = isPrereservation ? 'Pré-reserva' : 'Indisponível';
                const cursorStyle = isPrereservation ? 'cursor: pointer;' : '';
                const clickEvent = isPrereservation ? `onclick="showPrereservationInfo(${JSON.stringify(r).replace(/"/g, '&quot;')})"` : '';
                
                html += `
                    <div class="list-group-item list-group-item-danger d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-clock"></i> ${normalizeTime(r.start_time)} - ${normalizeTime(r.end_time)}</span>
                        <span class="badge ${badgeClass}" style="${cursorStyle}" ${clickEvent} 
                              ${isPrereservation ? 'title="Clique para mais informações"' : ''}>
                            ${badgeText}
                            ${isPrereservation ? '<i class="bi bi-info-circle ms-1"></i>' : ''}
                        </span>
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
                    notes: notes,
                    ...getCreditPaymentPayload('hourly'),
                })
            });
            
            const result = await response.json();
            
            console.log('Resposta da API:', {
                status: response.status,
                ok: response.ok,
                result: result
            });
            
            if (response.ok && result.message) {
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
                handleReservationChargeResult(result, successMsg);
            } else {
                console.error('Erro na resposta:', result);
                alert(result.error || 'Erro ao criar reserva. Verifique o console para mais detalhes.');
            }
        } catch (error) {
            console.error('Erro na requisição:', error);
            alert('Erro ao criar reserva. Tente novamente. Verifique o console para mais detalhes.');
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

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            if (typeof data.total === 'number' || typeof data.total === 'string') {
                updateCreditsDisplay(data.total);
            }
        } catch (error) {
            console.error('Erro ao carregar créditos:', error);
        }
    }

    // Atualizar display de créditos (card sempre visível)
    function updateCreditsDisplay(total) {
        const numericTotal = Math.max(0, parseFloat(total) || 0);
        userCredits = numericTotal;

        const alertEl = document.getElementById('creditsAlert');
        const totalEl = document.getElementById('totalCredits');

        if (totalEl) {
            totalEl.textContent = `R$ ${numericTotal.toFixed(2).replace('.', ',')}`;
        }

        if (!alertEl) {
            return;
        }

        alertEl.style.display = '';
        alertEl.classList.toggle('alert-success', numericTotal > 0);
        alertEl.classList.toggle('alert-light', numericTotal <= 0);
        alertEl.classList.toggle('border', numericTotal <= 0);
    }

    // Mostrar informações de pré-reserva
    function showPrereservationInfo(reservation) {
        // console.log('=== showPrereservationInfo chamada ===');
        // console.log('Reserva recebida:', reservation);
        
        // Formatar data
        const date = new Date(reservation.reservation_date);
        const formattedDate = date.toLocaleDateString('pt-BR', { 
            day: '2-digit', 
            month: '2-digit', 
            year: 'numeric' 
        });
        
        // Formatar horário
        const formattedTime = `${reservation.start_time} - ${reservation.end_time}`;
        
        // Calcular tempo restante
        let expirationText = '';
        if (reservation.hours_until_expiration !== undefined) {
            const hours = Math.floor(reservation.hours_until_expiration);
            const minutes = Math.floor((reservation.hours_until_expiration - hours) * 60);
            
            if (hours > 0) {
                expirationText = `${hours}h ${minutes}min`;
            } else if (minutes > 0) {
                expirationText = `${minutes} minutos`;
            } else {
                expirationText = 'Expirando em breve';
            }
            
        // Mudar cor se estiver perto de expirar
        const expirationElement = document.getElementById('prereservationExpiration');
        if (expirationElement) {
            if (hours < 1) {
                expirationElement.classList.remove('text-warning');
                expirationElement.classList.add('text-danger');
            } else {
                expirationElement.classList.remove('text-danger');
                expirationElement.classList.add('text-warning');
            }
        }
        } else {
            expirationText = 'Aguardando pagamento';
        }
        
        // Preencher modal com verificações de segurança
        const dateElement = document.getElementById('prereservationDate');
        const timeElement = document.getElementById('prereservationTime');
        const expirationElement = document.getElementById('prereservationExpiration');
        
        if (dateElement) dateElement.textContent = formattedDate;
        if (timeElement) timeElement.textContent = formattedTime;
        if (expirationElement) expirationElement.textContent = expirationText;
        
        // console.log('Preenchendo modal com dados:', {
        //     date: formattedDate,
        //     time: formattedTime,
        //     expiration: expirationText
        // });
        
        // Mostrar modal
        const modalEl = document.getElementById('prereservationInfoModal');
        // console.log('Modal element encontrado:', modalEl);
        // console.log('Bootstrap disponível:', typeof window.bootstrap);
        
        if (modalEl) {
            if (typeof window.bootstrap !== 'undefined' && window.bootstrap.Modal) {
                let modal = window.bootstrap.Modal.getInstance(modalEl);
                if (!modal) {
                    // console.log('Criando nova instância do modal...');
                    modal = new window.bootstrap.Modal(modalEl);
                }
                // console.log('Mostrando modal...');
                modal.show();
            } else {
                console.error('Bootstrap Modal não está disponível!');
                // Fallback: mostrar modal manualmente
                modalEl.style.display = 'block';
                modalEl.classList.add('show');
                document.body.classList.add('modal-open');
            }
        } else {
            console.error('Modal element não encontrado!');
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


