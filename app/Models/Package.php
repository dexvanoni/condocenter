<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'condominium_id', 'unit_id', 'registered_by', 'sender',
        'tracking_code', 'description', 'received_at', 'collected_at',
        'collected_by', 'status', 'notes', 'notification_sent',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'collected_at' => 'datetime',
        'notification_sent' => 'boolean',
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function markAsCollected($userId)
    {
        $this->update([
            'status' => 'collected',
            'collected_at' => now(),
            'collected_by' => $userId,
        ]);
    }
}
