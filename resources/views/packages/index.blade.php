@extends('layouts.app')

@section('title', 'Encomendas')

@section('content')
<div class="container-fluid packages-dashboard">
    <div class="row align-items-end mb-4">
        <div class="col-lg-6">
            <h2 class="mb-0">
                <i class="bi bi-box-seam"></i> Central de Encomendas
            </h2>
            <p class="text-muted mt-2 mb-0">
                Visualize todas as unidades, registre novas chegadas e confirme retiradas em poucos cliques.
            </p>
        </div>
        <div class="col-lg-6 text-lg-end mt-3 mt-lg-0">
            <span class="badge bg-primary-subtle text-primary fs-6 px-3 py-2">
                <i class="bi bi-bell-fill me-1"></i>
                Notificações automáticas para moradores e agregados
            </span>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-lg-5">
                    <label for="searchTerm" class="form-label fw-semibold">
                        Buscar unidade, morador ou CPF
                    </label>
                    <div class="position-relative">
                        <input type="text" class="form-control form-control-lg" id="searchTerm"
                               placeholder="Ex.: Bloco B 203, Ana Silva, 123.456.789-00">
                        <div id="residentSuggestions" class="list-group shadow-sm d-none suggestions-dropdown"></div>
                    </div>
                    <small class="text-muted d-block mt-1">
                        Digite para filtrar o quadro. Resultados correspondentes aparecem automaticamente.
                    </small>
                </div>
                <div class="col-lg-2 d-grid">
                    <button class="btn btn-primary btn-lg" id="searchButton">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
                <div class="col-lg-2 d-grid">
                    <button class="btn btn-outline-secondary btn-lg" id="clearFilters">
                        <i class="bi bi-eraser"></i> Limpar
                    </button>
                </div>
                <div class="col-lg-3 text-lg-end">
                    <div class="d-flex align-items-center justify-content-lg-end gap-3">
                        <div class="text-start">
                            <span class="text-muted small d-block">Total de pendências</span>
                            <strong class="fs-4" id="totalPendingCount">0</strong>
                        </div>
                        <div>
                            <span class="badge bg-warning text-dark fs-6 px-3 py-2" id="lastRefresh">
                                Atualizado agora
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="alertContainer"></div>

    <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="mt-3 text-muted">Carregando unidades e encomendas...</p>
    </div>

    <div id="emptyState" class="d-none">
        <div class="alert alert-info text-center">
            <i class="bi bi-inboxes"></i>
            <h5 class="mt-2">Nenhuma encomenda encontrada.</h5>
            <p class="mb-0">Utilize o botão "Registrar chegada" em qualquer unidade para começar.</p>
        </div>
    </div>

    <div class="row g-3" id="unitsGrid"></div>
</div>

<!-- Modal Registrar Encomenda -->
<div class="modal fade" id="registerPackageModal" tabindex="-1" aria-labelledby="registerPackageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registerPackageModalLabel">
                    <i class="bi bi-box-arrow-in-down me-2"></i>Registrar chegada de encomenda
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="registerPackageForm">
                <div class="modal-body">
                    <input type="hidden" id="registerUnitId">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-uppercase text-muted small">Unidade</label>
                        <div class="fs-5" id="registerUnitLabel"></div>
                        <div class="text-muted small" id="registerUnitResidents"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Tipo da encomenda</label>
                        <div class="type-selector-grid">
                            <label class="type-option">
                                <input type="radio" name="packageType" value="leve" required>
                                <span>
                                    <i class="bi bi-bag"></i>
                                    <strong>Leve</strong>
                                    <small>Envelope ou pequeno pacote</small>
                                </span>
                            </label>
                            <label class="type-option">
                                <input type="radio" name="packageType" value="pesado">
                                <span>
                                    <i class="bi bi-box2"></i>
                                    <strong>Pesado</strong>
                                    <small>Peso considerável</small>
                                </span>
                            </label>
                            <label class="type-option">
                                <input type="radio" name="packageType" value="caixa_grande">
                                <span>
                                    <i class="bi bi-boxes"></i>
                                    <strong>Caixa Grande</strong>
                                    <small>Volume acima do padrão</small>
                                </span>
                            </label>
                            <label class="type-option">
                                <input type="radio" name="packageType" value="fragil">
                                <span>
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Frágil</strong>
                                    <small>Manuseio cuidadoso</small>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Confirmar registro
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Confirmar Retirada -->
<div class="modal fade" id="collectPackageModal" tabindex="-1" aria-labelledby="collectPackageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="collectPackageModalLabel">
                    <i class="bi bi-box-arrow-up me-2"></i>Confirmar retirada
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="collectPackageId">
                <div class="mb-3">
                    <label class="form-label fw-semibold text-uppercase text-muted small">Unidade</label>
                    <div class="fs-5" id="collectUnitLabel"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-uppercase text-muted small">Encomenda</label>
                    <div id="collectPackageSummary"></div>
                </div>
                <p class="text-muted small mb-0">
                    A retirada será registrada com a hora atual. Os moradores e agregados serão avisados imediatamente.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmCollectButton">
                    <i class="bi bi-check-lg"></i> Confirmar retirada
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .packages-dashboard .card {
        border-radius: 16px;
    }

    .packages-dashboard .unit-card {
        transition: all 0.25s ease;
        border: 1px solid transparent;
    }

    .packages-dashboard .unit-card.has-pending {
        border-color: rgba(13, 110, 253, 0.35);
        box-shadow: 0 0.5rem 1rem rgba(13, 110, 253, 0.15);
    }

    .packages-dashboard .unit-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 1rem 1.5rem rgba(0,0,0,0.12);
    }

    .packages-dashboard .unit-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
    }

    .packages-dashboard .unit-code {
        font-size: 1.875rem;
        font-weight: 700;
        color: #0d6efd;
    }

    .packages-dashboard .resident-list {
        list-style: none;
        padding-left: 0;
        margin-bottom: 0;
    }

    .packages-dashboard .resident-list li {
        display: flex;
        justify-content: space-between;
        font-size: 0.9rem;
        padding: 0.15rem 0;
    }

    .packages-dashboard .package-chip {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f8f9fa;
        border-radius: 12px;
        padding: 0.5rem 0.75rem;
        gap: 0.5rem;
    }

    .packages-dashboard .package-chip .badge {
        font-size: 0.75rem;
    }

    .packages-dashboard .package-chip time {
        font-size: 0.8rem;
        color: #6c757d;
    }

    .suggestions-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 40;
        max-height: 260px;
        overflow-y: auto;
        border-radius: 12px;
        margin-top: 0.25rem;
    }

    .type-selector-grid {
        display: grid;
        gap: 0.75rem;
    }

    .type-option {
        position: relative;
        display: block;
        cursor: pointer;
    }

    .type-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .type-option span {
        display: block;
        border: 1px solid #dee2e6;
        border-radius: 12px;
        padding: 1rem;
        text-align: left;
        transition: all 0.2s ease;
        background: #fff;
    }

    .type-option span i {
        font-size: 1.4rem;
        color: #0d6efd;
        display: block;
        margin-bottom: 0.5rem;
    }

    .type-option strong {
        display: block;
        font-size: 1rem;
    }

    .type-option small {
        color: #6c757d;
    }

    .type-option input:checked + span {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    }

    @media (min-width: 576px) {
        .type-selector-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (min-width: 992px) {
        .packages-dashboard .unit-code {
            font-size: 2rem;
        }
    }
    @media (max-width: 575px) {
        .packages-dashboard .unit-card {
            border-radius: 12px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const searchField = document.getElementById('searchTerm');
        const searchButton = document.getElementById('searchButton');
        const clearFiltersButton = document.getElementById('clearFilters');
        const unitsGrid = document.getElementById('unitsGrid');
        const loadingState = document.getElementById('loadingState');
        const emptyState = document.getElementById('emptyState');
        const totalPendingCountEl = document.getElementById('totalPendingCount');
        const lastRefreshBadge = document.getElementById('lastRefresh');
        const alertContainer = document.getElementById('alertContainer');
        const suggestionsBox = document.getElementById('residentSuggestions');

        const registerModalEl = document.getElementById('registerPackageModal');
        const registerModal = new bootstrap.Modal(registerModalEl);
        const registerForm = document.getElementById('registerPackageForm');
        const registerUnitId = document.getElementById('registerUnitId');
        const registerUnitLabel = document.getElementById('registerUnitLabel');
        const registerUnitResidents = document.getElementById('registerUnitResidents');

        const collectModalEl = document.getElementById('collectPackageModal');
        const collectModal = new bootstrap.Modal(collectModalEl);
        const collectPackageIdField = document.getElementById('collectPackageId');
        const collectUnitLabel = document.getElementById('collectUnitLabel');
        const collectPackageSummary = document.getElementById('collectPackageSummary');
        const confirmCollectButton = document.getElementById('confirmCollectButton');

        let unitsCache = [];
        let selectedPackage = null;
        let debounceTimeout = null;

        async function loadSummary(search = '') {
            showLoading();
            try {
                const params = new URLSearchParams();
                if (search.trim()) {
                    params.append('search', search.trim());
                }

                const response = await fetch(`/api/packages/summary/units?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    if (response.status === 403) {
                        throw new Error('Você não tem permissão para acessar o painel de encomendas.');
                    }

                    const { error } = await response.json();
                    throw new Error(error ?? 'Não foi possível carregar as encomendas.');
                }

                const data = await response.json();
                unitsCache = data.data ?? [];

                renderUnits(unitsCache);
                updateTotals();
                updateRefreshTime();
            } catch (error) {
                renderUnits([]);
                showAlert('danger', error.message || 'Erro ao carregar as encomendas.');
            } finally {
                hideLoading();
            }
        }

        function renderUnits(units) {
            unitsGrid.innerHTML = '';

            if (!units.length) {
                emptyState.classList.remove('d-none');
                return;
            }

            emptyState.classList.add('d-none');

            units.forEach(unit => {
                const col = document.createElement('div');
                col.className = 'col-12 col-lg-6 col-xl-4';

                const hasPending = Number(unit.pending_packages_count) > 0;

                const residentsList = unit.residents.length
                    ? unit.residents.map(resident => `
                        <li>
                            <span class="text-truncate">${resident.name}</span>
                            <span class="text-muted">${resident.cpf ?? ''}</span>
                        </li>
                    `).join('')
                    : '<li class="text-muted fst-italic">Sem moradores cadastrados</li>';

                const packagesList = hasPending
                    ? unit.pending_packages.map(pkg => `
                        <div class="package-chip">
                            <div>
                                <span class="badge bg-primary-subtle text-primary">
                                    ${pkg.type_label}
                                </span>
                                <time datetime="${pkg.received_at}">
                                    Recebido em ${formatDateTime(pkg.received_at)}
                                </time>
                            </div>
                            <button class="btn btn-sm btn-success collect-package-btn"
                                    data-package-id="${pkg.id}"
                                    data-unit-label="${encodeURIComponent(buildUnitLabel(unit))}"
                                    data-type-label="${pkg.type_label}"
                                    data-received-at="${pkg.received_at}">
                                <i class="bi bi-check-circle"></i>
                            </button>
                        </div>
                    `).join('')
                    : '<p class="text-muted small mb-0">Nenhuma encomenda pendente nesta unidade.</p>';

                col.innerHTML = `
                    <div class="card unit-card h-100 ${hasPending ? 'has-pending' : ''}">
                        <div class="card-body d-flex flex-column gap-3">
                            <div class="unit-header">
                                <div>
                                    <div class="text-muted small text-uppercase">Unidade</div>
                                    <div class="unit-code">${buildUnitLabel(unit)}</div>
                                </div>
                                <span class="badge ${hasPending ? 'bg-danger' : 'bg-success'}">
                                    ${hasPending ? `${unit.pending_packages_count} pendente(s)` : 'Sem pendências'}
                                </span>
                            </div>

                            <div>
                                <div class="text-muted text-uppercase small fw-semibold mb-2">
                                    Moradores / Agregados
                                </div>
                                <ul class="resident-list">
                                    ${residentsList}
                                </ul>
                            </div>

                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex gap-2 flex-wrap">
                                    <button class="btn btn-outline-primary btn-sm flex-grow-1 register-package-btn"
                                            data-unit-id="${unit.id}"
                                            data-unit-label="${encodeURIComponent(buildUnitLabel(unit))}"
                                            data-residents='${encodeURIComponent(JSON.stringify(unit.residents))}'>
                                        <i class="bi bi-box-arrow-in-down"></i> Registrar chegada
                                    </button>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div class="text-muted text-uppercase small fw-semibold">
                                        Encomendas pendentes
                                    </div>
                                    <div class="d-flex flex-column gap-2">
                                        ${packagesList}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                unitsGrid.appendChild(col);
            });
        }

        function updateTotals() {
            const total = unitsCache.reduce((sum, unit) => sum + Number(unit.pending_packages_count ?? 0), 0);
            totalPendingCountEl.textContent = total;
        }

        function updateRefreshTime() {
            const now = new Date();
            const formatted = now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
            lastRefreshBadge.textContent = `Atualizado às ${formatted}`;
        }

        function showLoading() {
            loadingState.classList.remove('d-none');
            unitsGrid.innerHTML = '';
            emptyState.classList.add('d-none');
        }

        function hideLoading() {
            loadingState.classList.add('d-none');
        }

        function buildUnitLabel(unit) {
            return unit.block ? `${unit.block} • ${unit.number}` : `${unit.number}`;
        }

        function formatDateTime(dateTime) {
            if (!dateTime) return 'Data não informada';
            const date = new Date(dateTime);
            if (Number.isNaN(date.getTime())) {
                return dateTime;
            }
            return date.toLocaleString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function showAlert(type, message) {
            const wrapper = document.createElement('div');
            wrapper.className = `alert alert-${type} alert-dismissible fade show`;
            wrapper.innerHTML = `
                <span>${message}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            `;
            alertContainer.appendChild(wrapper);
            setTimeout(() => {
                wrapper.classList.remove('show');
                wrapper.classList.add('hide');
                wrapper.addEventListener('transitionend', () => wrapper.remove());
            }, 6000);
        }

        function toggleSuggestions(show) {
            suggestionsBox.classList.toggle('d-none', !show || !suggestionsBox.childElementCount);
        }

        function renderSuggestions(results) {
            suggestionsBox.innerHTML = '';
            if (!results.length) {
                toggleSuggestions(false);
                return;
            }

            results.forEach(item => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-start gap-3';
                button.dataset.unitId = item.unit?.id ?? '';
                button.dataset.unitLabel = item.unit ? buildUnitLabel(item.unit) : '';
                const preferredSearchTerm = item.name || item.cpf || button.dataset.unitLabel;
                if (preferredSearchTerm) {
                    button.dataset.searchTerm = preferredSearchTerm;
                }
                button.innerHTML = `
                    <div>
                        <div class="fw-semibold">${item.name}</div>
                        <div class="text-muted small">${item.cpf ?? 'CPF não informado'}</div>
                    </div>
                    ${item.unit ? `<span class="badge bg-primary rounded-pill">${buildUnitLabel(item.unit)}</span>` : ''}
                `;
                suggestionsBox.appendChild(button);
            });

            toggleSuggestions(true);
        }

        async function searchResidents(term) {
            const trimmed = term.trim();
            if (trimmed.length < 3) {
                toggleSuggestions(false);
                return;
            }

            try {
                const response = await fetch(`/api/packages/residents/search?search=${encodeURIComponent(trimmed)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    toggleSuggestions(false);
                    return;
                }

                const data = await response.json();
                renderSuggestions(data.data ?? []);
            } catch (error) {
                toggleSuggestions(false);
            }
        }

        // Event listeners
        searchButton.addEventListener('click', () => {
            loadSummary(searchField.value);
        });

        clearFiltersButton.addEventListener('click', () => {
            searchField.value = '';
            toggleSuggestions(false);
            loadSummary();
        });

        searchField.addEventListener('input', (event) => {
            const value = event.target.value;
            if (debounceTimeout) {
                clearTimeout(debounceTimeout);
            }
            debounceTimeout = setTimeout(() => searchResidents(value), 250);
        });

        searchField.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                loadSummary(searchField.value);
                toggleSuggestions(false);
            } else if (event.key === 'Escape') {
                toggleSuggestions(false);
            }
        });

        suggestionsBox.addEventListener('click', (event) => {
            const target = event.target.closest('button[data-unit-id]');
            if (!target) return;

            const searchValue = target.dataset.searchTerm || target.dataset.unitLabel || '';
            searchField.value = searchValue;
            toggleSuggestions(false);
            loadSummary(searchField.value);
        });

        document.addEventListener('click', (event) => {
            if (!suggestionsBox.contains(event.target) && event.target !== searchField) {
                toggleSuggestions(false);
            }
        });

        unitsGrid.addEventListener('click', (event) => {
            const registerButton = event.target.closest('.register-package-btn');
            if (registerButton) {
                const unitId = registerButton.dataset.unitId;
                const unitLabel = registerButton.dataset.unitLabel
                    ? decodeURIComponent(registerButton.dataset.unitLabel)
                    : '';
                let residentsData = [];
                if (registerButton.dataset.residents) {
                    try {
                        residentsData = JSON.parse(decodeURIComponent(registerButton.dataset.residents));
                    } catch (error) {
                        residentsData = [];
                    }
                }

                registerUnitId.value = unitId;
                registerUnitLabel.textContent = unitLabel;

                if (residentsData.length) {
                    registerUnitResidents.innerHTML = residentsData.map(resident => resident.name).join(', ');
                } else {
                    registerUnitResidents.innerHTML = '<span class="text-muted">Sem moradores vinculados</span>';
                }

                registerForm.reset();
                registerModal.show();
                return;
            }

            const collectButton = event.target.closest('.collect-package-btn');
            if (collectButton) {
                const packageId = collectButton.dataset.packageId;
                const unitLabel = collectButton.dataset.unitLabel
                    ? decodeURIComponent(collectButton.dataset.unitLabel)
                    : '';
                const typeLabel = collectButton.dataset.typeLabel;
                const receivedAt = collectButton.dataset.receivedAt;

                selectedPackage = packageId;
                collectPackageIdField.value = packageId;
                collectUnitLabel.textContent = unitLabel;
                collectPackageSummary.innerHTML = `
                    <div class="d-flex flex-column gap-1">
                        <div><strong>Tipo:</strong> ${typeLabel}</div>
                        <div><strong>Recebida em:</strong> ${formatDateTime(receivedAt)}</div>
                    </div>
                `;

                confirmCollectButton.disabled = false;
                collectModal.show();
            }
        });

        registerForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const unitId = registerUnitId.value;
            const type = registerForm.packageType.value;

            try {
                const response = await fetch('/api/packages', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        unit_id: Number(unitId),
                        type
                    })
                });

                if (!response.ok) {
                    const data = await response.json();
                    if (response.status === 403) {
                        throw new Error(data.error ?? 'Você não tem permissão para registrar encomendas.');
                    }

                    const errors = data.errors ?? {};
                    const firstError = Object.values(errors)[0];
                    throw new Error(Array.isArray(firstError) ? firstError[0] : firstError || 'Erro ao registrar a encomenda.');
                }

                registerModal.hide();
                showAlert('success', 'Encomenda registrada e moradores notificados.');
                await loadSummary(searchField.value);
            } catch (error) {
                showAlert('danger', error.message || 'Erro ao registrar a encomenda.');
            }
        });

        confirmCollectButton.addEventListener('click', async () => {
            if (!selectedPackage) {
                showAlert('danger', 'Selecione uma encomenda válida.');
                return;
            }

            confirmCollectButton.disabled = true;
            try {
                const response = await fetch(`/api/packages/${selectedPackage}/collect`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({})
                });

                if (!response.ok) {
                    const data = await response.json();
                    if (response.status === 403) {
                        throw new Error(data.error ?? 'Você não tem permissão para registrar retiradas.');
                    }

                    const errors = data.errors ?? {};
                    const firstError = Object.values(errors)[0];
                    throw new Error(Array.isArray(firstError) ? firstError[0] : firstError || 'Erro ao registrar retirada.');
                }

                collectModal.hide();
                showAlert('success', 'Retirada registrada com sucesso.');
                await loadSummary(searchField.value);
            } catch (error) {
                showAlert('danger', error.message || 'Erro ao registrar retirada.');
            } finally {
                confirmCollectButton.disabled = false;
                selectedPackage = null;
            }
        });

        // Inicialização
        loadSummary();
    });
</script>
@endpush

