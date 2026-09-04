<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class BankAccount extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'condominium_id',
        'name',
        'institution',
        'holder_name',
        'document_number',
        'bank_name',
        'agency',
        'account',
        'type',
        'pix_key',
        'active',
        'is_primary',
        'current_balance',
        'balance_updated_at',
        'notes',
    ];

    protected $casts = [
        'active' => 'boolean',
        'is_primary' => 'boolean',
        'current_balance' => 'decimal:2',
        'balance_updated_at' => 'datetime',
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function fees()
    {
        return $this->hasMany(Fee::class);
    }

    public function balances()
    {
        return $this->hasMany(BankAccountBalance::class);
    }

    public function reconciliations()
    {
        return $this->hasMany(BankAccountReconciliation::class);
    }

    public function routingRules()
    {
        return $this->hasMany(BankAccountRoutingRule::class);
    }
}

