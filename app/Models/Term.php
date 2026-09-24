<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Term extends Model
{
    public const TYPE_TERMS_OF_USE = 'terms_of_use';
    public const TYPE_PRIVACY_POLICY = 'privacy_policy';
    public const TYPE_MEDIA_CONSENT = 'media_consent';

    protected $fillable = [
        'type',
        'title',
        'is_required',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function versions(): HasMany
    {
        return $this->hasMany(TermVersion::class);
    }

    public function activeVersion(): ?TermVersion
    {
        return $this->versions()
            ->where('is_active', true)
            ->orderByDesc('published_at')
            ->first();
    }
}
