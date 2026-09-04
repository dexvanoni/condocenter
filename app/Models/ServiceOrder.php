<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceOrder extends Model
{
    use SoftDeletes;

    public const TYPES = [
        'maintenance' => 'Manutenção',
        'repair' => 'Reparo',
        'inspection' => 'Vistoria',
    ];

    public const LOCATION_TYPES = [
        'unit' => 'Minha unidade',
        'common_area' => 'Área comum',
    ];

    public const URGENCIES = [
        'low' => 'Baixa',
        'medium' => 'Média',
        'high' => 'Alta',
        'urgent' => 'Urgente',
    ];

    public const STATUSES = [
        'open' => 'Aberta',
        'dispatched' => 'Despachada',
        'in_progress' => 'Em andamento',
        'resolved' => 'Resolvida',
        'unresolved' => 'Não resolvida',
        'cancelled' => 'Cancelada',
    ];

    public const OPEN_STATUSES = ['open', 'dispatched', 'in_progress'];

    protected $fillable = [
        'condominium_id',
        'unit_id',
        'user_id',
        'protocol',
        'type',
        'location_type',
        'location_detail',
        'title',
        'description',
        'urgency',
        'preferred_date',
        'preferred_time_start',
        'preferred_time_end',
        'availability_notes',
        'status',
        'resolution_notes',
        'assigned_to',
        'charge_id',
        'reimbursement_total',
        'whatsapp_notify',
        'dispatched_at',
        'resolved_at',
        'closed_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
            'reimbursement_total' => 'decimal:2',
            'whatsapp_notify' => 'boolean',
            'dispatched_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
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

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function charge(): BelongsTo
    {
        return $this->belongsTo(Charge::class);
    }

    public function charges(): HasMany
    {
        return $this->hasMany(Charge::class)->orderBy('created_at');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ServiceOrderMessage::class)->orderBy('created_at');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceOrderItem::class)->orderBy('created_at');
    }

    public function unbilledItems(): HasMany
    {
        return $this->items()->whereNull('charge_id');
    }

    public function getUnbilledTotalAttribute(): float
    {
        return (float) $this->unbilledItems()->sum('total');
    }

    public function getBilledTotalAttribute(): float
    {
        return (float) $this->items()->whereNotNull('charge_id')->sum('total');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeByCondominium(Builder $query, int $condominiumId): Builder
    {
        return $query->where('condominium_id', $condominiumId);
    }

    public function scopeForRequester(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getLocationTypeLabelAttribute(): string
    {
        return self::LOCATION_TYPES[$this->location_type] ?? $this->location_type;
    }

    public function getUrgencyLabelAttribute(): string
    {
        return self::URGENCIES[$this->urgency] ?? $this->urgency;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'open' => 'bg-primary',
            'dispatched' => 'bg-info text-dark',
            'in_progress' => 'bg-warning text-dark',
            'resolved' => 'bg-success',
            'unresolved' => 'bg-danger',
            'cancelled' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    public function getUrgencyBadgeClassAttribute(): string
    {
        return match ($this->urgency) {
            'low' => 'bg-secondary',
            'medium' => 'bg-info text-dark',
            'high' => 'bg-warning text-dark',
            'urgent' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN_STATUSES, true);
    }

    public function canReceiveMessagesFrom(User $user): bool
    {
        if ($user->can('manage_service_orders') && $user->tenantCondominiumId() === $this->condominium_id) {
            return true;
        }

        return $this->user_id === $user->id;
    }
}
