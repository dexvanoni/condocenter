<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class CondominiumLandingItem extends Model
{
    use SoftDeletes;

    public const TYPE_NOTICE = 'notice';
    public const TYPE_NEWS = 'news';
    public const TYPE_EVENT = 'event';
    public const TYPE_CONSTRUCTION = 'construction';
    public const TYPE_GALLERY = 'gallery';
    public const TYPE_POPUP = 'popup';
    public const TYPE_CUSTOM = 'custom';

    public const TYPES = [
        self::TYPE_NOTICE => 'Aviso',
        self::TYPE_NEWS => 'Notícia',
        self::TYPE_EVENT => 'Evento',
        self::TYPE_CONSTRUCTION => 'Obra / Reforma',
        self::TYPE_GALLERY => 'Galeria',
        self::TYPE_POPUP => 'Popup',
        self::TYPE_CUSTOM => 'Bloco personalizado',
    ];

    protected $fillable = [
        'landing_page_id',
        'type',
        'title',
        'subtitle',
        'content',
        'image_path',
        'images',
        'event_starts_at',
        'event_ends_at',
        'is_popup',
        'popup_starts_at',
        'popup_ends_at',
        'is_featured',
        'is_published',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'images' => 'array',
        'event_starts_at' => 'datetime',
        'event_ends_at' => 'datetime',
        'is_popup' => 'boolean',
        'popup_starts_at' => 'datetime',
        'popup_ends_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'metadata' => 'array',
    ];

    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(CondominiumLandingPage::class, 'landing_page_id');
    }

    public function imageUrl(?string $path = null): ?string
    {
        $path = $path ?? $this->image_path;

        if (!$path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    public function imageUrls(): array
    {
        $urls = [];

        if ($this->image_path) {
            $urls[] = $this->imageUrl();
        }

        foreach ($this->images ?? [] as $image) {
            if (is_string($image) && $image !== '') {
                $urls[] = Storage::disk('public')->url($image);
            }
        }

        return array_values(array_unique($urls));
    }

    public function isPopupCandidate(): bool
    {
        return $this->is_popup || $this->type === self::TYPE_POPUP;
    }

    public function isPopupActive(): bool
    {
        if (!$this->is_published || !$this->isPopupCandidate()) {
            return false;
        }

        $now = now();

        if ($this->popup_starts_at && $now->lt($this->popup_starts_at)) {
            return false;
        }

        if ($this->popup_ends_at && $now->gt($this->popup_ends_at)) {
            return false;
        }

        return true;
    }

    public function popupStorageToken(): string
    {
        return (string) ($this->updated_at?->timestamp ?? $this->id);
    }
}
