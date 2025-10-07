<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'space_id', 'unit_id', 'user_id', 'reservation_date',
        'start_time', 'end_time', 'status', 'approved_by',
        'approved_at', 'notes', 'rejection_reason',
        'cancelled_by', 'cancelled_at', 'cancellation_reason',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'approved_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function approve($userId)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    public function reject($reason, $userId)
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }
}
