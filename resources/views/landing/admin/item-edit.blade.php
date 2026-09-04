@extends('layouts.app')

@section('content')
<style>
    .landing-item-edit-page {
        max-width: 980px;
        margin: 0 auto;
        padding-bottom: 5rem;
    }

    .landing-item-edit-actions {
        position: sticky;
        bottom: 0;
        z-index: 30;
        margin-top: 1.5rem;
        padding: 1rem 1.25rem;
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 1rem;
        box-shadow: 0 -8px 30px rgba(0, 0, 0, 0.08);
        backdrop-filter: blur(8px);
    }

    .landing-item-preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
        gap: 0.75rem;
    }

    .landing-item-preview-grid img {
        width: 100%;
        height: 88px;
        object-fit: cover;
        border-radius: 0.75rem;
        border: 1px solid rgba(0, 0, 0, 0.08);
    }
</style>

<div class="container-fluid py-4 landing-item-edit-page">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <a href="{{ route('condominium.landing.edit') }}#tab-conteudo" class="text-decoration-none small d-inline-flex align-items-center gap-1 mb-2">
                <i class="bi bi-arrow-left"></i> Voltar para conteúdos
            </a>
            <h1 class="h3 mb-1"><i class="bi bi-pencil-square me-2"></i>Editar conteúdo</h1>
            <p class="text-muted mb-0">{{ $itemTypes[$item->type] ?? $item->type }} · {{ $item->title }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if($page->is_published)
                <a href="{{ $page->publicUrl() }}" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-box-arrow-up-right"></i> Ver landing
                </a>
            @endif
            <button type="submit" form="landingItemEditForm" class="btn btn-primary btn-sm">
                <i class="bi bi-save"></i> Salvar
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="landingItemEditForm"
        method="POST"
        action="{{ route('condominium.landing.items.update', $item) }}"
        enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white"><strong>Informações principais</strong></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" for="item_type">Tipo</label>
                            <select name="type" id="item_type" class="form-select" required>
                                @foreach($itemTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('type', $item->type) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="item_title">Título</label>
                            <input type="text" name="title" id="item_title" class="form-control" value="{{ old('title', $item->title) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="item_subtitle">Subtítulo</label>
                            <input type="text" name="subtitle" id="item_subtitle" class="form-control" value="{{ old('subtitle', $item->subtitle) }}">
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="item_content">Conteúdo</label>
                            <textarea name="content" id="item_content" rows="10" class="form-control">{{ old('content', $item->content) }}</textarea>
                            <div class="form-text">Texto completo exibido na landing page e no modal de notícias.</div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white"><strong>Imagens</strong></div>
                    <div class="card-body">
                        @if($item->image_path)
                            <div class="mb-3">
                                <label class="form-label d-block">Imagem principal atual</label>
                                <img src="{{ $item->imageUrl() }}" alt="{{ $item->title }}" class="rounded" style="max-height:180px;max-width:100%;object-fit:cover">
                            </div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label" for="item_image">Substituir imagem principal</label>
                            <input type="file" name="image" id="item_image" class="form-control" accept="image/*">
                        </div>
                        @if(count($item->imageUrls()) > 0)
                            <div class="mb-3">
                                <label class="form-label d-block">Imagens extras atuais</label>
                                <div class="landing-item-preview-grid">
                                    @foreach($item->imageUrls() as $imageUrl)
                                        <img src="{{ $imageUrl }}" alt="">
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <div class="mb-0">
                            <label class="form-label" for="item_images">Adicionar imagens extras</label>
                            <input type="file" name="images[]" id="item_images" class="form-control" accept="image/*" multiple>
                            <div class="form-text">Útil para obras, galerias e álbuns com várias fotos.</div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4" id="itemEventSection">
                    <div class="card-header bg-white"><strong>Agenda do evento</strong></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="item_event_starts_at">Início</label>
                                <input type="datetime-local"
                                    name="event_starts_at"
                                    id="item_event_starts_at"
                                    class="form-control"
                                    value="{{ old('event_starts_at', $item->event_starts_at?->format('Y-m-d\TH:i')) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="item_event_ends_at">Fim</label>
                                <input type="datetime-local"
                                    name="event_ends_at"
                                    id="item_event_ends_at"
                                    class="form-control"
                                    value="{{ old('event_ends_at', $item->event_ends_at?->format('Y-m-d\TH:i')) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white"><strong>Popup na landing</strong></div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="is_popup" value="0">
                            <input class="form-check-input" type="checkbox" name="is_popup" value="1" id="item_is_popup" @checked(old('is_popup', $item->is_popup))>
                            <label class="form-check-label" for="item_is_popup">Exibir como popup na landing</label>
                        </div>
                        <p class="small text-muted">Vários popups ativos são exibidos em sequência na ordem da lista.</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="item_popup_starts_at">Popup início</label>
                                <input type="datetime-local"
                                    name="popup_starts_at"
                                    id="item_popup_starts_at"
                                    class="form-control"
                                    value="{{ old('popup_starts_at', $item->popup_starts_at?->format('Y-m-d\TH:i')) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="item_popup_ends_at">Popup fim</label>
                                <input type="datetime-local"
                                    name="popup_ends_at"
                                    id="item_popup_ends_at"
                                    class="form-control"
                                    value="{{ old('popup_ends_at', $item->popup_ends_at?->format('Y-m-d\TH:i')) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white"><strong>Publicação</strong></div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="is_published" value="0">
                            <input class="form-check-input" type="checkbox" name="is_published" value="1" id="item_is_published" @checked(old('is_published', $item->is_published))>
                            <label class="form-check-label" for="item_is_published">Publicado na landing</label>
                        </div>
                        <ul class="list-unstyled small text-muted mb-0">
                            <li class="mb-2"><i class="bi bi-tag me-1"></i> Tipo: {{ $itemTypes[$item->type] ?? $item->type }}</li>
                            <li class="mb-2"><i class="bi bi-sort-numeric-down me-1"></i> Ordem: {{ $item->sort_order }}</li>
                            <li><i class="bi bi-clock me-1"></i> Atualizado: {{ $item->updated_at?->format('d/m/Y H:i') }}</li>
                        </ul>
                    </div>
                </div>

                <div class="card shadow-sm border-0 border-danger-subtle">
                    <div class="card-header bg-white text-danger"><strong>Zona de risco</strong></div>
                    <div class="card-body">
                        <p class="small text-muted">A remoção é permanente e retira o conteúdo da landing page.</p>
                        <button type="submit"
                            form="landingItemDeleteForm"
                            class="btn btn-outline-danger w-100"
                            onclick="return confirm('Remover este conteúdo permanentemente?')">
                            <i class="bi bi-trash"></i> Excluir conteúdo
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="landing-item-edit-actions d-flex flex-wrap justify-content-between align-items-center gap-2">
            <a href="{{ route('condominium.landing.edit') }}#tab-conteudo" class="btn btn-outline-secondary">
                Cancelar
            </a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-save"></i> Salvar alterações
            </button>
        </div>
    </form>

    <form id="landingItemDeleteForm"
        method="POST"
        action="{{ route('condominium.landing.items.remove', $item) }}"
        class="d-none">
        @csrf
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const typeSelect = document.getElementById('item_type');
    const eventSection = document.getElementById('itemEventSection');

    const syncSections = () => {
        if (!typeSelect || !eventSection) {
            return;
        }

        eventSection.style.display = typeSelect.value === 'event' ? '' : 'none';
    };

    typeSelect?.addEventListener('change', syncSections);
    syncSections();
});
</script>
@endpush
