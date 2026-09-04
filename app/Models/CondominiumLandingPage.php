<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CondominiumLandingPage extends Model
{
    protected $fillable = [
        'condominium_id',
        'slug',
        'custom_domain',
        'is_published',
        'hero_title',
        'hero_subtitle',
        'hero_image',
        'hero_gallery',
        'tagline',
        'about_title',
        'about_content',
        'accent_color',
        'contact_phone',
        'contact_email',
        'contact_whatsapp',
        'show_rides_feed',
        'show_marketplace_feed',
        'show_platform_news',
        'show_announcements_feed',
        'settings',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'hero_gallery' => 'array',
        'show_rides_feed' => 'boolean',
        'show_marketplace_feed' => 'boolean',
        'show_platform_news' => 'boolean',
        'show_announcements_feed' => 'boolean',
        'settings' => 'array',
        'published_at' => 'datetime',
    ];

    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CondominiumLandingItem::class, 'landing_page_id');
    }

    public function publishedItems(): HasMany
    {
        return $this->items()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderByDesc('created_at');
    }

    public function activePopupItems(): Collection
    {
        return $this->items()
            ->where('is_published', true)
            ->where(function ($query) {
                $query->where('is_popup', true)
                    ->orWhere('type', CondominiumLandingItem::TYPE_POPUP);
            })
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get()
            ->filter(fn (CondominiumLandingItem $item) => $item->isPopupActive())
            ->values();
    }

    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'condominio';
        $slug = $base;
        $counter = 1;

        while (
            static::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function publicUrl(): string
    {
        if ($this->custom_domain) {
            $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'https';

            return "{$scheme}://{$this->custom_domain}";
        }

        return route('condominium.landing', $this->slug);
    }

    public static function normalizeDomain(?string $domain): ?string
    {
        if (!$domain) {
            return null;
        }

        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = rtrim($domain, '/');

        return $domain ?: null;
    }
}
