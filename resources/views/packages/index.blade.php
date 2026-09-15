@extends('layouts.app')

@section('title', 'Encomendas — Portaria')

@section('content')
<div class="container-fluid packages-portaria px-3 px-lg-4">
    {{-- Hero + ações rápidas --}}
    <div class="pkg-hero mb-4">
        <div class="pkg-hero__content">
            <div class="pkg-hero__text">
                <h1 class="pkg-hero__title"><i class="bi bi-box-seam"></i> Portaria</h1>
                <p class="pkg-hero__subtitle">Registre chegadas, confirme retiradas e notifique moradores em segundos.</p>
            </div>
            <div class="pkg-hero__stat">
                <span class="pkg-hero__stat-label">Pendentes</span>
                <strong class="pkg-hero__stat-value" id="totalPendingCount">0</strong>
            </div>
        </div>

        @can('register_packages')
        <div class="pkg-actions">
            <a href="{{ route('packages.register') }}" class="pkg-action pkg-action--primary">
                <i class="bi bi-camera-fill"></i>
                <span>Ler etiqueta</span>
            </a>
            <a href="{{ route('packages.pickup') }}" class="pkg-action pkg-action--success">
                <i class="bi bi-key-fill"></i>
                <span>Retirada</span>
            </a>
            <button type="button" class="pkg-action pkg-action--outline" id="scrollToUnits">
                <i class="bi bi-building"></i>
                <span>Por unidade</span>
            </button>
        </div>
        @endcan
    </div>

    {{-- Busca e filtros --}}
    <div class="pkg-toolbar card border-0 shadow-sm mb-3" id="unitsSection">
        <div class="card-body py-3">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-5">
                    <div class="position-relative">
                        <input type="search" class="form-control" id="searchTerm"
                               placeholder="Buscar unidade, morador ou CPF..." autocomplete="off">
                        <div id="residentSuggestions" class="list-group shadow-sm d-none suggestions-dropdown"></div>
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <button class="btn btn-primary w-100" id="searchButton">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
                <div class="col-6 col-lg-2">
                    <button class="btn btn-outline-secondary w-100" id="clearFilters">
                        <i class="bi bi-x-lg"></i> Limpar
                    </button>
                </div>
                <div class="col-12 col-lg-3 d-flex align-items-center justify-content-lg-end gap-2 flex-wrap">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="onlyPendingToggle" checked>
                        <label class="form-check-label small" for="onlyPendingToggle">Só com pendências</label>
                    </div>
                    <span class="badge text-bg-light border" id="lastRefresh">Atualizado agora</span>
                </div>
            </div>
        </div>
    </div>

    <div id="alertContainer"></div>

    <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="mt-3 text-muted small">Carregando unidades...</p>
    </div>

    <div id="emptyState" class="d-none">
        <div class="pkg-empty">
            <i class="bi bi-inbox"></i>
            <h5>Nenhuma unidade encontrada</h5>
            <p>Ajuste a busca ou registre uma nova chegada.</p>
        </div>
    </div>

    <div class="row g-3" id="unitsGrid"></div>
</div>

@include('packages.partials.register-modal')
@include('packages.partials.collect-modal')
@endsection

@push('styles')
<style>
    .packages-portaria { padding-bottom: 2rem; }

    .pkg-hero {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 55%, #084298 100%);
        border-radius: 16px;
        padding: 1.25rem 1.25rem 1rem;
        color: #fff;
        box-shadow: 0 0.5rem 1.5rem rgba(13, 110, 253, 0.25);
    }

    .pkg-hero__content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .pkg-hero__title {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        letter-spacing: -0.02em;
    }

    .pkg-hero__subtitle {
        margin: 0.35rem 0 0;
        opacity: 0.9;
        font-size: 0.9rem;
        max-width: 28rem;
    }

    .pkg-hero__stat {
        text-align: center;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 12px;
        padding: 0.5rem 1rem;
        min-width: 5rem;
        flex-shrink: 0;
    }

    .pkg-hero__stat-label {
        display: block;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        opacity: 0.85;
    }

    .pkg-hero__stat-value {
        font-size: 1.75rem;
        font-weight: 800;
        line-height: 1.1;
    }

    .pkg-actions {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.65rem;
    }

    @media (max-width: 575.98px) {
        .pkg-actions { grid-template-columns: 1fr; }
        .pkg-hero__content { flex-direction: column; }
        .pkg-hero__stat { align-self: flex-start; }
    }

    .pkg-action {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        padding: 0.85rem 0.5rem;
        border-radius: 12px;
        border: none;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        min-height: 4.5rem;
    }

    .pkg-action i { font-size: 1.35rem; }

    .pkg-action--primary {
        background: #fff;
        color: #0d6efd;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .pkg-action--success {
        background: rgba(255, 255, 255, 0.95);
        color: #198754;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .pkg-action--outline {
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.35);
    }

    .pkg-action:hover {
        transform: translateY(-2px);
        color: inherit;
    }

    .pkg-action--primary:hover { color: #0d6efd; }
    .pkg-action--success:hover { color: #198754; }
    .pkg-action--outline:hover { color: #fff; background: rgba(255, 255, 255, 0.2); }

    .pkg-toolbar { border-radius: 12px; }

    .pkg-empty {
        text-align: center;
        padding: 3rem 1.5rem;
        background: #f8fafc;
        border-radius: 16px;
        border: 1px dashed #dee2e6;
    }

    .pkg-empty i { font-size: 2.5rem; color: #94a3b8; }
    .pkg-empty h5 { margin-top: 0.75rem; }
    .pkg-empty p { color: #64748b; margin: 0; }

    .unit-card {
        border-radius: 14px;
        border: 1px solid #e8ecf1;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
        overflow: hidden;
    }

    .unit-card.has-pending {
        border-color: rgba(220, 53, 69, 0.35);
        box-shadow: 0 4px 14px rgba(220, 53, 69, 0.1);
    }

    .unit-card__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.85rem 1rem;
        background: #f8fafc;
        border-bottom: 1px solid #eef2f7;
    }

    .unit-card__code {
        font-size: 1.35rem;
        font-weight: 800;
        color: #0d6efd;
        letter-spacing: -0.02em;
    }

    .unit-card__body { padding: 0.85rem 1rem 1rem; }

    .unit-card__residents {
        font-size: 0.82rem;
        color: #64748b;
        margin-bottom: 0.75rem;
        line-height: 1.4;
    }

    .package-pill {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 10px;
        padding: 0.5rem 0.65rem;
        margin-bottom: 0.5rem;
    }

    .package-pill:last-child { margin-bottom: 0; }

    .package-pill__meta { font-size: 0.78rem; color: #78716c; }

    .suggestions-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 40;
        max-height: 240px;
        overflow-y: auto;
        border-radius: 10px;
        margin-top: 0.25rem;
    }

    .type-selector-grid { display: grid; gap: 0.65rem; }
    @media (min-width: 576px) { .type-selector-grid { grid-template-columns: repeat(2, 1fr); } }

    .type-option { position: relative; display: block; cursor: pointer; }
    .type-option input { position: absolute; opacity: 0; pointer-events: none; }
    .type-option span {
        display: block;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 0.85rem;
        transition: all 0.15s ease;
    }
    .type-option input:checked + span {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }
    .type-option span i { font-size: 1.25rem; color: #0d6efd; display: block; margin-bottom: 0.35rem; }

    .package-submit-progress {
        padding: 0.75rem 1rem;
        border: 1px solid rgba(13, 110, 253, 0.2);
        border-radius: 0.75rem;
        background: rgba(13, 110, 253, 0.04);
    }
    .package-submit-progress--success {
        border-color: rgba(25, 135, 84, 0.2);
        background: rgba(25, 135, 84, 0.04);
    }
    .package-submit-progress__bar { height: 6px; border-radius: 999px; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const searchField = document.getElementById('searchTerm');
    const searchButton = document.getElementById('searchButton');
    const clearFiltersButton = document.getElementById('clearFilters');
    const onlyPendingToggle = document.getElementById('onlyPendingToggle');
    const unitsGrid = document.getElementById('unitsGrid');
    const loadingState = document.getElementById('loadingState');
    const emptyState = document.getElementById('emptyState');
    const totalPendingCountEl = document.getElementById('totalPendingCount');
    const lastRefreshBadge = document.getElementById('lastRefresh');
    const alertContainer = document.getElementById('alertContainer');
    const suggestionsBox = document.getElementById('residentSuggestions');
    const scrollToUnitsBtn = document.getElementById('scrollToUnits');
    const unitsSection = document.getElementById('unitsSection');

    const registerModalEl = document.getElementById('registerPackageModal');
    const registerModal = new bootstrap.Modal(registerModalEl);
    const registerForm = document.getElementById('registerPackageForm');
    const registerSubmitButton = document.getElementById('registerSubmitButton');
    const registerProgressWrap = document.getElementById('registerPackageProgress');
    const registerProgressBar = document.getElementById('registerPackageProgressBar');
    const registerProgressLabel = document.getElementById('registerPackageProgressLabel');
    const registerProgressPct = document.getElementById('registerPackageProgressPct');
    const registerUnitId = document.getElementById('registerUnitId');
    const registerUnitLabel = document.getElementById('registerUnitLabel');
    const registerUnitResidents = document.getElementById('registerUnitResidents');

    const collectModalEl = document.getElementById('collectPackageModal');
    const collectModal = new bootstrap.Modal(collectModalEl);
    const collectPackageIdField = document.getElementById('collectPackageId');
    const collectRequiresPickupCodeField = document.getElementById('collectRequiresPickupCode');
    const collectPickupCodeField = document.getElementById('collectPickupCode');
    const pickupCodeGroup = document.getElementById('pickupCodeGroup');
    const pickupCodeHelp = document.getElementById('pickupCodeHelp');
    const collectUnitLabel = document.getElementById('collectUnitLabel');
    const collectPackageSummary = document.getElementById('collectPackageSummary');
    const confirmCollectButton = document.getElementById('confirmCollectButton');
    const collectProgressWrap = document.getElementById('collectPackageProgress');
    const collectProgressBar = document.getElementById('collectPackageProgressBar');
    const collectProgressLabel = document.getElementById('collectPackageProgressLabel');
    const collectProgressPct = document.getElementById('collectPackageProgressPct');

    let unitsCache = [];
    let selectedPackage = null;
    let debounceTimeout = null;
    let progressTimer = null;

    scrollToUnitsBtn?.addEventListener('click', () => {
        unitsSection?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    function updatePackageProgress(scope, value) {
        const pct = Math.round(value);
        scope.bar.style.width = `${pct}%`;
        scope.bar.setAttribute('aria-valuenow', String(pct));
        scope.pct.textContent = `${pct}%`;
    }

    function startPackageProgress(scope, label) {
        clearInterval(progressTimer);
        scope.wrap.classList.remove('d-none');
        scope.wrap.setAttribute('aria-busy', 'true');
        scope.label.textContent = label;
        scope.value = 8;
        updatePackageProgress(scope, scope.value);
        progressTimer = setInterval(() => {
            if (scope.value < 92) {
                scope.value = Math.min(92, scope.value + Math.random() * 7 + 3);
                updatePackageProgress(scope, scope.value);
            }
        }, 180);
    }

    function finishPackageProgress(scope) {
        clearInterval(progressTimer);
        updatePackageProgress(scope, 100);
        setTimeout(() => {
            scope.wrap.classList.add('d-none');
            scope.wrap.setAttribute('aria-busy', 'false');
            scope.value = 0;
            updatePackageProgress(scope, 0);
        }, 350);
    }

    function stopPackageProgress(scope) {
        clearInterval(progressTimer);
        scope.wrap.classList.add('d-none');
        scope.wrap.setAttribute('aria-busy', 'false');
        scope.value = 0;
        updatePackageProgress(scope, 0);
    }

    const registerProgressScope = { wrap: registerProgressWrap, bar: registerProgressBar, label: registerProgressLabel, pct: registerProgressPct, value: 0 };
    const collectProgressScope = { wrap: collectProgressWrap, bar: collectProgressBar, label: collectProgressLabel, pct: collectProgressPct, value: 0 };

    function getFilteredUnits() {
        if (!onlyPendingToggle?.checked) return unitsCache;
        return unitsCache.filter(u => Number(u.pending_packages_count) > 0);
    }

    async function loadSummary(search = '') {
        showLoading();
        try {
            const params = new URLSearchParams();
            if (search.trim()) params.append('search', search.trim());

            const response = await fetch(`/api/packages/summary/units?${params}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                if (response.status === 403) throw new Error('Você não tem permissão para acessar o painel de encomendas.');
                const { error } = await response.json();
                throw new Error(error ?? 'Não foi possível carregar as encomendas.');
            }

            const data = await response.json();
            unitsCache = data.data ?? [];
            renderUnits(getFilteredUnits());
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
            col.className = 'col-12 col-md-6 col-xl-4';
            const hasPending = Number(unit.pending_packages_count) > 0;
            const residents = unit.residents?.length
                ? unit.residents.map(r => r.name).join(' · ')
                : 'Sem moradores cadastrados';

            const packagesList = hasPending
                ? unit.pending_packages.map(pkg => `
                    <div class="package-pill">
                        <div>
                            <strong>${pkg.type_label}</strong>
                            <div class="package-pill__meta">${formatDateTime(pkg.received_at)}</div>
                        </div>
                        <button class="btn btn-sm btn-success collect-package-btn"
                                data-package-id="${pkg.id}"
                                data-unit-label="${encodeURIComponent(buildUnitLabel(unit))}"
                                data-type-label="${pkg.type_label}"
                                data-received-at="${pkg.received_at}"
                                data-requires-pickup-code="${pkg.requires_pickup_code ? '1' : '0'}">
                            <i class="bi bi-check-lg"></i> Retirar
                        </button>
                    </div>
                `).join('')
                : '<p class="text-muted small mb-0">Nenhuma pendência.</p>';

            col.innerHTML = `
                <div class="card unit-card h-100 ${hasPending ? 'has-pending' : ''}">
                    <div class="unit-card__head">
                        <span class="unit-card__code">${buildUnitLabel(unit)}</span>
                        <span class="badge ${hasPending ? 'bg-danger' : 'bg-success-subtle text-success'}">
                            ${hasPending ? unit.pending_packages_count + ' pend.' : 'OK'}
                        </span>
                    </div>
                    <div class="unit-card__body">
                        <div class="unit-card__residents">${residents}</div>
                        <div class="d-flex flex-column gap-2 mb-2">${packagesList}</div>
                        <button class="btn btn-outline-primary btn-sm w-100 register-package-btn"
                                data-unit-id="${unit.id}"
                                data-unit-label="${encodeURIComponent(buildUnitLabel(unit))}"
                                data-residents='${encodeURIComponent(JSON.stringify(unit.residents || []))}'>
                            <i class="bi bi-plus-lg"></i> Registrar chegada
                        </button>
                    </div>
                </div>
            `;
            unitsGrid.appendChild(col);
        });
    }

    function updateTotals() {
        const total = unitsCache.reduce((sum, u) => sum + Number(u.pending_packages_count ?? 0), 0);
        totalPendingCountEl.textContent = total;
    }

    function updateRefreshTime() {
        const now = new Date();
        lastRefreshBadge.textContent = `Atualizado ${now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })}`;
    }

    function showLoading() {
        loadingState.classList.remove('d-none');
        unitsGrid.innerHTML = '';
        emptyState.classList.add('d-none');
    }

    function hideLoading() { loadingState.classList.add('d-none'); }

    function buildUnitLabel(unit) {
        return unit.block ? `${unit.block} · ${unit.number}` : `${unit.number}`;
    }

    function formatDateTime(dateTime) {
        if (!dateTime) return '—';
        const date = new Date(dateTime);
        if (Number.isNaN(date.getTime())) return dateTime;
        return date.toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' });
    }

    function showAlert(type, message) {
        const wrapper = document.createElement('div');
        wrapper.className = `alert alert-${type} alert-dismissible fade show`;
        wrapper.innerHTML = `<span>${message}</span><button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        alertContainer.appendChild(wrapper);
        setTimeout(() => wrapper.remove(), 6000);
    }

    function toggleSuggestions(show) {
        suggestionsBox.classList.toggle('d-none', !show || !suggestionsBox.childElementCount);
    }

    function renderSuggestions(results) {
        suggestionsBox.innerHTML = '';
        if (!results.length) { toggleSuggestions(false); return; }
        results.forEach(item => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'list-group-item list-group-item-action d-flex justify-content-between';
            button.dataset.searchTerm = item.name || item.cpf || (item.unit ? buildUnitLabel(item.unit) : '');
            button.innerHTML = `
                <div><div class="fw-semibold">${item.name}</div><small class="text-muted">${item.cpf ?? ''}</small></div>
                ${item.unit ? `<span class="badge bg-primary">${buildUnitLabel(item.unit)}</span>` : ''}
            `;
            suggestionsBox.appendChild(button);
        });
        toggleSuggestions(true);
    }

    async function searchResidents(term) {
        if (term.trim().length < 3) { toggleSuggestions(false); return; }
        try {
            const response = await fetch(`/api/packages/residents/search?search=${encodeURIComponent(term.trim())}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            if (!response.ok) { toggleSuggestions(false); return; }
            const data = await response.json();
            renderSuggestions(data.data ?? []);
        } catch { toggleSuggestions(false); }
    }

    searchButton.addEventListener('click', () => loadSummary(searchField.value));
    clearFiltersButton.addEventListener('click', () => { searchField.value = ''; toggleSuggestions(false); loadSummary(); });
    onlyPendingToggle?.addEventListener('change', () => renderUnits(getFilteredUnits()));

    searchField.addEventListener('input', (e) => {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => searchResidents(e.target.value), 250);
    });

    searchField.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') { e.preventDefault(); loadSummary(searchField.value); toggleSuggestions(false); }
        if (e.key === 'Escape') toggleSuggestions(false);
    });

    suggestionsBox.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-search-term]');
        if (!btn) return;
        searchField.value = btn.dataset.searchTerm;
        toggleSuggestions(false);
        loadSummary(searchField.value);
    });

    document.addEventListener('click', (e) => {
        if (!suggestionsBox.contains(e.target) && e.target !== searchField) toggleSuggestions(false);
    });

    unitsGrid.addEventListener('click', (e) => {
        const registerButton = e.target.closest('.register-package-btn');
        if (registerButton) {
            registerUnitId.value = registerButton.dataset.unitId;
            registerUnitLabel.textContent = decodeURIComponent(registerButton.dataset.unitLabel || '');
            let residents = [];
            try { residents = JSON.parse(decodeURIComponent(registerButton.dataset.residents || '[]')); } catch {}
            registerUnitResidents.textContent = residents.length ? residents.map(r => r.name).join(', ') : 'Sem moradores';
            registerForm.reset();
            stopPackageProgress(registerProgressScope);
            registerSubmitButton.disabled = false;
            registerModal.show();
            return;
        }

        const collectButton = e.target.closest('.collect-package-btn');
        if (collectButton) {
            selectedPackage = collectButton.dataset.packageId;
            collectPackageIdField.value = selectedPackage;
            const requiresPickup = collectButton.dataset.requiresPickupCode === '1';
            collectRequiresPickupCodeField.value = requiresPickup ? '1' : '0';
            collectUnitLabel.textContent = decodeURIComponent(collectButton.dataset.unitLabel || '');
            collectPackageSummary.innerHTML = `<strong>${collectButton.dataset.typeLabel}</strong><br><small class="text-muted">${formatDateTime(collectButton.dataset.receivedAt)}</small>`;
            if (collectPickupCodeField) collectPickupCodeField.value = '';
            pickupCodeGroup?.classList.toggle('d-none', !requiresPickup);
            if (pickupCodeHelp) pickupCodeHelp.textContent = requiresPickup ? 'Senha de 4 dígitos do WhatsApp.' : 'Encomenda legada — confirmação direta.';
            confirmCollectButton.disabled = false;
            stopPackageProgress(collectProgressScope);
            collectModal.show();
        }
    });

    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        registerSubmitButton.disabled = true;
        startPackageProgress(registerProgressScope, 'Registrando e notificando...');
        try {
            const response = await fetch('/api/packages', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body: JSON.stringify({ unit_id: Number(registerUnitId.value), type: registerForm.packageType.value })
            });
            if (!response.ok) {
                const data = await response.json();
                const err = Object.values(data.errors ?? {})[0];
                throw new Error(Array.isArray(err) ? err[0] : err || data.error || 'Erro ao registrar.');
            }
            finishPackageProgress(registerProgressScope);
            registerModal.hide();
            showAlert('success', 'Encomenda registrada.');
            await loadSummary(searchField.value);
        } catch (error) {
            stopPackageProgress(registerProgressScope);
            showAlert('danger', error.message);
        } finally {
            registerSubmitButton.disabled = false;
        }
    });

    confirmCollectButton.addEventListener('click', async () => {
        if (!selectedPackage) return;
        const requiresPickup = collectRequiresPickupCodeField?.value === '1';
        const pickupCode = (collectPickupCodeField?.value || '').trim();
        if (requiresPickup && !/^\d{4}$/.test(pickupCode)) {
            showAlert('danger', 'Informe a senha de 4 dígitos.');
            collectPickupCodeField?.focus();
            return;
        }
        confirmCollectButton.disabled = true;
        startPackageProgress(collectProgressScope, 'Registrando retirada...');
        try {
            const body = requiresPickup ? { pickup_code: pickupCode } : {};
            const response = await fetch(`/api/packages/${selectedPackage}/collect`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body: JSON.stringify(body)
            });
            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.error || 'Erro ao confirmar retirada.');
            }
            finishPackageProgress(collectProgressScope);
            collectModal.hide();
            showAlert('success', 'Retirada registrada.');
            await loadSummary(searchField.value);
        } catch (error) {
            stopPackageProgress(collectProgressScope);
            showAlert('danger', error.message);
        } finally {
            confirmCollectButton.disabled = false;
        }
    });

    registerModalEl.addEventListener('hidden.bs.modal', () => { stopPackageProgress(registerProgressScope); registerSubmitButton.disabled = false; });
    collectModalEl.addEventListener('hidden.bs.modal', () => { stopPackageProgress(collectProgressScope); confirmCollectButton.disabled = false; selectedPackage = null; });

    loadSummary();
});
</script>
@endpush
