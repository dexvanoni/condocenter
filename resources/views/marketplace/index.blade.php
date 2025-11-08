@extends('layouts.app')

@section('title', 'Marketplace')

@php
    $categories = [
        'products' => 'Produtos',
        'services' => 'Serviços',
        'jobs' => 'Empregos',
        'real_estate' => 'Imóveis',
        'vehicles' => 'Veículos',
        'other' => 'Outros',
    ];

    $conditions = [
        'new' => 'Novo',
        'used' => 'Usado',
        'refurbished' => 'Recondicionado',
        'not_applicable' => 'Não se aplica',
    ];

    $statuses = [
        'active' => 'Disponível',
        'sold' => 'Vendido',
        'inactive' => 'Inativo',
    ];

    /** @var \App\Models\User $currentUser */
    $currentUser = Auth::user();
    $isAdminSindico = $currentUser?->hasAnyRole(['Administrador', 'Síndico']) ?? false;
@endphp

@section('content')
<div class="container-fluid px-4 marketplace-page">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
            <h2 class="mb-1">Marketplace</h2>
            <p class="text-muted mb-0">Produtos, serviços e oportunidades compartilhadas pelos moradores do condomínio.</p>
            </div>
            @if(\App\Helpers\SidebarHelper::canCrudModule(Auth::user(), 'marketplace'))
        <button class="btn btn-primary btn-lg shadow-sm" data-bs-toggle="modal" data-bs-target="#novoAnuncioModal">
            <i class="bi bi-plus-circle me-2"></i> Novo Anúncio
            </button>
            @endif
        </div>

    <div id="marketplaceFeedback"></div>

    <div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-lg-3 col-md-4">
                    <label class="form-label text-muted fw-semibold text-uppercase small">Categoria</label>
                <select class="form-select" id="filterCategory">
                        <option value="">Todas as categorias</option>
                        @foreach ($categories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                </select>
            </div>
                <div class="col-lg-6 col-md-5">
                    <label class="form-label text-muted fw-semibold text-uppercase small">Buscar</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Busque por título ou descrição">
            </div>
                <div class="col-lg-3 col-md-3 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1" id="marketplaceSearchButton">
                        <i class="bi bi-search me-1"></i> Buscar
                    </button>
                    <button class="btn btn-light" id="marketplaceClearButton" title="Limpar filtros">
                        <i class="bi bi-x-circle"></i>
                </button>
            </div>
        </div>
    </div>
</div>

    <div id="marketplaceLoader" class="text-center py-5 d-none">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="text-muted mt-3 mb-0">Carregando anúncios...</p>
    </div>

    <div id="marketplaceEmptyState" class="card border-0 shadow-sm text-center py-5 d-none">
            <div class="card-body">
            <i class="bi bi-shop fs-1 text-primary mb-3"></i>
            <h5 class="fw-semibold">Nenhum anúncio encontrado</h5>
            <p class="text-muted mb-0">Tente ajustar os filtros ou volte mais tarde para conferir novos anúncios.</p>
        </div>
    </div>

    <div class="row g-4 d-none" id="marketplaceGrid"
         data-storage-base="{{ asset('storage') }}"
         data-current-user-id="{{ $currentUser?->id }}"
         data-is-admin-sindico="{{ $isAdminSindico ? '1' : '0' }}"></div>

    <div class="d-flex flex-column flex-lg-row justify-content-lg-between align-items-lg-center gap-3 mt-4">
        <small class="text-muted" id="marketplaceSummary"></small>
        <nav id="marketplacePagination" class="d-none"></nav>
    </div>
</div>

<!-- Modal Novo Anúncio -->
<div class="modal fade" id="novoAnuncioModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo Anúncio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formNovoAnuncio" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Título *</label>
                        <input type="text" class="form-control" name="title" required placeholder="Ex: Bicicleta Mountain Bike">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição *</label>
                        <textarea class="form-control" name="description" rows="4" required placeholder="Descreva seu produto ou serviço..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Preço (R$) *</label>
                                <input type="number" class="form-control" name="price" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Categoria *</label>
                                <select class="form-select" name="category" required>
                                    <option value="">Selecione...</option>
                                    @foreach ($categories as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Condição *</label>
                                <select class="form-select" name="condition" required>
                                    @foreach ($conditions as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">WhatsApp do anunciante *</label>
                        <div class="input-group">
                            <span class="input-group-text">+55</span>
                            <input type="tel"
                                   class="form-control"
                                   name="whatsapp"
                                   id="marketplaceWhatsappInput"
                                   placeholder="11987654321"
                                   pattern="^\d{10,11}$"
                                   required>
                        </div>
                        <small class="text-muted">Informe apenas números, com DDD (ex.: 11987654321).</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Imagens (até 3)</label>
                        <input type="file" class="form-control" name="images[]" multiple accept="image/*" id="marketplaceImagesInput">
                        <div id="imagePreview" class="mt-2"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="submitNovoAnuncio">
                    <i class="bi bi-check-circle"></i> Publicar Anúncio
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalhes do Anúncio -->
<div class="modal fade" id="marketplaceItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-eye me-2 text-primary"></i>
                    Detalhes do Anúncio
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div id="marketplaceItemLoader" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-3 mb-0">Carregando informações...</p>
                </div>
                <div id="marketplaceItemDetails" class="d-none">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4">
                        <div>
                            <h4 id="modalItemTitle" class="mb-2"></h4>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-primary bg-opacity-10 text-primary" id="modalItemCategory"></span>
                                <span class="badge bg-info bg-opacity-10 text-info" id="modalItemCondition"></span>
                                <span class="badge bg-success bg-opacity-10 text-success" id="modalItemStatus"></span>
                            </div>
                        </div>
                        <div class="text-lg-end">
                            <div class="fs-3 fw-bold text-primary" id="modalItemPrice"></div>
                            <small class="text-muted d-block" id="modalItemCreatedAt"></small>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body">
                                    <h6 class="text-muted text-uppercase small fw-semibold mb-2">Vendedor</h6>
                                    <p class="mb-1 fw-semibold" id="modalItemSellerName">-</p>
                                    <p class="mb-1 text-muted" id="modalItemSellerContacts">-</p>
                                    <small class="text-muted" id="modalItemUnit">-</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body">
                                    <h6 class="text-muted text-uppercase small fw-semibold mb-2">Informações</h6>
                                    <p class="mb-1 text-muted" id="modalItemUpdatedAt"></p>
                                    <p class="mb-0 text-muted" id="modalItemViews"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted text-uppercase small fw-semibold mb-2">Descrição</h6>
                        <p class="text-muted" id="modalItemDescription"></p>
                    </div>

                    <div>
                        <h6 class="text-muted text-uppercase small fw-semibold mb-2">Imagens</h6>
                        <div id="modalItemImages" class="d-flex flex-wrap gap-2" data-storage-base="{{ asset('storage') }}">
                            <span class="text-muted">Nenhuma imagem enviada.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const grid = document.getElementById('marketplaceGrid');
        const loader = document.getElementById('marketplaceLoader');
        const emptyState = document.getElementById('marketplaceEmptyState');
        const feedbackContainer = document.getElementById('marketplaceFeedback');
        const pagination = document.getElementById('marketplacePagination');
        const summary = document.getElementById('marketplaceSummary');
        const categorySelect = document.getElementById('filterCategory');
        const searchInput = document.getElementById('searchInput');
        const searchButton = document.getElementById('marketplaceSearchButton');
        const clearButton = document.getElementById('marketplaceClearButton');
        const imagesInput = document.getElementById('marketplaceImagesInput');
        const submitButton = document.getElementById('submitNovoAnuncio');
        const formNovoAnuncio = document.getElementById('formNovoAnuncio');
        const whatsappInput = document.getElementById('marketplaceWhatsappInput');
        const imagePreview = document.getElementById('imagePreview');

        const createModalElement = document.getElementById('novoAnuncioModal');
        const createModal = createModalElement ? new bootstrap.Modal(createModalElement) : null;
        const itemModalElement = document.getElementById('marketplaceItemModal');
        const itemModal = itemModalElement ? new bootstrap.Modal(itemModalElement) : null;
        const itemLoader = document.getElementById('marketplaceItemLoader');
        const itemDetails = document.getElementById('marketplaceItemDetails');
        const modalTitle = createModalElement ? createModalElement.querySelector('.modal-title') : null;

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('acao') === 'novo' && createModal) {
            createModal.show();
            urlParams.delete('acao');
            const newQuery = urlParams.toString();
            const newUrl = `${window.location.pathname}${newQuery ? `?${newQuery}` : ''}${window.location.hash}`;
            window.history.replaceState({}, document.title, newUrl);
        }

        const currentUserId = Number(grid?.dataset?.currentUserId || 0);
        const isAdminSindico = grid?.dataset?.isAdminSindico === '1';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        const modalFields = {
            title: document.getElementById('modalItemTitle'),
            category: document.getElementById('modalItemCategory'),
            condition: document.getElementById('modalItemCondition'),
            status: document.getElementById('modalItemStatus'),
            price: document.getElementById('modalItemPrice'),
            createdAt: document.getElementById('modalItemCreatedAt'),
            sellerName: document.getElementById('modalItemSellerName'),
            sellerContacts: document.getElementById('modalItemSellerContacts'),
            unit: document.getElementById('modalItemUnit'),
            updatedAt: document.getElementById('modalItemUpdatedAt'),
            views: document.getElementById('modalItemViews'),
            description: document.getElementById('modalItemDescription'),
            images: document.getElementById('modalItemImages'),
        };

        const itemsCache = new Map();
        const editState = { isEditing: false, itemId: null };
        const submitButtonDefaultHtml = submitButton ? submitButton.innerHTML : '';
        const modalDefaultTitle = modalTitle ? modalTitle.textContent.trim() : 'Novo Anúncio';

        const config = {
            categories: @json($categories),
            conditions: @json($conditions),
            statuses: @json($statuses),
            statusStyles: {
                active: 'badge bg-success bg-opacity-10 text-success position-absolute top-0 end-0 m-3',
                sold: 'badge bg-warning bg-opacity-10 text-warning position-absolute top-0 end-0 m-3',
                inactive: 'badge bg-secondary bg-opacity-10 text-secondary position-absolute top-0 end-0 m-3',
                default: 'badge bg-secondary bg-opacity-10 text-secondary position-absolute top-0 end-0 m-3',
            },
            placeholderImage: 'https://via.placeholder.com/640x420?text=Marketplace',
        };

        const state = {
            category: '',
            search: '',
            pageUrl: null,
        };

        const currencyFormatter = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
        const whatsappMessage = 'Olá, vi seu anúncio no sistema do condomínio. Ainda está disponível?';
        const whatsappMessageEncoded = encodeURIComponent(whatsappMessage);
        const dateTimeFormatter = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' });

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function truncate(text, limit = 110) {
            const value = String(text ?? '').trim();
            if (value.length <= limit) {
                return value;
            }
            return `${value.substring(0, limit - 1)}…`;
        }

        function formatDateTime(value) {
            if (!value) {
                return '';
            }

            try {
                return dateTimeFormatter.format(new Date(value));
            } catch (error) {
                return value;
            }
        }

        function formatWhatsapp(value) {
            const digits = String(value || '').replace(/\D/g, '');
            if (digits.length === 10) {
                return digits.replace(/(\d{2})(\d{4})(\d{4})/, '$1 $2-$3');
            }
            if (digits.length === 11) {
                return digits.replace(/(\d{2})(\d{5})(\d{4})/, '$1 $2-$3');
            }
            return '';
        }

        function normalizeImageUrl(path) {
            if (!path) {
                return config.placeholderImage;
            }

            const storageBase = grid?.dataset?.storageBase || '';
            if (!storageBase) {
                return config.placeholderImage;
            }

            const normalizedBase = storageBase.replace(/\/$/, '');
            const normalizedPath = String(path).replace(/^\/+/, '');
            return `${normalizedBase}/${normalizedPath}`;
        }

        function showLoader() {
            loader.classList.remove('d-none');
            grid.classList.add('d-none');
            emptyState.classList.add('d-none');
        }

        function hideLoader() {
            loader.classList.add('d-none');
        }

        function clearFeedback() {
            if (feedbackContainer) {
                feedbackContainer.innerHTML = '';
            }
        }

        function showFeedback(type, message) {
            if (!feedbackContainer) {
                return;
            }

            const icons = {
                success: 'bi bi-check-circle',
                danger: 'bi bi-exclamation-triangle',
                warning: 'bi bi-exclamation-circle',
                info: 'bi bi-info-circle',
            };

            const iconClass = icons[type] || icons.info;

            feedbackContainer.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="${iconClass} me-2"></i>${escapeHtml(message)}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            `;
        }

        function renderSummary(data) {
            if (!summary) {
                return;
            }

            if (!data || typeof data.total !== 'number' || data.total === 0) {
                summary.textContent = '';
                return;
            }

            const from = Number.isFinite(data.from) ? data.from : 0;
            const to = Number.isFinite(data.to) ? data.to : (Array.isArray(data.data) ? data.data.length : 0);
            summary.textContent = `Mostrando ${from}-${to} de ${data.total} anúncio${data.total > 1 ? 's' : ''}.`;
        }

        function renderPagination(links = []) {
            if (!pagination) {
                return;
            }

            pagination.innerHTML = '';

            if (!Array.isArray(links) || links.length <= 3) {
                pagination.classList.add('d-none');
                return;
            }

            const ul = document.createElement('ul');
            ul.className = 'pagination mb-0 justify-content-end';

            links.forEach(link => {
                const li = document.createElement('li');
                li.className = 'page-item';

                if (link.active) {
                    li.classList.add('active');
                }

                if (!link.url) {
                    li.classList.add('disabled');
                }

                const a = document.createElement('a');
                a.className = 'page-link';
                a.innerHTML = link.label;
                a.href = '#';

                if (link.url) {
                    a.dataset.pageUrl = link.url;
                }

                li.appendChild(a);
                ul.appendChild(li);
            });

            pagination.innerHTML = '';
            pagination.appendChild(ul);
            pagination.classList.remove('d-none');
        }

        function renderItems(items = []) {
            if (!grid) {
                return;
            }

            grid.innerHTML = '';
            itemsCache.clear();

            if (!Array.isArray(items) || items.length === 0) {
                grid.classList.add('d-none');
                emptyState.classList.remove('d-none');
                return;
            }

            emptyState.classList.add('d-none');
            grid.classList.remove('d-none');

            items.forEach(item => {
                const col = document.createElement('div');
                col.className = 'col-xl-3 col-lg-4 col-md-6';

                const categoryLabel = config.categories[item.category] || 'Outros';
                const conditionLabel = config.conditions[item.condition] || 'Não informado';
                const statusLabel = config.statuses[item.status] || config.statuses.active;
                const statusClass = config.statusStyles[item.status] || config.statusStyles.default;
                const priceLabel = typeof item.price === 'number' || !Number.isNaN(Number(item.price))
                    ? currencyFormatter.format(Number(item.price))
                    : 'Valor sob consulta';
                const sellerName = item?.seller?.name || 'Morador';
                const unitBlock = item?.unit?.block ? `Bloco ${item.unit.block}` : null;
                const unitNumber = item?.unit?.number ? `Unidade ${item.unit.number}` : null;
                const unitLabel = [unitBlock, unitNumber].filter(Boolean).join(' • ') || 'Unidade não informada';
                const viewsCount = Number.isInteger(item.views) ? item.views : 0;
                const viewsLabel = `${viewsCount} ${viewsCount === 1 ? 'visualização' : 'visualizações'}`;
                const imagePath = Array.isArray(item.images) && item.images.length > 0 ? item.images[0] : null;
                const imageUrl = normalizeImageUrl(imagePath);
                const whatsappDigits = String(item.whatsapp || '').replace(/\D/g, '');
                const hasWhatsapp = /^\d{10,11}$/.test(whatsappDigits);
                const whatsappFormatted = hasWhatsapp ? formatWhatsapp(whatsappDigits) : '';
                const sellerId = Number(item.seller_id ?? item?.seller?.id ?? 0);
                const isOwner = sellerId === currentUserId;
                const canManageItem = isOwner || isAdminSindico;
                const manageButtons = canManageItem ? `
                                <button class="btn btn-outline-warning btn-sm" data-action="edit-item" data-item-id="${item.id}">
                                    <i class="bi bi-pencil me-1"></i> Editar
                                </button>
                                <button class="btn btn-outline-danger btn-sm" data-action="delete-item" data-item-id="${item.id}">
                                    <i class="bi bi-trash me-1"></i> Excluir
                                </button>
                ` : '';

                col.innerHTML = `
                    <div class="card h-100 shadow-sm border-0">
                        <div class="position-relative">
                            <img src="${imageUrl}" class="card-img-top" alt="${escapeHtml(item.title || 'Anúncio')}">
                            <span class="badge bg-primary bg-opacity-10 text-primary position-absolute top-0 start-0 m-3">
                                ${escapeHtml(categoryLabel)}
                            </span>
                            <span class="${statusClass}">
                                ${escapeHtml(statusLabel)}
                            </span>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-2">${escapeHtml(item.title)}</h5>
                            <p class="card-text text-muted flex-grow-1">${escapeHtml(truncate(item.description))}</p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <div class="fw-bold text-primary">${escapeHtml(priceLabel)}</div>
                                    <small class="text-muted">${escapeHtml(sellerName)}</small>
                                    ${hasWhatsapp ? `<small class="text-muted d-block">WhatsApp: +55 ${escapeHtml(whatsappFormatted)}</small>` : ''}
                                </div>
                                <div class="text-end">
                                    <small class="text-muted d-block">${escapeHtml(unitLabel)}</small>
                                    <small class="text-muted">${escapeHtml(viewsLabel)}</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 pt-0">
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary btn-sm" data-action="view-details" data-item-id="${item.id}">
                                    <i class="bi bi-eye me-1"></i> Ver Detalhes
                                </button>
                                <button class="btn btn-outline-success btn-sm" data-action="contact-seller" data-item-id="${item.id}" data-whatsapp="${whatsappDigits}" ${hasWhatsapp ? '' : 'disabled'}>
                                    <i class="bi bi-chat-dots me-1"></i> Contatar Vendedor
                                </button>
                                ${manageButtons}
                            </div>
                        </div>
                    </div>
                `;

                grid.appendChild(col);
                itemsCache.set(String(item.id), {
                    ...item,
                    whatsapp_digits: whatsappDigits,
                    whatsapp_formatted: whatsappFormatted,
                    can_manage: canManageItem,
                });
            });
        }

        function buildUrl(pageUrl = null) {
            const baseUrl = pageUrl || state.pageUrl || '/api/marketplace';
            const url = new URL(baseUrl, window.location.origin);

            url.searchParams.set('status', 'active');

            if (state.category) {
                url.searchParams.set('category', state.category);
            } else {
                url.searchParams.delete('category');
            }

            if (state.search) {
                url.searchParams.set('search', state.search);
            } else {
                url.searchParams.delete('search');
            }

            return url;
        }

        function loadMarketplace(pageUrl = null) {
            clearFeedback();
            showLoader();

            const url = buildUrl(pageUrl);
            state.pageUrl = `${url.pathname}${url.search}`;

            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                },
            })
            .then(async response => {
                if (!response.ok) {
                    let errorMessage = 'Não foi possível carregar os anúncios.';
                    try {
                        const errorData = await response.json();
                        if (errorData?.message) {
                            errorMessage = errorData.message;
                        }
                    } catch (error) {
                        // Ignorar erro de parsing
                    }
                    throw new Error(errorMessage);
                }
                return response.json();
            })
            .then(data => {
                renderItems(data.data);
                renderPagination(data.links);
                renderSummary(data);
            })
            .catch(error => {
                renderItems([]);
                renderPagination([]);
                renderSummary(null);
                showFeedback('danger', error.message);
            })
            .finally(() => {
                hideLoader();
            });
        }

        async function openItemModal(itemId) {
            if (!itemModal) {
                return;
            }

            itemLoader.classList.remove('d-none');
            itemDetails.classList.add('d-none');
            itemModal.show();

            try {
                const response = await fetch(`/api/marketplace/${itemId}`, {
                    headers: {
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Não foi possível carregar os detalhes do anúncio.');
                }

                const data = await response.json();
                populateItemModal(data);
            } catch (error) {
                showFeedback('danger', error.message);
                itemModal.hide();
            }
        }

        function populateItemModal(item) {
            if (!item || !modalFields.title) {
                return;
            }

            const categoryLabel = config.categories[item.category] || 'Categoria';
            const conditionLabel = config.conditions[item.condition] || 'Condição';
            const statusLabel = config.statuses[item.status] || config.statuses.active;
            const priceLabel = typeof item.price === 'number' || !Number.isNaN(Number(item.price))
                ? currencyFormatter.format(Number(item.price))
                : 'Valor sob consulta';
            const sellerName = item?.seller?.name || 'Morador';
            const sellerPhone = item?.seller?.phone || null;
            const sellerEmail = item?.seller?.email || null;
            const unitBlock = item?.seller?.unit?.block || item?.unit?.block;
            const unitNumber = item?.seller?.unit?.number || item?.unit?.number;
            const unitLabel = [unitBlock ? `Bloco ${unitBlock}` : null, unitNumber ? `Unidade ${unitNumber}` : null].filter(Boolean).join(' • ') || 'Unidade não informada';
            const viewsCount = Number.isInteger(item.views) ? item.views : 0;
            const viewsLabel = `${viewsCount} ${viewsCount === 1 ? 'visualização' : 'visualizações'}`;
            const createdAt = item.created_at ? `Publicado em ${formatDateTime(item.created_at)}` : '';
            const updatedAt = item.updated_at ? `Atualizado em ${formatDateTime(item.updated_at)}` : '';
            const sellerWhatsappDigits = String(item.whatsapp || '').replace(/\D/g, '');
            const sellerWhatsappFormatted = /^\d{10,11}$/.test(sellerWhatsappDigits) ? formatWhatsapp(sellerWhatsappDigits) : null;
            const sellerWhatsappLink = sellerWhatsappFormatted
                ? `https://wa.me/55${sellerWhatsappDigits}?text=${whatsappMessageEncoded}`
                : null;

            modalFields.title.textContent = item.title || 'Anúncio';
            modalFields.category.textContent = categoryLabel;
            modalFields.condition.textContent = conditionLabel;
            modalFields.status.textContent = statusLabel;
            modalFields.price.textContent = priceLabel;
            modalFields.createdAt.textContent = createdAt;
            modalFields.sellerName.textContent = sellerName;

            const contacts = [];
            if (sellerPhone) contacts.push(escapeHtml(sellerPhone));
            if (sellerEmail) contacts.push(escapeHtml(sellerEmail));
            if (sellerWhatsappFormatted && sellerWhatsappLink) {
                contacts.push(`<a href="${sellerWhatsappLink}" target="_blank" rel="noopener">WhatsApp: +55 ${escapeHtml(sellerWhatsappFormatted)}</a>`);
            }

            if (contacts.length > 0) {
                modalFields.sellerContacts.innerHTML = contacts.join(' • ');
            } else {
                modalFields.sellerContacts.textContent = 'Contato não informado.';
            }

            modalFields.unit.textContent = unitLabel;
            modalFields.updatedAt.textContent = updatedAt;
            modalFields.views.textContent = viewsLabel;
            modalFields.description.textContent = item.description || 'Sem descrição informada.';

            const imagesContainer = modalFields.images;
            const storageBase = imagesContainer?.dataset?.storageBase || '';
            imagesContainer.innerHTML = '';

            if (Array.isArray(item.images) && item.images.length > 0) {
                item.images.forEach(path => {
                    const normalizedBase = storageBase.replace(/\/$/, '');
                    const normalizedPath = String(path).replace(/^\/+/, '');
                    const url = `${normalizedBase}/${normalizedPath}`;

                    const img = document.createElement('img');
                    img.src = url;
                    img.alt = item.title || 'Imagem do anúncio';
                    img.className = 'rounded shadow-sm';
                    img.style.width = '150px';
                    img.style.height = '120px';
                    img.style.objectFit = 'cover';
                    imagesContainer.appendChild(img);
                });
            } else {
                const emptySpan = document.createElement('span');
                emptySpan.className = 'text-muted';
                emptySpan.textContent = 'Nenhuma imagem enviada.';
                imagesContainer.appendChild(emptySpan);
            }

            itemLoader.classList.add('d-none');
            itemDetails.classList.remove('d-none');
        }

        async function handlePublish() {
            if (!formNovoAnuncio || !submitButton) {
                return;
            }

            clearFeedback();

            const formData = new FormData(formNovoAnuncio);
            if (whatsappInput) {
                const digits = (whatsappInput.value || '').replace(/\D/g, '');
                if (!/^\d{10,11}$/.test(digits)) {
                    showFeedback('warning', 'Informe um WhatsApp válido com DDD (10 ou 11 dígitos).');
                    return;
                }
                formData.set('whatsapp', digits);
            }
            submitButton.disabled = true;
            const originalContent = submitButton.innerHTML;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Salvando...';

            let endpoint = '/api/marketplace';
            if (editState.isEditing && editState.itemId) {
                endpoint = `/api/marketplace/${editState.itemId}`;
                formData.append('_method', 'PUT');
            }

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const responseData = await response.json().catch(() => ({}));

                if (!response.ok) {
                    if (response.status === 422 && responseData?.errors) {
                        const messages = Object.values(responseData.errors).flat();
                        throw new Error(messages.join(' '));
                    }
                    throw new Error(responseData?.message || 'Não foi possível publicar o anúncio.');
                }

                const successMessage = responseData?.message
                    || (editState.isEditing ? 'Anúncio atualizado com sucesso!' : 'Anúncio publicado com sucesso!');
                showFeedback('success', successMessage);
                resetFormState(true);

                const modalInstance = bootstrap.Modal.getInstance(document.getElementById('novoAnuncioModal'));
                modalInstance?.hide();

                loadMarketplace(state.pageUrl || '/api/marketplace');
            } catch (error) {
                showFeedback('danger', error.message);
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = originalContent;
            }
        }

        function handleImagePreview(event) {
            const input = event.target;
        const preview = document.getElementById('imagePreview');
            if (!preview) {
                return;
            }

        preview.innerHTML = '';
        
            if (!input.files) {
                return;
            }

            if (input.files.length > 3) {
                showFeedback('warning', 'Você pode enviar no máximo 3 imagens.');
                input.value = '';
                return;
            }
            
            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = file.name;
                    img.className = 'rounded shadow-sm me-2 mb-2';
                    img.style.width = '110px';
                    img.style.height = '90px';
                    img.style.objectFit = 'cover';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        }

        function resetFormState(shouldReset = false) {
            editState.isEditing = false;
            editState.itemId = null;

            if (imagesInput) {
                imagesInput.disabled = false;
                if (shouldReset) {
                    imagesInput.value = '';
                }
            }

            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = submitButtonDefaultHtml;
            }

            if (modalTitle) {
                modalTitle.textContent = modalDefaultTitle;
            }

            if (shouldReset && formNovoAnuncio) {
                formNovoAnuncio.reset();
            }

            if (shouldReset && imagePreview) {
                imagePreview.innerHTML = '';
            }

            if (shouldReset && whatsappInput) {
                whatsappInput.value = '';
            }
        }

        async function openEditModal(itemId) {
            let item = itemsCache.get(String(itemId));

            if (!item) {
                try {
                    const response = await fetch(`/api/marketplace/${itemId}`, {
                        headers: { 'Accept': 'application/json' },
                    });
                    if (!response.ok) {
                        throw new Error('Não foi possível carregar os dados do anúncio.');
                    }
                    item = await response.json();
                } catch (error) {
                    showFeedback('danger', error.message);
                    return;
                }
            }

            if (!item) {
                showFeedback('danger', 'Dados do anúncio indisponíveis.');
                return;
            }

            if (formNovoAnuncio) {
                formNovoAnuncio.reset();
                formNovoAnuncio.elements['title'].value = item.title || '';
                formNovoAnuncio.elements['description'].value = item.description || '';
                formNovoAnuncio.elements['price'].value = item.price || '';

                const categoryField = formNovoAnuncio.elements['category'];
                if (categoryField) {
                    categoryField.value = item.category || '';
                }

                const conditionField = formNovoAnuncio.elements['condition'];
                if (conditionField) {
                    conditionField.value = item.condition || '';
                }
            }

            if (whatsappInput) {
                const digits = String(item.whatsapp_digits || item.whatsapp || '').replace(/\D/g, '');
                whatsappInput.value = digits;
            }

            if (imagesInput) {
                imagesInput.disabled = true;
                imagesInput.value = '';
            }

            if (imagePreview) {
                imagePreview.innerHTML = '<small class="text-muted">Imagens não podem ser alteradas após a publicação.</small>';
            }

            if (modalTitle) {
                modalTitle.textContent = 'Editar Anúncio';
            }

            if (submitButton) {
                submitButton.innerHTML = '<i class="bi bi-check-circle me-1"></i> Salvar alterações';
            }

            editState.isEditing = true;
            editState.itemId = itemId;

            createModal?.show();
        }

        async function deleteItem(itemId) {
            const item = itemsCache.get(String(itemId));
            const title = item?.title ? ` "${item.title}"` : '';

            if (!confirm(`Deseja realmente excluir o anúncio${title}?`)) {
                return;
            }

            try {
                const response = await fetch(`/api/marketplace/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(data?.message || 'Não foi possível excluir o anúncio.');
                }

                showFeedback('success', data?.message || 'Anúncio excluído com sucesso.');
                loadMarketplace(state.pageUrl || '/api/marketplace');
            } catch (error) {
                showFeedback('danger', error.message);
            }
        }

        if (pagination) {
            pagination.addEventListener('click', event => {
                const link = event.target.closest('[data-page-url]');
                if (!link) {
                    return;
                }
                event.preventDefault();
                loadMarketplace(link.dataset.pageUrl);
            });
        }

        if (grid) {
            grid.addEventListener('click', event => {
                const button = event.target.closest('[data-action]');
                if (!button) {
                    return;
                }

                const action = button.dataset.action;
                const itemId = button.dataset.itemId;

                if (!itemId) {
                    return;
                }

                event.preventDefault();

                if (action === 'contact-seller') {
                    const rawWhatsapp = button.dataset.whatsapp || '';
                    if (/^\d{10,11}$/.test(rawWhatsapp)) {
                        const url = `https://wa.me/55${rawWhatsapp}?text=${whatsappMessageEncoded}`;
                        window.open(url, '_blank');
                    } else {
                        showFeedback('warning', 'O vendedor não informou um WhatsApp válido.');
                    }
                    return;
                }

                if (action === 'edit-item') {
                    openEditModal(itemId);
                    return;
                }

                if (action === 'delete-item') {
                    deleteItem(itemId);
                    return;
                }

                openItemModal(itemId);
            });
        }

        if (categorySelect) {
            categorySelect.addEventListener('change', () => {
                state.category = categorySelect.value;
                state.pageUrl = '/api/marketplace';
                loadMarketplace();
            });
        }

        if (searchButton) {
            searchButton.addEventListener('click', event => {
                event.preventDefault();
                state.search = searchInput.value.trim();
                state.pageUrl = '/api/marketplace';
                loadMarketplace();
            });
        }

        if (clearButton) {
            clearButton.addEventListener('click', event => {
                event.preventDefault();
                categorySelect.value = '';
                searchInput.value = '';
                state.category = '';
                state.search = '';
                state.pageUrl = '/api/marketplace';
                loadMarketplace();
            });
        }

        if (searchInput) {
            searchInput.addEventListener('keyup', event => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    state.search = searchInput.value.trim();
                    state.pageUrl = '/api/marketplace';
                    loadMarketplace();
                }
            });
        }

        if (createModalElement) {
            createModalElement.addEventListener('show.bs.modal', () => {
                if (!editState.isEditing) {
                    resetFormState(true);
                }
            });

            createModalElement.addEventListener('hidden.bs.modal', () => resetFormState(true));
        }

        if (imagesInput) {
            imagesInput.addEventListener('change', handleImagePreview);
        }

        if (submitButton) {
            submitButton.addEventListener('click', handlePublish);
        }

        loadMarketplace('/api/marketplace');
    });
</script>
@endpush
@endsection
