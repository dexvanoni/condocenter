<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ride extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_OPEN = 'open';
    public const STATUS_FULL = 'full';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'condominium_id',
        'driver_id',
        'destination',
        'departure_at',
        'seats_total',
        'seats_available',
        'has_return',
        'return_at',
        'is_free',
        'price_per_seat',
        'notes',
        'status',
        'cancelled_by',
        'cancelled_at',
    ];

    protected $casts = [
        'departure_at' => 'datetime',
        'return_at' => 'datetime',
        'has_return' => 'boolean',
        'is_free' => 'boolean',
        'price_per_seat' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(RideBooking::class);
    }

    public function activeBookings(): HasMany
    {
        return $this->bookings()->where('status', RideBooking::STATUS_CONFIRMED);
    }

    public function scopeByCondominium(Builder $query, int $condominiumId): Builder
    {
        return $query->where('condominium_id', $condominiumId);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_OPEN)
            ->where('seats_available', '>', 0)
            ->where('departure_at', '>', now());
    }

    public function isBookable(): bool
    {
        return $this->status === self::STATUS_OPEN
            && $this->seats_available > 0
            && $this->departure_at->isFuture();
    }

    public function bookedSeatsCount(): int
    {
        return (int) $this->activeBookings()->sum('seats_booked');
    }
}
