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
        'ideal_fraction',
        'area',
        'floor',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'ideal_fraction' => 'decimal:4',
        'area' => 'decimal:2',
        'is_active' => 'boolean',
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

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeResidential($query)
    {
        return $query->where('type', 'residential');
    }

    public function scopeCommercial($query)
    {
        return $query->where('type', 'commercial');
    }
}
