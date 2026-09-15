@extends('layouts.app')

@section('title', 'Minhas Reservas')

@push('styles')
<style>
    .reservation-stats .card {
        border: 0;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .table-actions .btn {
        white-space: nowrap;
    }
    .status-pill {
        font-size: 0.75rem;
        font-weight: 600;
    }
    .empty-state {
        padding: 3rem 1rem;
    }
    .wallet-card {
        position: sticky;
        top: 1rem;
    }
    .wallet-list {
        max-height: 280px;
        overflow-y: auto;
    }
    .wallet-item {
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        padding: 0.65rem 0;
    }
    .wallet-item:last-child {
        border-bottom: 0;
    }
</style>
@endpush

@section('content')
@php
    use App\Helpers\SidebarHelper;
    $user = Auth::user();
    $defaulterBlocksReservations = $defaulterRestriction['active'] ?? false;
@endphp

<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <a href="{{ route('reservations.index') }}" class="text-decoration-none text-muted small">
                <i class="bi bi-arrow-left"></i> Voltar ao calendário
            </a>
            <h2 class="mb-1 mt-2"><i class="bi bi-bookmark-check text-primary"></i> Minhas Reservas</h2>
            <p class="text-muted mb-0">Acompanhe todas as suas reservas de espaços, pagamentos e cancelamentos.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            @if($canMakeReservations && !$defaulterBlocksReservations)
            <a href="{{ route('reservations.index') }}" class="btn btn-primary">
                <i class="bi bi-calendar-plus"></i> Nova reserva
            </a>
            @endif
        </div>
    </div>

    @if($defaulterBlocksReservations)
    <div class="alert alert-danger mb-3">
        <i class="bi bi-shield-exclamation"></i>
        Novas reservas estão bloqueadas por inadimplência.
        <a href="{{ $defaulterRestriction['regularize_url'] ?? route('my-charges.index', ['status' => 'overdue']) }}" class="alert-link fw-semibold">Regularizar débitos</a>
    </div>
    @endif

    <div class="row g-3 mb-4 reservation-stats">
        <div class="col-md-3 col-sm-6">
            <div class="card">
                <div class="card-body py-3">
                    <small class="text-muted">Próximas reservas</small>
                    <div class="fs-4 fw-bold text-primary" id="statUpcoming">0</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card">
                <div class="card-body py-3">
                    <small class="text-muted">Aguardando aprovação</small>
                    <div class="fs-4 fw-bold text-warning" id="statPending">0</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card">
                <div class="card-body py-3">
                    <small class="text-muted">Aguardando pagamento</small>
                    <div class="fs-4 fw-bold text-danger" id="statPayment">0</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card">
                <div class="card-body py-3">
                    <small class="text-muted">Total de reservas</small>
                    <div class="fs-4 fw-bold" id="statTotal">0</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8 order-lg-1 order-2">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h6 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h6>
        </div>
        <div class="card-body">
            <form id="filtersForm" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Período</label>
                    <select class="form-select form-select-sm" id="filterPeriod" name="period">
                        <option value="">Todas</option>
                        <option value="upcoming">Próximas</option>
                        <option value="past">Passadas</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select class="form-select form-select-sm" id="filterStatus" name="status">
                        <option value="">Todos</option>
                        <option value="pending">Pendente</option>
                        <option value="approved">Aprovada</option>
                        <option value="rejected">Rejeitada</option>
                        <option value="cancelled">Cancelada</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Espaço</label>
                    <select class="form-select form-select-sm" id="filterSpace" name="space_id">
                        <option value="">Todos os espaços</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">De</label>
                    <input type="date" class="form-control form-control-sm" id="filterDateFrom" name="date_from">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Até</label>
                    <input type="date" class="form-control form-control-sm" id="filterDateTo" name="date_to">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnClearFilters">
                        <i class="bi bi-x-circle"></i> Limpar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-table"></i> Reservas</h6>
            <small class="text-muted" id="tableSummary">Carregando...</small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Espaço</th>
                        <th>Data</th>
                        <th>Horário</th>
                        <th>Status</th>
                        <th>Pagamento</th>
                        <th class="text-end">Valor</th>
                        <th class="text-end" style="min-width: 220px;">Ações</th>
                    </tr>
                </thead>
                <tbody id="reservationsTableBody">
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="text-muted mt-2 mb-0">Carregando reservas...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white" id="paginationContainer"></div>
    </div>
        </div>

        <div class="col-lg-4 order-lg-2 order-1">
            <div class="card shadow-sm wallet-card mb-4" id="walletCard">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-wallet2 text-primary"></i> Minha Carteira</h6>
                    <span class="badge bg-{{ ($initialUserCredits ?? 0) > 0 ? 'success' : 'secondary' }}" id="walletTotalBadge">
                        R$ {{ number_format((float) ($initialUserCredits ?? 0), 2, ',', '.') }}
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="px-3 py-2 border-bottom bg-light small">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Saldo disponível</span>
                            <strong id="totalCredits" class="text-success">R$ {{ number_format((float) ($initialUserCredits ?? 0), 2, ',', '.') }}</strong>
                        </div>
                        <div class="text-muted mt-1">Use em novas reservas de espaços pagos.</div>
                    </div>

                    <div class="px-3 pt-2 pb-1">
                        <small class="text-muted fw-semibold text-uppercase">Créditos disponíveis</small>
                    </div>
                    <div class="wallet-list px-3" id="walletAvailableList">
                        <div class="text-center py-3 text-muted small">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                        </div>
                    </div>

                    <div class="px-3 pt-3 pb-1 border-top">
                        <small class="text-muted fw-semibold text-uppercase">Utilizações recentes</small>
                    </div>
                    <div class="wallet-list px-3 pb-3" id="walletUsageList">
                        <div class="text-center py-3 text-muted small">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cancelReservationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white" id="cancelModalHeader">
                <h5 class="modal-title"><i class="bi bi-x-circle"></i> Cancelar reserva</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" id="cancelModalCloseBtn"></button>
            </div>
            <div class="modal-body">
                <div id="cancelModalLoading">
                    <p class="text-muted small mb-2">Carregando informações da reserva...</p>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted">Aguarde</small>
                        <small class="text-muted" id="cancelProgressText">0%</small>
                    </div>
                    <div class="progress mb-0" style="height: 8px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-danger"
                             id="cancelProgressBar" style="width: 0%"></div>
                    </div>
                </div>

                <div id="cancelModalConfirm" class="d-none">
                    <div id="cancelConfirmContent"></div>
                    <div class="alert alert-warning small mb-0 mt-3">
                        <i class="bi bi-exclamation-triangle"></i>
                        Esta ação não pode ser desfeita.
                    </div>
                </div>

                <div id="cancelModalProcessing" class="d-none">
                    <p class="text-muted small mb-2">Cancelando reserva e atualizando sua carteira...</p>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted">Processando</small>
                        <small class="text-muted" id="cancelProcessingText">0%</small>
                    </div>
                    <div class="progress mb-0" style="height: 8px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-danger"
                             id="cancelProcessingBar" style="width: 0%"></div>
                    </div>
                </div>

                <div id="cancelModalSuccess" class="d-none">
                    <div class="text-center py-2">
                        <i class="bi bi-check-circle text-success display-5"></i>
                        <p class="mb-0 mt-2" id="cancelSuccessMessage"></p>
                    </div>
                </div>

                <div id="cancelModalError" class="d-none">
                    <div class="alert alert-danger mb-0" id="cancelErrorMessage"></div>
                </div>
            </div>
            <div class="modal-footer" id="cancelModalFooter">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelModalDismissBtn">Fechar</button>
                <button type="button" class="btn btn-danger d-none" id="cancelConfirmBtn">
                    <i class="bi bi-x-circle"></i> Confirmar cancelamento
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="reservationDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-calendar-event"></i> Detalhes da reserva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="reservationDetailBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
            <div class="modal-footer" id="reservationDetailFooter"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const onlinePaymentsEnabled = @json($onlinePaymentsEnabled ?? false);
    const myChargesUrl = @json(route('my-charges.index'));
    let currentPage = 1;
    let spacesLoaded = false;
    let pendingCancelReservationId = null;
    let pendingCancelReservation = null;
    const cancelModalEl = document.getElementById('cancelReservationModal');

    function getBootstrapModal(element) {
        if (!element || typeof window.bootstrap === 'undefined' || !window.bootstrap.Modal) {
            return null;
        }

        return window.bootstrap.Modal.getOrCreateInstance(element);
    }

    const statusLabels = {
        pending: { label: 'Pendente', class: 'bg-warning text-dark' },
        approved: { label: 'Aprovada', class: 'bg-success' },
        rejected: { label: 'Rejeitada', class: 'bg-danger' },
        cancelled: { label: 'Cancelada', class: 'bg-secondary' },
        completed: { label: 'Concluída', class: 'bg-info text-dark' },
    };

    const paymentLabels = {
        pending_payment: { label: 'Aguardando pagamento', class: 'bg-danger' },
        paid: { label: 'Pago', class: 'bg-success' },
        expired: { label: 'Prazo expirado', class: 'bg-secondary' },
    };

    document.getElementById('filtersForm')?.addEventListener('submit', (event) => {
        event.preventDefault();
        currentPage = 1;
        loadReservations();
    });

    document.getElementById('btnClearFilters')?.addEventListener('click', () => {
        document.getElementById('filtersForm').reset();
        currentPage = 1;
        loadReservations();
    });

    async function loadSpaces() {
        if (spacesLoaded) return;

        try {
            const response = await fetch('/api/spaces', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) return;

            const spaces = await response.json();
            const select = document.getElementById('filterSpace');

            (Array.isArray(spaces) ? spaces : []).forEach(space => {
                const option = document.createElement('option');
                option.value = space.id;
                option.textContent = space.name;
                select.appendChild(option);
            });

            spacesLoaded = true;
        } catch (error) {
            console.error(error);
        }
    }

    async function loadReservations(page = currentPage) {
        currentPage = page;
        const tbody = document.getElementById('reservationsTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </td>
            </tr>
        `;

        const params = new URLSearchParams();
        params.set('page', String(page));
        params.set('per_page', '20');
        params.set('with_charge', '1');

        ['period', 'status', 'space_id', 'date_from', 'date_to'].forEach(field => {
            const value = document.getElementById(
                field === 'period' ? 'filterPeriod'
                : field === 'status' ? 'filterStatus'
                : field === 'space_id' ? 'filterSpace'
                : field === 'date_from' ? 'filterDateFrom'
                : 'filterDateTo'
            )?.value;

            if (value) params.set(field, value);
        });

        try {
            const response = await fetch(`/api/reservations?${params.toString()}`, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) throw new Error('Não foi possível carregar as reservas.');

            const payload = await response.json();
            const reservations = payload.data || [];

            updateStats(payload);
            renderTable(reservations);
            renderPagination(payload);
        } catch (error) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-danger py-4">${escapeHtml(error.message)}</td>
                </tr>
            `;
        }
    }

    async function loadStats() {
        const requests = [
            fetch('/api/reservations?period=upcoming&per_page=1', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } }),
            fetch('/api/reservations?status=pending&per_page=1', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } }),
            fetch('/api/reservations?prereservation_status=pending_payment&per_page=1', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } }),
            fetch('/api/reservations?per_page=1', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } }),
        ];

        try {
            const [upcomingResponse, pendingResponse, paymentResponse, totalResponse] = await Promise.all(requests);
            const upcomingPayload = upcomingResponse.ok ? await upcomingResponse.json() : {};
            const pendingPayload = pendingResponse.ok ? await pendingResponse.json() : {};
            const paymentPayload = paymentResponse.ok ? await paymentResponse.json() : {};
            const totalPayload = totalResponse.ok ? await totalResponse.json() : {};

            document.getElementById('statUpcoming').textContent = upcomingPayload.total || 0;
            document.getElementById('statPending').textContent = pendingPayload.total || 0;
            document.getElementById('statPayment').textContent = paymentPayload.total || 0;
            document.getElementById('statTotal').textContent = totalPayload.total || 0;
        } catch (error) {
            console.error(error);
        }
    }

    function updateStats(payload) {
        document.getElementById('tableSummary').textContent = `${payload.total || 0} reserva(s) encontrada(s)`;
    }

    function renderTable(reservations) {
        const tbody = document.getElementById('reservationsTableBody');

        if (!reservations.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7">
                        <div class="empty-state text-center text-muted">
                            <i class="bi bi-calendar-x display-4 d-block mb-2"></i>
                            <p class="mb-2">Você ainda não possui reservas com estes filtros.</p>
                            <a href="{{ route('reservations.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-calendar-plus"></i> Fazer uma reserva
                            </a>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = reservations.map(reservation => {
            const status = statusLabels[reservation.status] || { label: reservation.status, class: 'bg-light text-dark' };
            const paymentBadge = renderPaymentBadge(reservation);
            const amount = formatAmount(reservation);
            const actions = renderActions(reservation);

            return `
                <tr>
                    <td>
                        <strong>${escapeHtml(reservation.space?.name || '—')}</strong>
                        ${reservation.notes ? `<div class="small text-muted text-truncate" style="max-width: 220px;">${escapeHtml(reservation.notes)}</div>` : ''}
                    </td>
                    <td>${formatDate(reservation.reservation_date)}</td>
                    <td>${formatTimeRange(reservation.start_time, reservation.end_time)}</td>
                    <td><span class="badge status-pill ${status.class}">${status.label}</span></td>
                    <td>${paymentBadge}</td>
                    <td class="text-end">${amount}</td>
                    <td class="text-end table-actions">${actions}</td>
                </tr>
            `;
        }).join('');
    }

    function renderPaymentBadge(reservation) {
        if (reservation.prereservation_status && paymentLabels[reservation.prereservation_status]) {
            const payment = paymentLabels[reservation.prereservation_status];
            return `<span class="badge status-pill ${payment.class}">${payment.label}</span>`;
        }

        if (reservation.charge_summary) {
            const charge = reservation.charge_summary;
            if (charge.status === 'paid') {
                return '<span class="badge status-pill bg-success">Cobrança paga</span>';
            }
            if (charge.can_pay) {
                return '<span class="badge status-pill bg-warning text-dark">Cobrança pendente</span>';
            }
            return `<span class="badge status-pill bg-secondary">${escapeHtml(charge.status)}</span>`;
        }

        return '<span class="text-muted small">—</span>';
    }

    function formatAmount(reservation) {
        const chargeAmount = reservation.charge_summary?.amount;
        const preAmount = reservation.prereservation_amount;

        if (chargeAmount > 0) {
            return formatMoney(chargeAmount);
        }

        if (preAmount > 0) {
            return formatMoney(preAmount);
        }

        return '<span class="text-muted">Grátis</span>';
    }

    function renderActions(reservation) {
        const buttons = [];

        buttons.push(`
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.openReservationDetail(${reservation.id})">
                <i class="bi bi-eye"></i> Ver
            </button>
        `);

        if (canPay(reservation)) {
            const chargeId = reservation.charge_summary?.id;
            const payUrl = chargeId ? `${myChargesUrl}?pay=${chargeId}` : myChargesUrl;
            buttons.push(`
                <a href="${payUrl}" class="btn btn-success btn-sm">
                    <i class="bi bi-wallet2"></i> Pagar
                </a>
            `);
        }

        if (canCancel(reservation)) {
            buttons.push(`
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="window.cancelReservation(${reservation.id})">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
            `);
        }

        return `<div class="d-flex flex-wrap gap-1 justify-content-end">${buttons.join('')}</div>`;
    }

    function canPay(reservation) {
        if (reservation.prereservation_status === 'pending_payment') {
            return reservation.charge_summary?.can_pay || reservation.charge_summary?.id;
        }

        return reservation.charge_summary?.can_pay;
    }

    function canCancel(reservation) {
        return ['pending', 'approved'].includes(reservation.status);
    }

    function renderPagination(payload) {
        const container = document.getElementById('paginationContainer');
        const current = payload.current_page || 1;
        const last = payload.last_page || 1;

        if (last <= 1) {
            container.innerHTML = '';
            return;
        }

        container.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">Página ${current} de ${last}</small>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary" ${current <= 1 ? 'disabled' : ''} onclick="window.loadReservationsPage(${current - 1})">Anterior</button>
                    <button type="button" class="btn btn-outline-secondary" ${current >= last ? 'disabled' : ''} onclick="window.loadReservationsPage(${current + 1})">Próxima</button>
                </div>
            </div>
        `;
    }

    window.loadReservationsPage = function(page) {
        loadReservations(page);
    };

    window.openReservationDetail = async function(id) {
        const modal = getBootstrapModal(document.getElementById('reservationDetailModal'));
        const body = document.getElementById('reservationDetailBody');
        const footer = document.getElementById('reservationDetailFooter');

        body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
        footer.innerHTML = '';
        modal?.show();

        try {
            const response = await fetch(`/api/reservations/${id}?with_charge=1`, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) throw new Error('Não foi possível carregar os detalhes.');

            const reservation = await response.json();

            body.innerHTML = renderDetailHtml(reservation);
            footer.innerHTML = renderActions(reservation).replace('justify-content-end', 'justify-content-start');
        } catch (error) {
            body.innerHTML = `<div class="alert alert-danger mb-0">${escapeHtml(error.message)}</div>`;
        }
    };

    function renderDetailHtml(reservation) {
        const status = statusLabels[reservation.status] || { label: reservation.status, class: 'bg-light text-dark' };

        return `
            <div class="row g-3">
                <div class="col-md-6">
                    <small class="text-muted d-block">Espaço</small>
                    <strong>${escapeHtml(reservation.space?.name || '—')}</strong>
                </div>
                <div class="col-md-6">
                    <small class="text-muted d-block">Status</small>
                    <span class="badge ${status.class}">${status.label}</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Data</small>
                    <strong>${formatDate(reservation.reservation_date)}</strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Horário</small>
                    <strong>${formatTimeRange(reservation.start_time, reservation.end_time)}</strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Valor</small>
                    <strong>${formatAmount(reservation).replace(/<[^>]+>/g, '')}</strong>
                </div>
                ${reservation.notes ? `
                <div class="col-12">
                    <small class="text-muted d-block">Observações</small>
                    <div>${escapeHtml(reservation.notes)}</div>
                </div>` : ''}
                ${reservation.rejection_reason ? `
                <div class="col-12">
                    <small class="text-muted d-block">Motivo da rejeição</small>
                    <div class="text-danger">${escapeHtml(reservation.rejection_reason)}</div>
                </div>` : ''}
                ${reservation.cancellation_reason ? `
                <div class="col-12">
                    <small class="text-muted d-block">Motivo do cancelamento</small>
                    <div>${escapeHtml(reservation.cancellation_reason)}</div>
                </div>` : ''}
                ${reservation.payment_deadline ? `
                <div class="col-md-6">
                    <small class="text-muted d-block">Prazo de pagamento</small>
                    <strong>${formatDateTime(reservation.payment_deadline)}</strong>
                </div>` : ''}
                ${reservation.charge_summary ? `
                <div class="col-12">
                    <div class="alert alert-light border mb-0">
                        <strong>Cobrança vinculada:</strong>
                        ${formatMoney(reservation.charge_summary.amount)}
                        — vencimento ${formatDate(reservation.charge_summary.due_date)}
                        — status ${escapeHtml(reservation.charge_summary.status)}
                    </div>
                </div>` : ''}
            </div>
        `;
    }

    function animateProgress(barEl, textEl, from, to, duration = 500) {
        return new Promise((resolve) => {
            const start = performance.now();

            function step(now) {
                const progress = Math.min((now - start) / duration, 1);
                const value = from + (to - from) * progress;
                barEl.style.width = `${value}%`;
                if (textEl) {
                    textEl.textContent = `${Math.round(value)}%`;
                }

                if (progress < 1) {
                    requestAnimationFrame(step);
                } else {
                    resolve();
                }
            }

            requestAnimationFrame(step);
        });
    }

    function setCancelModalState(state) {
        const states = ['cancelModalLoading', 'cancelModalConfirm', 'cancelModalProcessing', 'cancelModalSuccess', 'cancelModalError'];
        states.forEach((id) => {
            document.getElementById(id)?.classList.add('d-none');
        });

        const map = {
            loading: 'cancelModalLoading',
            confirm: 'cancelModalConfirm',
            processing: 'cancelModalProcessing',
            success: 'cancelModalSuccess',
            error: 'cancelModalError',
        };

        document.getElementById(map[state])?.classList.remove('d-none');

        const confirmBtn = document.getElementById('cancelConfirmBtn');
        const dismissBtn = document.getElementById('cancelModalDismissBtn');
        const closeBtn = document.getElementById('cancelModalCloseBtn');

        if (state === 'confirm') {
            confirmBtn?.classList.remove('d-none');
            dismissBtn.textContent = 'Voltar';
            closeBtn?.removeAttribute('disabled');
        } else if (state === 'success') {
            confirmBtn?.classList.add('d-none');
            dismissBtn.textContent = 'Fechar';
            closeBtn?.removeAttribute('disabled');
        } else if (state === 'processing' || state === 'loading') {
            confirmBtn?.classList.add('d-none');
            dismissBtn.textContent = 'Fechar';
            closeBtn?.setAttribute('disabled', 'disabled');
        } else {
            confirmBtn?.classList.add('d-none');
            dismissBtn.textContent = 'Fechar';
            closeBtn?.removeAttribute('disabled');
        }
    }

    function buildCancelImpactMessage(reservation) {
        const messages = [];

        if (reservation.charge_summary?.status === 'paid') {
            messages.push(`Como a cobrança já foi paga, um crédito de <strong>${formatMoney(reservation.charge_summary.amount)}</strong> será adicionado à sua carteira (válido por 12 meses).`);
        } else if (reservation.charge_summary?.can_pay) {
            messages.push('A cobrança pendente vinculada a esta reserva será removida.');
        }

        if (reservation.prereservation_status === 'paid') {
            messages.push('Créditos utilizados nesta reserva serão devolvidos à sua carteira, quando aplicável.');
        }

        if (!messages.length) {
            messages.push('O horário ficará disponível para outros moradores.');
        }

        return messages.map((message) => `<li>${message}</li>`).join('');
    }

    window.cancelReservation = async function(id) {
        pendingCancelReservationId = id;
        pendingCancelReservation = null;

        setCancelModalState('loading');
        document.getElementById('cancelProgressBar').style.width = '0%';
        document.getElementById('cancelProgressText').textContent = '0%';
        getBootstrapModal(cancelModalEl)?.show();

        try {
            await animateProgress(
                document.getElementById('cancelProgressBar'),
                document.getElementById('cancelProgressText'),
                0,
                35,
                300
            );

            const response = await fetch(`/api/reservations/${id}?with_charge=1`, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) {
                throw new Error('Não foi possível carregar os dados da reserva.');
            }

            pendingCancelReservation = await response.json();

            await animateProgress(
                document.getElementById('cancelProgressBar'),
                document.getElementById('cancelProgressText'),
                35,
                100,
                400
            );

            const status = statusLabels[pendingCancelReservation.status] || { label: pendingCancelReservation.status };

            document.getElementById('cancelConfirmContent').innerHTML = `
                <p class="mb-3">Deseja cancelar esta reserva?</p>
                <div class="border rounded p-3 bg-light small mb-3">
                    <div><strong>Espaço:</strong> ${escapeHtml(pendingCancelReservation.space?.name || '—')}</div>
                    <div><strong>Data:</strong> ${formatDate(pendingCancelReservation.reservation_date)}</div>
                    <div><strong>Horário:</strong> ${formatTimeRange(pendingCancelReservation.start_time, pendingCancelReservation.end_time)}</div>
                    <div><strong>Status:</strong> ${escapeHtml(status.label)}</div>
                    <div><strong>Valor:</strong> ${formatAmount(pendingCancelReservation).replace(/<[^>]+>/g, '')}</div>
                </div>
                <ul class="small text-muted mb-0 ps-3">
                    ${buildCancelImpactMessage(pendingCancelReservation)}
                </ul>
            `;

            setCancelModalState('confirm');
        } catch (error) {
            document.getElementById('cancelErrorMessage').textContent = error.message || 'Erro ao preparar o cancelamento.';
            setCancelModalState('error');
        }
    };

    async function executeCancelReservation() {
        if (!pendingCancelReservationId) {
            return;
        }

        setCancelModalState('processing');
        document.getElementById('cancelProcessingBar').style.width = '0%';
        document.getElementById('cancelProcessingText').textContent = '0%';

        try {
            const progressPromise = animateProgress(
                document.getElementById('cancelProcessingBar'),
                document.getElementById('cancelProcessingText'),
                0,
                85,
                800
            );

            const response = await fetch(`/api/reservations/${pendingCancelReservationId}`, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const result = await response.json();
            await progressPromise;

            if (!response.ok) {
                throw new Error(result.error || 'Erro ao cancelar reserva.');
            }

            await animateProgress(
                document.getElementById('cancelProcessingBar'),
                document.getElementById('cancelProcessingText'),
                85,
                100,
                200
            );

            let message = result.message || 'Reserva cancelada com sucesso.';
            if (result.credit_generated) {
                message += ` Crédito de ${formatMoney(result.credit_amount)} adicionado à sua carteira.`;
            }

            document.getElementById('cancelSuccessMessage').textContent = message;
            setCancelModalState('success');

            if (result.total_user_credits !== undefined) {
                updateCreditsDisplay(result.total_user_credits);
            }

            await loadUserCredits();
            loadStats();
            loadReservations(currentPage);
        } catch (error) {
            document.getElementById('cancelErrorMessage').textContent = error.message || 'Erro ao cancelar reserva.';
            setCancelModalState('error');
        }
    }

    async function loadUserCredits() {
        const availableList = document.getElementById('walletAvailableList');
        const usageList = document.getElementById('walletUsageList');

        try {
            const response = await fetch('/api/user/credits', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) {
                throw new Error('Não foi possível carregar a carteira.');
            }

            const data = await response.json();
            updateCreditsDisplay(data.total);
            renderWalletLists(data);
        } catch (error) {
            availableList.innerHTML = `<div class="text-danger small py-3">${escapeHtml(error.message)}</div>`;
            usageList.innerHTML = `<div class="text-danger small py-3">${escapeHtml(error.message)}</div>`;
        }
    }

    function renderWalletLists(data) {
        const availableList = document.getElementById('walletAvailableList');
        const usageList = document.getElementById('walletUsageList');
        const credits = Array.isArray(data.credits) ? data.credits : [];
        const usage = Array.isArray(data.usage_history) ? data.usage_history : [];

        if (!credits.length) {
            availableList.innerHTML = '<div class="text-muted small py-3">Nenhum crédito disponível no momento.</div>';
        } else {
            availableList.innerHTML = credits.map((credit) => `
                <div class="wallet-item">
                    <div class="d-flex justify-content-between gap-2">
                        <div class="min-w-0">
                            <div class="fw-semibold text-success">${formatMoney(credit.amount)}</div>
                            <div class="small text-muted text-truncate" title="${escapeHtml(credit.description || '')}">
                                ${escapeHtml(credit.description || credit.type_label || 'Crédito')}
                            </div>
                        </div>
                        <div class="text-end small text-muted">
                            ${credit.expires_at ? `até ${formatDate(credit.expires_at)}` : 'Sem validade'}
                        </div>
                    </div>
                </div>
            `).join('');
        }

        if (!usage.length) {
            usageList.innerHTML = '<div class="text-muted small py-3">Nenhuma utilização registrada ainda.</div>';
        } else {
            usageList.innerHTML = usage.map((entry) => {
                const reservationLabel = entry.reservation?.space_name
                    ? `${entry.reservation.space_name} (${formatDate(entry.reservation.date)})`
                    : (entry.description || 'Reserva');

                return `
                    <div class="wallet-item">
                        <div class="d-flex justify-content-between gap-2">
                            <div class="min-w-0">
                                <div class="fw-semibold text-danger">−${formatMoney(entry.amount)}</div>
                                <div class="small text-muted text-truncate" title="${escapeHtml(reservationLabel)}">
                                    ${escapeHtml(reservationLabel)}
                                </div>
                            </div>
                            <div class="text-end small text-muted text-nowrap">
                                ${entry.used_at ? formatDateTime(entry.used_at) : '—'}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }
    }

    function updateCreditsDisplay(total) {
        const numericTotal = Math.max(0, parseFloat(total) || 0);
        const formatted = `R$ ${numericTotal.toFixed(2).replace('.', ',')}`;

        const totalEl = document.getElementById('totalCredits');
        const badgeEl = document.getElementById('walletTotalBadge');

        if (totalEl) {
            totalEl.textContent = formatted;
        }

        if (badgeEl) {
            badgeEl.textContent = formatted;
            badgeEl.classList.toggle('bg-success', numericTotal > 0);
            badgeEl.classList.toggle('bg-secondary', numericTotal <= 0);
        }
    }

    function formatDate(value) {
        if (!value) return '—';
        const dateStr = String(value).split('T')[0];
        const [year, month, day] = dateStr.split('-');
        return new Date(Number(year), Number(month) - 1, Number(day)).toLocaleDateString('pt-BR');
    }

    function formatDateTime(value) {
        if (!value) return '—';
        return new Date(value).toLocaleString('pt-BR');
    }

    function formatTimeRange(start, end) {
        if (!start && !end) return 'Dia inteiro';
        return `${(start || '—').slice(0, 5)} às ${(end || '—').slice(0, 5)}`;
    }

    function formatMoney(value) {
        return `R$ ${Number(value || 0).toFixed(2).replace('.', ',')}`;
    }

    function escapeHtml(value) {
        return (value ?? '').toString().replace(/[&<>"']/g, (match) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
        })[match]);
    }

    document.getElementById('cancelConfirmBtn')?.addEventListener('click', executeCancelReservation);

    cancelModalEl?.addEventListener('hidden.bs.modal', () => {
        pendingCancelReservationId = null;
        pendingCancelReservation = null;
    });

    document.addEventListener('DOMContentLoaded', () => {
        loadSpaces();
        loadStats();
        loadReservations();
        loadUserCredits();
    });
})();
</script>
@endpush
