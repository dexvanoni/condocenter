<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccountReconciliationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'reconciliation_id',
        'source_type',
        'source_id',
        'direction',
        'reference_date',
        'amount',
        'label',
    ];

    protected $casts = [
        'reference_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(BankAccountReconciliation::class, 'reconciliation_id');
    }
}

