@extends('layouts.app')

@section('title', 'Painel de Acesso — Portaria')

@section('content')
<div class="access-porteiro-panel">
    <header class="porteiro-toolbar panel-panorama-bar">
        <div class="porteiro-toolbar__top">
            <div class="porteiro-brand">
                <span class="porteiro-brand__icon"><i class="bi bi-shield-check"></i></span>
                <div>
                    <strong class="porteiro-brand__title">Portaria</strong>
                    <span class="porteiro-brand__hint">Toque no card para registrar</span>
                </div>
            </div>

            <nav class="porteiro-tabs" id="panelTabs" aria-label="Filtrar liberações">
                <button type="button" class="porteiro-tab active" data-filter="all">
                    Todos <span class="porteiro-tab__count" id="tabCountAll">0</span>
                </button>
                <button type="button" class="porteiro-tab" data-filter="authorizations">
                    Liberações <span class="porteiro-tab__count porteiro-tab__count--green" id="tabCountAuth">0</span>
                </button>
                <button type="button" class="porteiro-tab" data-filter="prohibitions">
                    Proibidos <span class="porteiro-tab__count porteiro-tab__count--red" id="tabCountProhibitions">0</span>
                </button>
                <button type="button" class="porteiro-tab" data-filter="lists">
                    Listas <span class="porteiro-tab__count porteiro-tab__count--blue" id="tabCountLists">0</span>
                </button>
                <button type="button" class="porteiro-tab" data-filter="providers">
                    Prestadores <span class="porteiro-tab__count porteiro-tab__count--amber" id="tabCountProviders">0</span>
                </button>
            </nav>

            <div class="porteiro-actions">
                <button type="button" class="btn btn-sm btn-danger porteiro-prohibit-btn" id="btnOpenProhibition" title="Registrar proibição">
                    <i class="bi bi-slash-circle"></i> PROIBIR
                </button>
                <span class="porteiro-sync" id="lastRefresh" title="Última atualização">…</span>
                <button type="button" class="porteiro-icon-btn" id="btnPanorama" title="Modo panorama">
                    <i class="bi bi-arrows-fullscreen"></i>
                </button>
                <button type="button" class="porteiro-icon-btn" id="btnRefresh" title="Atualizar">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
        </div>

        <div class="porteiro-toolbar__search">
            <div class="porteiro-search">
                <i class="bi bi-search porteiro-search__icon"></i>
                <input type="search" id="panelSearch" placeholder="Buscar visitante, unidade, morador…" autocomplete="off">
                <button type="button" class="porteiro-search__clear d-none" id="btnClearSearch" title="Limpar"><i class="bi bi-x-lg"></i></button>
            </div>
            <span class="porteiro-search-meta d-none" id="searchCount"></span>
        </div>
    </header>

    <div id="alertBox" class="porteiro-alerts"></div>

    <div id="loadingPanel" class="porteiro-state">
        <div class="spinner-border text-primary spinner-border-sm"></div>
        <span>Carregando liberações…</span>
    </div>

    <div id="cardsGrid" class="porteiro-grid d-none"></div>

    <div id="emptyPanel" class="porteiro-state porteiro-state--empty d-none">
        <i class="bi bi-inbox"></i>
        <p id="emptyPanelText">Nenhuma liberação pendente no momento.</p>
    </div>
</div>

<div class="modal fade" id="actionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="actionModalHeader">
                <h5 class="modal-title" id="actionModalTitle">Confirmar acesso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="actionModalBody"></div>
                <div class="d-grid gap-2 mt-4" id="actionModalActions">
                    <button type="button" class="btn btn-success btn-lg" id="btnEntered"><i class="bi bi-check-circle"></i> ENTROU</button>
                    <button type="button" class="btn btn-danger btn-lg" id="btnDenied"><i class="bi bi-x-circle"></i> ACESSO NEGADO</button>
                    <button type="button" class="btn btn-danger btn-lg d-none" id="btnAlertResident">
                        <i class="bi bi-exclamation-octagon-fill"></i> ALERTAR MORADOR!
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="listModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="listModalTitle">Lista de convidados</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="listModalBody"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="earlyEntryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-warning">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-1"></i> Entrada antes do horário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">O visitante chegou <strong>antes</strong> do horário liberado (<span id="earlyEntryScheduledLabel"></span>).</p>
                <p class="mb-3">Avise o morador responsável antes de liberar a entrada.</p>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="earlyEntryConfirmCheck">
                    <label class="form-check-label" for="earlyEntryConfirmCheck">
                        Confirmo que o morador autorizou a entrada antecipada.
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmEarlyEntry" disabled>
                    <i class="bi bi-check-circle"></i> Confirmar e registrar entrada
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="prohibitionCreateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-slash-circle me-1"></i> Proibir visitante</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formPorteiroProhibition" class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Nome *</label>
                        <input type="text" name="visitor_name" class="form-control form-control-lg" required placeholder="Nome completo">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Documento (opcional)</label>
                        <input type="text" name="visitor_document" class="form-control" placeholder="RG, CPF...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Unidade *</label>
                        <select name="unit_id" id="prohibitionUnitSelect" class="form-select" required>
                            <option value="">Selecione...</option>
                        </select>
                    </div>
                    <div class="col-md-6" id="porteiroProhibitionExpireWrap">
                        <label class="form-label">Expira em</label>
                        <input type="date" name="expires_at" id="porteiroProhibitionExpiresAt" class="form-control">
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="porteiroProhibitionNeverExpires" value="1">
                            <label class="form-check-label fw-semibold" for="porteiroProhibitionNeverExpires">NUNCA EXPIRA</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger btn-lg" id="btnSubmitProhibition">
                    <i class="bi bi-slash-circle"></i> PROIBIR
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* ── Layout geral ── */
body:has(.access-porteiro-panel) main > .container-fluid.p-4 {
    padding: 0.65rem 0.85rem 1rem !important;
    background: #eef1f6;
}
.access-porteiro-panel {
    max-width: 1600px;
    margin: 0 auto;
}

/* ── Toolbar compacta ── */
.porteiro-toolbar {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    box-shadow: 0 1px 3px rgba(15, 23, 42, .06);
    padding: 0.55rem 0.75rem 0.65rem;
    margin-bottom: 0.75rem;
}
.porteiro-toolbar__top {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}
.porteiro-brand {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    min-width: 120px;
}
.porteiro-brand__icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #059669, #10b981);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}
.porteiro-brand__title {
    display: block;
    font-size: 0.95rem;
    line-height: 1.1;
    color: #0f172a;
}
.porteiro-brand__hint {
    display: block;
    font-size: 0.68rem;
    color: #64748b;
}

/* Tabs segmentadas */
.porteiro-tabs {
    display: flex;
    flex: 1;
    gap: 0.25rem;
    padding: 0.2rem;
    background: #f1f5f9;
    border-radius: 10px;
    overflow-x: auto;
    min-width: 0;
}
.porteiro-tab {
    border: 0;
    background: transparent;
    color: #475569;
    font-size: 0.78rem;
    font-weight: 600;
    padding: 0.4rem 0.65rem;
    border-radius: 8px;
    white-space: nowrap;
    transition: background .15s, color .15s;
}
.porteiro-tab:hover { color: #0f172a; background: rgba(255,255,255,.6); }
.porteiro-tab.active {
    background: #fff;
    color: #0f172a;
    box-shadow: 0 1px 2px rgba(15,23,42,.08);
}
.porteiro-tab__count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.25rem;
    height: 1.25rem;
    padding: 0 0.35rem;
    margin-left: 0.25rem;
    border-radius: 999px;
    background: #e2e8f0;
    color: #334155;
    font-size: 0.68rem;
    font-weight: 700;
}
.porteiro-tab.active .porteiro-tab__count { background: #0f172a; color: #fff; }
.porteiro-tab__count--green { background: #d1fae5; color: #047857; }
.porteiro-tab__count--blue { background: #dbeafe; color: #1d4ed8; }
.porteiro-tab__count--amber { background: #fef3c7; color: #b45309; }
.porteiro-tab__count--red { background: #fee2e2; color: #b91c1c; }

.porteiro-actions {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    margin-left: auto;
}
.porteiro-sync {
    font-size: 0.68rem;
    color: #64748b;
    white-space: nowrap;
    padding: 0 0.25rem;
}
.porteiro-icon-btn {
    width: 34px;
    height: 34px;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    background: #fff;
    color: #475569;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: border-color .15s, color .15s, background .15s;
}
.porteiro-icon-btn:hover { border-color: #94a3b8; color: #0f172a; background: #f8fafc; }
body.porteiro-panorama .porteiro-icon-btn#btnPanorama {
    background: #0f172a;
    border-color: #0f172a;
    color: #fff;
}

/* Busca */
.porteiro-toolbar__search {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.55rem;
}
.porteiro-search {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 0.45rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0 0.65rem;
    min-height: 38px;
    transition: border-color .15s, box-shadow .15s;
}
.porteiro-search:focus-within {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,.12);
    background: #fff;
}
.porteiro-search__icon { color: #94a3b8; font-size: 0.9rem; }
.porteiro-search input {
    border: 0;
    background: transparent;
    flex: 1;
    font-size: 0.875rem;
    padding: 0.35rem 0;
    outline: none;
    min-width: 0;
}
.porteiro-search__clear {
    border: 0;
    background: transparent;
    color: #64748b;
    padding: 0.15rem;
    line-height: 1;
}
.porteiro-search-meta {
    font-size: 0.72rem;
    color: #64748b;
    white-space: nowrap;
}

/* Grid de cards */
.porteiro-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 0.65rem;
}
.porteiro-alerts { margin-bottom: 0.5rem; }
.porteiro-alerts:empty { display: none; }

.porteiro-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 3rem 1rem;
    color: #64748b;
    font-size: 0.9rem;
}
.porteiro-state--empty i { font-size: 2.5rem; opacity: .35; }
.porteiro-state--empty p { margin: 0; }

/* ── Cards ── */
.access-porteiro-panel .access-card {
    position: relative;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    border-left-width: 4px;
    background: #fff;
    min-height: 148px;
    cursor: pointer;
    transition: transform .15s ease, box-shadow .15s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 0.85rem 0.9rem;
    overflow: hidden;
}
.access-porteiro-panel .access-card::after {
    content: 'Toque para abrir';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    font-size: 0.62rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #94a3b8;
    text-align: center;
    padding: 0.2rem;
    background: linear-gradient(transparent, rgba(255,255,255,.95));
    opacity: 0;
    transition: opacity .15s;
}
.access-porteiro-panel .access-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(15, 23, 42, .1);
}
.access-porteiro-panel .access-card:hover::after { opacity: 1; }
.access-porteiro-panel .access-card.allow { border-left-color: #10b981; }
.access-porteiro-panel .access-card.deny { border-left-color: #ef4444; background: linear-gradient(180deg, #fff 0%, #fff5f5 100%); }
.access-porteiro-panel .access-card.prohibition .access-card__alert-hint {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    margin-top: 0.35rem;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #b91c1c;
}
.access-porteiro-panel .access-card.list { border-left-color: #3b82f6; }
.access-porteiro-panel .access-card.provider { border-left-color: #f59e0b; }

.access-porteiro-panel .access-card__head {
    display: flex;
    flex-wrap: wrap;
    gap: 0.3rem;
    align-items: center;
    margin-bottom: 0.45rem;
}
.access-porteiro-panel .access-card__type {
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    padding: 0.2rem 0.45rem;
    border-radius: 6px;
}
.access-porteiro-panel .access-card.allow .access-card__type { background: #d1fae5; color: #047857; }
.access-porteiro-panel .access-card.deny .access-card__type { background: #fee2e2; color: #b91c1c; }
.access-porteiro-panel .access-card.list .access-card__type { background: #dbeafe; color: #1d4ed8; }
.access-porteiro-panel .access-card.provider .access-card__type { background: #fef3c7; color: #b45309; }
.access-porteiro-panel .access-card__early {
    font-size: 0.62rem;
    font-weight: 600;
    padding: 0.15rem 0.4rem;
    border-radius: 6px;
    background: #fef9c3;
    color: #a16207;
}
.access-porteiro-panel .visitor-name {
    font-size: 1.05rem;
    font-weight: 700;
    line-height: 1.25;
    color: #0f172a;
    margin-bottom: 0.5rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.access-porteiro-panel .access-card__meta {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    font-size: 0.75rem;
    color: #64748b;
    padding-bottom: 0.35rem;
}
.access-porteiro-panel .access-card__meta span {
    display: flex;
    align-items: center;
    gap: 0.35rem;
}
.access-porteiro-panel .access-card__meta i { color: #94a3b8; width: 14px; text-align: center; }
.access-porteiro-panel .provider-photo {
    width: 48px;
    height: 48px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,.08);
    flex-shrink: 0;
}
.access-porteiro-panel .access-card__provider-row {
    display: flex;
    gap: 0.55rem;
    align-items: flex-start;
}

.list-guest-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.65rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    margin-bottom: 0.45rem;
    background: #fff;
}
.list-guest-row.search-match { border-color: #3b82f6; background: #eff6ff; }

/* ── Modo panorama ── */
body.porteiro-panorama #sidebar,
body.porteiro-panorama #mobileSidebar { display: none !important; }
body.porteiro-panorama main { width: 100% !important; max-width: 100% !important; }
body.porteiro-panorama main > .navbar { display: none !important; }
body.porteiro-panorama main > .container-fluid.p-4 {
    padding: 0.4rem 0.55rem 0.65rem !important;
    max-width: 100%;
}
body.porteiro-panorama .access-porteiro-panel { max-width: none; }
body.porteiro-panorama .porteiro-toolbar {
    position: sticky;
    top: 0;
    z-index: 1020;
    margin-bottom: 0.55rem;
    border-radius: 0 0 12px 12px;
}
body.porteiro-panorama .porteiro-grid {
    grid-template-columns: repeat(auto-fill, minmax(175px, 1fr));
    gap: 0.55rem;
}
body.porteiro-panorama .access-porteiro-panel .access-card {
    min-height: 128px;
    padding: 0.7rem 0.75rem;
}
body.porteiro-panorama .access-porteiro-panel .visitor-name { font-size: 0.95rem; }
body.porteiro-panorama .porteiro-brand__hint { display: none; }

@media (max-width: 768px) {
    .porteiro-toolbar__top { flex-direction: column; align-items: stretch; }
    .porteiro-tabs { order: 2; }
    .porteiro-actions { order: 1; justify-content: flex-end; }
    .porteiro-brand__hint { display: none; }
    .porteiro-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const cardsGrid = document.getElementById('cardsGrid');
    const loadingPanel = document.getElementById('loadingPanel');
    const emptyPanel = document.getElementById('emptyPanel');
    const lastRefresh = document.getElementById('lastRefresh');
    const alertBox = document.getElementById('alertBox');
    const panelSearch = document.getElementById('panelSearch');
    const btnClearSearch = document.getElementById('btnClearSearch');
    const searchCount = document.getElementById('searchCount');
    const emptyPanelText = document.getElementById('emptyPanelText');
    const actionModal = new bootstrap.Modal(document.getElementById('actionModal'));
    const listModal = new bootstrap.Modal(document.getElementById('listModal'));
    const earlyEntryModal = new bootstrap.Modal(document.getElementById('earlyEntryModal'));
    const prohibitionCreateModal = new bootstrap.Modal(document.getElementById('prohibitionCreateModal'));
    const btnPanorama = document.getElementById('btnPanorama');
    const PANORAMA_STORAGE_KEY = 'access_porteiro_panorama';

    let panelData = { authorizations: [], prohibitions: [], lists: [], service_providers: [] };
    let currentAction = null;
    let currentList = null;
    let pendingEarlyEntry = null;
    let activeFilter = 'all';
    let searchQuery = '';
    let pollTimer = null;

    document.querySelectorAll('#panelTabs .porteiro-tab').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('#panelTabs .porteiro-tab').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            activeFilter = btn.dataset.filter;
            if (panelSearch) {
                panelSearch.placeholder = activeFilter === 'prohibitions'
                    ? 'Buscar pessoa proibida, unidade ou morador…'
                    : 'Buscar visitante, unidade, morador…';
            }
            renderCards();
        });
    });

    panelSearch?.addEventListener('input', () => {
        searchQuery = panelSearch.value.trim();
        btnClearSearch?.classList.toggle('d-none', !searchQuery);
        renderCards();
    });

    btnClearSearch?.addEventListener('click', () => {
        panelSearch.value = '';
        searchQuery = '';
        btnClearSearch.classList.add('d-none');
        panelSearch.focus();
        renderCards();
    });

    panelSearch?.addEventListener('keydown', e => {
        if (e.key === 'Escape') btnClearSearch?.click();
    });

    function setPanoramaMode(active) {
        document.body.classList.toggle('porteiro-panorama', active);
        sessionStorage.setItem(PANORAMA_STORAGE_KEY, active ? '1' : '0');
        if (!btnPanorama) return;
        btnPanorama.innerHTML = active
            ? '<i class="bi bi-layout-sidebar-inset-reverse"></i>'
            : '<i class="bi bi-arrows-fullscreen"></i>';
        btnPanorama.title = active ? 'Restaurar menu lateral' : 'Modo panorama — tela cheia';
    }

    btnPanorama?.addEventListener('click', () => {
        setPanoramaMode(!document.body.classList.contains('porteiro-panorama'));
    });

    if (sessionStorage.getItem(PANORAMA_STORAGE_KEY) === '1' || sessionStorage.getItem(PANORAMA_STORAGE_KEY) === null) {
        setPanoramaMode(true);
    }

    document.getElementById('btnRefresh').addEventListener('click', () => loadPanel(true));
    document.getElementById('btnOpenProhibition')?.addEventListener('click', () => {
        populateProhibitionUnits();
        prohibitionCreateModal.show();
    });

    const porteiroProhibitionNeverExpires = document.getElementById('porteiroProhibitionNeverExpires');
    const porteiroProhibitionExpireWrap = document.getElementById('porteiroProhibitionExpireWrap');
    const porteiroProhibitionExpiresAt = document.getElementById('porteiroProhibitionExpiresAt');

    function togglePorteiroProhibitionExpiration() {
        const never = porteiroProhibitionNeverExpires?.checked;
        porteiroProhibitionExpireWrap?.classList.toggle('d-none', !!never);
        if (porteiroProhibitionExpiresAt) {
            porteiroProhibitionExpiresAt.required = !never;
            if (never) porteiroProhibitionExpiresAt.value = '';
        }
    }

    porteiroProhibitionNeverExpires?.addEventListener('change', togglePorteiroProhibitionExpiration);
    togglePorteiroProhibitionExpiration();

    function populateProhibitionUnits() {
        const select = document.getElementById('prohibitionUnitSelect');
        if (!select) return;
        const units = panelData.units || [];
        const current = select.value;
        select.innerHTML = '<option value="">Selecione...</option>' + units.map(unit => {
            const label = unit.full_identifier || `${unit.block || ''} ${unit.number || unit.identifier || unit.id}`.trim();
            return `<option value="${unit.id}">${esc(label)}</option>`;
        }).join('');
        if (current) select.value = current;
    }

    document.getElementById('btnSubmitProhibition')?.addEventListener('click', async () => {
        const form = document.getElementById('formPorteiroProhibition');
        if (!form.reportValidity()) return;

        const fd = new FormData(form);
        const neverExpires = porteiroProhibitionNeverExpires?.checked;
        const btn = document.getElementById('btnSubmitProhibition');

        try {
            btn.disabled = true;
            const res = await fetch('/api/access-control/prohibitions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    visitor_name: (fd.get('visitor_name') || '').toString().trim(),
                    visitor_document: (fd.get('visitor_document') || '').toString().trim() || null,
                    unit_id: Number(fd.get('unit_id')),
                    never_expires: neverExpires,
                    expires_at: neverExpires ? null : fd.get('expires_at'),
                }),
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || Object.values(data.errors || {})[0]?.[0] || 'Erro ao registrar proibição.');
            prohibitionCreateModal.hide();
            form.reset();
            togglePorteiroProhibitionExpiration();
            showAlert('success', data.message || 'Proibição registrada.');
            activeFilter = 'prohibitions';
            document.querySelectorAll('#panelTabs .porteiro-tab').forEach(b => {
                b.classList.toggle('active', b.dataset.filter === 'prohibitions');
            });
            loadPanel(true);
        } catch (e) {
            showAlert('danger', e.message);
        } finally {
            btn.disabled = false;
        }
    });

    document.getElementById('btnEntered').addEventListener('click', () => {
        if (currentAction?.type === 'provider') {
            submitProvider();
            return;
        }
        submitAction('entered');
    });
    document.getElementById('btnDenied').addEventListener('click', () => submitAction('denied'));
    document.getElementById('btnAlertResident')?.addEventListener('click', () => submitProhibitionAlert());

    const earlyEntryConfirmCheck = document.getElementById('earlyEntryConfirmCheck');
    const btnConfirmEarlyEntry = document.getElementById('btnConfirmEarlyEntry');

    earlyEntryConfirmCheck?.addEventListener('change', () => {
        if (btnConfirmEarlyEntry) btnConfirmEarlyEntry.disabled = !earlyEntryConfirmCheck.checked;
    });

    btnConfirmEarlyEntry?.addEventListener('click', async () => {
        if (!pendingEarlyEntry || !earlyEntryConfirmCheck?.checked) return;
        const payload = { ...pendingEarlyEntry, earlyEntryConfirmed: true };
        pendingEarlyEntry = null;
        earlyEntryModal.hide();
        earlyEntryConfirmCheck.checked = false;
        btnConfirmEarlyEntry.disabled = true;

        if (payload.type === 'authorization') {
            await postProcessAuthorization(payload.id, payload.action, true);
        } else if (payload.type === 'list_item') {
            await postProcessListItem(payload.id, payload.action, true);
        }
    });

    async function loadPanel(manual = false) {
        if (!manual) loadingPanel.classList.remove('d-none');
        try {
            const res = await fetch('/api/access-control/porteiro/panel', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            if (!res.ok) throw new Error('Não foi possível carregar o painel.');
            panelData = await res.json();
            populateProhibitionUnits();
            renderCards();
            lastRefresh.textContent = new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        } catch (e) {
            showAlert('danger', e.message);
        } finally {
            loadingPanel.classList.add('d-none');
        }
    }

    function updatePanelOverview(items, filteredCount) {
        const auth = (panelData.authorizations || []).length;
        const prohibitions = (panelData.prohibitions || []).length;
        const lists = (panelData.lists || []).length;
        const providers = (panelData.service_providers || []).length;
        const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
        set('tabCountAuth', auth);
        set('tabCountProhibitions', prohibitions);
        set('tabCountLists', lists);
        set('tabCountProviders', providers);
        set('tabCountAll', activeFilter === 'all' ? filteredCount : auth + lists + providers);
    }

    function renderCards() {
        cardsGrid.innerHTML = '';
        const items = collectPanelItems();
        const filtered = items.filter(item => itemMatchesSearch(item, searchQuery));

        updateSearchMeta(items.length, filtered.length);
        updatePanelOverview(items, filtered.length);

        if (!filtered.length) {
            cardsGrid.classList.add('d-none');
            emptyPanel.classList.remove('d-none');
            emptyPanelText.textContent = searchQuery
                ? `Nenhum resultado para "${searchQuery}".`
                : (activeFilter === 'prohibitions'
                    ? 'Nenhuma proibição ativa no momento.'
                    : 'Nenhuma liberação pendente no momento.');
            return;
        }

        emptyPanel.classList.add('d-none');
        cardsGrid.classList.remove('d-none');

        filtered.forEach(item => {
            const wrap = document.createElement('div');
            wrap.innerHTML = buildCardHtml(item);
            const card = wrap.firstElementChild;
            card.addEventListener('click', () => openItem(item));
            cardsGrid.appendChild(card);
        });
    }

    function collectPanelItems() {
        const items = [];

        if (activeFilter === 'all' || activeFilter === 'authorizations') {
            (panelData.authorizations || []).forEach(a => items.push({ type: 'authorization', data: a }));
        }
        if (activeFilter === 'prohibitions') {
            (panelData.prohibitions || []).forEach(p => items.push({ type: 'prohibition', data: p }));
        }
        if (activeFilter === 'all' || activeFilter === 'lists') {
            (panelData.lists || []).forEach(l => items.push({ type: 'list', data: l }));
        }
        if (activeFilter === 'all' || activeFilter === 'providers') {
            (panelData.service_providers || []).forEach(p => items.push({ type: 'provider', data: p }));
        }

        return items;
    }

    function updateSearchMeta(total, visible) {
        if (!searchQuery) {
            searchCount?.classList.add('d-none');
            return;
        }
        searchCount?.classList.remove('d-none');
        searchCount.textContent = visible === total
            ? `${visible} resultado(s)`
            : `${visible} de ${total}`;
    }

    function normalizeSearch(value) {
        return (value ?? '').toString().toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    function pushSearchParts(parts, ...values) {
        values.forEach(value => {
            if (value === null || value === undefined) return;
            parts.push(String(value));
        });
    }

    function itemMatchesSearch(item, query) {
        if (!query) return true;

        const nq = normalizeSearch(query);
        const parts = [];

        if (item.type === 'authorization') {
            const a = item.data;
            pushSearchParts(parts,
                a.visitor_name,
                a.unit?.full_identifier,
                a.unit?.number,
                a.unit?.block,
                a.authorized_by?.name,
                a.authorizedBy?.name,
                a.notify_user?.name,
                a.notifyUser?.name
            );
        } else if (item.type === 'prohibition') {
            const p = item.data;
            pushSearchParts(parts,
                p.visitor_name,
                p.visitor_document,
                p.unit?.full_identifier,
                p.unit?.number,
                p.unit?.block,
                p.authorized_by?.name,
                p.authorizedBy?.name,
                p.notify_user?.name,
                p.notifyUser?.name
            );
        } else if (item.type === 'list') {
            const l = item.data;
            pushSearchParts(parts,
                l.title,
                l.notes,
                l.unit?.full_identifier,
                l.unit?.number,
                l.unit?.block,
                l.authorized_by?.name,
                l.authorizedBy?.name,
                l.notify_user?.name,
                l.notifyUser?.name
            );
            (l.items || []).forEach(i => pushSearchParts(parts, i.visitor_name));
        } else if (item.type === 'provider') {
            const p = item.data;
            pushSearchParts(parts,
                p.name,
                p.company,
                p.document,
                p.phone,
                p.unit?.full_identifier,
                p.unit?.number,
                p.unit?.block,
                p.authorized_by?.name,
                p.authorizedBy?.name
            );
        }

        return parts.some(part => normalizeSearch(part).includes(nq));
    }

    function listModalItems(list) {
        const items = list.items || [];
        if (!searchQuery) return items;

        const matched = items.filter(i => normalizeSearch(i.visitor_name).includes(normalizeSearch(searchQuery)));
        return matched.length ? matched : items;
    }

    function itemSearchMatchLabel(item) {
        if (!searchQuery || item.type !== 'list') return '';

        const nq = normalizeSearch(searchQuery);
        const guests = (item.data.items || [])
            .map(i => i.visitor_name)
            .filter(name => normalizeSearch(name).includes(nq));

        if (!guests.length) return '';
        return `<div class="small text-primary mb-1"><i class="bi bi-search"></i> ${esc(guests[0])}${guests.length > 1 ? ` +${guests.length - 1}` : ''}</div>`;
    }

    function buildCardHtml(item) {
        const metaRow = (icon, text) => text ? `<span><i class="bi ${icon}"></i>${esc(text)}</span>` : '';

        if (item.type === 'authorization') {
            const a = item.data;
            const cls = a.authorization_type === 'deny' ? 'deny' : 'allow';
            const label = a.authorization_type === 'deny' ? 'Proibido' : 'Liberado';
            const creator = a.authorizedBy?.name || a.authorized_by?.name || '';
            const early = isBeforeScheduled(a.scheduled_at)
                ? '<span class="access-card__early">Aguardando horário</span>' : '';
            return `<article class="access-card ${cls}">
                <div class="access-card__head">
                    <span class="access-card__type">${label}</span>${early}
                </div>
                <div class="visitor-name">${esc(a.visitor_name)}</div>
                <div class="access-card__meta">
                    ${metaRow('bi-building', a.unit?.full_identifier)}
                    ${metaRow('bi-person', creator)}
                    ${metaRow('bi-clock', fmtDate(a.scheduled_at))}
                </div>
            </article>`;
        }
        if (item.type === 'prohibition') {
            const p = item.data;
            const creator = p.authorizedBy?.name || p.authorized_by?.name || '';
            const resident = p.notifyUser?.name || p.notify_user?.name || '';
            const expiresLabel = p.never_expires ? 'Nunca expira' : (p.expires_at ? fmtDateOnly(p.expires_at) : '—');
            return `<article class="access-card deny prohibition">
                <div class="access-card__head">
                    <span class="access-card__type">Proibido</span>
                </div>
                <div class="visitor-name">${esc(p.visitor_name)}</div>
                <div class="access-card__meta">
                    ${metaRow('bi-card-text', p.visitor_document)}
                    ${metaRow('bi-building', p.unit?.full_identifier)}
                    ${metaRow('bi-person-x', 'Proibido por: ' + creator)}
                    ${metaRow('bi-bell', 'Morador: ' + resident)}
                    ${metaRow('bi-calendar-x', 'Validade: ' + expiresLabel)}
                </div>
                <div class="access-card__alert-hint"><i class="bi bi-exclamation-octagon"></i> Toque para alertar morador</div>
            </article>`;
        }
        if (item.type === 'list') {
            const l = item.data;
            const pending = (l.items || []).length;
            const creator = l.authorizedBy?.name || l.authorized_by?.name || '';
            const early = isBeforeScheduled(l.scheduled_at)
                ? '<span class="access-card__early">Aguardando horário</span>' : '';
            const match = itemSearchMatchLabel(item);
            return `<article class="access-card list">
                <div class="access-card__head">
                    <span class="access-card__type">Lista</span>${early}
                </div>
                <div class="visitor-name">${esc(l.title)}</div>
                ${match}
                <div class="access-card__meta">
                    ${metaRow('bi-building', l.unit?.full_identifier)}
                    ${metaRow('bi-person', creator)}
                    ${metaRow('bi-people', `${pending} convidado(s)`)}
                    ${metaRow('bi-clock', fmtDate(l.scheduled_at))}
                </div>
            </article>`;
        }
        const p = item.data;
        const photo = p.photo_path ? `/storage/${p.photo_path}` : null;
        const unitLabel = p.scope === 'condominium' ? 'Condomínio' : (p.unit?.full_identifier || 'Unidade');
        return `<article class="access-card provider">
            <div class="access-card__head"><span class="access-card__type">Prestador</span></div>
            <div class="access-card__provider-row">
                ${photo
                    ? `<img src="${photo}" class="provider-photo" alt="">`
                    : '<div class="provider-photo bg-light d-flex align-items-center justify-content-center"><i class="bi bi-person-badge text-secondary"></i></div>'}
                <div class="visitor-name mb-0 flex-grow-1">${esc(p.name)}</div>
            </div>
            <div class="access-card__meta">
                ${metaRow('bi-briefcase', p.company)}
                ${metaRow('bi-building', unitLabel)}
            </div>
        </article>`;
    }

    function isBeforeScheduled(iso) {
        if (!iso) return false;
        return new Date() < new Date(iso);
    }

    function earlyEntryWarningHtml(scheduledAt) {
        if (!isBeforeScheduled(scheduledAt)) return '';
        return `<div class="alert alert-warning mb-3">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            <strong>Visitante adiantado.</strong> Horário liberado: <strong>${fmtDate(scheduledAt)}</strong>.
            Avise o morador antes de registrar a entrada.
        </div>`;
    }

    function openEarlyEntryConfirm(scheduledAt, payload) {
        pendingEarlyEntry = payload;
        document.getElementById('earlyEntryScheduledLabel').textContent = fmtDate(scheduledAt);
        earlyEntryConfirmCheck.checked = false;
        btnConfirmEarlyEntry.disabled = true;
        earlyEntryModal.show();
    }

    function resetActionModalButtons() {
        const btnEntered = document.getElementById('btnEntered');
        const btnDenied = document.getElementById('btnDenied');
        const btnAlertResident = document.getElementById('btnAlertResident');
        btnEntered.innerHTML = '<i class="bi bi-check-circle"></i> ENTROU';
        btnEntered.classList.remove('d-none');
        btnDenied.classList.remove('d-none');
        btnAlertResident?.classList.add('d-none');
        document.getElementById('actionModalActions')?.classList.remove('d-none');
    }

    function showProhibitionModalButtons() {
        document.getElementById('btnEntered')?.classList.add('d-none');
        document.getElementById('btnDenied')?.classList.add('d-none');
        document.getElementById('btnAlertResident')?.classList.remove('d-none');
    }

    function openItem(item) {
        if (item.type === 'list') {
            openListModal(item.data);
            return;
        }
        if (item.type === 'prohibition') {
            openProhibitionModal(item.data);
            return;
        }
        currentAction = item;
        resetActionModalButtons();
        const header = document.getElementById('actionModalHeader');
        const title = document.getElementById('actionModalTitle');
        const body = document.getElementById('actionModalBody');
        const btnDenied = document.getElementById('btnDenied');

        if (item.type === 'provider') {
            const p = item.data;
            header.className = 'modal-header bg-warning';
            title.textContent = 'Prestador de serviço';
            btnDenied.classList.add('d-none');
            body.innerHTML = providerBody(p);
        } else {
            const a = item.data;
            header.className = 'modal-header bg-success text-white';
            title.textContent = 'Visitante liberado';
            btnDenied.classList.remove('d-none');
            body.innerHTML = `${earlyEntryWarningHtml(a.scheduled_at)}
                <p class="fs-4 fw-bold mb-1">${esc(a.visitor_name)}</p>
                <p class="mb-0">Unidade: <strong>${esc(a.unit?.full_identifier)}</strong></p>
                <p class="mb-0">Liberado por: <strong>${esc(a.authorizedBy?.name || a.authorized_by?.name || '—')}</strong></p>
                <p class="mb-0 text-muted">Entrada prevista: ${fmtDate(a.scheduled_at)}</p>`;
        }
        actionModal.show();
    }

    function openProhibitionModal(prohibition) {
        currentAction = { type: 'prohibition', data: prohibition };
        resetActionModalButtons();
        showProhibitionModalButtons();

        const header = document.getElementById('actionModalHeader');
        const title = document.getElementById('actionModalTitle');
        const body = document.getElementById('actionModalBody');
        const creator = prohibition.authorizedBy?.name || prohibition.authorized_by?.name || '—';
        const resident = prohibition.notifyUser?.name || prohibition.notify_user?.name || '—';
        const validity = prohibition.never_expires
            ? 'Nunca expira'
            : (prohibition.expires_at ? fmtDateOnly(prohibition.expires_at) : '—');
        const documentLine = prohibition.visitor_document
            ? `<p class="mb-0">Documento: <strong>${esc(prohibition.visitor_document)}</strong></p>`
            : '';

        header.className = 'modal-header bg-danger text-white';
        title.textContent = 'Pessoa proibida identificada';
        body.innerHTML = `
            <div class="alert alert-danger mb-3">
                <i class="bi bi-exclamation-octagon-fill me-1"></i>
                <strong>Atenção:</strong> esta pessoa consta na lista de proibições ativas.
            </div>
            <p class="fs-4 fw-bold mb-1">${esc(prohibition.visitor_name)}</p>
            ${documentLine}
            <p class="mb-0">Unidade: <strong>${esc(prohibition.unit?.full_identifier)}</strong></p>
            <p class="mb-0">Proibido por: <strong>${esc(creator)}</strong></p>
            <p class="mb-0">Morador a alertar: <strong>${esc(resident)}</strong></p>
            <p class="mb-3 text-muted">Validade: ${validity}</p>
            <label class="form-label small fw-semibold" for="prohibitionAlertNotes">Observações (opcional)</label>
            <textarea class="form-control" id="prohibitionAlertNotes" rows="2" maxlength="500" placeholder="Ex.: tentou entrar pelo portão principal"></textarea>
            <p class="small text-muted mt-2 mb-0">O morador receberá um <strong>ALERTA CRÍTICO</strong> e o registro aparecerá no relatório do síndico.</p>`;
        actionModal.show();
    }

    function providerBody(p) {
        const photo = p.photo_path ? `<img src="/storage/${p.photo_path}" class="img-fluid rounded mb-3" style="max-height:200px" alt="">` : '';
        const unitLabel = p.scope === 'condominium'
            ? 'Condomínio'
            : esc(p.unit?.full_identifier || 'Unidade');
        return `${photo}
            <p class="fs-4 fw-bold mb-1">${esc(p.name)}</p>
            <p class="mb-1">${esc(p.company || '')}${p.document ? ` · Doc: ${esc(p.document)}` : ''}</p>
            <p class="mb-1"><strong>Unidade:</strong> ${unitLabel}</p>
            <p class="mb-0 text-muted">Contrato válido até <strong>${fmtDateOnly(p.contract_valid_until)}</strong></p>
            <p class="small text-muted mt-3 mb-0">Confirme abaixo que o prestador entrou.</p>`;
    }

    function openListModal(list) {
        currentList = list;
        document.getElementById('listModalTitle').textContent = list.title;
        const body = document.getElementById('listModalBody');
        const items = listModalItems(list);
        const nq = normalizeSearch(searchQuery);
        const earlyWarning = earlyEntryWarningHtml(list.scheduled_at);

        body.innerHTML = `${earlyWarning}${items.map(item => {
            const match = searchQuery && normalizeSearch(item.visitor_name).includes(nq);
            return `
            <div class="list-guest-row${match ? ' search-match' : ''}">
                <strong>${esc(item.visitor_name)}</strong>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-success btn-list-enter" data-id="${item.id}">ENTROU</button>
                    <button class="btn btn-danger btn-list-deny" data-id="${item.id}">NEGADO</button>
                </div>
            </div>`;
        }).join('')}` || `${earlyWarning}<p class="text-muted">Nenhum convidado pendente.</p>`;

        if (searchQuery && items.length && items.some(i => normalizeSearch(i.visitor_name).includes(nq))) {
            body.insertAdjacentHTML('afterbegin', `<p class="small text-muted mb-2">Filtrando convidados que correspondem a <strong>${esc(searchQuery)}</strong>.</p>`);
        }

        body.querySelectorAll('.btn-list-enter').forEach(btn => {
            btn.addEventListener('click', () => handleListItemAction(btn.dataset.id, 'entered'));
        });
        body.querySelectorAll('.btn-list-deny').forEach(btn => {
            btn.addEventListener('click', () => handleListItemAction(btn.dataset.id, 'denied'));
        });
        listModal.show();
    }

    function handleListItemAction(id, action) {
        if (action === 'entered' && currentList && isBeforeScheduled(currentList.scheduled_at)) {
            openEarlyEntryConfirm(currentList.scheduled_at, {
                type: 'list_item',
                id,
                action,
            });
            return;
        }
        postProcessListItem(id, action, false);
    }

    async function submitAction(action) {
        if (!currentAction || currentAction.type !== 'authorization') return;

        if (action === 'entered' && isBeforeScheduled(currentAction.data.scheduled_at)) {
            actionModal.hide();
            openEarlyEntryConfirm(currentAction.data.scheduled_at, {
                type: 'authorization',
                id: currentAction.data.id,
                action,
            });
            return;
        }

        await postProcessAuthorization(currentAction.data.id, action, false);
    }

    async function submitProhibitionAlert() {
        if (!currentAction || currentAction.type !== 'prohibition') return;

        const notes = document.getElementById('prohibitionAlertNotes')?.value?.trim() || null;
        const btn = document.getElementById('btnAlertResident');

        try {
            btn.disabled = true;
            const res = await fetch(`/api/access-control/authorizations/${currentAction.data.id}/alert-resident`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ notes }),
            });
            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.error || Object.values(data.errors || {})[0]?.[0] || 'Erro ao alertar morador.');
            }
            actionModal.hide();
            showAlert('success', data.message || 'Morador alertado com sucesso.');
            loadPanel(true);
        } catch (e) {
            showAlert('danger', e.message);
        } finally {
            btn.disabled = false;
        }
    }

    async function postProcessAuthorization(id, action, earlyEntryConfirmed) {
        try {
            const res = await fetch(`/api/access-control/authorizations/${id}/process`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body: JSON.stringify({
                    action,
                    early_entry_confirmed: earlyEntryConfirmed,
                }),
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || Object.values(data.errors || {})[0]?.[0] || 'Erro ao registrar.');
            actionModal.hide();
            showAlert('success', earlyEntryConfirmed
                ? 'Entrada antecipada registrada com confirmação do morador.'
                : (data.message || 'Registrado.'));
            loadPanel(true);
        } catch (e) { showAlert('danger', e.message); }
    }

    async function postProcessListItem(id, action, earlyEntryConfirmed) {
        try {
            const res = await fetch(`/api/access-control/list-items/${id}/process`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body: JSON.stringify({
                    action,
                    early_entry_confirmed: earlyEntryConfirmed,
                }),
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || Object.values(data.errors || {})[0]?.[0] || 'Erro ao registrar.');
            listModal.hide();
            showAlert('success', earlyEntryConfirmed
                ? 'Entrada antecipada registrada com confirmação do morador.'
                : (data.message || 'Registrado.'));
            loadPanel(true);
        } catch (e) { showAlert('danger', e.message); }
    }

    async function submitProvider() {
        if (!currentAction || currentAction.type !== 'provider') return;
        try {
            const res = await fetch(`/api/access-control/providers/${currentAction.data.id}/enter`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body: JSON.stringify({})
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Erro.');
            actionModal.hide();
            showAlert('success', 'Entrada do prestador registrada.');
            loadPanel(true);
        } catch (e) { showAlert('danger', e.message); }
    }

    function showAlert(type, msg) {
        alertBox.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show">${esc(msg)}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
    }

    function esc(s) { return (s ?? '').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
    function fmtDate(iso) {
        if (!iso) return '';
        const d = new Date(iso);
        return d.toLocaleString('pt-BR', { day:'2-digit', month:'2-digit', hour:'2-digit', minute:'2-digit' });
    }

    function fmtDateOnly(value) {
        if (!value) return '—';
        const d = new Date(value);
        if (Number.isNaN(d.getTime())) return esc(String(value));
        return d.toLocaleDateString('pt-BR');
    }

    loadPanel();
    pollTimer = setInterval(() => loadPanel(), 12000);
});
</script>
@endpush
