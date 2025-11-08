<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;

class Fee extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'condominium_id',
        'bank_account_id',
        'name',
        'description',
        'amount',
        'recurrence',
        'due_day',
        'due_offset_days',
        'billing_type',
        'auto_generate_charges',
        'active',
        'starts_at',
        'ends_at',
        'custom_schedule',
        'metadata',
        'last_generated_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'auto_generate_charges' => 'boolean',
        'active' => 'boolean',
        'starts_at' => 'date',
        'ends_at' => 'date',
        'custom_schedule' => 'array',
        'metadata' => 'array',
        'last_generated_at' => 'datetime',
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function configurations()
    {
        return $this->hasMany(FeeUnitConfiguration::class);
    }

    public function charges()
    {
        return $this->hasMany(Charge::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByCondominium($query, int $condominiumId)
    {
        return $query->where('condominium_id', $condominiumId);
    }

    public function isActiveForDate(Carbon $date): bool
    {
        if (!$this->active) {
            return false;
        }

        if ($this->starts_at && $date->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $date->gt($this->ends_at)) {
            return false;
        }

        return true;
    }
}

