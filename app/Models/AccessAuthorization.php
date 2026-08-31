<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccessAuthorization extends Model
{
    public const TYPE_ALLOW = 'allow';
    public const TYPE_DENY = 'deny';

    public const STATUS_PENDING = 'pending';
    public const STATUS_ENTERED = 'entered';
    public const STATUS_DENIED = 'denied';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'condominium_id',
        'unit_id',
        'authorized_by',
        'notify_user_id',
        'visitor_name',
        'visitor_document',
        'authorization_type',
        'never_expires',
        'scheduled_at',
        'valid_until',
        'expires_at',
        'status',
        'processed_by',
        'processed_at',
        'porteiro_notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'valid_until' => 'datetime',
            'expires_at' => 'datetime',
            'processed_at' => 'datetime',
            'never_expires' => 'boolean',
        ];
    }

    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function authorizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }

    public function notifyUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notify_user_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function scopeForCondominium($query, int $condominiumId)
    {
        return $query->where('condominium_id', $condominiumId);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where(function ($query) {
                $query->where('never_expires', true)
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeAllows($query)
    {
        return $query->where('authorization_type', self::TYPE_ALLOW);
    }

    public function scopeProhibitions($query)
    {
        return $query->where('authorization_type', self::TYPE_DENY);
    }

    public function isDeny(): bool
    {
        return $this->authorization_type === self::TYPE_DENY;
    }

    public function isAllow(): bool
    {
        return $this->authorization_type === self::TYPE_ALLOW;
    }

    public function isExpired(): bool
    {
        if ($this->never_expires || $this->expires_at === null) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    public function expirationLabel(): string
    {
        if ($this->never_expires) {
            return 'Nunca expira';
        }

        return $this->expires_at?->format('d/m/Y') ?? '—';
    }
}
