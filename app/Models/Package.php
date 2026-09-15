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

    public const METHOD_MANUAL = 'manual';
    public const METHOD_OCR = 'ocr';
    public const METHOD_BARCODE = 'barcode';
    public const METHOD_HYBRID = 'hybrid';

    public const WHATSAPP_PENDING = 'pending';
    public const WHATSAPP_SENT = 'sent';
    public const WHATSAPP_FAILED = 'failed';

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

    public const IDENTIFICATION_METHODS = [
        self::METHOD_MANUAL,
        self::METHOD_OCR,
        self::METHOD_BARCODE,
        self::METHOD_HYBRID,
    ];

    public const METHOD_LABELS = [
        self::METHOD_MANUAL => 'Manual',
        self::METHOD_OCR => 'Etiqueta (OCR)',
        self::METHOD_BARCODE => 'Código de barras',
        self::METHOD_HYBRID => 'Híbrido',
    ];

    protected $fillable = [
        'condominium_id',
        'unit_id',
        'registered_by',
        'type',
        'received_at',
        'collected_at',
        'collected_by',
        'picked_up_by_name',
        'pickup_verified_at',
        'status',
        'notification_sent',
        'sender',
        'tracking_code',
        'description',
        'notes',
        'pickup_code_hash',
        'label_image_path',
        'ocr_text',
        'ocr_confidence',
        'identification_method',
        'identification_confidence',
        'identified_resident_id',
        'whatsapp_delivery_status',
    ];

    protected $appends = [
        'type_label',
        'status_label',
        'identification_method_label',
        'requires_pickup_code',
    ];

    protected $hidden = [
        'pickup_code_hash',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'collected_at' => 'datetime',
        'pickup_verified_at' => 'datetime',
        'notification_sent' => 'boolean',
        'ocr_confidence' => 'float',
        'identification_confidence' => 'float',
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

    public function identifiedResident()
    {
        return $this->belongsTo(User::class, 'identified_resident_id');
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

    public function requiresPickupCode(): bool
    {
        return !empty($this->pickup_code_hash);
    }

    public function getRequiresPickupCodeAttribute(): bool
    {
        return $this->requiresPickupCode();
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

    public function getIdentificationMethodLabelAttribute(): string
    {
        if (!$this->identification_method) {
            return '—';
        }

        return self::METHOD_LABELS[$this->identification_method] ?? ucfirst(str_replace('_', ' ', $this->identification_method));
    }

    public function markAsCollected($userId, ?string $pickedUpByName = null)
    {
        $this->update([
            'status' => self::STATUS_COLLECTED,
            'collected_at' => now(),
            'collected_by' => $userId,
            'picked_up_by_name' => $pickedUpByName,
            'pickup_verified_at' => $this->requiresPickupCode() ? now() : null,
        ]);
    }
}
