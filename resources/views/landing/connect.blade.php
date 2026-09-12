@extends('layouts.landing-connect')

@section('content')
<div class="cch-shell">
    <header class="cch-header">
        <div class="cch-header-inner">
            <a href="#inicio" class="cch-brand" aria-label="{{ $condominium->name }} — início">
                <span class="cch-brand-mark">{{ $brandInitials ?: 'CC' }}</span>
                <span class="cch-brand-name">{{ $condominium->name }}</span>
            </a>
            @if($sections->isNotEmpty())
                <nav class="cch-nav" aria-label="Navegação principal">
                    @foreach($sections as $section)
                        <a href="#{{ $section['id'] }}">{{ $section['label'] }}</a>
                    @endforeach
                </nav>
            @endif
            <div class="cch-header-actions">
                <a href="#portal" class="cch-btn cch-btn-primary cch-btn-hidden-sm">Entrar no SindCon</a>
                <button type="button" class="cch-btn cch-btn-frost cch-btn-icon" data-cch-menu-toggle aria-expanded="false" aria-label="Abrir menu">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>
        @if($sections->isNotEmpty())
            <nav class="cch-mobile-nav" aria-label="Navegação mobile">
                @foreach($sections as $section)
                    <a href="#{{ $section['id'] }}">{{ $section['label'] }}</a>
                @endforeach
                <a href="#portal" class="cch-btn cch-btn-primary" style="width:100%;margin-top:1rem;">Entrar no SindCon</a>
            </nav>
        @endif
    </header>

    <main class="cch-main" id="inicio">
        <section class="cch-hero-wrap">
            <div class="cch-container">
                <div class="cch-hero-card">
                    <div class="cch-hero-grid">
                        <div class="cch-hero-copy">
                            <span class="cch-badge">{{ $page->tagline ?? 'Portal oficial do condomínio' }}</span>
                            @if($page->hero_subtitle)
                                <p class="cch-hero-tagline">{{ $page->hero_subtitle }}</p>
                            @endif
                            <h1 class="cch-hero-title">
                                {!! nl2br(e($page->hero_title ?? 'Vida em comunidade, em um só lugar.')) !!}
                            </h1>
                            <p class="cch-hero-text">
                                Avisos, eventos, obras e novidades em uma experiência leve e organizada para moradores e visitantes.
                            </p>
                            <div class="cch-hero-actions">
                                @if($sections->contains(fn ($section) => $section['id'] === 'avisos'))
                                    <a href="#avisos" class="cch-btn cch-btn-primary cch-btn-lg">Ver avisos <i class="bi bi-arrow-right"></i></a>
                                @endif
                                <a href="#portal" class="cch-btn cch-btn-frost cch-btn-lg">Acessar moradores</a>
                            </div>
                        </div>
                        <div class="cch-hero-media">
                            <img src="{{ $heroImages->first() }}" alt="{{ $condominium->name }}" fetchpriority="high">
                            <div class="cch-hero-media-caption">{{ $page->about_title ?? 'Arquitetura, natureza e bem-estar' }}</div>
                        </div>
                    </div>
                    <div class="cch-stats">
                        <div class="cch-stat">
                            <p class="cch-stat-value cch-display">{{ $statsNotices }}</p>
                            <p class="cch-stat-label">Avisos ativos</p>
                        </div>
                        <div class="cch-stat">
                            <p class="cch-stat-value cch-display">{{ $statsEvents }}</p>
                            <p class="cch-stat-label">Eventos</p>
                        </div>
                        <div class="cch-stat">
                            <p class="cch-stat-value cch-display">{{ $statsConstruction }}</p>
                            <p class="cch-stat-label">Fases de obras</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if(filled($page->about_content) || filled($page->about_title))
        <section class="cch-section cch-section-soft" id="sobre">
            <div class="cch-container">
                <div class="cch-about-grid">
                    <div>
                        <img src="{{ $aboutImage }}" alt="{{ $condominium->name }}" class="cch-about-image" loading="lazy">
                    </div>
                    <div>
                        <div class="cch-section-title-wrap">
                            <p class="cch-kicker">Conheça</p>
                            <h2 class="cch-title">{{ $page->about_title ?? 'Um lugar pensado para viver bem' }}</h2>
                            @if($page->about_content)
                                <p class="cch-lead">{!! nl2br(e($page->about_content)) !!}</p>
                            @endif
                        </div>
                        <div class="cch-contact-list">
                            @if($condominium->address)
                                <p><i class="bi bi-geo-alt"></i> {{ $condominium->address }}@if($condominium->city) · {{ $condominium->city }}/{{ $condominium->state }}@endif</p>
                            @endif
                            @if($page->contact_phone ?? $condominium->phone)
                                <p><i class="bi bi-telephone"></i> {{ $page->contact_phone ?? $condominium->phone }}</p>
                            @endif
                            @if($page->contact_email ?? $condominium->email)
                                <p><i class="bi bi-envelope"></i> {{ $page->contact_email ?? $condominium->email }}</p>
                            @endif
                            @if($page->contact_whatsapp)
                                <p><i class="bi bi-whatsapp"></i> {{ $page->contact_whatsapp }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif

        @if($notices->isNotEmpty() || $announcements->isNotEmpty())
        <section class="cch-section" id="avisos">
            <div class="cch-container">
                <div class="cch-section-title-wrap">
                    <p class="cch-kicker">Importante</p>
                    <h2 class="cch-title">Avisos do condomínio</h2>
                    <p class="cch-lead">Comunicados relevantes, organizados para uma leitura rápida e confortável.</p>
                </div>
                <div class="cch-grid-3">
                    @foreach($notices as $index => $notice)
                        <article class="cch-card">
                            @if($notice->image_path)
                                <img src="{{ $notice->imageUrl() }}" alt="{{ $notice->title }}" class="cch-card-cover" loading="lazy">
                            @endif
                            <div class="cch-card-head">
                                <span class="cch-card-icon"><i class="bi bi-bell"></i></span>
                                <span class="cch-card-index">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <p class="cch-card-tag">Aviso</p>
                            <h3 class="cch-card-title">{{ $notice->title }}</h3>
                            @if($notice->subtitle)<p class="cch-card-text"><strong>{{ $notice->subtitle }}</strong></p>@endif
                            <p class="cch-card-text">{!! nl2br(e($notice->content)) !!}</p>
                        </article>
                    @endforeach
                    @foreach($announcements as $index => $announcement)
                        @php $message = $announcement->messages->first(); @endphp
                        <article class="cch-card">
                            <div class="cch-card-head">
                                <span class="cch-card-icon"><i class="bi bi-broadcast"></i></span>
                                <span class="cch-card-index">{{ str_pad((string) ($notices->count() + $index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <p class="cch-card-tag">SindCon</p>
                            <h3 class="cch-card-title">{{ $announcement->subject ?? 'Comunicado oficial' }}</h3>
                            <p class="cch-card-text">{{ \Illuminate\Support\Str::limit(strip_tags($message?->message ?? ''), 220) }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        @if($news->isNotEmpty() || $platformNews->isNotEmpty())
        <section class="cch-section cch-section-primary" id="noticias">
            <div class="cch-container">
                <div class="cch-section-title-wrap">
                    <p class="cch-kicker">Novidades</p>
                    <h2 class="cch-title">Notícias e atualizações</h2>
                    <p class="cch-lead">O que acontece no condomínio, com informações oficiais da administração.</p>
                </div>
                <div class="cch-news-grid">
                    @foreach($platformNews as $item)
                        <article class="cch-news-card landing-news-card"
                            data-news-id="platform-{{ $item->id }}"
                            role="button"
                            tabindex="0"
                            aria-label="Abrir notícia: {{ $item->title }}">
                            @if($item->imageUrl())
                                <img src="{{ $item->imageUrl() }}" alt="{{ $item->title }}" class="cch-news-cover" loading="lazy">
                            @endif
                            <p class="cch-news-date">{{ $item->published_at?->format('d M') ?? now()->format('d M') }}</p>
                            <h3 class="cch-news-title">{{ $item->title }}</h3>
                            <p class="cch-card-text" style="color:rgba(248,250,252,.65);">{{ \Illuminate\Support\Str::limit(strip_tags($item->content ?? ''), 160) }}</p>
                            <span class="cch-news-link">Ler notícia <i class="bi bi-chevron-right"></i></span>
                        </article>
                    @endforeach
                    @foreach($news as $item)
                        <article class="cch-news-card landing-news-card"
                            data-news-id="news-{{ $item->id }}"
                            role="button"
                            tabindex="0"
                            aria-label="Abrir notícia: {{ $item->title }}">
                            @if($item->image_path)
                                <img src="{{ $item->imageUrl() }}" alt="{{ $item->title }}" class="cch-news-cover" loading="lazy">
                            @endif
                            <p class="cch-news-date">{{ $item->created_at?->format('d M') ?? now()->format('d M') }}</p>
                            <h3 class="cch-news-title">{{ $item->title }}</h3>
                            <p class="cch-card-text" style="color:rgba(248,250,252,.65);">{{ \Illuminate\Support\Str::limit(strip_tags($item->content ?? ''), 160) }}</p>
                            <span class="cch-news-link">Ler notícia <i class="bi bi-chevron-right"></i></span>
                        </article>
                    @endforeach
                </div>
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
        <section class="cch-section" id="eventos">
            <div class="cch-container">
                <div class="cch-section-title-wrap">
                    <p class="cch-kicker">Agenda</p>
                    <h2 class="cch-title">Eventos e encontros</h2>
                    <p class="cch-lead">Datas importantes para participar e fortalecer a vida em comunidade.</p>
                </div>
                <div class="cch-events-grid">
                    @foreach($events as $index => $event)
                        <article class="cch-event-card {{ $index === 0 ? 'is-featured' : '' }}">
                            <div class="cch-event-top">
                                @if($event->event_starts_at)
                                    <div>
                                        <p class="cch-event-day cch-display">{{ $event->event_starts_at->format('d') }}</p>
                                        <p class="cch-event-month">{{ strtoupper($event->event_starts_at->translatedFormat('M')) }}</p>
                                    </div>
                                @endif
                                <i class="bi bi-calendar-event" style="opacity:.6;font-size:1.35rem;"></i>
                            </div>
                            <h3 class="cch-event-title">{{ $event->title }}</h3>
                            @if($event->subtitle || $event->event_location)
                                <p class="cch-event-meta">
                                    <i class="bi bi-clock"></i>
                                    {{ $event->subtitle ?? $event->event_location }}
                                    @if($event->event_starts_at)
                                        · {{ $event->event_starts_at->format('H\hi') }}
                                    @endif
                                </p>
                            @endif
                            @if($event->content)
                                <p class="cch-card-text">{{ \Illuminate\Support\Str::limit(strip_tags($event->content), 140) }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        @if($construction->isNotEmpty())
        <section class="cch-section cch-section-soft" id="obras">
            <div class="cch-container">
                <div class="cch-section-title-wrap">
                    <p class="cch-kicker">Transparência</p>
                    <h2 class="cch-title">Obras e reformas</h2>
                    <p class="cch-lead">Acompanhe cada fase e saiba o que está sendo melhorado.</p>
                </div>
                <div class="cch-works-grid">
                    @if($featuredWork)
                        <div class="cch-works-featured">
                            <i class="bi bi-hard-hat" style="font-size:1.75rem;"></i>
                            <p class="cch-kicker" style="margin-top:2rem;color:rgba(248,250,252,.6);">Fase atual</p>
                            <h3 class="cch-title" style="color:#f8fafc;">{{ $featuredWork->title }}</h3>
                            @if($featuredWork->subtitle)<p style="opacity:.85;">{{ $featuredWork->subtitle }}</p>@endif
                            @if($featuredWork->content)
                                <p style="margin-top:1rem;line-height:1.7;opacity:.65;">{!! nl2br(e($featuredWork->content)) !!}</p>
                            @endif
                            @if($workProgress > 0)
                                <div class="cch-progress">
                                    <div style="display:flex;justify-content:space-between;font-size:.875rem;">
                                        <span>Progresso</span>
                                        <strong>{{ $workProgress }}%</strong>
                                    </div>
                                    <div class="cch-progress-bar">
                                        <div class="cch-progress-fill" style="width:{{ min(100, $workProgress) }}%;"></div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                    @if($otherWorks->isNotEmpty())
                        <div class="cch-works-list">
                            @foreach($otherWorks as $phase)
                                <article class="cch-card">
                                    <p class="cch-card-tag">{{ $phase->metadata['phase'] ?? 'Fase da obra' }}</p>
                                    <h3 class="cch-card-title">{{ $phase->title }}</h3>
                                    @if($phase->subtitle)<p class="cch-card-text"><strong>{{ $phase->subtitle }}</strong></p>@endif
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>
        @endif

        @if($gallery->isNotEmpty() && $featuredGallery)
        <section class="cch-section" id="galeria">
            <div class="cch-container">
                <div class="cch-section-title-wrap">
                    <p class="cch-kicker">Galeria</p>
                    <h2 class="cch-title">Momentos do condomínio</h2>
                </div>
                <figure class="cch-gallery-figure landing-gallery-slide"
                    data-gallery-index="0"
                    role="button"
                    tabindex="0"
                    aria-label="Ampliar foto da galeria">
                    <img src="{{ $featuredGallery['url'] }}" alt="{{ $featuredGallery['title'] ?? $condominium->name }}" loading="lazy">
                    <figcaption class="cch-gallery-caption">
                        <h3 class="cch-title" style="color:#f8fafc;font-size:1.35rem;">{{ $featuredGallery['title'] ?? 'Galeria do condomínio' }}</h3>
                        @if(filled($featuredGallery['caption']))
                            <p style="margin:.5rem 0 0;font-size:.875rem;opacity:.7;">{{ $featuredGallery['caption'] }}</p>
                        @endif
                    </figcaption>
                </figure>
            </div>

            @if($gallerySlides->isNotEmpty())
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
        </section>
        @endif

        @if($rides->isNotEmpty() || $marketplace->isNotEmpty())
        <section class="cch-section cch-section-soft" id="comunidade">
            <div class="cch-container">
                <div class="cch-section-title-wrap">
                    <p class="cch-kicker">Comunidade</p>
                    <h2 class="cch-title">Caronas e marketplace</h2>
                    <p class="cch-lead">Conexões úteis entre moradores, com oportunidades perto de casa.</p>
                </div>
                <div class="cch-community-grid">
                    @if($rides->isNotEmpty())
                        <div class="cch-card">
                            <h3 class="cch-card-title"><i class="bi bi-car-front" style="color:var(--cch-accent);"></i> Caronas disponíveis</h3>
                            <div class="cch-feed-list">
                                @foreach($rides as $ride)
                                    <div class="cch-feed-item">
                                        <div>
                                            <strong>{{ $ride->destination }}</strong>
                                            <small>{{ $ride->departure_at?->format('d/m/Y H:i') }} · {{ $ride->seats_available }} vaga(s)</small>
                                        </div>
                                        <a href="{{ $landingDeepLink('rides.index', ['carona' => $ride->id]) }}" class="cch-btn cch-btn-frost cch-btn-sm">Ver</a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if($marketplace->isNotEmpty())
                        <div class="cch-card">
                            <h3 class="cch-card-title"><i class="bi bi-bag" style="color:var(--cch-accent);"></i> Marketplace</h3>
                            <div class="cch-feed-list">
                                @foreach($marketplace as $item)
                                    <div class="cch-feed-item">
                                        <div>
                                            <p class="cch-card-tag">{{ $marketplaceCategories[$item->category] ?? ($item->category ?? 'Item') }}</p>
                                            <strong>{{ $item->title }}</strong>
                                        </div>
                                        <strong style="font-size:.875rem;">R$ {{ number_format((float) $item->price, 2, ',', '.') }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
        @endif

        @foreach($customBlocks as $block)
            <section class="cch-section">
                <div class="cch-container">
                    <div class="cch-section-title-wrap">
                        <p class="cch-kicker">Destaque</p>
                        <h2 class="cch-title">{{ $block->title }}</h2>
                        @if($block->subtitle)<p class="cch-lead"><strong>{{ $block->subtitle }}</strong></p>@endif
                        @if($block->content)<p class="cch-lead">{!! nl2br(e($block->content)) !!}</p>@endif
                    </div>
                </div>
            </section>
        @endforeach

        <section class="cch-portal" id="portal">
            <i class="bi bi-building" style="font-size:2rem;opacity:.7;"></i>
            <h2 class="cch-title">Moradores, acessem o SindCon</h2>
            <p class="cch-lead">Reservas, cobranças, comunicados e muito mais — tudo integrado ao portal do condomínio.</p>
            <a href="{{ route('login') }}" class="cch-btn cch-btn-frost cch-btn-lg" style="margin-top:2rem;">Entrar agora <i class="bi bi-arrow-right"></i></a>
            <p class="cch-portal-note">Síndico, indique o SindCon e ganhe 50% de desconto na próxima mensalidade.</p>
        </section>
    </main>

    <footer class="cch-footer">
        <div class="cch-container cch-footer-grid">
            <div>
                <div class="cch-brand">
                    <span class="cch-brand-mark">{{ $brandInitials ?: 'CC' }}</span>
                    <strong class="cch-display">{{ $condominium->name }}</strong>
                </div>
                <p>Portal oficial do condomínio powered by SindCon.</p>
            </div>
            <div>
                <p class="cch-kicker">Endereço</p>
                <p>{{ $condominium->address }}@if($condominium->city)<br>{{ $condominium->city }}/{{ $condominium->state }}@endif</p>
            </div>
            <div>
                <p class="cch-kicker">Contato</p>
                <p>{{ $page->contact_phone ?? $condominium->phone }}<br>{{ $page->contact_email ?? $condominium->email }}</p>
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
