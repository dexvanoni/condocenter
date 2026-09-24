<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TermVersion extends Model
{
    protected $fillable = [
        'term_id',
        'version',
        'title',
        'content',
        'content_hash',
        'published_at',
        'is_active',
        'published_by',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (TermVersion $version) {
            if ($version->isDirty('content') || !$version->content_hash) {
                $version->content_hash = hash('sha256', (string) $version->content);
            }
        });
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function acceptances(): HasMany
    {
        return $this->hasMany(TermAcceptance::class);
    }

    public function publish(?User $publisher = null): void
    {
        $this->term->versions()->where('id', '!=', $this->id)->update(['is_active' => false]);

        $this->forceFill([
            'is_active' => true,
            'published_at' => $this->published_at ?? now(),
            'published_by' => $publisher?->id ?? $this->published_by,
            'content_hash' => hash('sha256', (string) $this->content),
        ])->save();
    }
}
