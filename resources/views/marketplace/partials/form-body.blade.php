@php
    $isEdit = $isEdit ?? false;
    $item = $item ?? null;
    $selectedCategory = old('category', $item->category ?? null);
    $selectedCondition = old('condition', $item->condition ?? 'used');
    $whatsappDigits = old('whatsapp', $prefilledWhatsapp ?? '');
    $formId = $isEdit ? 'marketplaceEditForm' : 'marketplaceCreateForm';
    $submitBtnId = $isEdit ? 'marketplaceEditSubmitBtn' : 'marketplaceSubmitBtn';
    $feedbackId = $isEdit ? 'marketplaceEditFeedback' : 'marketplaceCreateFeedback';
    $imagesInputId = $isEdit ? 'marketplaceImagesInputEdit' : 'marketplaceImagesInput';
    $existingImages = $isEdit
        ? collect($item->images ?? [])->map(fn ($path) => [
            'path' => $path,
            'url' => asset('storage/' . str_replace('\\', '/', ltrim($path, '/'))),
        ])->values()->all()
        : [];
@endphp

<div class="pet-form-page">
    <div class="pet-form-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <a href="{{ route('marketplace.index') }}" class="pet-form-back">
                    <i class="bi bi-arrow-left"></i> Voltar para marketplace
                </a>
                <h1 class="pet-form-title mt-2 mb-1">
                    <i class="bi bi-{{ $isEdit ? 'pencil-square' : 'megaphone-fill' }}"></i>
                    {{ $isEdit ? 'Editar anúncio' : 'Publicar novo anúncio' }}
                </h1>
                <p class="pet-form-subtitle mb-0">
                    {{ $isEdit ? 'Atualize as informações e fotos do seu anúncio.' : 'Compartilhe produtos, serviços ou oportunidades com os moradores do condomínio.' }}
                </p>
            </div>
            <div class="pet-form-hero-badge">
                <i class="bi bi-whatsapp"></i>
                <span>Contato via WhatsApp</span>
            </div>
        </div>
    </div>

    <div id="{{ $feedbackId }}"></div>

    <form id="{{ $formId }}" enctype="multipart/form-data" novalidate>
        <div class="row g-4">
            <div class="col-lg-8">
                <section class="pet-form-section">
                    <div class="pet-form-section__header">
                        <span class="pet-form-step">1</span>
                        <div>
                            <h2>Anunciante</h2>
                            <p>Seus dados de contato no anúncio</p>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="pet-info-chip">
                                <div class="pet-info-chip__icon"><i class="bi bi-building"></i></div>
                                <div>
                                    <small>Unidade</small>
                                    <strong>{{ $prefilledUnit?->full_identifier ?? '—' }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="pet-info-chip">
                                <div class="pet-info-chip__icon"><i class="bi bi-person-badge"></i></div>
                                <div>
                                    <small>Anunciante</small>
                                    <strong>{{ $currentUser->name }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <label for="whatsapp" class="form-label">WhatsApp para contato *</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text">+55</span>
                        <input type="tel"
                               class="form-control"
                               id="whatsapp"
                               name="whatsapp"
                               value="{{ $whatsappDigits }}"
                               placeholder="11987654321"
                               pattern="^\d{10,11}$"
                               required>
                    </div>
                    <small class="text-muted">Informe apenas números, com DDD (ex.: 11987654321).</small>
                </section>

                <section class="pet-form-section">
                    <div class="pet-form-section__header">
                        <span class="pet-form-step">2</span>
                        <div>
                            <h2>Informações do anúncio</h2>
                            <p>Título, descrição e valor</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="title" class="form-label">Título *</label>
                        <input type="text"
                               class="form-control form-control-lg"
                               id="title"
                               name="title"
                               value="{{ old('title', $item->title ?? '') }}"
                               placeholder="Ex.: Bicicleta Mountain Bike, Aulas de inglês..."
                               required
                               autofocus>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label">Descrição *</label>
                        <textarea class="form-control"
                                  id="description"
                                  name="description"
                                  rows="5"
                                  placeholder="Descreva o produto ou serviço, estado, detalhes e formas de contato..."
                                  required>{{ old('description', $item->description ?? '') }}</textarea>
                    </div>

                    <div class="mb-0">
                        <label for="price" class="form-label">Preço (R$) *</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">R$</span>
                            <input type="number"
                                   class="form-control"
                                   id="price"
                                   name="price"
                                   value="{{ old('price', $item->price ?? '') }}"
                                   step="0.01"
                                   min="0"
                                   placeholder="0,00"
                                   required>
                        </div>
                    </div>
                </section>

                <section class="pet-form-section">
                    <div class="pet-form-section__header">
                        <span class="pet-form-step">3</span>
                        <div>
                            <h2>Categoria e condição</h2>
                            <p>Classifique seu anúncio</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label d-block mb-2">Categoria *</label>
                        <div class="pet-type-grid">
                            @foreach([
                                'products' => ['label' => 'Produtos', 'icon' => 'bi-bag'],
                                'services' => ['label' => 'Serviços', 'icon' => 'bi-tools'],
                                'jobs' => ['label' => 'Empregos', 'icon' => 'bi-briefcase'],
                                'real_estate' => ['label' => 'Imóveis', 'icon' => 'bi-house'],
                                'vehicles' => ['label' => 'Veículos', 'icon' => 'bi-car-front'],
                                'other' => ['label' => 'Outros', 'icon' => 'bi-grid'],
                            ] as $value => $meta)
                                <label class="pet-type-option {{ $selectedCategory === $value ? 'is-selected' : '' }}">
                                    <input type="radio" name="category" value="{{ $value }}" class="d-none" {{ $selectedCategory === $value ? 'checked' : '' }} required>
                                    <i class="bi {{ $meta['icon'] }}"></i>
                                    <span>{{ $meta['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label d-block mb-2">Condição *</label>
                        <div class="pet-size-grid">
                            @foreach($conditions as $value => $label)
                                <label class="pet-size-option {{ $selectedCondition === $value ? 'is-selected' : '' }}">
                                    <input type="radio" name="condition" value="{{ $value }}" class="d-none" {{ $selectedCondition === $value ? 'checked' : '' }} required>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    @if($isEdit)
                    <div class="mb-0">
                        <label for="status" class="form-label">Status do anúncio</label>
                        <select class="form-select form-select-lg" id="status" name="status">
                            @foreach([
                                'active' => 'Disponível',
                                'sold' => 'Vendido',
                                'inactive' => 'Inativo',
                            ] as $value => $label)
                                <option value="{{ $value }}" {{ old('status', $item->status ?? 'active') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </section>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-success btn-lg px-4" id="{{ $submitBtnId }}">
                        <i class="bi bi-check2-circle"></i> {{ $isEdit ? 'Salvar alterações' : 'Publicar anúncio' }}
                    </button>
                    <a href="{{ route('marketplace.index') }}" class="btn btn-outline-secondary btn-lg">Cancelar</a>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="pet-form-sidebar sticky-top">
                    <section class="pet-form-section mb-3">
                        <div class="pet-form-section__header mb-3">
                            <span class="pet-form-step"><i class="bi bi-images"></i></span>
                            <div>
                                <h2 class="h5 mb-0">Fotos</h2>
                                <p class="mb-0">Até 3 imagens do item</p>
                            </div>
                        </div>

                        @include('marketplace.partials.images-upload', [
                            'imagesInputId' => $imagesInputId,
                            'existingImages' => $existingImages,
                        ])
                    </section>

                    <div class="pet-form-tip">
                        <i class="bi bi-shield-check"></i>
                        <div>
                            <strong>Anúncio visível no condomínio</strong>
                            <p class="mb-0">Somente moradores do seu condomínio verão o anúncio. Interessados entrarão em contato pelo WhatsApp informado.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
