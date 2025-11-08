<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;

class FeeUnitConfiguration extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'fee_id',
        'unit_id',
        'payment_channel',
        'custom_amount',
        'starts_at',
        'ends_at',
        'notes',
    ];

    protected $casts = [
        'custom_amount' => 'decimal:2',
        'starts_at' => 'date',
        'ends_at' => 'date',
    ];

    public function fee()
    {
        return $this->belongsTo(Fee::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function scopeActive($query, ?Carbon $date = null)
    {
        $date = $date ?? now();

        return $query->where(function ($q) use ($date) {
            $q->whereNull('starts_at')->orWhere('starts_at', '<=', $date->toDateString());
        })->where(function ($q) use ($date) {
            $q->whereNull('ends_at')->orWhere('ends_at', '>=', $date->toDateString());
        });
    }

    public function isActiveForDate(Carbon $date): bool
    {
        if ($this->starts_at && $date->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $date->gt($this->ends_at)) {
            return false;
        }

        return true;
    }
}

