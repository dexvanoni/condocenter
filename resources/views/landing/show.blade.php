@extends('layouts.landing')

@php
    $heroImages = collect($page->hero_gallery ?? [])
        ->when($page->hero_image, fn ($c) => $c->prepend($page->hero_image))
        ->map(fn ($path) => \Illuminate\Support\Facades\Storage::disk('public')->url($path))
        ->filter()
        ->values();

    if ($heroImages->isEmpty()) {
        $heroImages = collect([
            'https://images.unsplash.com/photo-1545324417-cc1a3fa10c00?auto=format&fit=crop&w=1600&q=80',
        ]);
    }

    $gallerySlides = $gallery->flatMap(function ($photo) {
        return collect($photo->imageUrls())->map(fn ($url) => [
            'url' => $url,
            'title' => $photo->title,
            'caption' => $photo->subtitle,
        ]);
    })->values();

    $landingDeepLink = function (string $routeName, array $params = []): string {
        $url = route($routeName, $params);

        if (auth()->check()) {
            return $url;
        }

        return route('login', ['redirect' => $url]);
    };

    $marketplaceCategories = [
        'products' => 'Produtos',
        'services' => 'Serviços',
        'jobs' => 'Empregos',
        'real_estate' => 'Imóveis',
        'vehicles' => 'Veículos',
        'other' => 'Outros',
    ];

    $landingNewsItems = collect();

    foreach ($platformNews as $item) {
        $landingNewsItems["platform-{$item->id}"] = [
            'tag' => $item->badge_label ?? 'SindCon',
            'tag_icon' => 'bi-stars',
            'title' => $item->title,
            'subtitle' => null,
            'content' => strip_tags($item->content ?? ''),
            'image' => $item->imageUrl(),
            'link_url' => $item->link_url,
        ];
    }

    foreach ($news as $item) {
        $landingNewsItems["news-{$item->id}"] = [
            'tag' => 'Notícia',
            'tag_icon' => 'bi-newspaper',
            'title' => $item->title,
            'subtitle' => $item->subtitle,
            'content' => $item->content ?? '',
            'image' => $item->image_path ? $item->imageUrl() : null,
            'link_url' => null,
        ];
    }

    $sections = collect([
        ['id' => 'sobre', 'label' => 'Sobre', 'visible' => filled($page->about_content) || filled($page->about_title)],
        ['id' => 'avisos', 'label' => 'Avisos', 'visible' => $notices->isNotEmpty() || $announcements->isNotEmpty()],
        ['id' => 'noticias', 'label' => 'Notícias', 'visible' => $news->isNotEmpty() || $platformNews->isNotEmpty()],
        ['id' => 'eventos', 'label' => 'Eventos', 'visible' => $events->isNotEmpty()],
        ['id' => 'obras', 'label' => 'Obras', 'visible' => $construction->isNotEmpty()],
        ['id' => 'galeria', 'label' => 'Galeria', 'visible' => $gallery->isNotEmpty()],
        ['id' => 'comunidade', 'label' => 'Comunidade', 'visible' => $rides->isNotEmpty() || $marketplace->isNotEmpty()],
    ])->where('visible', true);
@endphp

@section('content')
<div class="landing-shell">
    <header class="landing-nav">
        <div class="landing-nav-inner">
            <a href="#" class="landing-brand">
                <span class="landing-brand-mark"><i class="bi bi-buildings"></i></span>
                <span>{{ $condominium->name }}</span>
            </a>

            <nav class="landing-nav-links">
                @foreach($sections as $section)
                    <a href="#{{ $section['id'] }}">{{ $section['label'] }}</a>
                @endforeach
            </nav>

            <div class="landing-nav-actions">
                <a href="{{ route('login') }}" class="landing-btn landing-btn-outline d-none d-md-inline-flex">Entrar no SindCon</a>
                <button type="button" class="landing-menu-toggle" data-landing-menu-toggle aria-label="Abrir menu">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>
    </header>

    <div class="landing-mobile-drawer">
        <div class="landing-mobile-backdrop"></div>
        <div class="landing-mobile-panel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <strong>{{ $condominium->name }}</strong>
                <button type="button" class="landing-menu-toggle" data-landing-menu-close aria-label="Fechar menu">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            @foreach($sections as $section)
                <a href="#{{ $section['id'] }}">{{ $section['label'] }}</a>
            @endforeach
            <a href="{{ route('login') }}">Entrar no SindCon</a>
        </div>
    </div>

    <section class="landing-hero">
        <div class="landing-hero-media">
            @foreach($heroImages as $index => $imageUrl)
                <div class="landing-hero-slide {{ $index === 0 ? 'is-active' : '' }}" style="background-image:url('{{ $imageUrl }}')"></div>
            @endforeach
        </div>
        <div class="landing-hero-overlay"></div>
        <div class="landing-hero-content landing-reveal is-visible">
            @if($page->tagline)
                <div class="landing-hero-badge"><i class="bi bi-stars"></i> {{ $page->tagline }}</div>
            @endif
            <h1 class="landing-hero-title">{{ $page->hero_title ?? $condominium->name }}</h1>
            <p class="landing-hero-subtitle">
                {{ $page->hero_subtitle ?? 'Portal oficial do condomínio — avisos, eventos, obras e novidades da comunidade em um só lugar.' }}
            </p>
            <div class="landing-hero-actions">
                <a href="#avisos" class="landing-btn landing-btn-primary"><i class="bi bi-megaphone"></i> Ver avisos</a>
                <a href="{{ route('login') }}" class="landing-btn landing-btn-ghost"><i class="bi bi-box-arrow-in-right"></i> Acessar moradores</a>
            </div>
            <div class="landing-hero-stats">
                <div class="landing-stat-card">
                    <strong>{{ $notices->count() + $announcements->count() }}</strong>
                    <span>Avisos ativos</span>
                </div>
                <div class="landing-stat-card">
                    <strong>{{ $events->count() }}</strong>
                    <span>Eventos publicados</span>
                </div>
                <div class="landing-stat-card">
                    <strong>{{ $construction->count() }}</strong>
                    <span>Fases de obras</span>
                </div>
            </div>
        </div>
    </section>

    @if(filled($page->about_content) || filled($page->about_title))
    <section class="landing-section" id="sobre">
        <div class="landing-container landing-about landing-reveal">
            <div>
                <span class="landing-section-kicker">Conheça</span>
                <h2 class="landing-section-title">{{ $page->about_title ?? 'Sobre o condomínio' }}</h2>
                <p class="landing-section-desc">{!! nl2br(e($page->about_content ?? $condominium->description)) !!}</p>
                <div class="mt-4 d-flex flex-wrap gap-2">
                    @if($condominium->address)
                        <span class="landing-card-tag"><i class="bi bi-geo-alt"></i> {{ $condominium->address }}, {{ $condominium->city }}/{{ $condominium->state }}</span>
                    @endif
                    @if($page->contact_phone)
                        <span class="landing-card-tag"><i class="bi bi-telephone"></i> {{ $page->contact_phone }}</span>
                    @endif
                </div>
            </div>
            <div class="landing-about-panel">
                <h3 class="h5 mb-3">Informações rápidas</h3>
                <div class="landing-feed-list">
                    <div class="landing-feed-item">
                        <div class="landing-feed-icon"><i class="bi bi-building"></i></div>
                        <div>
                            <strong>{{ $condominium->name }}</strong>
                            <p class="landing-card-text mb-0">Portal oficial alimentado pelo SindCon.</p>
                        </div>
                    </div>
                    @if($page->contact_email)
                    <div class="landing-feed-item">
                        <div class="landing-feed-icon"><i class="bi bi-envelope"></i></div>
                        <div>
                            <strong>Contato</strong>
                            <p class="landing-card-text mb-0">{{ $page->contact_email }}</p>
                        </div>
                    </div>
                    @endif
                    @if($page->contact_whatsapp)
                    <div class="landing-feed-item">
                        <div class="landing-feed-icon"><i class="bi bi-whatsapp"></i></div>
                        <div>
                            <strong>WhatsApp</strong>
                            <p class="landing-card-text mb-0">{{ $page->contact_whatsapp }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
    @endif

    @if($notices->isNotEmpty() || $announcements->isNotEmpty())
    <section class="landing-section" id="avisos" style="background:#fff;">
        <div class="landing-container landing-reveal">
            <div class="landing-section-head">
                <div>
                    <span class="landing-section-kicker">Importante</span>
                    <h2 class="landing-section-title">Avisos do condomínio</h2>
                </div>
            </div>
            @component('landing.partials.card-carousel', ['carouselId' => 'avisos-carousel'])
                @foreach($notices as $notice)
                    <div class="landing-carousel-slide">
                        <article class="landing-card h-100">
                            @if($notice->image_path)
                                <div class="landing-card-media" style="background-image:url('{{ $notice->imageUrl() }}')"></div>
                            @endif
                            <div class="landing-card-body">
                                <span class="landing-card-tag"><i class="bi bi-exclamation-circle"></i> Aviso</span>
                                <h3 class="landing-card-title">{{ $notice->title }}</h3>
                                @if($notice->subtitle)<p class="fw-semibold">{{ $notice->subtitle }}</p>@endif
                                <p class="landing-card-text">{!! nl2br(e($notice->content)) !!}</p>
                            </div>
                        </article>
                    </div>
                @endforeach
                @foreach($announcements as $announcement)
                    @php $message = $announcement->messages->first(); @endphp
                    <div class="landing-carousel-slide">
                        <article class="landing-card h-100">
                            <div class="landing-card-body">
                                <span class="landing-card-tag"><i class="bi bi-broadcast"></i> SindCon</span>
                                <h3 class="landing-card-title">{{ $announcement->subject ?? 'Comunicado oficial' }}</h3>
                                <p class="landing-card-text">{{ \Illuminate\Support\Str::limit(strip_tags($message?->message ?? ''), 220) }}</p>
                            </div>
                        </article>
                    </div>
                @endforeach
            @endcomponent
        </div>
    </section>
    @endif

    @if($news->isNotEmpty() || $platformNews->isNotEmpty())
    <section class="landing-section" id="noticias">
        <div class="landing-container landing-reveal">
            <div class="landing-section-head">
                <div>
                    <span class="landing-section-kicker">Novidades</span>
                    <h2 class="landing-section-title">Notícias e atualizações</h2>
                </div>
            </div>
            @component('landing.partials.card-carousel', ['carouselId' => 'noticias-carousel'])
                @foreach($platformNews as $item)
                    <div class="landing-carousel-slide">
                        <article class="landing-card landing-news-card h-100"
                            data-news-id="platform-{{ $item->id }}"
                            role="button"
                            tabindex="0"
                            aria-label="Abrir notícia: {{ $item->title }}">
                            @if($item->imageUrl())
                                <div class="landing-card-media" style="background-image:url('{{ $item->imageUrl() }}')"></div>
                            @endif
                            <div class="landing-card-body">
                                <span class="landing-card-tag"><i class="bi bi-stars"></i> {{ $item->badge_label ?? 'SindCon' }}</span>
                                <h3 class="landing-card-title">{{ $item->title }}</h3>
                                <p class="landing-card-text">{{ \Illuminate\Support\Str::limit(strip_tags($item->content ?? ''), 180) }}</p>
                            </div>
                        </article>
                    </div>
                @endforeach
                @foreach($news as $item)
                    <div class="landing-carousel-slide">
                        <article class="landing-card landing-news-card h-100"
                            data-news-id="news-{{ $item->id }}"
                            role="button"
                            tabindex="0"
                            aria-label="Abrir notícia: {{ $item->title }}">
                            @if($item->image_path)
                                <div class="landing-card-media" style="background-image:url('{{ $item->imageUrl() }}')"></div>
                            @endif
                            <div class="landing-card-body">
                                <span class="landing-card-tag"><i class="bi bi-newspaper"></i> Notícia</span>
                                <h3 class="landing-card-title">{{ $item->title }}</h3>
                                <p class="landing-card-text">{!! nl2br(e(\Illuminate\Support\Str::limit($item->content ?? '', 220))) !!}</p>
                            </div>
                        </article>
                    </div>
                @endforeach
            @endcomponent
        </div>
    </section>

    @push('landing-modals')
        <script type="application/json" id="landing-news-data">@json($landingNewsItems)</script>
        <div class="landing-news-modal" id="landingNewsModal" hidden aria-hidden="true">
            <div class="landing-news-modal-backdrop" data-news-modal-close></div>
            <div class="landing-news-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="landingNewsModalTitle">
                <button type="button" class="landing-news-modal-close" data-news-modal-close aria-label="Fechar notícia">
                    <i class="bi bi-x-lg"></i>
                </button>
                <div class="landing-news-modal-media" id="landingNewsModalMedia" hidden></div>
                <div class="landing-news-modal-body">
                    <span class="landing-card-tag" id="landingNewsModalTag"></span>
                    <h3 class="landing-news-modal-title" id="landingNewsModalTitle"></h3>
                    <p class="landing-news-modal-subtitle fw-semibold" id="landingNewsModalSubtitle" hidden></p>
                    <div class="landing-news-modal-content landing-card-text" id="landingNewsModalContent"></div>
                    <a id="landingNewsModalLink" class="landing-btn landing-btn-outline mt-3" target="_blank" rel="noopener" hidden>Saiba mais</a>
                </div>
            </div>
        </div>
    @endpush
    @endif

    @if($events->isNotEmpty())
    <section class="landing-section" id="eventos" style="background:#fff;">
        <div class="landing-container landing-reveal">
            <div class="landing-section-head">
                <div>
                    <span class="landing-section-kicker">Agenda</span>
                    <h2 class="landing-section-title">Eventos e encontros</h2>
                </div>
            </div>
            <div class="landing-grid landing-grid-2">
                @foreach($events as $event)
                    <article class="landing-card">
                        <div class="landing-card-body landing-event-card">
                            @if($event->event_starts_at)
                                <div class="landing-event-date">
                                    <strong>{{ $event->event_starts_at->format('d') }}</strong>
                                    <span>{{ $event->event_starts_at->translatedFormat('M') }}</span>
                                </div>
                            @endif
                            <div>
                                <h3 class="landing-card-title">{{ $event->title }}</h3>
                                @if($event->subtitle)<p class="fw-semibold mb-2">{{ $event->subtitle }}</p>@endif
                                <p class="landing-card-text">{!! nl2br(e($event->content)) !!}</p>
                                @if($event->event_starts_at)
                                    <p class="landing-card-text mt-2 mb-0"><i class="bi bi-clock"></i> {{ $event->event_starts_at->format('d/m/Y H:i') }}</p>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if($construction->isNotEmpty())
    <section class="landing-section" id="obras">
        <div class="landing-container landing-reveal">
            <div class="landing-section-head">
                <div>
                    <span class="landing-section-kicker">Transparência</span>
                    <h2 class="landing-section-title">Obras e reformas</h2>
                    <p class="landing-section-desc">Acompanhe as fases do condomínio com fotos e atualizações oficiais.</p>
                </div>
            </div>
            <div class="landing-timeline">
                @foreach($construction as $phase)
                    <div class="landing-timeline-item">
                        <div class="landing-card">
                            <div class="landing-card-body">
                                <span class="landing-card-tag"><i class="bi bi-hammer"></i> {{ $phase->metadata['phase'] ?? 'Fase da obra' }}</span>
                                <h3 class="landing-card-title">{{ $phase->title }}</h3>
                                @if($phase->subtitle)<p class="fw-semibold">{{ $phase->subtitle }}</p>@endif
                                <p class="landing-card-text">{!! nl2br(e($phase->content)) !!}</p>
                                @if(count($phase->imageUrls()) > 0)
                                    <div class="landing-grid landing-grid-3 mt-3">
                                        @foreach($phase->imageUrls() as $imageUrl)
                                            <div class="landing-card-media" style="background-image:url('{{ $imageUrl }}')"></div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if($gallery->isNotEmpty())
    <section class="landing-section" id="galeria" style="background:#fff;">
        <div class="landing-container landing-reveal">
            <div class="landing-section-head">
                <div>
                    <span class="landing-section-kicker">Galeria</span>
                    <h2 class="landing-section-title">Momentos do condomínio</h2>
                </div>
            </div>
            @if($gallerySlides->isNotEmpty())
            <div class="landing-slideshow" data-landing-slideshow>
                <div class="landing-slideshow-viewport">
                    @foreach($gallerySlides as $index => $slide)
                        <figure class="landing-slideshow-slide landing-gallery-slide {{ $index === 0 ? 'is-active' : '' }}"
                            data-gallery-index="{{ $index }}"
                            role="button"
                            tabindex="0"
                            aria-label="Ampliar foto {{ $index + 1 }}">
                            <img src="{{ $slide['url'] }}" alt="{{ $slide['title'] }}" loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
                            @if(filled($slide['title']) || filled($slide['caption']))
                                <figcaption class="landing-slideshow-caption">
                                    @if(filled($slide['title']))<strong>{{ $slide['title'] }}</strong>@endif
                                    @if(filled($slide['caption']))<span>{{ $slide['caption'] }}</span>@endif
                                </figcaption>
                            @endif
                        </figure>
                    @endforeach
                </div>
                @if($gallerySlides->count() > 1)
                    <button type="button" class="landing-slideshow-btn landing-slideshow-prev" data-slideshow-prev aria-label="Foto anterior">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button type="button" class="landing-slideshow-btn landing-slideshow-next" data-slideshow-next aria-label="Próxima foto">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                    <div class="landing-slideshow-dots">
                        @foreach($gallerySlides as $index => $slide)
                            <button type="button"
                                class="landing-slideshow-dot {{ $index === 0 ? 'is-active' : '' }}"
                                data-slideshow-dot="{{ $index }}"
                                aria-label="Ir para foto {{ $index + 1 }}"></button>
                        @endforeach
                    </div>
                @endif
            </div>

            @push('landing-modals')
                <script type="application/json" id="landing-gallery-data">@json($gallerySlides->values())</script>
                <div class="landing-gallery-modal" id="landingGalleryModal" hidden aria-hidden="true">
                    <div class="landing-gallery-modal-backdrop" data-gallery-modal-close></div>
                    <div class="landing-gallery-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="landingGalleryModalTitle">
                        <button type="button" class="landing-gallery-modal-close" data-gallery-modal-close aria-label="Fechar galeria">
                            <i class="bi bi-x-lg"></i>
                        </button>
                        @if($gallerySlides->count() > 1)
                            <button type="button" class="landing-gallery-modal-nav landing-gallery-modal-prev" data-gallery-modal-prev aria-label="Foto anterior">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <button type="button" class="landing-gallery-modal-nav landing-gallery-modal-next" data-gallery-modal-next aria-label="Próxima foto">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        @endif
                        <figure class="landing-gallery-modal-figure">
                            <img id="landingGalleryModalImage" src="" alt="" class="landing-gallery-modal-image">
                            <figcaption class="landing-gallery-modal-caption" id="landingGalleryModalCaption" hidden></figcaption>
                        </figure>
                        <div class="landing-gallery-modal-meta">
                            <strong id="landingGalleryModalTitle"></strong>
                            <span id="landingGalleryModalCounter"></span>
                        </div>
                    </div>
                </div>
            @endpush
            @endif
        </div>
    </section>
    @endif

    @if($rides->isNotEmpty() || $marketplace->isNotEmpty())
    <section class="landing-section" id="comunidade">
        <div class="landing-container landing-reveal">
            <div class="landing-section-head">
                <div>
                    <span class="landing-section-kicker">Comunidade</span>
                    <h2 class="landing-section-title">Caronas e marketplace</h2>
                </div>
            </div>
            <div class="landing-grid landing-grid-2">
                @if($rides->isNotEmpty())
                    <div>
                        <h3 class="h5 mb-3"><i class="bi bi-car-front"></i> Caronas disponíveis</h3>
                        <div class="landing-feed-list">
                            @foreach($rides as $ride)
                                <div class="landing-feed-item">
                                    <div class="landing-feed-icon"><i class="bi bi-geo"></i></div>
                                    <div class="flex-grow-1">
                                        <strong>{{ $ride->destination }}</strong>
                                        <p class="landing-card-text mb-0">{{ $ride->departure_at?->format('d/m/Y H:i') }} · {{ $ride->seats_available }} vaga(s)</p>
                                    </div>
                                    <a href="{{ $landingDeepLink('rides.index', ['carona' => $ride->id]) }}" class="landing-btn landing-btn-outline landing-btn-sm">
                                        Ver
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if($marketplace->isNotEmpty())
                    <div>
                        <h3 class="h5 mb-3"><i class="bi bi-shop"></i> Marketplace</h3>
                        <div class="landing-grid landing-grid-2">
                            @foreach($marketplace as $item)
                                @php $itemImage = $item->image_urls[0] ?? null; @endphp
                                <article class="landing-card h-100">
                                    @if($itemImage)
                                        <div class="landing-card-media" style="background-image:url('{{ $itemImage }}')"></div>
                                    @endif
                                    <div class="landing-card-body d-flex flex-column">
                                        <span class="landing-card-tag">{{ $marketplaceCategories[$item->category] ?? ($item->category ?? 'Item') }}</span>
                                        <h3 class="landing-card-title">{{ $item->title }}</h3>
                                        <p class="landing-card-text mb-3">R$ {{ number_format((float) $item->price, 2, ',', '.') }}</p>
                                        <a href="{{ $landingDeepLink('marketplace.index', ['anuncio' => $item->id]) }}" class="landing-btn landing-btn-outline mt-auto align-self-start">
                                            Ver anúncio
                                        </a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
    @endif

    @foreach($customBlocks as $block)
        <section class="landing-section">
            <div class="landing-container landing-reveal">
                <div class="landing-about-panel">
                    <span class="landing-section-kicker">Destaque</span>
                    <h2 class="landing-section-title">{{ $block->title }}</h2>
                    @if($block->subtitle)<p class="fw-semibold">{{ $block->subtitle }}</p>@endif
                    <p class="landing-section-desc">{!! nl2br(e($block->content)) !!}</p>
                </div>
            </div>
        </section>
    @endforeach

    <section class="landing-cta landing-reveal">
        <h2>Moradores, acessem o SindCon</h2>
        <p class="mb-3">Reservas, cobranças, comunicados e muito mais — tudo integrado ao portal do condomínio.</p>
        <p class="landing-cta-promo mb-4">
            <i class="bi bi-gift"></i>
            <span><strong>Síndico, indique o SindCON</strong> e ganhe <strong>50% de desconto</strong> na sua assinatura do próximo mês!</span>
        </p>
        <a href="{{ route('login') }}" class="landing-btn landing-btn-primary"><i class="bi bi-box-arrow-in-right"></i> Entrar agora</a>
    </section>

    <footer class="landing-footer">
        <div class="landing-container landing-footer-grid">
            <div>
                <strong>{{ $condominium->name }}</strong>
                <p class="mb-0 mt-2">Portal oficial do condomínio powered by SindCon.</p>
            </div>
            <div>
                <strong>Endereço</strong>
                <p class="mb-0 mt-2">{{ $condominium->address }}<br>{{ $condominium->city }}/{{ $condominium->state }}</p>
            </div>
            <div>
                <strong>Contato</strong>
                <p class="mb-0 mt-2">{{ $page->contact_phone ?? $condominium->phone }}<br>{{ $page->contact_email ?? $condominium->email }}</p>
            </div>
        </div>
    </footer>
</div>

@if($activePopups->isNotEmpty())
@push('landing-popups')
    @foreach($activePopups as $popup)
        <div class="landing-popup"
            data-popup-key="{{ $page->slug }}"
            data-popup-id="{{ $popup->id }}"
            data-popup-version="{{ $popup->popupStorageToken() }}">
            <div class="landing-popup-card position-relative">
                <button type="button" class="landing-popup-close" data-popup-close aria-label="Fechar popup">
                    <i class="bi bi-x-lg"></i>
                </button>
                @if($popup->image_path)
                    <div class="landing-popup-media" style="background-image:url('{{ $popup->imageUrl() }}')"></div>
                @endif
                <div class="landing-popup-body">
                    <span class="landing-card-tag"><i class="bi bi-megaphone"></i> Aviso em destaque</span>
                    <h3 class="landing-card-title">{{ $popup->title }}</h3>
                    @if($popup->subtitle)<p class="fw-semibold">{{ $popup->subtitle }}</p>@endif
                    <p class="landing-card-text">{!! nl2br(e($popup->content)) !!}</p>
                </div>
            </div>
        </div>
    @endforeach
@endpush
@endif
@endsection
