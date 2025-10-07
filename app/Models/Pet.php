<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'unit_id', 'owner_id', 'name', 'type', 'breed', 'color',
        'birth_date', 'size', 'photo', 'observations', 'is_active',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getAgeAttribute()
    {
        return $this->birth_date ? $this->birth_date->age : null;
    }
}
