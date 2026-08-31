@extends('layouts.app')

@section('title', 'Liberações de Acesso')

@section('content')
<div class="container-fluid">
    <h2 class="mb-3"><i class="bi bi-person-badge"></i> Liberações de Acesso</h2>

    @if($isMorador)
    <div class="card mb-4 border-primary">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h6 class="mb-1">Permissão para agregados</h6>
                <p class="text-muted small mb-0">Autorize agregados da sua unidade a criar liberações de visitantes.</p>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="agregadoAccessSwitch" {{ auth()->user()->agregado_can_authorize_access ? 'checked' : '' }} style="width:3rem;height:1.5rem">
                <label class="form-check-label ms-2" for="agregadoAccessSwitch">Agregados podem liberar</label>
            </div>
        </div>
    </div>
    @endif

    @if($isAgregado)
    <div class="alert alert-info">Liberações criadas por você notificam o morador responsável da unidade.</div>
    @endif

    @include('dashboard.partials.access-alerts')

    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabAuth">Individual</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabList">Lista / Evento</button></li>
        @if($canManageProviders || $canManageCondoProviders)
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabProvider">Prestadores</button></li>
        @endif
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabHistory">Minhas liberações</button></li>
    </ul>

    <div id="accessFeedbackMain" class="access-feedback mb-3 d-none">
        <div id="accessSubmitProgress" class="access-submit-progress d-none" aria-live="polite" aria-busy="false">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <small class="fw-semibold text-primary" id="accessSubmitProgressLabel">Processando...</small>
                <small class="text-muted" id="accessSubmitProgressPct">0%</small>
            </div>
            <div class="progress access-submit-progress__bar">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                     id="accessSubmitProgressBar"
                     role="progressbar"
                     style="width: 0%"
                     aria-valuenow="0"
                     aria-valuemin="0"
                     aria-valuemax="100"></div>
            </div>
        </div>
        <div id="accessFeedbackAlert" class="access-feedback-alert d-none" aria-live="polite"></div>
    </div>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="tabAuth">
            <div class="card mb-3"><div class="card-body">
                <form id="formAuth" class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Quem vai entrar? *</label>
                        <div class="row g-2">
                            @foreach($visitorPresets as $index => $preset)
                            <div class="col-6 col-md-3 col-lg-auto">
                                <input type="radio" class="btn-check" name="visitor_preset" id="preset-{{ $preset['key'] }}" value="{{ $preset['label'] }}" {{ $index === 0 ? 'checked' : '' }} required>
                                <label class="btn btn-sm btn-outline-primary w-100 py-1 px-2 d-flex align-items-center justify-content-center gap-1" for="preset-{{ $preset['key'] }}">
                                    <i class="bi {{ $preset['icon'] }}"></i>
                                    <span class="small">{{ $preset['label'] }}</span>
                                </label>
                            </div>
                            @endforeach
                            <div class="col-6 col-md-3 col-lg-auto">
                                <input type="radio" class="btn-check" name="visitor_preset" id="preset-outro" value="__other__">
                                <label class="btn btn-sm btn-outline-secondary w-100 py-1 px-2 d-flex align-items-center justify-content-center gap-1" for="preset-outro">
                                    <i class="bi bi-person-fill"></i>
                                    <span class="small">Outro</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 d-none" id="authOtherNameWrap">
                        <label class="form-label">Nome do visitante *</label>
                        <input type="text" name="visitor_name_other" class="form-control" placeholder="Ex: João Silva">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Entrada prevista *</label>
                        <input type="datetime-local" name="scheduled_at" id="authScheduledAt" class="form-control" required>
                        <small class="text-muted">Selecione quando a pessoa deve entrar.</small>
                    </div>
                    <div class="col-12">
                        <details class="border rounded p-3 bg-light">
                            <summary class="fw-semibold user-select-none" style="cursor:pointer">Mais opções</summary>
                            <div class="row g-3 mt-1">
                                <div class="col-md-6">
                                    <label class="form-label">Saída prevista (opcional)</label>
                                    <input type="datetime-local" name="valid_until" class="form-control">
                                    <small class="text-muted">Sem saída: expira em 24h após a entrada.</small>
                                </div>
                            </div>
                        </details>
                    </div>
                    <div class="col-12"><button type="submit" class="btn btn-primary" id="btnSubmitAuth"><i class="bi bi-check2-circle me-1"></i> Criar liberação</button></div>
                </form>
            </div></div>

            <div class="card mb-3 border-danger">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-slash-circle me-1"></i> Proibir visitante</strong>
                    <span class="small opacity-75">Cadastro rápido</span>
                </div>
                <div class="card-body">
                    <form id="formProhibition" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Nome *</label>
                            <input type="text" name="visitor_name" class="form-control" required placeholder="Ex: Fulano de Tal">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Documento (opcional)</label>
                            <input type="text" name="visitor_document" class="form-control" placeholder="RG, CPF...">
                        </div>
                        <div class="col-md-3" id="prohibitionExpireWrap">
                            <label class="form-label">Expira em</label>
                            <input type="date" name="expires_at" id="prohibitionExpiresAt" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-danger w-100" id="btnSubmitProhibition">
                                <i class="bi bi-slash-circle me-1"></i> PROIBIR
                            </button>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="prohibitionNeverExpires" name="never_expires" value="1">
                                <label class="form-check-label fw-semibold" for="prohibitionNeverExpires">NUNCA EXPIRA</label>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tabList">
            <div class="card mb-3"><div class="card-body">
                <form id="formList" class="row g-3">
                    <div class="col-md-6"><label class="form-label">Título do evento *</label><input type="text" name="title" class="form-control" required placeholder="Ex: Festa de aniversário"></div>
                    <div class="col-md-3"><label class="form-label">Data e hora *</label><input type="datetime-local" name="scheduled_at" class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label">Saída prevista</label><input type="datetime-local" name="valid_until" class="form-control"></div>
                    <div class="col-12"><label class="form-label">Nomes (um por linha) *</label><textarea name="visitor_names" class="form-control" rows="5" required placeholder="João Silva&#10;Maria Souza"></textarea></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary" id="btnSubmitList">Criar lista</button></div>
                </form>
            </div></div>
            <div class="card">
                <div class="card-header bg-white"><strong>Minhas listas</strong></div>
                <div class="card-body p-0"><div id="myListsContainer" class="table-responsive"><p class="text-muted p-3 mb-0">Carregando...</p></div></div>
            </div>
        </div>

        @if($canManageProviders || $canManageCondoProviders)
        <div class="tab-pane fade" id="tabProvider">
            <div class="card mb-3"><div class="card-body">
                <form id="formProvider" class="row g-3" enctype="multipart/form-data">
                    @if($canManageCondoProviders)
                    <div class="col-12"><label class="form-label">Escopo</label>
                        <select name="scope" class="form-select"><option value="unit">Minha unidade</option><option value="condominium">Condomínio (síndico)</option></select>
                    </div>
                    @else<input type="hidden" name="scope" value="unit">@endif
                    <div class="col-md-6"><label class="form-label">Nome *</label><input type="text" name="name" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Empresa</label><input type="text" name="company" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Documento</label><input type="text" name="document" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Telefone</label><input type="text" name="phone" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Contrato válido até *</label><input type="date" name="contract_valid_until" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Foto</label><input type="file" name="photo" class="form-control" accept="image/*"></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary" id="btnSubmitProvider">Cadastrar prestador</button></div>
                </form>
            </div></div>
            <div class="card">
                <div class="card-header bg-white"><strong>Meus prestadores</strong></div>
                <div class="card-body p-0"><div id="myProvidersContainer" class="table-responsive"><p class="text-muted p-3 mb-0">Carregando...</p></div></div>
            </div>
        </div>
        @endif

        <div class="tab-pane fade" id="tabHistory">
            <div id="historyContainer" class="table-responsive"><p class="text-muted">Carregando...</p></div>
        </div>
    </div>
</div>

<div class="modal fade" id="editListModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar lista / evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="editListFeedback" class="access-feedback mb-3 d-none">
                    <div id="editListSubmitProgress" class="access-submit-progress access-submit-progress--modal d-none" aria-live="polite" aria-busy="false">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="fw-semibold text-primary" id="editListSubmitProgressLabel">Processando...</small>
                            <small class="text-muted" id="editListSubmitProgressPct">0%</small>
                        </div>
                        <div class="progress access-submit-progress__bar">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                 id="editListSubmitProgressBar"
                                 role="progressbar"
                                 style="width: 0%"
                                 aria-valuenow="0"
                                 aria-valuemin="0"
                                 aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div id="editListFeedbackAlert" class="access-feedback-alert d-none" aria-live="polite"></div>
                </div>
                <form id="formEditList" class="row g-3">
                    <input type="hidden" id="editListId">
                    <div class="col-md-6"><label class="form-label">Título *</label><input type="text" id="editListTitle" class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label">Data e hora *</label><input type="datetime-local" id="editListScheduled" class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label">Saída prevista</label><input type="datetime-local" id="editListValidUntil" class="form-control"></div>
                    <div class="col-12"><label class="form-label">Convidados (um por linha) *</label><textarea id="editListNames" class="form-control" rows="6" required></textarea></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary" id="btnSubmitEditList">Salvar alterações</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editProviderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar prestador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="editProviderFeedback" class="access-feedback mb-3 d-none">
                    <div id="editProviderSubmitProgress" class="access-submit-progress access-submit-progress--modal d-none" aria-live="polite" aria-busy="false">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="fw-semibold text-primary" id="editProviderSubmitProgressLabel">Processando...</small>
                            <small class="text-muted" id="editProviderSubmitProgressPct">0%</small>
                        </div>
                        <div class="progress access-submit-progress__bar">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                 id="editProviderSubmitProgressBar"
                                 role="progressbar"
                                 style="width: 0%"
                                 aria-valuenow="0"
                                 aria-valuemin="0"
                                 aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div id="editProviderFeedbackAlert" class="access-feedback-alert d-none" aria-live="polite"></div>
                </div>
                <form id="formEditProvider" class="row g-3" enctype="multipart/form-data">
                    <input type="hidden" id="editProviderId">
                    @if($canManageCondoProviders)
                    <div class="col-12">
                        <label class="form-label">Escopo</label>
                        <select id="editProviderScope" class="form-select">
                            <option value="unit">Unidade</option>
                            <option value="condominium">Condomínio</option>
                        </select>
                    </div>
                    @endif
                    <div class="col-md-6"><label class="form-label">Nome *</label><input type="text" id="editProviderName" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Empresa</label><input type="text" id="editProviderCompany" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Documento</label><input type="text" id="editProviderDocument" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Telefone</label><input type="text" id="editProviderPhone" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Contrato válido até *</label><input type="date" id="editProviderContract" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Nova foto</label><input type="file" id="editProviderPhoto" class="form-control" accept="image/*"></div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="editProviderRemovePhoto">
                            <label class="form-check-label" for="editProviderRemovePhoto">Remover foto atual</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="editProviderActive">
                            <label class="form-check-label" for="editProviderActive">Prestador ativo na portaria</label>
                        </div>
                    </div>
                    <div class="col-12"><button type="submit" class="btn btn-primary" id="btnSubmitEditProvider">Salvar alterações</button></div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.access-submit-progress {
    padding: 0.85rem 1rem;
    border: 1px solid rgba(13, 110, 253, 0.2);
    border-radius: 0.75rem;
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.06), rgba(13, 110, 253, 0.02));
}
.access-submit-progress__bar {
    height: 8px;
    border-radius: 999px;
    overflow: hidden;
    background: rgba(13, 110, 253, 0.12);
}
.access-submit-progress__bar .progress-bar {
    border-radius: 999px;
    transition: width 0.25s ease;
}
.access-submit-progress--modal {
    padding: 0.65rem 0.85rem;
}
.access-feedback-alert .alert {
    margin-bottom: 0;
    border-radius: 0.75rem;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const editListModal = new bootstrap.Modal(document.getElementById('editListModal'));
    const editProviderModal = document.getElementById('editProviderModal')
        ? new bootstrap.Modal(document.getElementById('editProviderModal'))
        : null;
    const canManageCondoProviders = @json($canManageCondoProviders ?? false);
    let listsCache = [];
    let providersCache = [];

    const formAuth = document.getElementById('formAuth');
    const formList = document.getElementById('formList');
    const formProvider = document.getElementById('formProvider');
    const formEditList = document.getElementById('formEditList');
    const formEditProvider = document.getElementById('formEditProvider');
    const btnSubmitAuth = document.getElementById('btnSubmitAuth');
    const btnSubmitProhibition = document.getElementById('btnSubmitProhibition');
    const btnSubmitList = document.getElementById('btnSubmitList');
    const btnSubmitProvider = document.getElementById('btnSubmitProvider');
    const btnSubmitEditList = document.getElementById('btnSubmitEditList');
    const btnSubmitEditProvider = document.getElementById('btnSubmitEditProvider');
    const progressScopes = {
        main: {
            host: document.getElementById('accessFeedbackMain'),
            wrap: document.getElementById('accessSubmitProgress'),
            bar: document.getElementById('accessSubmitProgressBar'),
            label: document.getElementById('accessSubmitProgressLabel'),
            pct: document.getElementById('accessSubmitProgressPct'),
            alert: document.getElementById('accessFeedbackAlert'),
        },
        editList: {
            host: document.getElementById('editListFeedback'),
            wrap: document.getElementById('editListSubmitProgress'),
            bar: document.getElementById('editListSubmitProgressBar'),
            label: document.getElementById('editListSubmitProgressLabel'),
            pct: document.getElementById('editListSubmitProgressPct'),
            alert: document.getElementById('editListFeedbackAlert'),
        },
        editProvider: {
            host: document.getElementById('editProviderFeedback'),
            wrap: document.getElementById('editProviderSubmitProgress'),
            bar: document.getElementById('editProviderSubmitProgressBar'),
            label: document.getElementById('editProviderSubmitProgressLabel'),
            pct: document.getElementById('editProviderSubmitProgressPct'),
            alert: document.getElementById('editProviderFeedbackAlert'),
        },
    };
    let progressTimer = null;
    let progressValue = 0;
    let activeProgressScope = 'main';
    let submittingAuth = false;
    let submittingProhibition = false;
    let submittingList = false;
    let submittingProvider = false;
    let submittingEditList = false;
    let submittingEditProvider = false;
    const authOtherNameWrap = document.getElementById('authOtherNameWrap');
    const authScheduledAt = document.getElementById('authScheduledAt');

    function setAuthDefaultSchedule() {
        if (!authScheduledAt || authScheduledAt.value) return;
        const now = new Date();
        now.setMinutes(now.getMinutes() + 15 - (now.getMinutes() % 15));
        now.setSeconds(0, 0);
        authScheduledAt.value = toLocalInput(now.toISOString());
        authScheduledAt.min = toLocalInput(new Date().toISOString());
    }

    function toggleAuthOtherName() {
        const preset = formAuth?.querySelector('input[name="visitor_preset"]:checked')?.value;
        const showOther = preset === '__other__';
        authOtherNameWrap?.classList.toggle('d-none', !showOther);
        const otherInput = formAuth?.querySelector('[name="visitor_name_other"]');
        if (otherInput) otherInput.required = showOther;
    }

    formAuth?.querySelectorAll('input[name="visitor_preset"]').forEach(radio => {
        radio.addEventListener('change', toggleAuthOtherName);
    });
    setAuthDefaultSchedule();
    toggleAuthOtherName();

    const formProhibition = document.getElementById('formProhibition');
    const prohibitionNeverExpires = document.getElementById('prohibitionNeverExpires');
    const prohibitionExpireWrap = document.getElementById('prohibitionExpireWrap');
    const prohibitionExpiresAt = document.getElementById('prohibitionExpiresAt');

    function toggleProhibitionExpiration() {
        const never = prohibitionNeverExpires?.checked;
        prohibitionExpireWrap?.classList.toggle('d-none', !!never);
        if (prohibitionExpiresAt) {
            prohibitionExpiresAt.required = !never;
            if (never) prohibitionExpiresAt.value = '';
        }
    }

    prohibitionNeverExpires?.addEventListener('change', toggleProhibitionExpiration);
    toggleProhibitionExpiration();

    formProhibition?.addEventListener('submit', async e => {
        e.preventDefault();
        if (submittingProhibition) return;

        const fd = new FormData(e.target);
        const neverExpires = prohibitionNeverExpires?.checked;
        const visitorName = (fd.get('visitor_name') || '').toString().trim();
        if (!visitorName) return msg('danger', 'Informe o nome.');

        submittingProhibition = true;
        startFormProgress('Registrando proibição...', 'danger', 'main');
        setFormBusy(formProhibition, true, btnSubmitProhibition);

        let result = null;

        try {
            result = await postJson('/api/access-control/prohibitions', {
                visitor_name: visitorName,
                visitor_document: (fd.get('visitor_document') || '').toString().trim() || null,
                never_expires: neverExpires,
                expires_at: neverExpires ? null : fd.get('expires_at'),
            });
            if (result.ok) {
                e.target.reset();
                toggleProhibitionExpiration();
                loadHistory();
            }
        } finally {
            finishFormProgress('main', result);
            setFormBusy(formProhibition, false, btnSubmitProhibition);
            submittingProhibition = false;
        }
    });

    formAuth?.addEventListener('submit', async e => {
        e.preventDefault();
        if (submittingAuth) return;

        const fd = new FormData(e.target);
        const preset = fd.get('visitor_preset');
        let visitorName = preset;
        if (preset === '__other__') {
            visitorName = (fd.get('visitor_name_other') || '').toString().trim();
            if (!visitorName) return msg('danger', 'Informe o nome do visitante.');
        }
        if (!fd.get('scheduled_at')) return msg('danger', 'Informe a data e hora de entrada prevista.');

        submittingAuth = true;
        startFormProgress('Criando liberação...', 'primary', 'main');
        setFormBusy(formAuth, true, btnSubmitAuth);

        let result = null;

        try {
            result = await postJson('/api/access-control/authorizations', {
                visitor_name: visitorName,
                authorization_type: 'allow',
                scheduled_at: fd.get('scheduled_at'),
                valid_until: fd.get('valid_until') || null,
            });
            if (result.ok) {
                e.target.reset();
                const firstPreset = formAuth.querySelector('input[name="visitor_preset"]');
                if (firstPreset) firstPreset.checked = true;
                setAuthDefaultSchedule();
                toggleAuthOtherName();
                loadHistory();
            }
        } finally {
            finishFormProgress('main', result);
            setFormBusy(formAuth, false, btnSubmitAuth);
            submittingAuth = false;
        }
    });

    formList?.addEventListener('submit', async e => {
        e.preventDefault();
        if (submittingList) return;

        const fd = new FormData(e.target);
        const names = fd.get('visitor_names').split('\n').map(s => s.trim()).filter(Boolean);

        submittingList = true;
        startFormProgress('Criando lista de convidados...', 'primary', 'main');
        setFormBusy(formList, true, btnSubmitList);

        let result = null;

        try {
            result = await postJson('/api/access-control/lists', {
                title: fd.get('title'),
                scheduled_at: fd.get('scheduled_at'),
                valid_until: fd.get('valid_until') || null,
                visitor_names: names,
            });
            if (result.ok) {
                e.target.reset();
                loadMyLists();
                loadHistory();
            }
        } finally {
            finishFormProgress('main', result);
            setFormBusy(formList, false, btnSubmitList);
            submittingList = false;
        }
    });

    formEditList?.addEventListener('submit', async e => {
        e.preventDefault();
        if (submittingEditList) return;

        const id = document.getElementById('editListId').value;
        const names = document.getElementById('editListNames').value.split('\n').map(s => s.trim()).filter(Boolean);
        const body = {
            title: document.getElementById('editListTitle').value,
            scheduled_at: document.getElementById('editListScheduled').value,
            valid_until: document.getElementById('editListValidUntil').value || null,
            visitor_names: names,
        };

        submittingEditList = true;
        startFormProgress('Salvando lista...', 'primary', 'editList');
        setFormBusy(formEditList, true, btnSubmitEditList);

        let result = null;
        let feedbackScope = 'editList';

        try {
            result = await putJson(`/api/access-control/lists/${id}`, body);
            if (result.ok) {
                editListModal.hide();
                loadMyLists();
                loadHistory();
                feedbackScope = 'main';
            }
        } finally {
            finishFormProgress('editList', result, feedbackScope);
            setFormBusy(formEditList, false, btnSubmitEditList);
            submittingEditList = false;
        }
    });

    formProvider?.addEventListener('submit', async e => {
        e.preventDefault();
        if (submittingProvider) return;

        submittingProvider = true;
        startFormProgress('Cadastrando prestador...', 'primary', 'main');
        setFormBusy(formProvider, true, btnSubmitProvider);

        let result = null;

        try {
            result = await postForm('/api/access-control/providers', new FormData(e.target));
            if (result.ok) {
                e.target.reset();
                loadMyProviders();
            }
        } finally {
            finishFormProgress('main', result);
            setFormBusy(formProvider, false, btnSubmitProvider);
            submittingProvider = false;
        }
    });

    formEditProvider?.addEventListener('submit', async e => {
        e.preventDefault();
        if (submittingEditProvider) return;

        const id = document.getElementById('editProviderId').value;
        const fd = new FormData();
        fd.append('_method', 'PUT');
        fd.append('name', document.getElementById('editProviderName').value);
        fd.append('company', document.getElementById('editProviderCompany').value);
        fd.append('document', document.getElementById('editProviderDocument').value);
        fd.append('phone', document.getElementById('editProviderPhone').value);
        fd.append('contract_valid_until', document.getElementById('editProviderContract').value);
        fd.append('scope', canManageCondoProviders
            ? document.getElementById('editProviderScope').value
            : 'unit');
        fd.append('is_active', document.getElementById('editProviderActive').checked ? '1' : '0');
        if (document.getElementById('editProviderRemovePhoto').checked) {
            fd.append('remove_photo', '1');
        }
        const photo = document.getElementById('editProviderPhoto').files[0];
        if (photo) fd.append('photo', photo);

        submittingEditProvider = true;
        startFormProgress('Salvando prestador...', 'primary', 'editProvider');
        setFormBusy(formEditProvider, true, btnSubmitEditProvider);

        let result = null;
        let feedbackScope = 'editProvider';

        try {
            result = await postForm(`/api/access-control/providers/${id}`, fd);
            if (result.ok) {
                editProviderModal?.hide();
                loadMyProviders();
                feedbackScope = 'main';
            }
        } finally {
            finishFormProgress('editProvider', result, feedbackScope);
            setFormBusy(formEditProvider, false, btnSubmitEditProvider);
            submittingEditProvider = false;
        }
    });

    document.getElementById('agregadoAccessSwitch')?.addEventListener('change', async e => {
        const result = await postJson('/api/access-control/settings/agregado-access', { agregado_can_authorize_access: e.target.checked });
        if (result) {
            msg(result.ok ? 'success' : 'danger', result.message, 'main');
        }
    });

    document.querySelector('[data-bs-target="#tabHistory"]')?.addEventListener('shown.bs.tab', loadHistory);
    document.querySelector('[data-bs-target="#tabList"]')?.addEventListener('shown.bs.tab', loadMyLists);
    document.querySelector('[data-bs-target="#tabProvider"]')?.addEventListener('shown.bs.tab', loadMyProviders);

    async function postJson(url, body) {
        try {
            const res = await fetch(url, {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin', body: JSON.stringify(body)
            });
            const data = await res.json();
            if (!res.ok) {
                return {
                    ok: false,
                    message: data.error || Object.values(data.errors || {})[0]?.[0] || 'Erro.',
                };
            }
            return {
                ok: true,
                message: data.message || 'Operação realizada com sucesso.',
            };
        } catch {
            return {
                ok: false,
                message: 'Falha de conexão. Verifique sua internet e tente novamente.',
            };
        }
    }

    async function putJson(url, body) {
        try {
            const res = await fetch(url, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body: JSON.stringify(body),
            });
            const data = await res.json();
            if (!res.ok) {
                return {
                    ok: false,
                    message: data.error || Object.values(data.errors || {})[0]?.[0] || 'Erro ao salvar.',
                };
            }
            return {
                ok: true,
                message: data.message || 'Alterações salvas com sucesso.',
            };
        } catch {
            return {
                ok: false,
                message: 'Falha de conexão. Verifique sua internet e tente novamente.',
            };
        }
    }

    async function postForm(url, body) {
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body,
            });
            const data = await res.json();
            if (!res.ok) {
                return {
                    ok: false,
                    message: data.error || Object.values(data.errors || {})[0]?.[0] || 'Erro.',
                };
            }
            return {
                ok: true,
                message: data.message || 'Operação realizada com sucesso.',
            };
        } catch {
            return {
                ok: false,
                message: 'Falha de conexão. Verifique sua internet e tente novamente.',
            };
        }
    }

    function getProgressScope(scope = 'main') {
        return progressScopes[scope] || progressScopes.main;
    }

    function updateProgressBar(value, scope = activeProgressScope) {
        const els = getProgressScope(scope);
        const pct = Math.round(value);
        if (els.bar) {
            els.bar.style.width = `${pct}%`;
            els.bar.setAttribute('aria-valuenow', String(pct));
        }
        if (els.pct) {
            els.pct.textContent = `${pct}%`;
        }
    }

    function startFormProgress(label, variant = 'primary', scope = 'main') {
        const els = getProgressScope(scope);
        if (!els.wrap) return;

        hideFeedback(scope);
        activeProgressScope = scope;
        clearInterval(progressTimer);
        progressValue = 8;
        els.wrap.classList.remove('d-none');
        els.wrap.setAttribute('aria-busy', 'true');
        els.label.textContent = label;
        els.label.className = `fw-semibold text-${variant}`;
        els.bar.className = `progress-bar progress-bar-striped progress-bar-animated bg-${variant}`;
        updateProgressBar(progressValue, scope);
        syncFeedbackHost(scope);

        if (scope === 'main') {
            els.host?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        progressTimer = setInterval(() => {
            if (progressValue < 92) {
                progressValue += Math.random() * 7 + 3;
                progressValue = Math.min(92, progressValue);
                updateProgressBar(progressValue, scope);
            }
        }, 180);
    }

    function finishFormProgress(scope = activeProgressScope, result = null, feedbackScope = scope) {
        clearInterval(progressTimer);
        updateProgressBar(100, scope);

        const els = getProgressScope(scope);
        setTimeout(() => {
            els.wrap?.classList.add('d-none');
            els.wrap?.setAttribute('aria-busy', 'false');
            progressValue = 0;
            updateProgressBar(0, scope);

            if (result?.message) {
                showFeedback(
                    feedbackScope,
                    result.ok ? 'success' : 'danger',
                    result.message
                );
            } else {
                syncFeedbackHost(feedbackScope);
            }
        }, 500);
    }

    function hideFeedback(scope = 'main') {
        const els = getProgressScope(scope);
        if (!els.alert) return;
        els.alert.classList.add('d-none');
        els.alert.innerHTML = '';
        syncFeedbackHost(scope);
    }

    function showFeedback(scope, type, text) {
        const els = getProgressScope(scope);
        if (!els.alert || !text) return;

        els.alert.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show mb-0">${esc(text)}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
        els.alert.classList.remove('d-none');
        syncFeedbackHost(scope);

        if (scope === 'main') {
            els.host?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    function syncFeedbackHost(scope = 'main') {
        const els = getProgressScope(scope);
        if (!els.host) return;
        const visible = !els.wrap?.classList.contains('d-none') || !els.alert?.classList.contains('d-none');
        els.host.classList.toggle('d-none', !visible);
    }

    function setFormBusy(form, busy, submitBtn) {
        if (!form) return;

        form.querySelectorAll('input, select, textarea, button').forEach(el => {
            if (busy) {
                el.dataset.wasDisabled = el.disabled ? '1' : '0';
                el.disabled = true;
            } else if (el.dataset.wasDisabled !== '1') {
                el.disabled = false;
            }
            delete el.dataset.wasDisabled;
        });

        if (!submitBtn) return;

        if (busy) {
            submitBtn.dataset.originalHtml = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Aguarde...';
        } else if (submitBtn.dataset.originalHtml) {
            submitBtn.innerHTML = submitBtn.dataset.originalHtml;
            delete submitBtn.dataset.originalHtml;
        }
    }

    async function loadMyProviders() {
        const container = document.getElementById('myProvidersContainer');
        if (!container) return;
        try {
            const res = await fetch('/api/access-control/providers', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            const data = await res.json();
            providersCache = data.data || [];
            container.innerHTML = renderProvidersTable(providersCache);
            bindProviderActions(container);
        } catch {
            container.innerHTML = '<p class="text-danger p-3 mb-0">Erro ao carregar prestadores.</p>';
        }
    }

    function providerStatusLabel(p) {
        if (!p.is_active) return '<span class="badge bg-secondary">Inativo</span>';
        const validUntil = p.contract_valid_until ? new Date(p.contract_valid_until) : null;
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        if (validUntil && validUntil < today) {
            return '<span class="badge bg-danger">Contrato vencido</span>';
        }
        return '<span class="badge bg-success">Ativo</span>';
    }

    function renderProvidersTable(providers) {
        if (!providers.length) {
            return '<p class="text-muted p-3 mb-0">Nenhum prestador cadastrado.</p>';
        }

        const rows = providers.map(p => {
            const photo = p.photo_path ? `/storage/${p.photo_path}` : null;
            const scope = p.scope === 'condominium'
                ? 'Condomínio'
                : esc(p.unit?.full_identifier || 'Unidade');
            const actions = `<button class="btn btn-sm btn-outline-primary btn-edit-provider" data-id="${p.id}">Editar</button>
                <button class="btn btn-sm btn-outline-danger btn-deactivate-provider" data-id="${p.id}">Excluir</button>`;

            return `<tr>
                <td>${photo ? `<img src="${photo}" alt="" class="rounded" width="40" height="40" style="object-fit:cover">` : '—'}</td>
                <td><strong>${esc(p.name)}</strong>${p.company ? `<br><small class="text-muted">${esc(p.company)}</small>` : ''}</td>
                <td>${scope}</td>
                <td>${fmtDateOnly(p.contract_valid_until)}</td>
                <td>${providerStatusLabel(p)}</td>
                <td class="text-nowrap">${actions}</td>
            </tr>`;
        }).join('');

        return `<table class="table table-sm mb-0">
            <thead><tr><th>Foto</th><th>Prestador</th><th>Escopo</th><th>Contrato até</th><th>Status</th><th>Ações</th></tr></thead>
            <tbody>${rows}</tbody>
        </table>`;
    }

    function bindProviderActions(container) {
        container.querySelectorAll('.btn-edit-provider').forEach(btn => {
            btn.addEventListener('click', () => openEditProvider(btn.dataset.id));
        });
        container.querySelectorAll('.btn-deactivate-provider').forEach(btn => {
            btn.addEventListener('click', () => deactivateProvider(btn.dataset.id));
        });
    }

    function openEditProvider(id) {
        hideFeedback('editProvider');
        const provider = providersCache.find(p => String(p.id) === String(id));
        if (!provider || !editProviderModal) return;

        document.getElementById('editProviderId').value = provider.id;
        document.getElementById('editProviderName').value = provider.name || '';
        document.getElementById('editProviderCompany').value = provider.company || '';
        document.getElementById('editProviderDocument').value = provider.document || '';
        document.getElementById('editProviderPhone').value = provider.phone || '';
        document.getElementById('editProviderContract').value = toDateInput(provider.contract_valid_until);
        document.getElementById('editProviderActive').checked = !!provider.is_active;
        document.getElementById('editProviderRemovePhoto').checked = false;
        document.getElementById('editProviderPhoto').value = '';

        if (canManageCondoProviders) {
            document.getElementById('editProviderScope').value = provider.scope || 'unit';
        }

        editProviderModal.show();
    }

    async function deactivateProvider(id) {
        if (!confirm('Desativar este prestador? Ele deixará de aparecer na portaria.')) return;
        const res = await fetch(`/api/access-control/providers/${id}/deactivate`, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        const data = await res.json();
        if (!res.ok) return msg('danger', data.error || 'Erro ao desativar.', 'main');
        msg('success', data.message || 'Prestador desativado.', 'main');
        loadMyProviders();
    }

    function toDateInput(value) {
        if (!value) return '';
        return String(value).substring(0, 10);
    }

    function fmtDateOnly(value) {
        if (!value) return '—';
        const d = new Date(value);
        if (Number.isNaN(d.getTime())) return esc(String(value));
        return d.toLocaleDateString('pt-BR');
    }

    async function loadMyLists() {
        const container = document.getElementById('myListsContainer');
        if (!container) return;
        try {
            const res = await fetch('/api/access-control/lists', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            const data = await res.json();
            listsCache = data.data || [];
            container.innerHTML = renderListsTable(listsCache);
            bindListActions(container);
        } catch {
            container.innerHTML = '<p class="text-danger p-3">Erro ao carregar listas.</p>';
        }
    }

    async function loadHistory() {
        const container = document.getElementById('historyContainer');
        const [authRes, listRes] = await Promise.all([
            fetch('/api/access-control/authorizations', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' }),
            fetch('/api/access-control/lists', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' }),
        ]);
        const authData = await authRes.json();
        const listData = await listRes.json();
        listsCache = listData.data || [];

        const authRows = (authData.data || []).map(a => {
            const actions = a.status === 'pending'
                ? `<button class="btn btn-sm btn-outline-danger btn-cancel-auth" data-id="${a.id}">Cancelar</button>`
                : '—';
            return `<tr>
                <td><span class="badge bg-secondary">Individual</span></td>
                <td>${esc(a.visitor_name)}</td>
                <td>${a.authorization_type === 'deny' ? 'Proibido' : 'Liberado'}</td>
                <td>${esc(a.status)}</td>
                <td>${fmtDate(a.scheduled_at)}</td>
                <td>${actions}</td>
            </tr>`;
        }).join('');

        const listRows = listsCache.map(l => {
            const pending = (l.items || []).filter(i => i.status === 'pending').length;
            const canManage = l.status === 'active';
            const actions = canManage
                ? `<button class="btn btn-sm btn-outline-primary btn-edit-list" data-id="${l.id}">Editar</button>
                   <button class="btn btn-sm btn-outline-danger btn-cancel-list" data-id="${l.id}">Excluir</button>`
                : '—';
            return `<tr>
                <td><span class="badge bg-primary">Lista</span></td>
                <td>${esc(l.title)} <small class="text-muted">(${(l.items || []).length} convidados, ${pending} pendentes)</small></td>
                <td>—</td>
                <td>${esc(l.status)}</td>
                <td>${fmtDate(l.scheduled_at)}</td>
                <td>${actions}</td>
            </tr>`;
        }).join('');

        container.innerHTML = `<table class="table table-sm mb-0">
            <thead><tr><th>Tipo</th><th>Descrição</th><th>Classificação</th><th>Status</th><th>Horário</th><th>Ações</th></tr></thead>
            <tbody>${authRows}${listRows || ''}${(!authRows && !listRows) ? '<tr><td colspan="6" class="text-center text-muted">Nenhuma liberação cadastrada</td></tr>' : ''}</tbody>
        </table>`;

        bindListActions(container);
        container.querySelectorAll('.btn-cancel-auth').forEach(btn => {
            btn.addEventListener('click', () => cancelAuth(btn.dataset.id));
        });
    }

    function renderListsTable(lists) {
        if (!lists.length) {
            return '<p class="text-muted p-3 mb-0">Nenhuma lista cadastrada.</p>';
        }
        const rows = lists.map(l => {
            const pending = (l.items || []).filter(i => i.status === 'pending').length;
            const canManage = l.status === 'active';
            const actions = canManage
                ? `<button class="btn btn-sm btn-outline-primary btn-edit-list" data-id="${l.id}">Editar</button>
                   <button class="btn btn-sm btn-outline-danger btn-cancel-list" data-id="${l.id}">Excluir</button>`
                : `<span class="text-muted small">${esc(l.status)}</span>`;
            return `<tr>
                <td>${esc(l.title)}</td>
                <td>${(l.items || []).length} convidado(s)</td>
                <td>${pending} pendente(s)</td>
                <td>${esc(l.status)}</td>
                <td>${fmtDate(l.scheduled_at)}</td>
                <td class="text-nowrap">${actions}</td>
            </tr>`;
        }).join('');
        return `<table class="table table-sm mb-0"><thead><tr><th>Evento</th><th>Convidados</th><th>Pendentes</th><th>Status</th><th>Horário</th><th>Ações</th></tr></thead><tbody>${rows}</tbody></table>`;
    }

    function bindListActions(container) {
        container.querySelectorAll('.btn-edit-list').forEach(btn => {
            btn.addEventListener('click', () => openEditList(btn.dataset.id));
        });
        container.querySelectorAll('.btn-cancel-list').forEach(btn => {
            btn.addEventListener('click', () => cancelList(btn.dataset.id));
        });
    }

    function openEditList(id) {
        hideFeedback('editList');
        const list = listsCache.find(l => String(l.id) === String(id));
        if (!list) return;
        document.getElementById('editListId').value = list.id;
        document.getElementById('editListTitle').value = list.title;
        document.getElementById('editListScheduled').value = toLocalInput(list.scheduled_at);
        document.getElementById('editListValidUntil').value = list.valid_until ? toLocalInput(list.valid_until) : '';
        document.getElementById('editListNames').value = (list.items || []).map(i => i.visitor_name).join('\n');
        editListModal.show();
    }

    async function cancelList(id) {
        if (!confirm('Cancelar esta lista? Ela deixará de aparecer na portaria.')) return;
        const res = await fetch(`/api/access-control/lists/${id}/cancel`, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        const data = await res.json();
        if (!res.ok) return msg('danger', data.error || 'Erro ao cancelar.', 'main');
        msg('success', data.message || 'Lista cancelada.', 'main');
        loadMyLists();
        loadHistory();
    }

    async function cancelAuth(id) {
        if (!confirm('Cancelar esta liberação?')) return;
        const res = await fetch(`/api/access-control/authorizations/${id}/cancel`, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        const data = await res.json();
        if (!res.ok) return msg('danger', data.error || 'Erro ao cancelar.', 'main');
        msg('success', data.message || 'Liberação cancelada.', 'main');
        loadHistory();
    }

    function toLocalInput(iso) {
        if (!iso) return '';
        const d = new Date(iso);
        const pad = n => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    }

    function fmtDate(iso) {
        if (!iso) return '';
        return new Date(iso).toLocaleString('pt-BR', { day:'2-digit', month:'2-digit', hour:'2-digit', minute:'2-digit' });
    }

    function esc(s) { return (s ?? '').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

    function msg(type, text, scope = 'main') {
        hideFeedback(scope);
        showFeedback(scope, type, text);
    }

    loadMyLists();
    loadMyProviders();
});
</script>
@endpush
