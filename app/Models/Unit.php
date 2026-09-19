<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use App\Support\PublicPropertyKinds;
use App\Support\UnitModels;
use App\Support\UnitOccupancyRegimes;
use App\Support\UnitRentalPeriods;

class Unit extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'condominium_id',
        'default_payment_channel',
        'number',
        'block',
        'type',
        'unit_model',
        'occupancy_regime',
        'rental_period',
        'public_property_kind',
        'owner_user_id',
        'lease_contract_ends_at',
        'situacao',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'ideal_fraction',
        'area',
        'floor',
        'num_quartos',
        'num_banheiros',
        'foto',
        'possui_dividas',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'ideal_fraction' => 'decimal:4',
        'area' => 'decimal:2',
        'is_active' => 'boolean',
        'default_payment_channel' => 'string',
        'possui_dividas' => 'boolean',
        'lease_contract_ends_at' => 'date',
    ];

    protected $appends = [
        'full_identifier',
    ];

    // Relacionamentos
    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /**
     * Retorna o morador responsável pela unidade
     */
    public function morador()
    {
        return $this->hasOne(User::class)->whereHas('roles', function($query) {
            $query->where('name', 'Morador');
        });
    }

    public function charges()
    {
        return $this->hasMany(Charge::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function pets()
    {
        return $this->hasMany(Pet::class);
    }

    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    public function entries()
    {
        return $this->hasMany(Entry::class);
    }

    public function feeConfigurations()
    {
        return $this->hasMany(FeeUnitConfiguration::class);
    }

    // Atributos computados
    public function getFullIdentifierAttribute()
    {
        return $this->block ? "{$this->block} - {$this->number}" : $this->number;
    }

    public function getFullAddressAttribute()
    {
        $condominium = $this->relationLoaded('condominium')
            ? $this->condominium
            : $this->condominium()->first();

        if (!$condominium || !$condominium->address) {
            return null;
        }

        $address = $condominium->address;

        if ($condominium->city && $condominium->state) {
            $address .= " - {$condominium->city}/{$condominium->state}";
        }

        if ($condominium->zip_code) {
            $address .= " - CEP: {$condominium->zip_code}";
        }

        $address .= " - Unidade {$this->full_identifier}";

        return $address;
    }

    public function getSituacaoLabelAttribute()
    {
        return match($this->situacao) {
            'habitado' => 'Habitado',
            'fechado' => 'Fechado',
            'indisponivel' => 'Indisponível',
            'em_obra' => 'Em Obra',
            default => 'Desconhecido',
        };
    }

    public function getUnitModelLabelAttribute(): string
    {
        return UnitModels::label($this->unit_model);
    }

    public function getOccupancyRegimeLabelAttribute(): string
    {
        return UnitOccupancyRegimes::label($this->occupancy_regime);
    }

    public function getRentalPeriodLabelAttribute(): string
    {
        return UnitRentalPeriods::label($this->rental_period);
    }

    public function getPublicPropertyKindLabelAttribute(): string
    {
        return PublicPropertyKinds::label($this->public_property_kind);
    }

    public function isRental(): bool
    {
        return $this->occupancy_regime === UnitOccupancyRegimes::ALUGUEL;
    }

    public function isParticular(): bool
    {
        return $this->occupancy_regime === UnitOccupancyRegimes::PARTICULAR
            || $this->occupancy_regime === null;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCondominium($query, $condominiumId)
    {
        return $query->where('condominium_id', $condominiumId);
    }

    public function scopeResidential($query)
    {
        return $query->where('type', 'residential');
    }

    public function scopeCommercial($query)
    {
        return $query->where('type', 'commercial');
    }

    public function scopeOfModel($query, ?string $model)
    {
        if (!$model) {
            return $query;
        }

        return $query->where('unit_model', $model);
    }

    public function scopeMatchingFeeModels($query, ?array $models)
    {
        if (empty($models)) {
            return $query;
        }

        return $query->whereIn('unit_model', $models);
    }

    public function scopeHabitado($query)
    {
        return $query->where('situacao', 'habitado');
    }

    /**
     * Unidades elegíveis para aplicação automática de taxas/cobranças:
     * ativa, situacao habitado e com morador ativo vinculado.
     */
    public function scopeEligibleForAutomaticFee($query)
    {
        return $query->active()
            ->habitado()
            ->whereHas('users', function ($userQuery) {
                $userQuery->where('is_active', true)
                    ->whereHas('roles', function ($roleQuery) {
                        $roleQuery->where('name', 'Morador');
                    });
            });
    }

    public function isEligibleForAutomaticFee(): bool
    {
        if (!$this->is_active || $this->situacao !== 'habitado') {
            return false;
        }

        return $this->users()
            ->where('is_active', true)
            ->whereHas('roles', function ($roleQuery) {
                $roleQuery->where('name', 'Morador');
            })
            ->exists();
    }

    public function scopeWithDebts($query)
    {
        return $query->where('possui_dividas', true);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('number', 'like', "%{$term}%")
              ->orWhere('block', 'like', "%{$term}%");
        });
    }
}
