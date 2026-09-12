<?php

namespace App\Services;

use App\Models\Condominium;
use App\Models\CondominiumLandingItem;
use App\Models\CondominiumLandingPage;
use App\Models\Conversation;
use App\Models\MarketplaceItem;
use App\Models\PlatformAnnouncement;
use App\Models\Ride;
use App\Support\PublicAssetUrl;
use Illuminate\Support\Collection;

class CondominiumLandingService
{
    public function findOrCreateForCondominium(Condominium $condominium): CondominiumLandingPage
    {
        $page = CondominiumLandingPage::query()
            ->where('condominium_id', $condominium->id)
            ->first();

        if ($page) {
            return $page;
        }

        return CondominiumLandingPage::create([
            'condominium_id' => $condominium->id,
            'slug' => CondominiumLandingPage::generateUniqueSlug($condominium->name),
            'template' => CondominiumLandingPage::TEMPLATE_CLASSIC,
            'hero_title' => $condominium->name,
            'hero_subtitle' => $condominium->city && $condominium->state
                ? "{$condominium->city} / {$condominium->state}"
                : null,
            'about_title' => 'Sobre o condomínio',
            'about_content' => $condominium->description,
            'contact_phone' => $condominium->phone,
            'contact_email' => $condominium->email,
        ]);
    }

    public function findPublishedBySlug(string $slug): ?CondominiumLandingPage
    {
        return CondominiumLandingPage::query()
            ->with(['condominium', 'publishedItems'])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->whereHas('condominium', fn ($q) => $q->where('is_active', true))
            ->first();
    }

    public function buildPublicPayload(CondominiumLandingPage $page): array
    {
        $condominium = $page->condominium;
        $items = $page->publishedItems;

        $payload = [
            'page' => $page,
            'condominium' => $condominium,
            'notices' => $items->where('type', CondominiumLandingItem::TYPE_NOTICE)->values(),
            'news' => $items->where('type', CondominiumLandingItem::TYPE_NEWS)->values(),
            'events' => $items->where('type', CondominiumLandingItem::TYPE_EVENT)->values(),
            'construction' => $items->where('type', CondominiumLandingItem::TYPE_CONSTRUCTION)->values(),
            'gallery' => $items->where('type', CondominiumLandingItem::TYPE_GALLERY)->values(),
            'customBlocks' => $items->where('type', CondominiumLandingItem::TYPE_CUSTOM)->values(),
            'activePopups' => $page->activePopupItems(),
            'platformNews' => $page->show_platform_news ? $this->platformNews() : collect(),
            'rides' => $page->show_rides_feed ? $this->recentRides($condominium->id) : collect(),
            'marketplace' => $page->show_marketplace_feed ? $this->recentMarketplace($condominium->id) : collect(),
            'announcements' => $page->show_announcements_feed ? $this->recentAnnouncements($condominium->id) : collect(),
        ];

        return $this->enrichPublicViewData($payload);
    }

    public function enrichPublicViewData(array $payload): array
    {
        /** @var CondominiumLandingPage $page */
        $page = $payload['page'];
        $gallery = $payload['gallery'];
        $notices = $payload['notices'];
        $news = $payload['news'];
        $platformNews = $payload['platformNews'];
        $events = $payload['events'];
        $construction = $payload['construction'];
        $rides = $payload['rides'];
        $marketplace = $payload['marketplace'];
        $condominium = $payload['condominium'];

        $gallerySlides = $gallery->flatMap(function ($photo) {
            return collect($photo->imageUrls())->map(fn ($url) => [
                'url' => $url,
                'title' => $photo->title,
                'caption' => $photo->subtitle,
            ]);
        })->values();

        $heroImages = collect($page->hero_gallery ?? [])
            ->when($page->hero_image, fn ($collection) => $collection->prepend($page->hero_image))
            ->map(fn ($path) => PublicAssetUrl::storage($path))
            ->filter()
            ->values();

        if ($heroImages->isEmpty()) {
            $heroImages = $gallerySlides->pluck('url')->filter()->unique()->values();
        }

        if ($heroImages->isEmpty()) {
            $heroImages = collect([
                'https://images.unsplash.com/photo-1545324417-cc1a3fa10c00?auto=format&fit=crop&w=1600&q=80',
            ]);
        }

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
            ['id' => 'avisos', 'label' => 'Avisos', 'visible' => $notices->isNotEmpty() || $payload['announcements']->isNotEmpty()],
            ['id' => 'noticias', 'label' => 'Notícias', 'visible' => $news->isNotEmpty() || $platformNews->isNotEmpty()],
            ['id' => 'eventos', 'label' => 'Eventos', 'visible' => $events->isNotEmpty()],
            ['id' => 'obras', 'label' => 'Obras', 'visible' => $construction->isNotEmpty()],
            ['id' => 'galeria', 'label' => 'Galeria', 'visible' => $gallery->isNotEmpty()],
            ['id' => 'comunidade', 'label' => 'Comunidade', 'visible' => $rides->isNotEmpty() || $marketplace->isNotEmpty()],
        ])->where('visible', true)->values();

        $brandInitials = collect(preg_split('/\s+/u', trim($condominium->name)))
            ->filter()
            ->map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->take(2)
            ->implode('');

        $featuredWork = $construction->first();

        return array_merge($payload, [
            'heroImages' => $heroImages,
            'gallerySlides' => $gallerySlides,
            'landingDeepLink' => fn (string $routeName, array $params = []): string => auth()->check()
                ? route($routeName, $params)
                : route('login', ['redirect' => route($routeName, $params)]),
            'marketplaceCategories' => [
                'products' => 'Produtos',
                'services' => 'Serviços',
                'jobs' => 'Empregos',
                'real_estate' => 'Imóveis',
                'vehicles' => 'Veículos',
                'other' => 'Outros',
            ],
            'landingNewsItems' => $landingNewsItems,
            'sections' => $sections,
            'brandInitials' => $brandInitials,
            'aboutImage' => $heroImages->get(1, $heroImages->first()),
            'featuredGallery' => $gallerySlides->first(),
            'featuredWork' => $featuredWork,
            'otherWorks' => $construction->slice(1)->values(),
            'statsNotices' => str_pad((string) ($notices->count() + $payload['announcements']->count()), 2, '0', STR_PAD_LEFT),
            'statsEvents' => str_pad((string) $events->count(), 2, '0', STR_PAD_LEFT),
            'statsConstruction' => str_pad((string) $construction->count(), 2, '0', STR_PAD_LEFT),
            'workProgress' => (int) ($featuredWork?->metadata['progress'] ?? 0),
        ]);
    }

    public function platformNews(): Collection
    {
        return PlatformAnnouncement::published()->limit(6)->get();
    }

    public function recentRides(int $condominiumId): Collection
    {
        return Ride::query()
            ->with('driver:id,name')
            ->byCondominium($condominiumId)
            ->where('status', Ride::STATUS_OPEN)
            ->where('departure_at', '>=', now())
            ->orderBy('departure_at')
            ->limit(6)
            ->get();
    }

    public function recentMarketplace(int $condominiumId): Collection
    {
        return MarketplaceItem::query()
            ->where('condominium_id', $condominiumId)
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();
    }

    public function recentAnnouncements(int $condominiumId): Collection
    {
        return Conversation::query()
            ->where('condominium_id', $condominiumId)
            ->where('type', 'announcement')
            ->with(['messages' => fn ($q) => $q->latest()->limit(1)])
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();
    }
}
