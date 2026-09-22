<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class CondominiumLibraryDocument extends Model
{
    use SoftDeletes;

    public const SOURCE_UPLOAD = 'upload';

    public const SOURCE_REGULATION = 'regulation';

    protected $fillable = [
        'condominium_id',
        'internal_regulation_id',
        'title',
        'description',
        'source',
        'content',
        'file_path',
        'file_mime',
        'file_size',
        'search_text',
        'sort_order',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'file_size' => 'integer',
        'sort_order' => 'integer',
    ];

    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class);
    }

    public function internalRegulation(): BelongsTo
    {
        return $this->belongsTo(InternalRegulation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForCondominium(Builder $query, int $condominiumId): Builder
    {
        return $query->where('condominium_id', $condominiumId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    public function isFile(): bool
    {
        return $this->file_path !== null && $this->source === self::SOURCE_UPLOAD;
    }

    public function isRegulation(): bool
    {
        return $this->source === self::SOURCE_REGULATION;
    }

    public function isPdf(): bool
    {
        return $this->file_mime === 'application/pdf'
            || str_ends_with(strtolower((string) $this->file_path), '.pdf');
    }

    public function publicFileUrl(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return route('library-documents.file', $this);
    }

    public function deleteStoredFile(): void
    {
        if ($this->file_path && Storage::disk('local')->exists($this->file_path)) {
            Storage::disk('local')->delete($this->file_path);
        }
    }
}
