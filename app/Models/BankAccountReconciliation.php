<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccountReconciliation extends Model
{
    use HasFactory;

    protected $fillable = [
        'condominium_id',
        'bank_account_id',
        'start_date',
        'end_date',
        'total_income',
        'total_expense',
        'net_amount',
        'previous_balance',
        'resulting_balance',
        'previous_balance_updated_at',
        'bank_account_balance_id',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_income' => 'decimal:2',
        'total_expense' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'previous_balance' => 'decimal:2',
        'resulting_balance' => 'decimal:2',
        'previous_balance_updated_at' => 'datetime',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BankAccountReconciliationItem::class, 'reconciliation_id');
    }
}

