@extends('layouts.app')

@section('title', 'Cobranças')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gerenciamento de Cobranças</h2>
            @can('manage_charges')
            <div>
                <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#gerarCobrancasModal">
                    <i class="bi bi-plus-circle"></i> Gerar Cobranças em Lote
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#novaCobrancaModal">
                    <i class="bi bi-receipt"></i> Nova Cobrança
                </button>
            </div>
            @endcan
        </div>
    </div>
</div>

<div id="chargesAlertContainer" class="mb-3"></div>

<!-- Resumo -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <h6 class="text-muted mb-2">Pendentes</h6>
                <h3 class="mb-0" id="totalPending">--</h3>
                <small class="text-muted">cobranças</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card danger">
            <div class="card-body">
                <h6 class="text-muted mb-2">Em Atraso</h6>
                <h3 class="mb-0 text-danger" id="totalOverdue">--</h3>
                <small class="text-muted">cobranças</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card success">
            <div class="card-body">
                <h6 class="text-muted mb-2">Pagas</h6>
                <h3 class="mb-0 text-success" id="totalPaid">--</h3>
                <small class="text-muted">este mês</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <h6 class="text-muted mb-2">A Receber</h6>
                <h3 class="mb-0" id="totalToReceive">R$ --</h3>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-2">
                <select class="form-select" id="filterStatus">
                    <option value="">Todos os Status</option>
                    <option value="pending">Pendentes</option>
                    <option value="overdue">Em Atraso</option>
                    <option value="paid">Pagas</option>
                    <option value="cancelled">Canceladas</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filterUnit">
                    <option value="">Todas as Unidades</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="month" class="form-control" id="filterMonth">
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" placeholder="Buscar..." id="searchInput">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" onclick="loadCharges()">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabela de Cobranças -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="chargesTable">
                <thead>
                    <tr>
                        <th>Unidade</th>
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
                        <td colspan="7" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                            <p class="text-muted mt-2">Carregando cobranças...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="chargesPagination" class="mt-3"></div>
    </div>
</div>

<!-- Modais utilitários -->
<div class="modal fade" id="gerarCobrancasModal" tabindex="-1" aria-labelledby="gerarCobrancasModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gerarCobrancasModalLabel">Gerar cobranças em lote</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Para gerar cobranças em lote, selecione a taxa desejada e utilize a ação <strong>"Gerar próxima cobrança"</strong> na página da taxa.</p>
                <p class="mb-0">Você pode acessar a lista de taxas no botão abaixo.</p>
            </div>
            <div class="modal-footer">
                <a href="{{ route('fees.index') }}" class="btn btn-primary">Ir para taxas</a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="novaCobrancaModal" tabindex="-1" aria-labelledby="novaCobrancaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="novaCobrancaModalLabel">Nova cobrança manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">As cobranças são geradas automaticamente a partir das taxas cadastradas. Para lançar uma cobrança manual, utilize a função de <strong>"Recebimento avulso"</strong> em <em>Contas do Condomínio</em>.</p>
                <p class="mb-0">Abra a área financeira para registrar uma entrada manual.</p>
            </div>
            <div class="modal-footer">
                <a href="{{ route('financial.accounts.index') }}" class="btn btn-primary">Ir para Contas do Condomínio</a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modais -->
<div class="modal fade" id="chargeDetailsModal" tabindex="-1" aria-labelledby="chargeDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="chargeDetailsModalLabel">Detalhes da Cobrança</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Título</dt>
                    <dd class="col-sm-8" id="detailChargeTitle">—</dd>

                    <dt class="col-sm-4">Unidade</dt>
                    <dd class="col-sm-8" id="detailChargeUnit">—</dd>

                    <dt class="col-sm-4">Valor</dt>
                    <dd class="col-sm-8" id="detailChargeAmount">—</dd>

                    <dt class="col-sm-4">Vencimento</dt>
                    <dd class="col-sm-8" id="detailChargeDueDate">—</dd>

                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8" id="detailChargeStatus">—</dd>

                    <dt class="col-sm-4">Tipo/Taxa</dt>
                    <dd class="col-sm-8" id="detailChargeFee">—</dd>

                    <dt class="col-sm-4">Observações</dt>
                    <dd class="col-sm-8" id="detailChargeNotes">—</dd>
                </dl>

                <hr>

                <h6 class="fw-semibold">Pagamentos registrados</h6>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Método</th>
                                <th class="text-end">Quantidade</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody id="detailChargePaymentsBody">
                            <tr>
                                <td colspan="3" class="text-center text-muted">Nenhum pagamento registrado.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="receiveChargeModal" tabindex="-1" aria-labelledby="receiveChargeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receiveChargeModalLabel">Receber Cobrança</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="receiveChargeForm">
                <div class="modal-body">
                    <div id="receiveChargeErrors" class="alert alert-danger d-none"></div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Cobrança</label>
                        <div id="receiveChargeSummary" class="text-muted">—</div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="receivePaidAt" class="form-label">Data do recebimento</label>
                            <input type="date" class="form-control" id="receivePaidAt" required>
                        </div>
                        <div class="col-md-6">
                            <label for="receivePaymentMethod" class="form-label">Método</label>
                            <select class="form-select" id="receivePaymentMethod" required>
                                <option value="cash">Dinheiro</option>
                                <option value="pix">PIX</option>
                                <option value="bank_transfer">Transferência bancária</option>
                                <option value="credit_card">Cartão de crédito</option>
                                <option value="debit_card">Cartão de débito</option>
                                <option value="boleto">Boleto</option>
                                <option value="other">Outros</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label for="receiveNotes" class="form-label">Observações (opcional)</label>
                        <textarea id="receiveNotes" class="form-control" rows="3" placeholder="Ex.: identificação do comprovante"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Confirmar recebimento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteChargeModal" tabindex="-1" aria-labelledby="deleteChargeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteChargeModalLabel">Cancelar Cobrança</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="deleteChargeForm">
                <div class="modal-body">
                    <div id="deleteChargeErrors" class="alert alert-danger d-none"></div>
                    <p class="mb-3">Tem certeza de que deseja cancelar esta cobrança? A unidade deixará de ser cobrada e o lançamento será removido da previsão de recebimentos.</p>
                    <div class="mb-3">
                        <label for="deleteChargeReason" class="form-label">Motivo do cancelamento (opcional)</label>
                        <textarea id="deleteChargeReason" class="form-control" rows="3" placeholder="Descreva o motivo, se necessário."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Voltar</button>
                    <button type="submit" class="btn btn-danger">Confirmar cancelamento</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const chargesDataUrl = "{{ route('charges.data') }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const chargeBaseUrl = "{{ url('/charges') }}";

    let chargesCurrentPage = 1;
    let chargesCache = new Map();
    let chargePermissions = { can_manage: false };
    let chargeDetailsModal;
    let receiveChargeModal;
    let deleteChargeModal;
    let selectedChargeId = null;

    function formatCurrency(value) {
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(value ?? 0));
    }

    function formatDate(value) {
        if (!value) {
            return '—';
        }
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return value;
        }
        return date.toLocaleDateString('pt-BR');
    }

    function statusBadge(status) {
        const map = {
            pending: { label: 'Pendente', color: 'warning' },
            overdue: { label: 'Em Atraso', color: 'danger' },
            paid: { label: 'Pago', color: 'success' },
            cancelled: { label: 'Cancelado', color: 'secondary' },
        };

        const info = map[status] || { label: status ?? '—', color: 'secondary' };
        return `<span class="badge bg-${info.color}">${info.label}</span>`;
    }

    function buildMonthRange(monthValue) {
        if (!monthValue) {
            return {};
        }
        const parts = monthValue.split('-');
        if (parts.length !== 2) {
            return {};
        }
        const year = parseInt(parts[0], 10);
        const month = parseInt(parts[1], 10);
        if (Number.isNaN(year) || Number.isNaN(month)) {
            return {};
        }
        const lastDay = new Date(year, month, 0).getDate();
        return {
            start: `${year}-${parts[1]}-01`,
            end: `${year}-${parts[1]}-${String(lastDay).padStart(2, '0')}`,
        };
    }

    function setLoadingState() {
        const tbody = document.querySelector('#chargesTable tbody');
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="text-muted mt-2">Carregando cobranças...</p>
                </td>
            </tr>
        `;
    }

    function showAlert(type, message) {
        const container = document.getElementById('chargesAlertContainer');
        if (!container) return;

        const wrapper = document.createElement('div');
        wrapper.className = `alert alert-${type} alert-dismissible fade show`;
        wrapper.role = 'alert';
        wrapper.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        `;
        container.appendChild(wrapper);

        setTimeout(() => {
            const alert = bootstrap.Alert.getOrCreateInstance(wrapper);
            alert.close();
        }, 6000);
    }

    function clearErrors(container) {
        if (!container) return;
        container.classList.add('d-none');
        container.innerHTML = '';
    }

    function displayErrors(container, errors) {
        if (!container) return;
        container.classList.remove('d-none');

        if (typeof errors === 'string') {
            container.innerHTML = errors;
            return;
        }

        const list = document.createElement('ul');
        list.className = 'mb-0';

        Object.values(errors).forEach(messages => {
            (Array.isArray(messages) ? messages : [messages]).forEach(message => {
                const li = document.createElement('li');
                li.textContent = message;
                list.appendChild(li);
            });
        });

        container.innerHTML = '';
        container.appendChild(list);
    }

    function buildActions(charge) {
        const buttons = [];
        buttons.push(`<button type="button" class="btn btn-outline-secondary btn-sm" onclick="openChargeDetails(${charge.id})">Ver</button>`);

        if (chargePermissions.can_manage && ['pending', 'overdue'].includes(charge.status)) {
            buttons.push(`<button type="button" class="btn btn-outline-success btn-sm" onclick="openReceiveChargeModal(${charge.id})">Receber</button>`);
        }

        if (chargePermissions.can_manage && ['pending', 'overdue'].includes(charge.status)) {
            buttons.push(`<button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmDeleteCharge(${charge.id})">Excluir</button>`);
        }

        if (buttons.length === 0) {
            return '—';
        }

        return `<div class="btn-group" role="group">${buttons.join('')}</div>`;
    }

    function loadCharges(page = 1) {
        chargesCurrentPage = page;
        setLoadingState();

        const status = document.getElementById('filterStatus').value;
        const unitId = document.getElementById('filterUnit').value;
        const month = document.getElementById('filterMonth').value;
        const search = document.getElementById('searchInput').value.trim();
        const params = new URLSearchParams();

        params.append('page', page);
        if (status) params.append('status', status);
        if (unitId) params.append('unit_id', unitId);

        if (month) {
            const range = buildMonthRange(month);
            if (range.start && range.end) {
                params.append('start_date', range.start);
                params.append('end_date', range.end);
            }
        }

        if (search) params.append('search', search);

        fetch(`${chargesDataUrl}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erro ${response.status}`);
                }
                return response.json();
            })
            .then(renderCharges)
            .catch(error => {
                console.error('Erro ao carregar cobranças:', error);
                const tbody = document.querySelector('#chargesTable tbody');
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center text-danger py-4">
                            Não foi possível carregar as cobranças. Tente novamente.
                        </td>
                    </tr>
                `;
            });
    }

    function updateSummary(summary = {}) {
        document.getElementById('totalPending').textContent = summary.pending ?? 0;
        document.getElementById('totalOverdue').textContent = summary.overdue ?? 0;
        document.getElementById('totalPaid').textContent = summary.paid_this_month ?? 0;
        document.getElementById('totalToReceive').textContent = formatCurrency(summary.amount_to_receive ?? 0);
    }

    function populateUnits(units = []) {
        const select = document.getElementById('filterUnit');
        const currentValue = select.value;
        select.innerHTML = '<option value="">Todas as Unidades</option>';

        units.forEach(unit => {
            const option = document.createElement('option');
            option.value = unit.id;
            option.textContent = unit.label;
            select.appendChild(option);
        });

        if (currentValue && Array.from(select.options).some(option => option.value === currentValue)) {
            select.value = currentValue;
        }
    }

    function renderPagination(meta = {}) {
        const container = document.getElementById('chargesPagination');
        container.innerHTML = '';

        if (!meta || meta.last_page <= 1) {
            return;
        }

        const nav = document.createElement('nav');
        nav.setAttribute('aria-label', 'Paginação de cobranças');

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
                const li = createItem(page, page === meta.current_page, page);
                if (page === meta.current_page) {
                    li.classList.add('active');
                    li.querySelector('a').setAttribute('aria-current', 'page');
                }
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
        chargePermissions = response.permissions || { can_manage: false };
        chargesCache = new Map();

        updateSummary(response.summary);
        if (response.filters && Array.isArray(response.filters.units)) {
            populateUnits(response.filters.units);
        }

        const tbody = document.querySelector('#chargesTable tbody');
        tbody.innerHTML = '';

        const charges = Array.isArray(response.data) ? response.data : [];

        if (charges.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        Nenhuma cobrança encontrada para os filtros selecionados.
                    </td>
                </tr>
            `;
        } else {
            charges.forEach(charge => {
                chargesCache.set(charge.id, charge);

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${charge.unit?.full_identifier ?? '—'}</td>
                    <td>${charge.title}</td>
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

    function openChargeDetails(id) {
        selectedChargeId = id;
        document.getElementById('detailChargeTitle').textContent = 'Carregando...';
        document.getElementById('detailChargeUnit').textContent = '—';
        document.getElementById('detailChargeAmount').textContent = '—';
        document.getElementById('detailChargeDueDate').textContent = '—';
        document.getElementById('detailChargeStatus').innerHTML = statusBadge(null);
        document.getElementById('detailChargeFee').textContent = '—';
        document.getElementById('detailChargeNotes').textContent = '—';
        document.getElementById('detailChargePaymentsBody').innerHTML = `
            <tr>
                <td colspan="3" class="text-center text-muted">Carregando dados...</td>
            </tr>
        `;

        fetch(`${chargeBaseUrl}/${id}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erro ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                const charge = data.charge || {};
                document.getElementById('detailChargeTitle').textContent = charge.title ?? '—';
                document.getElementById('detailChargeUnit').textContent = charge.unit?.full_identifier ?? '—';
                document.getElementById('detailChargeAmount').textContent = formatCurrency(charge.amount);
                document.getElementById('detailChargeDueDate').textContent = formatDate(charge.due_date);
                document.getElementById('detailChargeStatus').innerHTML = statusBadge(charge.status);
                document.getElementById('detailChargeFee').textContent = charge.fee?.name ?? '—';
                document.getElementById('detailChargeNotes').textContent = charge.description ?? '—';

                const paymentsBody = document.getElementById('detailChargePaymentsBody');
                paymentsBody.innerHTML = '';
                const summary = Array.isArray(data.payment_summary) ? data.payment_summary : [];
                if (summary.length === 0) {
                    paymentsBody.innerHTML = `
                        <tr>
                            <td colspan="3" class="text-center text-muted">Nenhum pagamento registrado.</td>
                        </tr>
                    `;
                } else {
                    summary.forEach(item => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${item.method}</td>
                            <td class="text-end">${item.transactions}</td>
                            <td class="text-end">${formatCurrency(item.total)}</td>
                        `;
                        paymentsBody.appendChild(tr);
                    });
                }
            })
            .catch(error => {
                console.error('Erro ao buscar detalhes da cobrança:', error);
                showAlert('danger', 'Não foi possível carregar os detalhes da cobrança.');
            });

        chargeDetailsModal.show();
    }

    function openReceiveChargeModal(id) {
        const charge = chargesCache.get(id);
        if (!charge) {
            showAlert('danger', 'Não foi possível identificar a cobrança selecionada.');
            return;
        }

        selectedChargeId = id;
        clearErrors(document.getElementById('receiveChargeErrors'));

        document.getElementById('receiveChargeSummary').textContent = `${charge.unit?.full_identifier ?? '—'} • ${charge.title} (${formatCurrency(charge.amount)})`;
        document.getElementById('receivePaidAt').value = new Date().toISOString().slice(0, 10);
        document.getElementById('receivePaymentMethod').value = charge.metadata?.manual_payment_method || charge.metadata?.payment_channel || 'cash';
        document.getElementById('receiveNotes').value = '';

        receiveChargeModal.show();
    }

    function submitReceiveCharge(event) {
        event.preventDefault();
        if (!selectedChargeId) {
            showAlert('danger', 'Nenhuma cobrança selecionada.');
            return;
        }

        const paidAt = document.getElementById('receivePaidAt').value;
        const paymentMethod = document.getElementById('receivePaymentMethod').value;
        const notes = document.getElementById('receiveNotes').value;
        const errorsContainer = document.getElementById('receiveChargeErrors');
        clearErrors(errorsContainer);

        fetch(`${chargeBaseUrl}/${selectedChargeId}/mark-paid`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ paid_at: paidAt, payment_method: paymentMethod, notes })
        })
            .then(async response => {
                if (!response.ok) {
                    const data = await response.json().catch(() => ({}));
                    throw { status: response.status, data };
                }
                return response.json();
            })
            .then(data => {
                receiveChargeModal.hide();
                showAlert('success', data.message || 'Cobrança recebida com sucesso.');
                loadCharges(chargesCurrentPage);
            })
            .catch(error => {
                if (error.status === 422) {
                    displayErrors(errorsContainer, error.data?.errors || 'Verifique os dados informados.');
                } else {
                    console.error('Erro ao marcar cobrança como paga:', error);
                    displayErrors(errorsContainer, 'Não foi possível registrar o recebimento.');
                }
            });
    }

    function confirmDeleteCharge(id) {
        const charge = chargesCache.get(id);
        if (!charge) {
            showAlert('danger', 'Não foi possível identificar a cobrança selecionada.');
            return;
        }

        selectedChargeId = id;
        clearErrors(document.getElementById('deleteChargeErrors'));
        document.getElementById('deleteChargeReason').value = '';
        document.getElementById('deleteChargeModalLabel').textContent = `Cancelar Cobrança - ${charge.title}`;
        deleteChargeModal.show();
    }

    function submitDeleteCharge(event) {
        event.preventDefault();
        if (!selectedChargeId) {
            showAlert('danger', 'Nenhuma cobrança selecionada.');
            return;
        }

        const reason = document.getElementById('deleteChargeReason').value;
        const errorsContainer = document.getElementById('deleteChargeErrors');
        clearErrors(errorsContainer);

        fetch(`${chargeBaseUrl}/${selectedChargeId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ _method: 'DELETE', reason })
        })
            .then(async response => {
                if (!response.ok) {
                    const data = await response.json().catch(() => ({}));
                    throw { status: response.status, data };
                }
                return response.json();
            })
            .then(data => {
                deleteChargeModal.hide();
                showAlert('success', data.message || 'Cobrança cancelada com sucesso.');
                loadCharges(chargesCurrentPage);
            })
            .catch(error => {
                if (error.status === 422) {
                    displayErrors(errorsContainer, error.data?.errors || 'Não foi possível cancelar a cobrança.');
                } else {
                    console.error('Erro ao cancelar cobrança:', error);
                    displayErrors(errorsContainer, 'Não foi possível cancelar a cobrança.');
                }
            });
    }

    document.addEventListener('DOMContentLoaded', () => {
        chargeDetailsModal = new bootstrap.Modal(document.getElementById('chargeDetailsModal'));
        receiveChargeModal = new bootstrap.Modal(document.getElementById('receiveChargeModal'));
        deleteChargeModal = new bootstrap.Modal(document.getElementById('deleteChargeModal'));

        document.getElementById('filterStatus').addEventListener('change', () => loadCharges());
        document.getElementById('filterUnit').addEventListener('change', () => loadCharges());
        document.getElementById('filterMonth').addEventListener('change', () => loadCharges());
        document.getElementById('searchInput').addEventListener('keyup', event => {
            if (event.key === 'Enter') {
                loadCharges();
            }
        });

        document.getElementById('receiveChargeForm').addEventListener('submit', submitReceiveCharge);
        document.getElementById('deleteChargeForm').addEventListener('submit', submitDeleteCharge);

        document.getElementById('receiveChargeModal').addEventListener('hidden.bs.modal', () => {
            clearErrors(document.getElementById('receiveChargeErrors'));
            selectedChargeId = null;
        });

        document.getElementById('deleteChargeModal').addEventListener('hidden.bs.modal', () => {
            clearErrors(document.getElementById('deleteChargeErrors'));
            selectedChargeId = null;
        });

        loadCharges();
    });

    window.loadCharges = loadCharges;
    window.openChargeDetails = openChargeDetails;
    window.openReceiveChargeModal = openReceiveChargeModal;
    window.confirmDeleteCharge = confirmDeleteCharge;
</script>
@endpush
@endsection

