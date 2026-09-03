<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Unit extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'condominium_id',
        'default_payment_channel',
        'number',
        'block',
        'type',
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
