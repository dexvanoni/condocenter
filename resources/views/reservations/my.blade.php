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
            <div class="alert alert-{{ ($initialUserCredits ?? 0) > 0 ? 'success' : 'light border' }} py-2 px-3 mb-0">
                <i class="bi bi-wallet2"></i>
                <strong>Créditos:</strong>
                <span id="totalCredits">R$ {{ number_format((float) ($initialUserCredits ?? 0), 2, ',', '.') }}</span>
            </div>
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
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('reservationDetailModal'));
        const body = document.getElementById('reservationDetailBody');
        const footer = document.getElementById('reservationDetailFooter');

        body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
        footer.innerHTML = '';
        modal.show();

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

    window.cancelReservation = async function(id) {
        if (!confirm('Deseja cancelar esta reserva? Esta ação não pode ser desfeita.')) {
            return;
        }

        try {
            const response = await fetch(`/api/reservations/${id}`, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const result = await response.json();

            if (!response.ok) {
                alert(result.error || 'Erro ao cancelar reserva.');
                return;
            }

            let message = result.message || 'Reserva cancelada com sucesso.';

            if (result.credit_generated) {
                message += `\n\nCrédito gerado: R$ ${Number(result.credit_amount || 0).toFixed(2).replace('.', ',')}`;
            }

            alert(message);
            loadStats();
            loadReservations(currentPage);
        } catch (error) {
            alert('Erro ao cancelar reserva.');
        }
    };

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

    document.addEventListener('DOMContentLoaded', () => {
        loadSpaces();
        loadStats();
        loadReservations();
    });
})();
</script>
@endpush
