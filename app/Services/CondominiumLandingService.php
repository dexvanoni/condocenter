<?php

namespace App\Services;

use App\Models\Condominium;
use App\Models\CondominiumLandingItem;
use App\Models\CondominiumLandingPage;
use App\Models\Conversation;
use App\Models\MarketplaceItem;
use App\Models\PlatformAnnouncement;
use App\Models\Ride;
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

        return [
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
