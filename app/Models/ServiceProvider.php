<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceProvider extends Model
{
    public const SCOPE_UNIT = 'unit';
    public const SCOPE_CONDOMINIUM = 'condominium';

    protected $fillable = [
        'condominium_id',
        'unit_id',
        'authorized_by',
        'scope',
        'name',
        'document',
        'phone',
        'company',
        'photo_path',
        'contract_valid_until',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'contract_valid_until' => 'date',
            'is_active' => 'boolean',
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

    public function scopeForCondominium($query, int $condominiumId)
    {
        return $query->where('condominium_id', $condominiumId);
    }

    public function scopeActiveValid($query)
    {
        return $query->where('is_active', true)
            ->whereDate('contract_valid_until', '>=', today());
    }

    public function isContractValid(): bool
    {
        return $this->contract_valid_until !== null
            && $this->contract_valid_until->gte(today());
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }
}
