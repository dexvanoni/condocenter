<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserCredit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'condominium_id',
        'user_id',
        'amount',
        'type',
        'description',
        'reservation_id',
        'charge_id',
        'status',
        'used_in_reservation_id',
        'used_at',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Relacionamentos
    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function charge()
    {
        return $this->belongsTo(Charge::class);
    }

    public function usedInReservation()
    {
        return $this->belongsTo(Reservation::class, 'used_in_reservation_id');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Métodos
    public function markAsUsed($reservationId)
    {
        $this->update([
            'status' => 'used',
            'used_in_reservation_id' => $reservationId,
            'used_at' => now(),
        ]);
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at < now();
    }
}
