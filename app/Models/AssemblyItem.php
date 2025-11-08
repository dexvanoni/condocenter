<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssemblyItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'assembly_id',
        'title',
        'description',
        'options',
        'position',
        'status',
        'opens_at',
        'closes_at',
    ];

    protected $casts = [
        'options' => 'array',
        'opens_at' => 'datetime',
        'closes_at' => 'datetime',
    ];

    public function assembly()
    {
        return $this->belongsTo(Assembly::class);
    }

    public function votes()
    {
        return $this->hasMany(AssemblyVote::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    public function isOpen(): bool
    {
        $now = now();

        if ($this->status !== 'open') {
            return false;
        }

        if ($this->opens_at && $now->lt($this->opens_at)) {
            return false;
        }

        if ($this->closes_at && $now->gt($this->closes_at)) {
            return false;
        }

        return true;
    }

    public function availableOptions(): array
    {
        return $this->options ?? ['yes', 'no', 'abstain'];
    }
}

