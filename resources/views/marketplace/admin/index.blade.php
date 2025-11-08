@extends('layouts.app')

@section('title', 'Administração do Marketplace')

@php
use Illuminate\Support\Str;
$queryString = request()->getQueryString();
@endphp

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h2 class="mb-1">Administração do Marketplace</h2>
            <p class="text-muted mb-0">Modere, edite e organize os anúncios publicados pelos moradores.</p>
        </div>
        <div class="card shadow-sm border-0">
            <div class="card-body py-3 px-4">
                <form id="toggleAggregadosForm" action="{{ route('marketplace.admin.settings.toggle') }}" method="POST" class="d-flex align-items-center gap-3">
                    @csrf
                    <input type="hidden" name="marketplace_allow_agregados" id="toggleAggregadosInput" value="{{ $allowAggregados ? 1 : 0 }}">
                    <div>
                        <p class="fw-semibold mb-1 text-muted text-uppercase small">Anúncios por agregados</p>
                        <div class="text-muted small">
                            {{ $allowAggregados ? 'Agregados com permissão específica podem anunciar.' : 'Somente moradores podem anunciar no momento.' }}
                        </div>
                    </div>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="toggleAggregadosSwitch" {{ $allowAggregados ? 'checked' : '' }}>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>Ocorreu um problema ao processar a solicitação.
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label text-muted fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        @foreach ($statuses as $key => $label)
                            <option value="{{ $key }}" {{ $filters['status'] === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted fw-semibold">Categoria</label>
                    <select name="category" class="form-select">
                        <option value="">Todas</option>
                        @foreach ($categories as $key => $label)
                            <option value="{{ $key }}" {{ $filters['category'] === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted fw-semibold">Condição</label>
                    <select name="condition" class="form-select">
                        <option value="">Todas</option>
                        @foreach ($conditions as $key => $label)
                            <option value="{{ $key }}" {{ $filters['condition'] === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted fw-semibold">Busca</label>
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Título ou descrição" value="{{ $filters['search'] }}">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="bi bi-funnel"></i> Aplicar filtros
                    </button>
                    <a href="{{ route('marketplace.admin.index') }}" class="btn btn-light" id="clearMarketplaceFilters">
                        Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-shop me-2 text-primary"></i> Anúncios publicados
                </h5>
                <span class="badge bg-primary bg-opacity-10 text-primary">
                    {{ $items->total() }} {{ Str::plural('anúncio', $items->total()) }}
                </span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Título</th>
                        <th>Categoria</th>
                        <th>Condição</th>
                        <th class="text-center">Preço</th>
                        <th>Vendedor</th>
                        <th>Status</th>
                        <th class="text-center">Visualizações</th>
                        <th class="text-center">Publicado em</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @php
                            $categoryLabel = $categories[$item->category] ?? ucfirst($item->category);
                            $conditionLabel = $conditions[$item->condition] ?? ucfirst($item->condition);
                            $statusLabel = $statuses[$item->status] ?? ucfirst($item->status);
                            $statusClass = match($item->status) {
                                'active' => 'bg-success bg-opacity-10 text-success',
                                'sold' => 'bg-warning bg-opacity-10 text-warning',
                                'inactive' => 'bg-secondary bg-opacity-10 text-secondary',
                                default => 'bg-secondary bg-opacity-10 text-secondary',
                            };
                            $priceFormatted = 'R$ ' . number_format((float) $item->price, 2, ',', '.');
                            $querySuffix = $queryString ? '?' . $queryString : '';
                            $updateUrl = route('marketplace.admin.update', $item->id) . $querySuffix;
                            $deleteUrl = route('marketplace.admin.destroy', $item->id) . $querySuffix;
                            $unitBlock = optional($item->unit)->block;
                            $unitNumber = optional($item->unit)->number;
                            $whatsappDigits = preg_replace('/\D/', '', (string) $item->whatsapp);
                            $whatsappFormatted = $whatsappDigits
                                ? preg_replace('/^(\d{2})(\d{4,5})(\d{4})$/', '$1 $2-$3', $whatsappDigits)
                                : null;
                            $dataItem = [
                                'id' => $item->id,
                                'title' => $item->title,
                                'description' => $item->description,
                                'price' => $item->price,
                                'price_formatted' => $priceFormatted,
                                'status' => $item->status,
                                'status_label' => $statusLabel,
                                'category' => $item->category,
                                'category_label' => $categoryLabel,
                                'condition' => $item->condition,
                                'condition_label' => $conditionLabel,
                                'images' => $item->images ?? [],
                                'whatsapp' => $whatsappDigits,
                                'whatsapp_formatted' => $whatsappFormatted,
                                'views' => (int) ($item->views ?? 0),
                                'seller' => [
                                    'name' => optional($item->seller)->name,
                                    'phone' => optional($item->seller)->phone,
                                    'email' => optional($item->seller)->email,
                                ],
                                'unit' => [
                                    'block' => $unitBlock,
                                    'number' => $unitNumber,
                                ],
                                'created_at' => optional($item->created_at)->format('d/m/Y H:i'),
                                'updated_at' => optional($item->updated_at)->format('d/m/Y H:i'),
                            ];
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $item->title }}</div>
                                <small class="text-muted">{{ Str::limit($item->description, 70) }}</small>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary">{{ $categoryLabel }}</span>
                            </td>
                            <td>
                                <span class="badge bg-info bg-opacity-10 text-info">{{ $conditionLabel }}</span>
                            </td>
                            <td class="text-center">
                                <span class="fw-semibold text-primary">{{ $priceFormatted }}</span>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold">{{ optional($item->seller)->name ?? 'Usuário removido' }}</span>
                                    <small class="text-muted">
                                        {{ $unitBlock ? 'Bloco ' . $unitBlock . ' • ' : '' }}
                                        {{ $unitNumber ? 'Unidade ' . $unitNumber : '' }}
                                    </small>
                                    @if($whatsappFormatted)
                                    <small class="text-muted">WhatsApp: +55 {{ $whatsappFormatted }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $statusClass }} text-uppercase">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-eye me-1"></i>{{ $item->views ?? 0 }}
                                </span>
                            </td>
                            <td class="text-center">
                                <small class="text-muted">{{ optional($item->created_at)->format('d/m/Y') }}</small>
                                <div class="text-muted small">{{ optional($item->created_at)->format('H:i') }}</div>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button"
                                        class="btn btn-outline-secondary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#viewItemModal"
                                        data-item='@json($dataItem, JSON_UNESCAPED_UNICODE)'>
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button"
                                        class="btn btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editItemModal"
                                        data-item='@json($dataItem, JSON_UNESCAPED_UNICODE)'
                                        data-update-url="{{ $updateUrl }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form action="{{ $deleteUrl }}" method="POST" onsubmit="return confirm('Deseja realmente remover este anúncio? Esta ação não pode ser desfeita.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bi bi-inboxes fs-1 text-muted d-block mb-3"></i>
                                <p class="text-muted mb-0">Nenhum anúncio encontrado com os filtros atuais.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Mostrando {{ $items->firstItem() }}-{{ $items->lastItem() }} de {{ $items->total() }}
                </small>
                {{ $items->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal: Visualizar anúncio -->
<div class="modal fade" id="viewItemModal" tabindex="-1" aria-hidden="true">
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
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-3 gap-3">
                    <div>
                        <h4 id="viewItemTitle" class="mb-1"></h4>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary" id="viewItemCategory"></span>
                            <span class="badge bg-info bg-opacity-10 text-info" id="viewItemCondition"></span>
                            <span class="badge bg-light text-dark" id="viewItemStatus"></span>
                        </div>
                    </div>
                    <div class="text-lg-end">
                        <div class="fs-4 fw-bold text-primary" id="viewItemPrice"></div>
                        <small class="text-muted" id="viewItemCreatedAt"></small>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body">
                                <h6 class="text-muted text-uppercase small fw-semibold mb-2">Vendedor</h6>
                                <p class="mb-1 fw-semibold" id="viewItemSellerName">-</p>
                                <p class="mb-1 text-muted" id="viewItemSellerContact">-</p>
                                <small class="text-muted d-block" id="viewItemUnit">-</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body">
                                <h6 class="text-muted text-uppercase small fw-semibold mb-2">Resumo</h6>
                                <p class="mb-1 text-muted" id="viewItemUpdatedAt"></p>
                                <p class="mb-0 text-muted" id="viewItemViews"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-4">
                    <h6 class="text-muted text-uppercase small fw-semibold mb-2">Descrição</h6>
                    <p class="text-muted" id="viewItemDescription"></p>
                </div>
                <div>
                    <h6 class="text-muted text-uppercase small fw-semibold mb-2">Imagens</h6>
                    <div id="viewItemImages" class="d-flex flex-wrap gap-2" data-storage-base="{{ asset('storage') }}">
                        <span class="text-muted">Nenhuma imagem enviada.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Editar anúncio -->
<div class="modal fade" id="editItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="editItemForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>
                        Editar Anúncio
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info border-0">
                        <small>
                            <i class="bi bi-info-circle me-1"></i>
                            As imagens do anúncio não podem ser alteradas nesta interface.
                        </small>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Título *</label>
                            <input type="text" class="form-control" id="editItemTitle" name="title" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Preço (R$) *</label>
                            <input type="number" class="form-control" id="editItemPrice" name="price" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Categoria *</label>
                            <select class="form-select" id="editItemCategory" name="category" required>
                                @foreach ($categories as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Condição *</label>
                            <select class="form-select" id="editItemCondition" name="condition" required>
                                @foreach ($conditions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status *</label>
                            <select class="form-select" id="editItemStatus" name="status" required>
                                @foreach ($statuses as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">WhatsApp do anunciante *</label>
                            <div class="input-group">
                                <span class="input-group-text">+55</span>
                                <input type="tel" class="form-control" id="editItemWhatsapp" name="whatsapp" pattern="^\d{10,11}$" required>
                            </div>
                            <small class="text-muted">Informe apenas números, com DDD.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição *</label>
                            <textarea class="form-control" id="editItemDescription" name="description" rows="5" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Salvar alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggleForm = document.getElementById('toggleAggregadosForm');
    const toggleSwitch = document.getElementById('toggleAggregadosSwitch');
    const toggleInput = document.getElementById('toggleAggregadosInput');

    if (toggleSwitch && toggleForm && toggleInput) {
        toggleSwitch.addEventListener('change', () => {
            toggleInput.value = toggleSwitch.checked ? 1 : 0;
            toggleForm.submit();
        });
    }

    const viewItemModal = document.getElementById('viewItemModal');
    if (viewItemModal) {
        viewItemModal.addEventListener('show.bs.modal', event => {
            const trigger = event.relatedTarget;
            if (!trigger) return;

            const data = trigger.getAttribute('data-item');
            if (!data) return;

            const item = JSON.parse(data);
            viewItemModal.querySelector('#viewItemTitle').textContent = item.title || '-';
            viewItemModal.querySelector('#viewItemCategory').textContent = item.category_label || '-';
            viewItemModal.querySelector('#viewItemCondition').textContent = item.condition_label || '-';
            viewItemModal.querySelector('#viewItemStatus').textContent = item.status_label || '-';
            viewItemModal.querySelector('#viewItemPrice').textContent = item.price_formatted || '-';
            viewItemModal.querySelector('#viewItemCreatedAt').textContent = item.created_at ? `Criado em ${item.created_at}` : '';
            viewItemModal.querySelector('#viewItemUpdatedAt').textContent = item.updated_at ? `Atualizado em ${item.updated_at}` : '';
            const viewsElement = viewItemModal.querySelector('#viewItemViews');
            if (Number.isInteger(item.views)) {
                const viewsLabel = item.views === 1 ? 'visualização' : 'visualizações';
                viewsElement.textContent = `${item.views} ${viewsLabel}`;
            } else {
                viewsElement.textContent = 'Sem visualizações registradas.';
            }
            viewItemModal.querySelector('#viewItemDescription').textContent = item.description || '-';

            const sellerName = item?.seller?.name || 'Usuário removido';
            const sellerPhone = item?.seller?.phone || null;
            const sellerEmail = item?.seller?.email || null;
            const sellerWhatsappDigits = item?.whatsapp || '';
            const sellerWhatsappFormatted = item?.whatsapp_formatted ? `+55 ${item.whatsapp_formatted}` : null;
            const sellerWhatsappLink = item?.whatsapp ? `https://wa.me/55${item.whatsapp}?text=${encodeURIComponent('Olá, vi seu anúncio no sistema do condomínio. Ainda está disponível?')}` : null;
            const unitBlock = item?.unit?.block ? `Bloco ${item.unit.block}` : null;
            const unitNumber = item?.unit?.number ? `Unidade ${item.unit.number}` : null;

            viewItemModal.querySelector('#viewItemSellerName').textContent = sellerName;

            const contactLines = [];
            if (sellerPhone) contactLines.push(escapeHtml(sellerPhone));
            if (sellerEmail) contactLines.push(escapeHtml(sellerEmail));
            if (sellerWhatsappFormatted && sellerWhatsappLink) {
                contactLines.push(`<a href="${sellerWhatsappLink}" target="_blank" rel="noopener">WhatsApp: ${escapeHtml(sellerWhatsappFormatted)}</a>`);
            }
            const contactElement = viewItemModal.querySelector('#viewItemSellerContact');
            if (contactLines.length > 0) {
                contactElement.innerHTML = contactLines.join(' • ');
            } else {
                contactElement.textContent = 'Contato não informado.';
            }

            const unitInfo = [unitBlock, unitNumber].filter(Boolean).join(' • ');
            viewItemModal.querySelector('#viewItemUnit').textContent = unitInfo || 'Unidade não informada.';

            const imagesContainer = viewItemModal.querySelector('#viewItemImages');
            const storageBase = imagesContainer?.dataset?.storageBase || '';
            imagesContainer.innerHTML = '';

            if (Array.isArray(item.images) && item.images.length > 0) {
                item.images.forEach(path => {
                    const normalizedBase = (storageBase || '').replace(/\/$/, '');
                    const normalizedPath = String(path || '').replace(/^\/+/, '');
                    const imageUrl = `${normalizedBase}/${normalizedPath}`;

                    const img = document.createElement('img');
                    img.src = imageUrl;
                    img.alt = item.title || 'Imagem do anúncio';
                    img.className = 'rounded shadow-sm';
                    img.style.width = '140px';
                    img.style.height = '110px';
                    img.style.objectFit = 'cover';
                    imagesContainer.appendChild(img);
                });
            } else {
                const emptyState = document.createElement('span');
                emptyState.className = 'text-muted';
                emptyState.textContent = 'Nenhuma imagem enviada.';
                imagesContainer.appendChild(emptyState);
            }
        });
    }

    const editItemModal = document.getElementById('editItemModal');
    if (editItemModal) {
        editItemModal.addEventListener('show.bs.modal', event => {
            const trigger = event.relatedTarget;
            if (!trigger) return;

            const data = trigger.getAttribute('data-item');
            const updateUrl = trigger.getAttribute('data-update-url');
            if (!data || !updateUrl) return;

            const item = JSON.parse(data);
            const form = document.getElementById('editItemForm');
            form.action = updateUrl;

            form.querySelector('#editItemTitle').value = item.title || '';
            form.querySelector('#editItemPrice').value = item.price || '';
            form.querySelector('#editItemDescription').value = item.description || '';
            const whatsappField = form.querySelector('#editItemWhatsapp');
            if (whatsappField) {
                whatsappField.value = item.whatsapp || '';
            }

            const categorySelect = form.querySelector('#editItemCategory');
            if (categorySelect) {
                categorySelect.value = item.category || '';
            }

            const conditionSelect = form.querySelector('#editItemCondition');
            if (conditionSelect) {
                conditionSelect.value = item.condition || '';
            }

            const statusSelect = form.querySelector('#editItemStatus');
            if (statusSelect) {
                statusSelect.value = item.status || '';
            }
        });
    }
});
</script>
@endpush

