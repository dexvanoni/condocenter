@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="bi bi-globe2 me-2"></i>Landing Page do Condomínio</h1>
            <p class="text-muted mb-0">Publique avisos, eventos, obras, fotos e novidades para moradores e visitantes.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if($page->is_published)
                <a href="{{ $page->publicUrl() }}" target="_blank" class="btn btn-outline-primary">
                    <i class="bi bi-box-arrow-up-right"></i> Ver página pública
                </a>
                <a href="{{ route('condominium.landing.qr') }}" target="_blank" class="btn btn-outline-secondary">
                    <i class="bi bi-qr-code"></i> QR Code
                </a>
                <a href="{{ route('condominium.landing.qr.download') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-download"></i> Baixar QR
                </a>
            @else
                <span class="badge bg-warning text-dark align-self-center">Rascunho — publique para tornar visível</span>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-geral" type="button">Geral</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-conteudo" type="button">Conteúdos</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-integracoes" type="button">Integrações</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-dominio" type="button">Domínio & QR</button></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="tab-geral">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form method="POST" action="{{ route('condominium.landing.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Título principal</label>
                                <input type="text" name="hero_title" class="form-control" value="{{ old('hero_title', $page->hero_title) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Slug da URL</label>
                                <div class="input-group">
                                    <span class="input-group-text">/c/</span>
                                    <input type="text" name="slug" class="form-control" value="{{ old('slug', $page->slug) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Subtítulo</label>
                                <input type="text" name="hero_subtitle" class="form-control" value="{{ old('hero_subtitle', $page->hero_subtitle) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tagline (badge no hero)</label>
                                <input type="text" name="tagline" class="form-control" value="{{ old('tagline', $page->tagline) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cor de destaque</label>
                                <input type="color" name="accent_color" class="form-control form-control-color w-100" value="{{ old('accent_color', $page->accent_color) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Imagem principal do hero</label>
                                <input type="file" name="hero_image" class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Galeria do hero (múltiplas)</label>
                                <input type="file" name="hero_gallery[]" class="form-control" accept="image/*" multiple>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Sobre — título</label>
                                <input type="text" name="about_title" class="form-control" value="{{ old('about_title', $page->about_title) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Sobre — texto</label>
                                <textarea name="about_content" rows="5" class="form-control">{{ old('about_content', $page->about_content) }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Telefone</label>
                                <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone', $page->contact_phone) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">E-mail</label>
                                <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $page->contact_email) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">WhatsApp</label>
                                <input type="text" name="contact_whatsapp" class="form-control" value="{{ old('contact_whatsapp', $page->contact_whatsapp) }}">
                            </div>
                            <div class="col-12">
                                <input type="hidden" name="show_rides_feed" value="{{ $page->show_rides_feed ? 1 : 0 }}">
                                <input type="hidden" name="show_marketplace_feed" value="{{ $page->show_marketplace_feed ? 1 : 0 }}">
                                <input type="hidden" name="show_platform_news" value="{{ $page->show_platform_news ? 1 : 0 }}">
                                <input type="hidden" name="show_announcements_feed" value="{{ $page->show_announcements_feed ? 1 : 0 }}">
                                <input type="hidden" name="is_published" value="0">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_published" value="1" id="is_published" @checked(old('is_published', $page->is_published))>
                                    <label class="form-check-label" for="is_published">Publicar landing page</label>
                                </div>
                            </div>
                        </div>

                        @if($page->hero_image)
                            <div class="mt-3">
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($page->hero_image) }}" alt="Hero" class="rounded" style="max-height:160px">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="remove_hero_image" value="1" id="remove_hero_image">
                                    <label class="form-check-label" for="remove_hero_image">Remover imagem principal</label>
                                </div>
                            </div>
                        @endif

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Salvar configurações</button>
                        </div>
                    </form>

                    @if(!empty($page->hero_gallery))
                        <div class="d-flex flex-wrap gap-2 mt-3 pt-3 border-top">
                            @foreach($page->hero_gallery as $galleryImage)
                                <div class="position-relative">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($galleryImage) }}" alt="" class="rounded" style="height:90px;width:120px;object-fit:cover">
                                    <button type="submit"
                                        form="landing-gallery-remove-{{ $loop->index }}"
                                        class="btn btn-sm btn-outline-danger mt-1"
                                        onclick="return confirm('Remover esta imagem da galeria?')">
                                        Remover
                                    </button>
                                </div>
                            @endforeach
                        </div>
                        @foreach($page->hero_gallery as $galleryImage)
                            <form id="landing-gallery-remove-{{ $loop->index }}"
                                method="POST"
                                action="{{ route('condominium.landing.gallery.remove') }}"
                                class="d-none">
                                @csrf
                                <input type="hidden" name="path" value="{{ $galleryImage }}">
                            </form>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-conteudo">
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white"><strong>Novo conteúdo</strong></div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('condominium.landing.items.store') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Tipo</label>
                                    <select name="type" class="form-select" required>
                                        @foreach($itemTypes as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Título</label>
                                    <input type="text" name="title" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Subtítulo</label>
                                    <input type="text" name="subtitle" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Conteúdo</label>
                                    <textarea name="content" rows="4" class="form-control"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Imagem principal</label>
                                    <input type="file" name="image" class="form-control" accept="image/*">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Imagens extras (obras/galeria)</label>
                                    <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label">Início evento</label>
                                        <input type="datetime-local" name="event_starts_at" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Fim evento</label>
                                        <input type="datetime-local" name="event_ends_at" class="form-control">
                                    </div>
                                </div>
                                <div class="form-check form-switch mt-3">
                                    <input type="hidden" name="is_popup" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_popup" value="1" id="is_popup">
                                    <label class="form-check-label" for="is_popup">Exibir como popup na landing</label>
                                </div>
                                <p class="small text-muted mb-0">Vários popups são exibidos em sequência (ordem da lista). Fechar um popup mostra o próximo ativo.</p>
                                <div class="row g-2 mt-2">
                                    <div class="col-md-6">
                                        <label class="form-label">Popup início</label>
                                        <input type="datetime-local" name="popup_starts_at" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Popup fim</label>
                                        <input type="datetime-local" name="popup_ends_at" class="form-control">
                                    </div>
                                </div>
                                <div class="form-check form-switch mt-3">
                                    <input type="hidden" name="is_published" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_published" value="1" id="item_published" checked>
                                    <label class="form-check-label" for="item_published">Publicado</label>
                                </div>
                                <button type="submit" class="btn btn-success mt-3"><i class="bi bi-plus-circle"></i> Adicionar</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <strong>Conteúdos publicados</strong>
                            <small class="text-muted"><i class="bi bi-grip-vertical"></i> Arraste para reordenar</small>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead>
                                        <tr>
                                            <th style="width:36px"></th>
                                            <th>Tipo</th>
                                            <th>Título</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="landingItemsSortable">
                                        @forelse($page->items as $item)
                                            <tr data-item-id="{{ $item->id }}">
                                                <td class="text-muted landing-drag-handle" style="cursor:grab"><i class="bi bi-grip-vertical"></i></td>
                                                <td><span class="badge bg-light text-dark">{{ $itemTypes[$item->type] ?? $item->type }}</span></td>
                                                <td>
                                                    <strong>{{ $item->title }}</strong>
                                                    @if($item->is_popup)<span class="badge bg-info ms-1">Popup</span>@endif
                                                </td>
                                                <td>
                                                    @if($item->is_published)
                                                        <span class="badge bg-success">Publicado</span>
                                                    @else
                                                        <span class="badge bg-secondary">Rascunho</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <a href="{{ route('condominium.landing.items.edit', $item) }}" class="btn btn-sm btn-outline-primary me-1">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button type="submit"
                                                        form="landing-delete-item-{{ $item->id }}"
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Remover este conteúdo?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted py-4">Nenhum conteúdo cadastrado ainda.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @foreach($page->items as $item)
            <form id="landing-delete-item-{{ $item->id }}"
                method="POST"
                action="{{ route('condominium.landing.items.remove', $item) }}"
                class="d-none">
                @csrf
            </form>
        @endforeach

        <div class="tab-pane fade" id="tab-integracoes">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form method="POST" action="{{ route('condominium.landing.update') }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="is_published" value="{{ $page->is_published ? 1 : 0 }}">
                        <p class="text-muted">Escolha quais feeds automáticos aparecem na landing page pública.</p>
                        <div class="form-check form-switch mb-2">
                            <input type="hidden" name="show_announcements_feed" value="0">
                            <input class="form-check-input" type="checkbox" name="show_announcements_feed" value="1" id="show_announcements_feed" @checked($page->show_announcements_feed)>
                            <label class="form-check-label" for="show_announcements_feed">Comunicados oficiais do SindCon (avisos)</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input type="hidden" name="show_platform_news" value="0">
                            <input class="form-check-input" type="checkbox" name="show_platform_news" value="1" id="show_platform_news" @checked($page->show_platform_news)>
                            <label class="form-check-label" for="show_platform_news">Novidades da plataforma SindCon</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input type="hidden" name="show_rides_feed" value="0">
                            <input class="form-check-input" type="checkbox" name="show_rides_feed" value="1" id="show_rides_feed" @checked($page->show_rides_feed)>
                            <label class="form-check-label" for="show_rides_feed">Caronas disponíveis</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="show_marketplace_feed" value="0">
                            <input class="form-check-input" type="checkbox" name="show_marketplace_feed" value="1" id="show_marketplace_feed" @checked($page->show_marketplace_feed)>
                            <label class="form-check-label" for="show_marketplace_feed">Itens do marketplace</label>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Salvar integrações</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-dominio">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white"><strong>Domínio personalizado</strong></div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('condominium.landing.update') }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="is_published" value="{{ $page->is_published ? 1 : 0 }}">
                                <input type="hidden" name="show_rides_feed" value="{{ $page->show_rides_feed ? 1 : 0 }}">
                                <input type="hidden" name="show_marketplace_feed" value="{{ $page->show_marketplace_feed ? 1 : 0 }}">
                                <input type="hidden" name="show_platform_news" value="{{ $page->show_platform_news ? 1 : 0 }}">
                                <input type="hidden" name="show_announcements_feed" value="{{ $page->show_announcements_feed ? 1 : 0 }}">
                                <div class="mb-3">
                                    <label class="form-label">Domínio (sem http://)</label>
                                    <input type="text" name="custom_domain" class="form-control" value="{{ old('custom_domain', $page->custom_domain) }}" placeholder="portal.meucondominio.com.br">
                                    <div class="form-text">
                                        Aponte um registro CNAME ou A do domínio para o servidor do SindCon.
                                        Visitantes acessarão a landing diretamente na raiz do domínio.
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Salvar domínio</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white"><strong>QR Code do portal</strong></div>
                        <div class="card-body text-center">
                            @if($page->is_published)
                                <img src="{{ route('condominium.landing.qr') }}" alt="QR Code" class="img-fluid mb-3" style="max-width:260px">
                                <p class="text-muted small mb-3">Escaneie para abrir: {{ $page->publicUrl() }}</p>
                                <a href="{{ route('condominium.landing.qr.download') }}" class="btn btn-outline-primary">
                                    <i class="bi bi-download"></i> Baixar PNG para impressão
                                </a>
                            @else
                                <p class="text-muted mb-0">Publique a landing page na aba Geral para gerar o QR Code.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const sortableEl = document.getElementById('landingItemsSortable');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    if (sortableEl && sortableEl.querySelector('[data-item-id]')) {
        Sortable.create(sortableEl, {
            handle: '.landing-drag-handle',
            animation: 180,
            onEnd: async () => {
                const items = [...sortableEl.querySelectorAll('[data-item-id]')].map((row) => row.dataset.itemId);
                await fetch(@json(route('condominium.landing.items.reorder')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ items }),
                });
            },
        });
    }

    if (window.location.hash === '#tab-conteudo') {
        document.querySelector('[data-bs-target="#tab-conteudo"]')?.click();
    }
});
</script>
@endpush
