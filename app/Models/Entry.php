<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    use HasFactory;

    protected $fillable = [
        'condominium_id', 'unit_id', 'registered_by', 'type',
        'visitor_name', 'visitor_document', 'visitor_phone',
        'vehicle_plate', 'entry_type', 'entry_time', 'exit_time',
        'authorized', 'authorized_by', 'notes', 'photo',
    ];

    protected $casts = [
        'entry_time' => 'datetime',
        'exit_time' => 'datetime',
        'authorized' => 'boolean',
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

    public function authorizedBy()
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }

    public function registerExit()
    {
        $this->update([
            'exit_time' => now(),
            'entry_type' => 'exit',
        ]);
    }
}
