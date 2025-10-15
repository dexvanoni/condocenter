<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PanicAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'condominium_id',
        'user_id',
        'alert_type',
        'title',
        'description',
        'location',
        'severity',
        'status',
        'resolved_by',
        'resolved_at',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'resolved_at' => 'datetime',
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

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeForCondominium($query, $condominiumId)
    {
        return $query->where('condominium_id', $condominiumId);
    }

    // Métodos
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isResolved()
    {
        return $this->status === 'resolved';
    }

    public function resolve(User $user)
    {
        $this->update([
            'status' => 'resolved',
            'resolved_by' => $user->id,
            'resolved_at' => now()
        ]);
    }

    public function getDurationAttribute()
    {
        if ($this->isResolved() && $this->resolved_at) {
            return $this->created_at->diffForHumans($this->resolved_at, true);
        }
        
        return $this->created_at->diffForHumans(now(), true);
    }

    public function getSeverityColorAttribute()
    {
        return match($this->severity) {
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'critical' => 'dark',
            default => 'danger'
        };
    }

    public function getSeverityIconAttribute()
    {
        return match($this->severity) {
            'low' => 'bi-exclamation-circle',
            'medium' => 'bi-exclamation-triangle',
            'high' => 'bi-exclamation-octagon',
            'critical' => 'bi-lightning-charge',
            default => 'bi-exclamation-octagon'
        };
    }
}