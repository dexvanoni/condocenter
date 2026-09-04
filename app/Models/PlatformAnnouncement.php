<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PlatformAnnouncement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'image_path',
        'link_url',
        'badge_label',
        'is_published',
        'starts_at',
        'ends_at',
        'sort_order',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function scopePublished(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('is_published', true)
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->orderBy('sort_order')
            ->orderByDesc('created_at');
    }

    public function imageUrl(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->image_path);
    }
}
