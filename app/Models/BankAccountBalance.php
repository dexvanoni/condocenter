<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccountBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_account_id',
        'balance',
        'recorded_at',
        'reference',
        'notes',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'recorded_at' => 'date',
    ];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }
}

