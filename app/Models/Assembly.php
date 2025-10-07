<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assembly extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'condominium_id', 'created_by', 'title', 'description', 'agenda',
        'scheduled_at', 'started_at', 'ended_at', 'duration_minutes',
        'status', 'voting_type', 'allow_delegation', 'minutes', 'minutes_pdf',
    ];

    protected $casts = [
        'agenda' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'allow_delegation' => 'boolean',
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function start()
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'ended_at' => now(),
        ]);
    }
}
