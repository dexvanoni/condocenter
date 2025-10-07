@extends('layouts.app')

@section('title', 'Marketplace')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Marketplace</h2>
                <p class="text-muted mb-0">Produtos e serviços entre moradores</p>
            </div>
            @can('create_marketplace_items')
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#novoAnuncioModal">
                <i class="bi bi-plus-circle"></i> Novo Anúncio
            </button>
            @endcan
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <select class="form-select" id="filterCategory">
                    <option value="">Todas as Categorias</option>
                    <option value="products">Produtos</option>
                    <option value="services">Serviços</option>
                    <option value="jobs">Empregos</option>
                    <option value="vehicles">Veículos</option>
                    <option value="other">Outros</option>
                </select>
            </div>
            <div class="col-md-6">
                <input type="text" class="form-control" id="searchInput" placeholder="Buscar anúncios...">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Buscar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Grid de Anúncios -->
<div class="row g-4" id="marketplaceGrid">
    <!-- Exemplo de card de anúncio -->
    <div class="col-md-4">
        <div class="card h-100">
            <img src="https://via.placeholder.com/400x300" class="card-img-top" alt="Produto">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-primary">Produtos</span>
                    <span class="badge bg-success">Novo</span>
                </div>
                <h5 class="card-title">Exemplo de Produto</h5>
                <p class="card-text text-muted">Esta é uma descrição de exemplo do produto anunciado...</p>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <h4 class="mb-0 text-primary">R$ 150,00</h4>
                    <small class="text-muted">
                        <i class="bi bi-person"></i> Vendedor<br>
                        <i class="bi bi-building"></i> Unidade 101
                    </small>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye"></i> Ver Detalhes
                    </button>
                    <button class="btn btn-outline-success btn-sm">
                        <i class="bi bi-chat-dots"></i> Contatar Vendedor
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mais cards serão carregados via JavaScript -->
    <div class="col-12 text-center py-5">
        <p class="text-muted">Carregando anúncios...</p>
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
    </div>
</div>

<!-- Modal Novo Anúncio -->
<div class="modal fade" id="novoAnuncioModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo Anúncio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNovoAnuncio" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Título *</label>
                        <input type="text" class="form-control" name="title" required 
                               placeholder="Ex: Bicicleta Mountain Bike">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição *</label>
                        <textarea class="form-control" name="description" rows="4" required
                                  placeholder="Descreva seu produto ou serviço..."></textarea>
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
                                    <option value="products">Produtos</option>
                                    <option value="services">Serviços</option>
                                    <option value="jobs">Empregos</option>
                                    <option value="real_estate">Imóveis</option>
                                    <option value="vehicles">Veículos</option>
                                    <option value="other">Outros</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Condição *</label>
                                <select class="form-select" name="condition" required>
                                    <option value="new">Novo</option>
                                    <option value="used">Usado</option>
                                    <option value="refurbished">Recondicionado</option>
                                    <option value="not_applicable">Não se Aplica</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Imagens (até 3)</label>
                        <input type="file" class="form-control" name="images[]" multiple accept="image/*" 
                               onchange="previewImages(this)">
                        <div id="imagePreview" class="mt-2"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="publicarAnuncio()">
                    <i class="bi bi-check-circle"></i> Publicar Anúncio
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function previewImages(input) {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';
        
        if (input.files) {
            if (input.files.length > 3) {
                alert('Máximo de 3 imagens permitidas');
                input.value = '';
                return;
            }
            
            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '100px';
                    img.style.marginRight = '10px';
                    img.className = 'rounded';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        }
    }

    function publicarAnuncio() {
        const formData = new FormData(document.getElementById('formNovoAnuncio'));
        
        fetch('/api/marketplace', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                alert(data.message);
                location.reload();
            }
        })
        .catch(error => console.error('Erro:', error));
    }

    // Carregar anúncios via AJAX
    function loadMarketplace() {
        fetch('/api/marketplace')
            .then(response => response.json())
            .then(data => {
                // Renderizar cards dinamicamente
                console.log('Marketplace items:', data);
            });
    }

    // loadMarketplace();
</script>
@endpush
@endsection

