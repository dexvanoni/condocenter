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
        'possui_dividas' => 'boolean',
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

    // Atributos computados
    public function getFullIdentifierAttribute()
    {
        return $this->block ? "{$this->block} - {$this->number}" : $this->number;
    }

    public function getFullAddressAttribute()
    {
        if (!$this->logradouro) {
            return null;
        }

        $address = $this->logradouro;
        if ($this->numero) $address .= ", {$this->numero}";
        if ($this->complemento) $address .= " - {$this->complemento}";
        if ($this->bairro) $address .= ", {$this->bairro}";
        if ($this->cidade && $this->estado) $address .= " - {$this->cidade}/{$this->estado}";
        if ($this->cep) $address .= " - CEP: {$this->cep}";

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

    public function scopeWithDebts($query)
    {
        return $query->where('possui_dividas', true);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('number', 'like', "%{$term}%")
              ->orWhere('block', 'like', "%{$term}%")
              ->orWhere('logradouro', 'like', "%{$term}%")
              ->orWhere('cep', 'like', "%{$term}%");
        });
    }
}
