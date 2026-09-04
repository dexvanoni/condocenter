<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class OccurrenceBookEntry extends Model
{
    use SoftDeletes;

    public const TYPE_OCCURRENCE = 'occurrence';
    public const TYPE_CRITICISM = 'criticism';
    public const TYPE_SUGGESTION = 'suggestion';

    public const TYPES = [
        self::TYPE_OCCURRENCE => 'Ocorrência',
        self::TYPE_CRITICISM => 'Crítica',
        self::TYPE_SUGGESTION => 'Sugestão',
    ];

    public const TYPE_ICONS = [
        self::TYPE_OCCURRENCE => 'exclamation-octagon',
        self::TYPE_CRITICISM => 'chat-square-text',
        self::TYPE_SUGGESTION => 'lightbulb',
    ];

    protected $fillable = [
        'condominium_id',
        'user_id',
        'unit_id',
        'type',
        'title',
        'body',
        'photo_path',
        'notify_whatsapp',
        'whatsapp_notified_at',
        'acknowledged_at',
        'acknowledged_by',
        'acknowledgment_note',
        'syndic_comment',
        'show_syndic_comment_publicly',
        'syndic_commented_at',
        'syndic_commented_by',
    ];

    protected $casts = [
        'notify_whatsapp' => 'boolean',
        'whatsapp_notified_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'show_syndic_comment_publicly' => 'boolean',
        'syndic_commented_at' => 'datetime',
    ];

    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function syndicCommentedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'syndic_commented_by');
    }

    public function hasSyndicComment(): bool
    {
        return filled($this->syndic_comment);
    }

    public function publicSyndicComment(): ?string
    {
        return $this->show_syndic_comment_publicly ? $this->syndic_comment : null;
    }

    public function isAcknowledged(): bool
    {
        return $this->acknowledged_at !== null;
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function typeIcon(): string
    {
        return self::TYPE_ICONS[$this->type] ?? 'journal-text';
    }

    public function typeBadgeClass(): string
    {
        return match ($this->type) {
            self::TYPE_OCCURRENCE => 'bg-danger',
            self::TYPE_CRITICISM => 'bg-warning text-dark',
            self::TYPE_SUGGESTION => 'bg-info text-dark',
            default => 'bg-secondary',
        };
    }

    public function acknowledgmentBadgeClass(): string
    {
        return $this->isAcknowledged() ? 'bg-success' : 'bg-warning text-dark';
    }

    public function acknowledgmentLabel(): string
    {
        return $this->isAcknowledged() ? 'Ciência registrada' : 'Aguardando ciência';
    }

    public function referenceCode(): string
    {
        return sprintf('OB-%s-%04d', $this->created_at?->format('Y') ?? now()->format('Y'), $this->id);
    }

    public function hasPhoto(): bool
    {
        return filled($this->photo_path);
    }

    public function photoUrl(): ?string
    {
        if (!$this->photo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->photo_path);
    }

    public function scopeForCondominium($query, int $condominiumId)
    {
        return $query->where('condominium_id', $condominiumId);
    }

    public function scopePendingAcknowledgment($query)
    {
        return $query->whereNull('acknowledged_at');
    }
}
