<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Str;

class Pet extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'condominium_id',
        'unit_id',
        'owner_id',
        'name',
        'type',
        'breed',
        'color',
        'size',
        'birth_date',
        'observations',
        'photo',
        'qr_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'birth_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pet) {
            if (empty($pet->qr_code)) {
                $pet->qr_code = 'PET-' . Str::upper(Str::random(10)) . '-' . time();
            }
        });
    }

    // Relacionamentos
    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // Atributos computados
    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            'dog' => 'Cachorro',
            'cat' => 'Gato',
            'bird' => 'Pássaro',
            'other' => 'Outro',
            default => 'Desconhecido',
        };
    }

    public function getSizeLabelAttribute()
    {
        return match($this->size) {
            'small' => 'Pequeno',
            'medium' => 'Médio',
            'large' => 'Grande',
            default => 'Desconhecido',
        };
    }
    
    public function getDescriptionAttribute()
    {
        return $this->observations;
    }

    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        return asset('images/pet-placeholder.png');
    }

    public function getQrCodeUrlAttribute()
    {
        return route('pets.show-qr', $this->qr_code);
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

    public function scopeByOwner($query, $ownerId)
    {
        return $query->where('owner_id', $ownerId);
    }

    public function scopeByUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('breed', 'like', "%{$term}%")
              ->orWhere('color', 'like', "%{$term}%")
              ->orWhereHas('owner', function($ownerQuery) use ($term) {
                  $ownerQuery->where('name', 'like', "%{$term}%");
              });
        });
    }
}
