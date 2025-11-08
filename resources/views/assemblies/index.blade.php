@extends('layouts.app')

@section('title', 'Assembleias')

@push('styles')
<style>
    .assembly-section-card {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
    }

    .assembly-section-card header h6 {
        letter-spacing: .08em;
    }

    #novaAssembleiaModal .modal-content {
        border-radius: 1.25rem;
    }

    #novaAssembleiaModal .btn-check:checked + .btn {
        color: #fff;
    }

    #novaAssembleiaModal textarea {
        min-height: 120px;
    }

    #novaAssembleiaModal .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }

    @media (max-width: 992px) {
        .assembly-section-card {
            padding: 1.25rem;
        }
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h2 class="mb-0">Assembleias</h2>
                <small class="text-muted">Gerencie convocações, votações e atas com transparência.</small>
            </div>
            @can('create_assemblies')
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#novaAssembleiaModal">
                <i class="bi bi-plus-circle"></i> Nova Assembleia
            </button>
            @endcan
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <ul class="nav nav-pills" id="assembliesTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" href="#" data-status="scheduled">Agendadas</a>
    </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" href="#" data-status="in_progress">Em Andamento</a>
    </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" href="#" data-status="completed">Concluídas</a>
    </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" href="#" data-status="cancelled">Canceladas</a>
    </li>
</ul>
                </div>
            </div>

<div class="card">
            <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="card-title mb-0" data-section-title>Assembleias Agendadas</h5>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="filtroVotacaoAberta">
                <label class="form-check-label" for="filtroVotacaoAberta">Somente votação aberta</label>
                    </div>
                </div>

        <div class="row g-4" id="assembliesContainer">
            <div class="col-12 text-center py-5" data-loading-state>
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <p class="mt-3 mb-0 text-muted">Buscando assembleias...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Nova Assembleia -->
<div class="modal fade" id="novaAssembleiaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                <h5 class="modal-title">Nova Assembleia</h5>
                    <small class="text-muted">Configure o ciclo completo da assembleia e os itens de votação.</small>
            </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
                <form id="formNovaAssembleia">
            <div class="modal-body">
                    <div class="container-fluid px-0">
                        <section class="assembly-section-card mb-4">
                            <header class="mb-3">
                                <h6 class="mb-1 text-uppercase text-primary fw-semibold small">Informações gerais</h6>
                                <span class="text-muted small">Defina o contexto da assembleia antes de convidar os participantes.</span>
                            </header>
                            <div class="row g-3">
                                <div class="col-lg-7">
                        <label class="form-label">Título *</label>
                                    <input type="text" class="form-control form-control-lg" name="title" placeholder="Ex: Assembleia Ordinária 02/2026" required>
                    </div>
                                <div class="col-lg-5">
                                    <label class="form-label">Urgência *</label>
                                    <select class="form-select form-select-lg" name="urgency" required>
                                        <option value="normal" selected>Normal</option>
                                        <option value="low">Baixa</option>
                                        <option value="high">Alta</option>
                                        <option value="critical">Crítica</option>
                                    </select>
                    </div>
                                <div class="col-12">
                        <label class="form-label">Descrição</label>
                                    <textarea class="form-control" name="description" rows="3" placeholder="Contextualize a assembleia, objetivos e orientações."></textarea>
                    </div>
                    </div>
                        </section>

                        <div class="row g-4">
                            <div class="col-lg-6 d-flex flex-column gap-4">
                                <section class="assembly-section-card">
                                    <header class="mb-3">
                                        <h6 class="mb-1 text-uppercase text-primary fw-semibold small">Cronograma e janela de votação</h6>
                                        <span class="text-muted small">Informe quando os moradores serão convocados e até quando poderão votar.</span>
                                    </header>
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label">Data de início *</label>
                                <input type="datetime-local" class="form-control" name="scheduled_at" required>
                            </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Início da votação</label>
                                            <input type="datetime-local" class="form-control" name="voting_opens_at">
                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Encerramento da votação</label>
                                            <input type="datetime-local" class="form-control" name="voting_closes_at">
                        </div>
                                        <div class="col-md-4">
                                <label class="form-label">Duração (minutos) *</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="duration_minutes" value="120" min="15" max="1440" required>
                                                <span class="input-group-text">min</span>
                            </div>
                        </div>
                    </div>
                                </section>

                                <section class="assembly-section-card" data-role-checkboxes>
                                    <header class="mb-3">
                                        <h6 class="mb-1 text-uppercase text-primary fw-semibold small">Quem pode votar *</h6>
                                        <span class="text-muted small">Selecione os perfis autorizados a registrar votos nesta assembleia.</span>
                                    </header>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" value="Morador" id="roleMorador" checked>
                                        <label class="form-check-label" for="roleMorador">Moradores</label>
                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" value="Agregado" id="roleAgregado">
                                        <label class="form-check-label" for="roleAgregado">Agregados</label>
                            </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="Síndico" id="roleSindico" checked>
                                        <label class="form-check-label" for="roleSindico">Síndicos</label>
                        </div>
                                </section>
                    </div>

                            <div class="col-lg-6 d-flex flex-column gap-4">
                                <section class="assembly-section-card">
                                    <header class="mb-3">
                                        <h6 class="mb-1 text-uppercase text-primary fw-semibold small">Configurações de voto</h6>
                                        <span class="text-muted small">Defina o tipo de votação, visibilidade e preferências adicionais.</span>
                                    </header>
                                    <div class="mb-4">
                                        <label class="form-label">Tipo de votação *</label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="voting_type" id="votingTypeOpen" value="open" autocomplete="off" checked>
                                            <label class="btn btn-outline-primary" for="votingTypeOpen">Aberta (voto identificado)</label>

                                            <input type="radio" class="btn-check" name="voting_type" id="votingTypeSecret" value="secret" autocomplete="off">
                                            <label class="btn btn-outline-primary" for="votingTypeSecret">Secreta (voto anônimo)</label>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label">Visibilidade dos resultados *</label>
                    <div class="form-check">
                                            <input class="form-check-input" type="radio" name="results_visibility" id="resultsVisibilityFinal" value="final_only" checked>
                                            <label class="form-check-label" for="resultsVisibilityFinal">
                                                Exibir somente o resultado final
                                            </label>
                                        </div>
                                      <div class="form-check">
                                            <input class="form-check-input" type="radio" name="results_visibility" id="resultsVisibilityRealtime" value="real_time">
                                            <label class="form-check-label" for="resultsVisibilityRealtime">
                                                Exibir parcial dos votos durante a votação
                                            </label>
                                            <small class="text-muted d-block">Os moradores verão barras de progresso atualizadas com o total de votos por opção.</small>
                                        </div>
                                    </div>
                                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="allow_delegation" id="allowDelegation">
                        <label class="form-check-label" for="allowDelegation">
                            Permitir delegação de voto
                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="allow_comments" id="allowComments">
                                        <label class="form-check-label" for="allowComments">
                                            Permitir comentários no voto
                                        </label>
                                    </div>
                                </section>

                                <section class="assembly-section-card">
                                    <header class="mb-3">
                                        <h6 class="mb-1 text-uppercase text-primary fw-semibold small">Documentos de apoio</h6>
                                        <span class="text-muted small">Anexe convocatórias, orçamentos e demais evidências.</span>
                                    </header>
                                    <div class="mb-2">
                                        <input class="form-control" type="file" name="attachments[]" accept=".png,.jpg,.jpeg,.webp,.pdf" multiple>
                                    </div>
                                    <small class="text-muted">Máximo 10 arquivos, até 5MB cada.</small>
                                </section>
                            </div>
                        </div>

                        <section class="mt-4 pt-4 border-top">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                                <div>
                                    <h6 class="mb-1 text-uppercase text-primary fw-semibold small">Itens da pauta</h6>
                                    <span class="text-muted small">Cada item será votado separadamente. Use opções livres para personalizar o voto.</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addAgendaItemBtn">
                            <i class="bi bi-plus"></i> Adicionar Item
                        </button>
                    </div>

                            <div id="agendaItems" class="d-flex flex-column gap-3" data-items-container></div>
                        </section>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-rocket-takeoff"></i> Criar Assembleia
                </button>
                    </div>
                </form>
        </div>
    </div>
</div>
@endsection

<!-- Modal Votação -->
<div class="modal fade" id="voteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="voteModalTitle">Registrar voto</h5>
                    <small class="text-muted" id="voteModalResultsNote"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="voteForm">
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="voteModalError"></div>
                    <h6 class="fw-semibold mb-1" id="voteItemTitle"></h6>
                    <p class="text-muted small mb-3" id="voteModalNote"></p>
                    <div class="small text-muted mb-3" id="voteModalThreshold"></div>

                    <div class="d-flex flex-column gap-2" id="voteOptions"></div>

                    <div class="mt-3 d-none" id="voteCommentGroup">
                        <label class="form-label small" id="voteCommentLabel">Comentário (opcional)</label>
                        <textarea class="form-control" id="voteComment" rows="3" maxlength="1000" placeholder="Adicione um comentário ao seu voto"></textarea>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="voteSubmitButton">
                        <i class="bi bi-check-circle"></i> Confirmar voto
                </button>
            </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.ASSEMBLIES_CONTEXT = {
        userId: {{ auth()->id() }},
        roles: @json(auth()->user()->roles->pluck('name')),
        csrf: document.querySelector('meta[name="csrf-token"]').content,
    };

    document.addEventListener('DOMContentLoaded', () => AssembliesPage.init());

    const AssembliesPage = {
        state: {
            status: 'scheduled',
            assemblies: [],
            loading: true,
            editingAssemblyId: null,
            currentVote: null,
        },

        init() {
            this.cacheElements();
            this.bindEvents();
            this.resetAgendaItems();
            this.loadAssemblies();
        },

        cacheElements() {
            this.tabContainer = document.getElementById('assembliesTabs');
            this.sectionTitle = document.querySelector('[data-section-title]');
            this.container = document.getElementById('assembliesContainer');
            this.form = document.getElementById('formNovaAssembleia');
            this.itemsContainer = this.form.querySelector('[data-items-container]');
            this.rolesContainer = this.form.querySelector('[data-role-checkboxes]');
            this.modalEl = document.getElementById('novaAssembleiaModal');
            this.onlyOpenSwitch = document.getElementById('filtroVotacaoAberta');
            this.submitButton = this.form.querySelector('button[type="submit"]');
            this.modalTitle = this.modalEl.querySelector('.modal-title');
            this.voteModalEl = document.getElementById('voteModal');
            this.voteModal = window.bootstrap ? new bootstrap.Modal(this.voteModalEl) : null;
            this.voteForm = document.getElementById('voteForm');
            this.voteOptionsContainer = document.getElementById('voteOptions');
            this.voteCommentGroup = document.getElementById('voteCommentGroup');
            this.voteCommentInput = document.getElementById('voteComment');
            this.voteCommentLabel = this.voteCommentGroup ? this.voteCommentGroup.querySelector('label') : null;
            this.voteModalTitle = document.getElementById('voteModalTitle');
            this.voteItemTitle = document.getElementById('voteItemTitle');
            this.voteModalNote = document.getElementById('voteModalNote');
            this.voteModalResultsNote = document.getElementById('voteModalResultsNote');
            this.voteModalThreshold = document.getElementById('voteModalThreshold');
            this.voteModalError = document.getElementById('voteModalError');
            this.voteSubmitButton = document.getElementById('voteSubmitButton');
            this.defaultVoteSubmitLabel = this.voteSubmitButton?.innerHTML ?? '';
            this.defaultVoteCommentLabel = this.voteCommentLabel?.innerHTML ?? '';
            this.defaultVoteCommentPlaceholder = this.voteCommentInput?.getAttribute('placeholder') ?? '';
            this.closeSubmitLabel = '<i class="bi bi-stop-circle"></i> Confirmar encerramento';
        },

        bindEvents() {
            this.tabContainer.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', event => {
                    event.preventDefault();
                    this.setStatus(link.getAttribute('data-status'));
                });
            });

            this.onlyOpenSwitch.addEventListener('change', () => this.loadAssemblies());

            document.getElementById('addAgendaItemBtn').addEventListener('click', () => this.addAgendaItem());

            this.form.addEventListener('submit', event => {
                event.preventDefault();
                this.submitAssembly();
            });

            this.container.addEventListener('click', event => {
                if (event.target.closest('[data-action="export-minutes"]')) {
                    const assemblyId = event.target.closest('[data-action="export-minutes"]').dataset.id;
                    this.exportMinutes(assemblyId);
                }

                if (event.target.closest('[data-action="edit-assembly"]')) {
                    const assemblyId = event.target.closest('[data-action="edit-assembly"]').dataset.id;
                    this.startEditAssembly(assemblyId);
                }

                if (event.target.closest('[data-action="delete-assembly"]')) {
                    const assemblyId = event.target.closest('[data-action="delete-assembly"]').dataset.id;
                    this.deleteAssembly(assemblyId);
                }

                if (event.target.closest('[data-action="open-vote"]')) {
                    const button = event.target.closest('[data-action="open-vote"]');
                    this.openVoteModal(Number(button.dataset.assembly), Number(button.dataset.item));
                }

                if (event.target.closest('[data-action="close-assembly"]')) {
                    const button = event.target.closest('[data-action="close-assembly"]');
                    this.openCloseModal(Number(button.dataset.id));
                }
            });

            if (this.voteForm) {
                this.voteForm.addEventListener('submit', event => {
                    event.preventDefault();
                    this.submitVote();
                });
            }

            if (this.voteModalEl) {
                this.voteModalEl.addEventListener('hidden.bs.modal', () => {
                    this.state.currentVote = null;
                    if (this.voteForm) {
                        this.voteForm.reset();
                    }
                    this.showVoteError('');
                    if (this.voteSubmitButton && this.defaultVoteSubmitLabel) {
                        this.voteSubmitButton.innerHTML = this.defaultVoteSubmitLabel;
                    }
                    if (this.voteCommentLabel && this.defaultVoteCommentLabel) {
                        this.voteCommentLabel.innerHTML = this.defaultVoteCommentLabel;
                    }
                    if (this.voteCommentInput && this.defaultVoteCommentPlaceholder) {
                        this.voteCommentInput.setAttribute('placeholder', this.defaultVoteCommentPlaceholder);
                    }
                    if (this.voteCommentGroup) {
                        this.voteCommentGroup.classList.add('d-none');
                    }
                });
            }
        },

        setStatus(status) {
            if (this.state.status === status) {
                return;
            }
            this.state.status = status;
            this.updateTabs();
            this.updateSectionTitle();
            this.loadAssemblies();
        },

        updateTabs() {
            this.tabContainer.querySelectorAll('.nav-link').forEach(link => {
                link.classList.toggle('active', link.getAttribute('data-status') === this.state.status);
            });
        },

        updateSectionTitle() {
            const labels = {
                scheduled: 'Assembleias Agendadas',
                in_progress: 'Assembleias em Andamento',
                completed: 'Assembleias Concluídas',
                cancelled: 'Assembleias Canceladas'
            };
            this.sectionTitle.textContent = labels[this.state.status] ?? 'Assembleias';
        },

        async loadAssemblies() {
            this.renderLoading();

            const params = new URLSearchParams({
                status: this.state.status,
                per_page: 20,
                with_items: '1'
            });

            if (this.onlyOpenSwitch.checked) {
                params.append('only_open_for_voting', '1');
            }

            try {
                const response = await fetch(`/api/assemblies?${params.toString()}`, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error('Erro ao carregar assembleias.');
                }

                const data = await response.json();
                this.state.assemblies = data.data ?? [];
                this.renderAssemblies();
            } catch (error) {
                console.error(error);
                this.renderError('Não foi possível carregar as assembleias. Tente novamente em instantes.');
            }
        },

        renderLoading() {
            this.container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="mt-3 mb-0 text-muted">Buscando assembleias...</p>
                </div>
            `;
        },

        buildCloseButton(assembly) {
            const roles = window.ASSEMBLIES_CONTEXT.roles ?? [];
            const userId = window.ASSEMBLIES_CONTEXT.userId;
            const canClose = (assembly.display_status ?? assembly.status) === 'in_progress'
                && (assembly.created_by === userId || roles.some(role => ['Síndico', 'Administrador'].includes(role)));

            if (!canClose) {
                return '';
            }

            return `
                <button class="btn btn-sm btn-outline-danger" data-action="close-assembly" data-id="${assembly.id}">
                    <i class="bi bi-stop-circle"></i> Encerrar votação
            </button>
        `;
        },

        renderError(message) {
            this.container.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-danger mb-0">
                        <strong>Ops!</strong> ${message}
                    </div>
                </div>
            `;
        },

        renderAssemblies() {
            if (!this.state.assemblies.length) {
                this.container.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-clipboard-x fs-1 text-muted"></i>
                        <p class="mt-3 mb-1 fw-semibold">Nenhuma assembleia encontrada.</p>
                        <p class="text-muted mb-0">Ajuste os filtros ou crie uma nova assembleia.</p>
                    </div>
                `;
                return;
            }

            this.container.innerHTML = this.state.assemblies
                .map(assembly => this.buildAssemblyCard(assembly))
                .join('');
        },

        buildAssemblyCard(assembly) {
            const effectiveStatus = assembly.display_status ?? assembly.status;
            const statusBadge = this.getStatusBadge(effectiveStatus);
            const votingType = assembly.voting_type === 'secret'
                ? '<span class="badge bg-warning-subtle text-warning-emphasis">Votação secreta</span>'
                : '<span class="badge bg-info-subtle text-info-emphasis">Votação aberta</span>';

            const votingWindow = this.getVotingWindow(assembly);
            const allowedRoles = (assembly.allowed_roles ?? assembly.allowedRoles ?? [])
                .map(role => role.name ?? role)
                .join(', ') || 'Público padrão';
            const items = (assembly.items ?? []).map(item => this.buildAssemblyItem(item, assembly)).join('');
            const attachments = this.buildAttachments(assembly.attachments ?? []);
            const minutesButton = assembly.status === 'completed' && assembly.minutes
                ? `<button class="btn btn-outline-success btn-sm" data-action="export-minutes" data-id="${assembly.id}">
                        <i class="bi bi-download"></i> Exportar Ata
                   </button>`
                : '';
            const manageButtons = this.buildAssemblyActions(assembly);
            const closeButton = this.buildCloseButton(assembly);

            return `
                <div class="col-12">
                    <div class="card h-100">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-start gap-2">
                            <div>
                                <h5 class="card-title mb-1 d-flex flex-wrap align-items-center gap-2">
                                    <span>${assembly.title}</span>
                                    ${votingType}
                                </h5>
                                <div class="d-flex flex-wrap align-items-center gap-3 text-muted small">
                                    <span><i class="bi bi-calendar-event"></i> ${this.formatDate(assembly.scheduled_at)}</span>
                                    ${votingWindow}
                                    <span><i class="bi bi-person-check"></i> ${allowedRoles}</span>
                                </div>
                            </div>
                            <div class="text-end">
                            <span class="badge ${statusBadge.class}">${statusBadge.label}</span>
                            <div class="text-muted small">${this.getUrgencyLabel(assembly.urgency)}</div>
                            </div>
                        </div>
                        <div class="card-body">
                            ${assembly.description ? `<p class="text-muted">${assembly.description}</p>` : ''}
                            <div class="mb-4">
                                <h6 class="fw-semibold mb-2">Itens da pauta</h6>
                                <div class="d-flex flex-column gap-3">
                                    ${items || '<span class="text-muted">Nenhum item cadastrado.</span>'}
                                </div>
                            </div>
                            ${attachments}
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                Criado por ${assembly.creator?.name ?? 'N/A'} em ${this.formatDate(assembly.created_at)}
                            </div>
                            <div class="d-flex gap-2">
                                ${closeButton}
                                ${manageButtons}
                                ${minutesButton}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        },

        buildAssemblyActions(assembly) {
            const roles = window.ASSEMBLIES_CONTEXT.roles ?? [];
            const userId = window.ASSEMBLIES_CONTEXT.userId;
            const effectiveStatus = assembly.display_status ?? assembly.status;
            const canManage = effectiveStatus === 'scheduled'
                && Number(assembly.votes_count ?? 0) === 0
                && (assembly.created_by === userId || roles.some(role => ['Síndico', 'Administrador'].includes(role)));

            if (!canManage) {
                return '';
            }

            return `
                <div class="btn-group">
                    <button class="btn btn-outline-secondary btn-sm" data-action="edit-assembly" data-id="${assembly.id}">
                        <i class="bi bi-pencil"></i> Editar
                    </button>
                    <button class="btn btn-outline-danger btn-sm" data-action="delete-assembly" data-id="${assembly.id}">
                        <i class="bi bi-trash"></i> Excluir
            </button>
                </div>
            `;
        },

        buildAssemblyItem(item, assembly) {
            const status = this.getItemStatus(item.status);
            const voteSummary = assembly.vote_summary?.[item.id] ?? null;
            const votes = item.votes ?? [];
            const results = voteSummary
                ? Object.entries(voteSummary.totals)
                    .map(([option, count]) => `<span class="badge bg-light text-dark me-2">${this.formatOptionLabel(option)}: ${count}</span>`)
                    .join('')
                : '';

            const effectiveStatus = assembly.display_status ?? assembly.status;
            const allowPartial = assembly.results_visibility === 'real_time'
                && ['scheduled', 'in_progress'].includes(effectiveStatus);
            const allowFinal = effectiveStatus === 'completed';

            const canShowProgress = voteSummary && (allowPartial || allowFinal);
            const breakdown = voteSummary?.breakdown ?? [];
            const thresholdValue = voteSummary?.threshold ?? (voteSummary ? Math.floor((voteSummary.total_votes ?? 0) / 2) + 1 : null);
            const thresholdLabel = thresholdValue
                ? `${thresholdValue} voto${thresholdValue > 1 ? 's' : ''}`
                : '50% + 1 dos votos';

            const progressBars = canShowProgress
                ? `<div class="mt-3">
                        ${breakdown.map(detail => {
                            const label = this.formatOptionLabel(detail.choice);
                            return `
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>${label}</span>
                                    <span class="small text-muted">${detail.count} voto${detail.count === 1 ? '' : 's'} (${detail.percentage}%)</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar ${this.progressBarClass(effectiveStatus, voteSummary.winner, detail)}" style="width: ${detail.percentage}%;"></div>
                                </div>
                            </div>
                        `;
                        }).join('')}
                        <div class="text-muted small">Maioria necessária: ${thresholdLabel}.</div>
                    </div>`
                : `<div class="text-muted small mt-3">Maioria necessária: ${thresholdLabel}.</div>`;

            const canShowWinner = voteSummary?.winner && (allowPartial || allowFinal);
            const winnerBadge = canShowWinner
                ? `<span class="badge text-bg-success">Decisão: ${this.formatOptionLabel(voteSummary.winner.choice)} (${voteSummary.winner.count})</span>`
                : '';

            const userHasVoted = this.userHasVoted(assembly, item);
            const canVote = assembly.is_voting_open
                && this.userCanVote(assembly)
                && !userHasVoted
                && ['open', 'pending'].includes(item.status);

            const voteCta = canVote
                ? `<button class="btn btn-sm btn-primary" data-action="open-vote" data-assembly="${assembly.id}" data-item="${item.id}">
                        <i class="bi bi-hand-thumbs-up"></i> Registrar voto
                   </button>`
                : '';

            const votedBadge = userHasVoted
                ? `<span class="badge text-bg-secondary">Você já votou neste item</span>`
                : '';

            const actionView = voteCta || votedBadge
                ? `<div class="mt-3 d-flex align-items-center gap-2">${voteCta}${votedBadge}</div>`
                : '';

            return `
                <div class="border rounded p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">${item.title}</h6>
                            ${item.description ? `<p class="text-muted small mb-2">${item.description}</p>` : ''}
                            ${results ? `<div class="small mb-2">${results}</div>` : ''}
                            ${winnerBadge}
                        </div>
                        <span class="${status.class}">${status.label}</span>
                    </div>
                    ${progressBars}
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        ${actionView}
                    </div>
                    ${votes.length && assembly.voting_type === 'open' ? this.buildVotesList(item, votes) : ''}
                </div>
            `;
        },

        progressBarClass(status, winner, detail) {
            if (winner && winner.choice === detail.choice) {
                return 'bg-success';
            }
            return status === 'completed' ? 'bg-secondary' : 'bg-primary-subtle';
        },

        userCanVote(assembly) {
            const allowed = (assembly.allowed_roles ?? assembly.allowedRoles ?? [])
                .map(role => (role.name ?? role))
                .filter(Boolean);
            const allowedRoles = allowed.length ? allowed : ['Morador', 'Agregado', 'Síndico'];
            const userRoles = window.ASSEMBLIES_CONTEXT.roles ?? [];
            return allowedRoles.some(role => userRoles.includes(role));
        },

        userHasVoted(assembly, item) {
            const userId = Number(window.ASSEMBLIES_CONTEXT.userId);
            return (item.votes ?? []).some(vote => Number(vote.voter_id ?? vote.voter?.id) === userId);
        },

        getItemOptions(item, assembly, summary) {
            let options = [];
            if (Array.isArray(item.options) && item.options.length) {
                options = item.options;
            } else if (summary && summary.totals) {
                options = Object.keys(summary.totals);
            }

            if (!options.length) {
                options = ['yes', 'no', 'abstain'];
            }

            return options
                .map(option => {
                    const value = (typeof option === 'string' ? option : String(option)).trim();
                    if (!value) {
                        return null;
                    }
                    return {
                        value,
                        label: this.formatOptionLabel(value),
                    };
                })
                .filter(Boolean);
        },

        formatOptionLabel(option) {
            const map = {
                yes: 'Sim',
                no: 'Não',
                abstain: 'Abstenção',
            };
            const key = option ? option.toString().toLowerCase() : '';
            return map[key] ?? option;
        },

        openVoteModal(assemblyId, itemId) {
            const assembly = this.state.assemblies.find(a => Number(a.id) === Number(assemblyId));
            if (!assembly || !assembly.is_voting_open) {
                this.notify('warning', 'Esta assembleia não está aberta para votação.');
                return;
            }

            const item = (assembly.items ?? []).find(i => Number(i.id) === Number(itemId));
            if (!item) {
                return;
            }

            if (this.userHasVoted(assembly, item)) {
                this.notify('info', 'Você já registrou voto para este item.');
                return;
            }

            const voteSummary = assembly.vote_summary?.[item.id];
            const options = this.getItemOptions(item, assembly, voteSummary);
            const threshold = voteSummary?.threshold ?? (voteSummary ? Math.floor((voteSummary.total_votes ?? 0) / 2) + 1 : null);
            const thresholdLabel = threshold
                ? `${threshold} voto${threshold > 1 ? 's' : ''} (50% + 1).`
                : 'Maioria simples (50% + 1 dos votos).';

            if (this.voteModalTitle) {
                this.voteModalTitle.textContent = assembly.title;
            }
            if (this.voteItemTitle) {
                this.voteItemTitle.textContent = item.title;
            }
            if (this.voteModalNote) {
                this.voteModalNote.textContent = assembly.voting_type === 'secret'
                    ? 'Votação secreta: seu voto será contabilizado sem vincular seu nome.'
                    : 'Voto identificado: sua decisão ficará registrada com o seu nome.';
            }
            if (this.voteModalResultsNote) {
                this.voteModalResultsNote.textContent = assembly.results_visibility === 'real_time'
                    ? 'Parciais dos votos ficam visíveis durante a votação.'
                    : 'Os resultados serão exibidos apenas após o encerramento.';
            }
            if (this.voteModalThreshold) {
                this.voteModalThreshold.textContent = `Maioria necessária: ${thresholdLabel}`;
            }

            if (this.voteOptionsContainer) {
                this.voteOptionsContainer.innerHTML = options.map((option, index) => {
                    const inputId = `vote-choice-${assembly.id}-${item.id}-${index}`;
                    return `
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="vote_choice" id="${inputId}" value="${option.value}">
                            <label class="form-check-label" for="${inputId}">${option.label}</label>
                        </div>
                    `;
                }).join('');
            }

            if (this.voteCommentGroup) {
                this.voteCommentGroup.classList.toggle('d-none', !assembly.allow_comments);
            }
            if (this.voteCommentInput) {
                this.voteCommentInput.value = '';
                if (this.defaultVoteCommentPlaceholder) {
                    this.voteCommentInput.setAttribute('placeholder', this.defaultVoteCommentPlaceholder);
                }
            }
            if (this.voteCommentLabel && this.defaultVoteCommentLabel) {
                this.voteCommentLabel.innerHTML = this.defaultVoteCommentLabel;
            }
            if (this.voteSubmitButton && this.defaultVoteSubmitLabel) {
                this.voteSubmitButton.innerHTML = this.defaultVoteSubmitLabel;
            }
            this.showVoteError('');

            this.state.currentVote = {
                assemblyId: Number(assembly.id),
                itemId: Number(item.id),
            };

            if (this.voteModal) {
                this.voteModal.show();
            }
        },

        openCloseModal(assemblyId) {
            const assembly = this.state.assemblies.find(a => Number(a.id) === Number(assemblyId));
            if (!assembly) {
                return;
            }
            this.state.currentVote = { assemblyId: Number(assembly.id), itemId: null, closingReason: true };

            if (this.voteModalTitle) {
                this.voteModalTitle.textContent = assembly.title;
            }
            if (this.voteItemTitle) {
                this.voteItemTitle.textContent = 'Encerrar votação antecipadamente';
            }
            if (this.voteModalNote) {
                this.voteModalNote.textContent = 'Informe o motivo do encerramento antes do horário previsto.';
            }
            if (this.voteModalResultsNote) {
                this.voteModalResultsNote.textContent = '';
            }
            if (this.voteModalThreshold) {
                this.voteModalThreshold.textContent = '';
            }
            if (this.voteOptionsContainer) {
                this.voteOptionsContainer.innerHTML = '';
            }
            if (this.voteCommentGroup) {
                this.voteCommentGroup.classList.remove('d-none');
            }
            if (this.voteCommentInput) {
                this.voteCommentInput.value = '';
                this.voteCommentInput.setAttribute('placeholder', 'Descreva o motivo do encerramento');
            }
            if (this.voteCommentLabel) {
                this.voteCommentLabel.innerHTML = 'Motivo do encerramento (obrigatório)';
            }
            if (this.voteModal) {
                this.voteModal.show();
            }

            this.voteCommentInput?.focus();
        },

        setVoteModalLoading(isLoading) {
            if (!this.voteSubmitButton) {
                return;
            }
            this.voteSubmitButton.disabled = isLoading;
            if (isLoading) {
                this.voteSubmitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Enviando...';
            } else {
                const isClosing = this.state.currentVote && this.state.currentVote.closingReason !== undefined;
                if (isClosing) {
                    this.voteSubmitButton.innerHTML = this.closeSubmitLabel;
                } else {
                    this.voteSubmitButton.innerHTML = this.defaultVoteSubmitLabel;
                }
            }
        },

        showVoteError(message) {
            if (!this.voteModalError) {
                return;
            }

            if (!message) {
                this.voteModalError.classList.add('d-none');
                this.voteModalError.innerHTML = '';
                return;
            }

            this.voteModalError.classList.remove('d-none');
            this.voteModalError.innerHTML = message;
        },

        hideVoteModal() {
            if (this.voteModal) {
                this.voteModal.hide();
            }
            if (this.voteSubmitButton && this.defaultVoteSubmitLabel) {
                this.voteSubmitButton.innerHTML = this.defaultVoteSubmitLabel;
            }
            if (this.voteCommentLabel && this.defaultVoteCommentLabel) {
                this.voteCommentLabel.innerHTML = this.defaultVoteCommentLabel;
            }
            if (this.voteCommentInput && this.defaultVoteCommentPlaceholder) {
                this.voteCommentInput.setAttribute('placeholder', this.defaultVoteCommentPlaceholder);
            }
            if (this.voteCommentGroup) {
                this.voteCommentGroup.classList.add('d-none');
            }
        },

        async submitVote() {
            if (!this.state.currentVote) {
                return;
            }

            const { assemblyId, itemId, closingReason } = this.state.currentVote;

            if (closingReason !== undefined) {
                const reason = this.voteCommentInput?.value?.trim() ?? '';
                return this.submitCloseAssembly(assemblyId, reason);
            }

            const assembly = this.state.assemblies.find(a => Number(a.id) === Number(assemblyId));
            if (!assembly) {
                return;
            }
            const item = (assembly.items ?? []).find(i => Number(i.id) === Number(itemId));
            if (!item) {
                return;
            }

            const selected = this.voteOptionsContainer.querySelector('input[name="vote_choice"]:checked');
            if (!selected) {
                this.showVoteError('Selecione uma opção de voto.');
                return;
            }

            const payload = {
                choice: selected.value,
            };

            if (assembly.allow_comments) {
                const comment = this.voteCommentInput.value.trim();
                if (comment.length > 0) {
                    payload.comment = comment;
                }
            }

            this.showVoteError('');
            this.setVoteModalLoading(true);

            try {
                const response = await fetch(`/api/assemblies/${assemblyId}/items/${itemId}/vote`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.ASSEMBLIES_CONTEXT.csrf,
                    },
                    body: JSON.stringify(payload),
                    credentials: 'same-origin',
                });

                let data = {};
                try {
                    data = await response.json();
                } catch (error) {
                    data = {};
                }

                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        const message = Object.values(data.errors).flat().join('<br>');
                        this.showVoteError(message);
                        return;
                    }
                    throw new Error(data.error ?? 'Não foi possível registrar o voto.');
                }

                this.notify('success', data.message ?? 'Voto registrado com sucesso.');
                this.hideVoteModal();
                this.state.currentVote = null;
                await this.loadAssemblies();
            } catch (error) {
                console.error(error);
                this.showVoteError(error.message ?? 'Erro inesperado ao registrar o voto.');
            } finally {
                this.setVoteModalLoading(false);
            }
        },

        async submitCloseAssembly(assemblyId, reason) {
            const trimmedReason = (reason ?? '').trim();
            if (!trimmedReason) {
                this.showVoteError('Informe o motivo do encerramento.');
                this.voteCommentInput?.focus();
                return;
            }

            this.showVoteError('');
            this.setVoteModalLoading(true);

            try {
                const response = await fetch(`/api/assemblies/${assemblyId}/complete`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.ASSEMBLIES_CONTEXT.csrf,
                    },
                    body: JSON.stringify({ reason: trimmedReason }),
                    credentials: 'same-origin',
                });

                const data = await response.json();

                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        const message = Object.values(data.errors).flat().join('<br>');
                        this.showVoteError(message);
                        return;
                    }
                    throw new Error(data.error ?? 'Não foi possível encerrar a votação.');
                }

                this.notify('success', data.message ?? 'Votação encerrada com sucesso.');
                this.hideVoteModal();
                this.state.currentVote = null;
                await this.loadAssemblies();
            } catch (error) {
                console.error(error);
                this.showVoteError(error.message ?? 'Erro inesperado ao encerrar a votação.');
            } finally {
                this.setVoteModalLoading(false);
            }
        },

        buildVotesList(item, votes) {
            const items = votes.map(vote => {
                let voterName = 'Votante';
                let unitLabel = '';
                if (vote.voter) {
                    voterName = vote.voter.name ?? voterName;
                }
                const unit = vote.unit?.full_identifier ?? vote.unit?.number ?? null;
                if (unit) {
                    unitLabel = unit;
                }
                const comment = vote.comment ? `<div class="text-muted small">Comentário: ${vote.comment}</div>` : '';
                const choiceLabel = this.formatOptionLabel(vote.choice);
                return `
                    <li class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>
                                <strong>${voterName}</strong>
                                ${unitLabel ? `<small class="text-muted"> • Unidade ${unitLabel}</small>` : ''}
                            </span>
                            <span class="badge bg-primary-subtle text-primary-emphasis text-uppercase">${choiceLabel}</span>
                        </div>
                        ${comment}
                    </li>
                `;
            }).join('');

            return `
                <div class="accordion mt-3" id="accordion-votes-${item.id}">
                    <div class="accordion-item">
                        <h6 class="accordion-header small text-uppercase text-muted" id="heading-votes-${item.id}">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-votes-${item.id}" aria-expanded="false" aria-controls="collapse-votes-${item.id}">
                                Votos Registrados (${votes.length})
                            </button>
                        </h6>
                        <div id="collapse-votes-${item.id}" class="accordion-collapse collapse" aria-labelledby="heading-votes-${item.id}" data-bs-parent="#accordion-votes-${item.id}">
                            <div class="accordion-body p-0">
                                <ul class="list-group list-group-flush">
                                    ${items}
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        },

        buildAttachments(attachments) {
            if (!attachments.length) {
                return '';
            }

            const items = attachments.map(file => `
                <a href="${file.url}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-paperclip"></i> ${file.original_name}
                </a>
            `).join(' ');

            return `
                <div>
                    <h6 class="fw-semibold mb-2">Anexos</h6>
                    <div class="d-flex flex-wrap gap-2">
                        ${items}
                    </div>
                </div>
            `;
        },

        getStatusBadge(status) {
            const map = {
                scheduled: { label: 'Agendada', class: 'text-bg-primary' },
                in_progress: { label: 'Em andamento', class: 'text-bg-warning' },
                completed: { label: 'Concluída', class: 'text-bg-success' },
                cancelled: { label: 'Cancelada', class: 'text-bg-danger' }
            };
            return map[status] ?? map.scheduled;
        },

        getItemStatus(status) {
            const map = {
                pending: { label: 'Pendente', class: 'badge text-bg-secondary' },
                open: { label: 'Aberto', class: 'badge text-bg-primary' },
                closed: { label: 'Encerrado', class: 'badge text-bg-success' },
                cancelled: { label: 'Cancelado', class: 'badge text-bg-danger' }
            };
            return map[status] ?? map.pending;
        },

        getUrgencyLabel(urgency) {
            const map = {
                low: 'Urgência baixa',
                normal: 'Urgência normal',
                high: 'Urgência alta',
                critical: 'Urgência crítica'
            };
            return map[urgency] ?? map.normal;
        },

        getVotingWindow(assembly) {
            if (!assembly.voting_opens_at && !assembly.voting_closes_at) {
                return '';
            }

            const parts = [];
            if (assembly.voting_opens_at) {
                parts.push(`<i class="bi bi-unlock"></i> Início votação: ${this.formatDate(assembly.voting_opens_at)}`);
            }
            if (assembly.voting_closes_at) {
                parts.push(`<i class="bi bi-lock"></i> Fim votação: ${this.formatDate(assembly.voting_closes_at)}`);
            }
            return `<span>${parts.join(' • ')}</span>`;
        },

        async submitAssembly() {
            const formData = new FormData();
            const elements = this.form.elements;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            formData.append('title', elements['title'].value.trim());
            formData.append('urgency', elements['urgency'].value);
            formData.append('description', elements['description'].value.trim());
            formData.append('scheduled_at', elements['scheduled_at'].value);
            if (elements['voting_opens_at'].value) {
                formData.append('voting_opens_at', elements['voting_opens_at'].value);
            }
            if (elements['voting_closes_at'].value) {
                formData.append('voting_closes_at', elements['voting_closes_at'].value);
            }
            formData.append('duration_minutes', elements['duration_minutes'].value);
            formData.append('voting_type', elements['voting_type'].value);

            if (elements['allow_delegation'].checked) {
                formData.append('allow_delegation', '1');
            }
            if (elements['allow_comments'].checked) {
                formData.append('allow_comments', '1');
            }

            const resultsVisibility = this.form.querySelector('input[name="results_visibility"]:checked')?.value ?? 'final_only';
            formData.append('results_visibility', resultsVisibility);

            this.rolesContainer.querySelectorAll('.form-check-input:checked').forEach(checkbox => {
                formData.append('allowed_roles[]', checkbox.value);
            });

            this.itemsContainer.querySelectorAll('[data-item-row]').forEach((row, index) => {
                const title = row.querySelector('[data-item-title]').value.trim();
                const description = row.querySelector('[data-item-description]').value.trim();
                const optionsValue = row.querySelector('[data-item-options]').value.trim();
                const opensAt = row.querySelector('[data-item-opens-at]').value;
                const closesAt = row.querySelector('[data-item-closes-at]').value;

                formData.append(`items[${index}][title]`, title);
                if (description) formData.append(`items[${index}][description]`, description);
                if (opensAt) formData.append(`items[${index}][opens_at]`, opensAt);
                if (closesAt) formData.append(`items[${index}][closes_at]`, closesAt);

                const options = optionsValue
                    ? optionsValue.split('\n').map(option => option.trim()).filter(Boolean)
                    : [];
                options.forEach(option => formData.append(`items[${index}][options][]`, option));
                const existingId = row.getAttribute('data-item-id');
                if (existingId) {
                    formData.append(`items[${index}][id]`, existingId);
                }
            });

            const attachmentsInput = elements['attachments[]'];
            if (attachmentsInput && attachmentsInput.files.length) {
                Array.from(attachmentsInput.files).forEach(file => formData.append('attachments[]', file));
            }

            try {
                let url = '/api/assemblies';
                let method = 'POST';
                if (this.state.editingAssemblyId) {
                    url = `/api/assemblies/${this.state.editingAssemblyId}`;
                    formData.append('_method', 'PUT');
                }

                const response = await fetch(url, {
                    method,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData,
                    credentials: 'same-origin'
                });

                const payload = await response.json();

                if (!response.ok) {
                    if (response.status === 422) {
                        this.presentValidationErrors(payload.errors ?? {});
                        return;
                    }
                    const defaultError = this.state.editingAssemblyId ? 'Erro ao salvar assembleia.' : 'Erro ao criar assembleia.';
                    throw new Error(payload.error ?? defaultError);
                }

                const successMessage = payload.message ?? (this.state.editingAssemblyId ? 'Assembleia atualizada com sucesso.' : 'Assembleia criada com sucesso.');
                this.notify('success', successMessage);
                this.hideModal();
                this.resetForm();
                await this.loadAssemblies();
            } catch (error) {
                console.error(error);
                const defaultMsg = this.state.editingAssemblyId ? 'Erro inesperado ao salvar assembleia.' : 'Erro inesperado ao criar assembleia.';
                this.notify('danger', error.message ?? defaultMsg);
            }
        },

        async startEditAssembly(assemblyId) {
            try {
                const response = await fetch(`/api/assemblies/${assemblyId}`, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error('Não foi possível carregar a assembleia.');
                }

                const assembly = await response.json();
                if (Number(assembly.votes_count ?? 0) > 0) {
                    this.notify('warning', 'Assembleias com votos registrados não podem ser editadas.');
                    return;
                }

                this.populateFormForEdit(assembly);
                this.state.editingAssemblyId = assembly.id;
                this.submitButton.innerHTML = '<i class="bi bi-save"></i> Salvar alterações';
                this.modalTitle.textContent = 'Editar Assembleia';
                this.showModal();
            } catch (error) {
                console.error(error);
                this.notify('danger', error.message ?? 'Erro ao carregar assembleia para edição.');
            }
        },

        populateFormForEdit(assembly) {
            const elements = this.form.elements;
            elements['title'].value = assembly.title ?? '';
            elements['urgency'].value = assembly.urgency ?? 'normal';
            elements['description'].value = assembly.description ?? '';
            elements['scheduled_at'].value = this.toInputDateTime(assembly.scheduled_at);
            elements['voting_opens_at'].value = this.toInputDateTime(assembly.voting_opens_at);
            elements['voting_closes_at'].value = this.toInputDateTime(assembly.voting_closes_at);
            elements['duration_minutes'].value = assembly.duration_minutes ?? 120;

            const votingTypeOpen = this.form.querySelector('#votingTypeOpen');
            const votingTypeSecret = this.form.querySelector('#votingTypeSecret');
            if (assembly.voting_type === 'secret') {
                votingTypeSecret.checked = true;
            } else {
                votingTypeOpen.checked = true;
            }

            this.rolesContainer.querySelectorAll('.form-check-input').forEach(checkbox => {
                checkbox.checked = (assembly.allowed_roles ?? assembly.allowedRoles ?? [])
                    .some(role => (role.name ?? role) === checkbox.value);
            });

            this.form.querySelector('#allowDelegation').checked = Boolean(assembly.allow_delegation);
            this.form.querySelector('#allowComments').checked = Boolean(assembly.allow_comments);

            const resultsVisibility = assembly.results_visibility ?? 'final_only';
            const visibilityRadio = this.form.querySelector(`input[name="results_visibility"][value="${resultsVisibility}"]`);
            if (visibilityRadio) {
                visibilityRadio.checked = true;
            }

            this.itemsContainer.innerHTML = '';
            (assembly.items ?? []).forEach(item => this.addAgendaItem(item));
        },

        toInputDateTime(value) {
            if (!value) {
                return '';
            }
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return '';
            }
            const pad = num => String(num).padStart(2, '0');
            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
        },

        async deleteAssembly(assemblyId) {
            if (!confirm('Tem certeza de que deseja excluir esta assembleia? Esta ação não pode ser desfeita.')) {
                return;
            }

            try {
                const response = await fetch(`/api/assemblies/${assemblyId}`, {
                    method: 'DELETE',
            headers: {
                        'X-CSRF-TOKEN': window.ASSEMBLIES_CONTEXT.csrf,
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });

                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.error ?? 'Erro ao excluir assembleia.');
                }

                this.notify('success', payload.message ?? 'Assembleia excluída com sucesso.');
                await this.loadAssemblies();
            } catch (error) {
                console.error(error);
                this.notify('danger', error.message ?? 'Erro inesperado ao excluir assembleia.');
            }
        },

        async exportMinutes(assemblyId) {
            try {
                const response = await fetch(`/api/assemblies/${assemblyId}/minutes/export`, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.error ?? 'Ata ainda não disponível.');
                }

                if (payload.pdf_url) {
                    const link = document.createElement('a');
                    link.href = payload.pdf_url;
                    link.setAttribute('download', `ata-assembleia-${assemblyId}.pdf`);
                    link.setAttribute('target', '_blank');
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    return;
                }

                if (payload.minutes) {
                    const blob = new Blob([payload.minutes], { type: 'text/markdown;charset=utf-8' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = `ata-assembleia-${assemblyId}.md`;
                    link.click();
                    URL.revokeObjectURL(url);
                    return;
                }

                throw new Error('Não foi possível localizar o arquivo da ata.');
            } catch (error) {
                console.error(error);
                this.notify('danger', error.message ?? 'Erro ao exportar ata.');
            }
        },

        presentValidationErrors(errors) {
            const messages = Object.values(errors)
                .flat()
                .map(message => `<li>${message}</li>`)
                .join('');

            this.notify('danger', `<ul class="mb-0">${messages}</ul>`);
        },

        notify(type, message) {
            const wrapper = document.createElement('div');
            wrapper.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 mt-4 me-4 shadow" role="alert" style="z-index: 1080; max-width: 320px;">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            `;
            document.body.appendChild(wrapper);
            setTimeout(() => wrapper.remove(), 6000);
        },

        showModal() {
            if (window.bootstrap) {
                const instance = bootstrap.Modal.getInstance(this.modalEl) ?? new bootstrap.Modal(this.modalEl);
                instance.show();
            }
        },

        hideModal() {
            if (window.bootstrap) {
                const instance = bootstrap.Modal.getInstance(this.modalEl) ?? new bootstrap.Modal(this.modalEl);
                instance.hide();
            }
        },

        resetForm() {
            this.form.reset();
            this.state.editingAssemblyId = null;
            this.submitButton.innerHTML = '<i class="bi bi-rocket-takeoff"></i> Criar Assembleia';
            this.modalTitle.textContent = 'Nova Assembleia';

            const visibilityDefault = this.form.querySelector('#resultsVisibilityFinal');
            if (visibilityDefault) {
                visibilityDefault.checked = true;
            }

            this.rolesContainer.querySelectorAll('.form-check-input').forEach(checkbox => {
                checkbox.checked = ['Morador', 'Síndico'].includes(checkbox.value);
            });
            this.resetAgendaItems();
        },

        resetAgendaItems() {
            this.itemsContainer.innerHTML = '';
            this.addAgendaItem();
        },

        addAgendaItem(item = null) {
            const wrapper = document.createElement('div');
            wrapper.className = 'border rounded p-3';
            wrapper.setAttribute('data-item-row', Date.now());
            wrapper.innerHTML = `
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <div>
                        <label class="form-label mb-0">Título do item *</label>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-remove-item>
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <input type="text" class="form-control mb-2" data-item-title placeholder="Ex: Aprovação da reforma da fachada" required>
                <textarea class="form-control mb-2" rows="2" data-item-description placeholder="Detalhes e contexto do item"></textarea>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label small mb-1">Opções de voto (uma por linha)</label>
                        <textarea class="form-control" rows="3" data-item-options placeholder="Sim&#10;Não&#10;Abstenção"></textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Abertura</label>
                        <input type="datetime-local" class="form-control" data-item-opens-at>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Encerramento</label>
                        <input type="datetime-local" class="form-control" data-item-closes-at>
                    </div>
                </div>
            `;

            wrapper.querySelector('[data-remove-item]').addEventListener('click', () => {
                wrapper.remove();
                if (!this.itemsContainer.querySelector('[data-item-row]')) {
                    this.addAgendaItem();
                }
            });

            if (item) {
                wrapper.querySelector('[data-item-title]').value = item.title ?? '';
                if (item.description) {
                    wrapper.querySelector('[data-item-description]').value = item.description;
                }
                if (item.options?.length) {
                    wrapper.querySelector('[data-item-options]').value = item.options.join('\n');
                }
                if (item.opens_at) {
                    wrapper.querySelector('[data-item-opens-at]').value = this.toInputDateTime(item.opens_at);
                }
                if (item.closes_at) {
                    wrapper.querySelector('[data-item-closes-at]').value = this.toInputDateTime(item.closes_at);
                }
                wrapper.setAttribute('data-item-id', item.id);
            }

            this.itemsContainer.appendChild(wrapper);
        },

        formatDate(value) {
            if (!value) {
                return 'N/A';
            }
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return value;
            }
            return date.toLocaleString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    };
</script>
@endpush
