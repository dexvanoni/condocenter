@extends('layouts.app')

@section('title', 'Minhas Cobranças')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">Minhas Cobranças</h2>
        <p class="text-muted mb-0">
            Unidade <strong>{{ $unitLabel ?? '—' }}</strong> — acompanhe, pague e exporte o histórico das suas cobranças.
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="#" id="exportChargesPdfBtn" class="btn btn-outline-primary">
            <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
        </a>
    </div>
</div>

<div id="chargesAlertContainer" class="mb-3"></div>

@if(!($onlinePaymentsEnabled ?? false))
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    O condomínio utiliza <strong>recebimento manual</strong>. Você pode consultar suas cobranças aqui; o pagamento online (PIX/cartão) só aparece quando o síndico habilitar o recebimento via Asaas.
</div>
@endif

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <h6 class="text-muted mb-2">Pendentes</h6>
                <h3 class="mb-0" id="totalPending">--</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card danger">
            <div class="card-body">
                <h6 class="text-muted mb-2">Em atraso</h6>
                <h3 class="mb-0 text-danger" id="totalOverdue">--</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card success">
            <div class="card-body">
                <h6 class="text-muted mb-2">Pagas este mês</h6>
                <h3 class="mb-0 text-success" id="totalPaid">--</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <h6 class="text-muted mb-2">Em aberto</h6>
                <h3 class="mb-0" id="totalOpenAmount">R$ --</h3>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label" for="filterStatus">Status</label>
                <select class="form-select" id="filterStatus">
                    <option value="">Todos</option>
                    <option value="pending">Pendentes</option>
                    <option value="overdue">Em atraso</option>
                    <option value="paid">Pagas</option>
                    <option value="cancelled">Canceladas</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="filterMonth">Mês de vencimento</label>
                <input type="month" class="form-control" id="filterMonth">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="searchInput">Buscar</label>
                <input type="text" class="form-control" id="searchInput" placeholder="Título ou descrição">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" type="button" onclick="loadCharges()">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="chargesTable">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Vencimento</th>
                        <th>Pago em</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="text-muted mt-2 mb-0">Carregando cobranças...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="chargesPagination" class="mt-3"></div>
    </div>
</div>

<div class="modal fade" id="chargeDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes da cobrança</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Título</dt>
                    <dd class="col-sm-8" id="detailChargeTitle">—</dd>
                    <dt class="col-sm-4">Valor</dt>
                    <dd class="col-sm-8" id="detailChargeAmount">—</dd>
                    <dt class="col-sm-4">Vencimento</dt>
                    <dd class="col-sm-8" id="detailChargeDueDate">—</dd>
                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8" id="detailChargeStatus">—</dd>
                    <dt class="col-sm-4">Observações</dt>
                    <dd class="col-sm-8" id="detailChargeNotes">—</dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" id="detailChargePayBtn" class="btn btn-success d-none" onclick="openChargeCheckout(selectedChargeId)">
                    <i class="bi bi-wallet2"></i> Pagar online
                </button>
                <a href="#" id="detailChargeReceiptBtn" class="btn btn-outline-primary d-none" target="_blank" rel="noopener">
                    <i class="bi bi-file-earmark-pdf"></i> Comprovante
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

@if($onlinePaymentsEnabled ?? false)
    @include('charges.partials.payment-checkout')
@endif

@push('scripts')
<script>
    const chargesDataUrl = "{{ route('charges.data') }}";
    const chargesExportUrl = "{{ route('my-charges.export-pdf') }}";
    const chargeBaseUrl = "{{ url('/charges') }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let chargesCurrentPage = 1;
    let chargesCache = new Map();
    let chargePermissions = { can_pay_online: false };
    let chargeDetailsModal;
    let selectedChargeId = null;

    function formatCurrency(value) {
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(value ?? 0));
    }

    function formatDate(value) {
        if (!value) return '—';
        const date = new Date(value);
        return Number.isNaN(date.getTime()) ? value : date.toLocaleDateString('pt-BR');
    }

    function statusBadge(status) {
        const map = {
            pending: { label: 'Pendente', color: 'warning' },
            overdue: { label: 'Em atraso', color: 'danger' },
            paid: { label: 'Pago', color: 'success' },
            cancelled: { label: 'Cancelada', color: 'secondary' },
        };
        const info = map[status] || { label: status ?? '—', color: 'secondary' };
        return `<span class="badge bg-${info.color}">${info.label}</span>`;
    }

    function buildMonthRange(monthValue) {
        if (!monthValue) return { start: null, end: null };
        const [year, month] = monthValue.split('-').map(Number);
        const lastDay = new Date(year, month, 0).getDate();
        return {
            start: `${year}-${String(month).padStart(2, '0')}-01`,
            end: `${year}-${String(month).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`,
        };
    }

    function currentFilterParams() {
        const params = new URLSearchParams();
        const status = document.getElementById('filterStatus').value;
        const month = document.getElementById('filterMonth').value;
        const search = document.getElementById('searchInput').value.trim();

        if (status) params.append('status', status);
        if (search) params.append('search', search);

        if (month) {
            const range = buildMonthRange(month);
            if (range.start && range.end) {
                params.append('start_date', range.start);
                params.append('end_date', range.end);
            }
        }

        return params;
    }

    function showAlert(type, message) {
        const container = document.getElementById('chargesAlertContainer');
        const wrapper = document.createElement('div');
        wrapper.className = `alert alert-${type} alert-dismissible fade show`;
        wrapper.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        container.appendChild(wrapper);
        setTimeout(() => wrapper.remove(), 6000);
    }

    function buildActions(charge) {
        const buttons = [
            `<button type="button" class="btn btn-outline-secondary btn-sm" onclick="openChargeDetails(${charge.id})">Ver</button>`,
        ];

        if (charge.can_pay_online) {
            buttons.push(`<button type="button" class="btn btn-success btn-sm" onclick="openChargeCheckout(${charge.id})"><i class="bi bi-wallet2"></i> Pagar</button>`);
        }

        if (charge.status === 'paid') {
            buttons.push(`<a href="${chargeBaseUrl}/${charge.id}/receipt" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener" title="Comprovante PDF"><i class="bi bi-file-earmark-pdf"></i></a>`);
        }

        return `<div class="d-flex flex-wrap gap-1">${buttons.join('')}</div>`;
    }

    function setLoadingState() {
        document.querySelector('#chargesTable tbody').innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2 mb-0">Carregando cobranças...</p>
                </td>
            </tr>
        `;
    }

    function updateSummary(summary = {}) {
        document.getElementById('totalPending').textContent = summary.pending ?? 0;
        document.getElementById('totalOverdue').textContent = summary.overdue ?? 0;
        document.getElementById('totalPaid').textContent = summary.paid_this_month ?? 0;
        document.getElementById('totalOpenAmount').textContent = formatCurrency(summary.amount_to_receive ?? 0);
    }

    function renderPagination(meta = {}) {
        const container = document.getElementById('chargesPagination');
        container.innerHTML = '';
        if (!meta || meta.last_page <= 1) return;

        const nav = document.createElement('nav');
        const ul = document.createElement('ul');
        ul.className = 'pagination pagination-sm justify-content-end mb-0';

        const createItem = (label, disabled, page) => {
            const li = document.createElement('li');
            li.className = `page-item ${disabled ? 'disabled' : ''}`;
            const a = document.createElement('a');
            a.className = 'page-link';
            a.href = '#';
            a.textContent = label;
            if (!disabled) {
                a.addEventListener('click', event => {
                    event.preventDefault();
                    loadCharges(page);
                });
            }
            li.appendChild(a);
            return li;
        };

        ul.appendChild(createItem('Anterior', meta.current_page <= 1, meta.current_page - 1));
        for (let page = 1; page <= meta.last_page; page++) {
            if (page === meta.current_page || page === 1 || page === meta.last_page || Math.abs(page - meta.current_page) <= 1) {
                const li = createItem(String(page), page === meta.current_page, page);
                if (page === meta.current_page) li.classList.add('active');
                ul.appendChild(li);
            } else if (Math.abs(page - meta.current_page) === 2) {
                const li = document.createElement('li');
                li.className = 'page-item disabled';
                li.innerHTML = '<span class="page-link">…</span>';
                ul.appendChild(li);
            }
        }
        ul.appendChild(createItem('Próximo', meta.current_page >= meta.last_page, meta.current_page + 1));
        nav.appendChild(ul);
        container.appendChild(nav);
    }

    function renderCharges(response) {
        chargePermissions = response.permissions || { can_pay_online: false };
        chargesCache = new Map();
        updateSummary(response.summary);

        const tbody = document.querySelector('#chargesTable tbody');
        tbody.innerHTML = '';
        const charges = Array.isArray(response.data) ? response.data : [];

        if (charges.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">Nenhuma cobrança encontrada.</td></tr>`;
        } else {
            charges.forEach(charge => {
                chargesCache.set(charge.id, charge);
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><strong>${charge.title}</strong></td>
                    <td>${formatDate(charge.due_date)}</td>
                    <td>${formatDate(charge.paid_at)}</td>
                    <td>${formatCurrency(charge.amount)}</td>
                    <td>${statusBadge(charge.status)}</td>
                    <td>${buildActions(charge)}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        renderPagination(response.meta);
    }

    function loadCharges(page = 1) {
        chargesCurrentPage = page;
        setLoadingState();

        const params = currentFilterParams();
        params.append('page', page);

        fetch(`${chargesDataUrl}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(response => {
                if (!response.ok) throw new Error(`Erro ${response.status}`);
                return response.json();
            })
            .then(renderCharges)
            .catch(() => {
                document.querySelector('#chargesTable tbody').innerHTML = `
                    <tr><td colspan="6" class="text-center text-danger py-4">Não foi possível carregar as cobranças.</td></tr>
                `;
            });
    }

    function openChargeDetails(id) {
        selectedChargeId = id;
        const receiptBtn = document.getElementById('detailChargeReceiptBtn');
        const payBtn = document.getElementById('detailChargePayBtn');
        receiptBtn.classList.add('d-none');
        payBtn.classList.add('d-none');

        fetch(`${chargeBaseUrl}/${id}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(response => response.json())
            .then(data => {
                const charge = data.charge || {};
                document.getElementById('detailChargeTitle').textContent = charge.title ?? '—';
                document.getElementById('detailChargeAmount').textContent = formatCurrency(charge.amount);
                document.getElementById('detailChargeDueDate').textContent = formatDate(charge.due_date);
                document.getElementById('detailChargeStatus').innerHTML = statusBadge(charge.status);
                document.getElementById('detailChargeNotes').textContent = charge.description ?? '—';

                if (data.receipt_url) {
                    receiptBtn.href = data.receipt_url;
                    receiptBtn.classList.remove('d-none');
                }

                if (data.can_pay_online) {
                    payBtn.classList.remove('d-none');
                }
            })
            .catch(() => showAlert('danger', 'Não foi possível carregar os detalhes.'));

        chargeDetailsModal.show();
    }

    function exportChargesPdf(event) {
        event.preventDefault();
        const params = currentFilterParams();
        window.location.href = `${chargesExportUrl}?${params.toString()}`;
    }

    document.addEventListener('DOMContentLoaded', () => {
        window.chargePaymentOnSuccess = () => loadCharges(chargesCurrentPage);

        chargeDetailsModal = new bootstrap.Modal(document.getElementById('chargeDetailsModal'));
        document.getElementById('exportChargesPdfBtn').addEventListener('click', exportChargesPdf);
        document.getElementById('filterStatus').addEventListener('change', () => loadCharges());
        document.getElementById('filterMonth').addEventListener('change', () => loadCharges());
        document.getElementById('searchInput').addEventListener('keyup', event => {
            if (event.key === 'Enter') loadCharges();
        });

        const payParam = new URLSearchParams(window.location.search).get('pay');
        loadCharges();
        if (payParam && typeof window.openChargeCheckout === 'function') {
            window.openChargeCheckout(payParam);
        }
    });

    window.loadCharges = loadCharges;
    window.openChargeDetails = openChargeDetails;
</script>
@endpush
@endsection
