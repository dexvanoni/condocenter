<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_LEVE = 'leve';
    public const TYPE_PESADO = 'pesado';
    public const TYPE_CAIXA_GRANDE = 'caixa_grande';
    public const TYPE_FRAGIL = 'fragil';

    public const STATUS_PENDING = 'pending';
    public const STATUS_COLLECTED = 'collected';

    public const TYPES = [
        self::TYPE_LEVE,
        self::TYPE_PESADO,
        self::TYPE_CAIXA_GRANDE,
        self::TYPE_FRAGIL,
    ];

    public const TYPE_LABELS = [
        self::TYPE_LEVE => 'Leve',
        self::TYPE_PESADO => 'Pesado',
        self::TYPE_CAIXA_GRANDE => 'Caixa Grande',
        self::TYPE_FRAGIL => 'Frágil',
    ];

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_COLLECTED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Pendente',
        self::STATUS_COLLECTED => 'Retirada',
    ];

    protected $fillable = [
        'condominium_id',
        'unit_id',
        'registered_by',
        'type',
        'received_at',
        'collected_at',
        'collected_by',
        'status',
        'notification_sent',
        'sender',
        'tracking_code',
        'description',
        'notes',
    ];

    protected $appends = [
        'type_label',
        'status_label',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'collected_at' => 'datetime',
        'notification_sent' => 'boolean',
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeByCondominium($query, int $condominiumId)
    {
        return $query->where('condominium_id', $condominiumId);
    }

    public function scopeForUnit($query, int $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCollected(): bool
    {
        return $this->status === self::STATUS_COLLECTED;
    }

    public static function typeLabels(): array
    {
        return self::TYPE_LABELS;
    }

    public static function statusLabels(): array
    {
        return self::STATUS_LABELS;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function markAsCollected($userId)
    {
        $this->update([
            'status' => self::STATUS_COLLECTED,
            'collected_at' => now(),
            'collected_by' => $userId,
        ]);
    }
}
