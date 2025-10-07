<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Space extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'condominium_id', 'name', 'description', 'type', 'capacity',
        'price_per_hour', 'requires_approval', 'max_hours_per_reservation',
        'min_hours_per_reservation', 'interval_between_reservations',
        'max_reservations_per_month_per_unit', 'available_from',
        'available_until', 'is_active', 'rules', 'reservation_mode',
    ];

    protected $casts = [
        'price_per_hour' => 'decimal:2',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
        'available_from' => 'datetime:H:i',
        'available_until' => 'datetime:H:i',
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
