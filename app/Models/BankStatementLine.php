<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankStatementLine extends Model
{
    public const STATUS_UNMATCHED = 'unmatched';

    public const STATUS_SUGGESTED = 'suggested';

    public const STATUS_AUTO_MATCHED = 'auto_matched';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_CREATED = 'created';

    public const STATUS_IGNORED = 'ignored';

    public const SOURCE_TRANSACTION = 'transaction';

    public const SOURCE_CONDOMINIUM_ACCOUNT = 'condominium_account';

    protected $fillable = [
        'bank_statement_id',
        'bank_account_id',
        'line_order',
        'posted_at',
        'amount',
        'description',
        'fitid',
        'trn_type',
        'status',
        'matched_source_type',
        'matched_source_id',
        'suggested_source_type',
        'suggested_source_id',
        'suggestion_meta',
    ];

    protected $casts = [
        'posted_at' => 'date',
        'amount' => 'decimal:2',
        'suggestion_meta' => 'array',
    ];

    public function statement(): BelongsTo
    {
        return $this->belongsTo(BankStatement::class, 'bank_statement_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function isIncome(): bool
    {
        return (float) $this->amount >= 0;
    }

    public function isExpense(): bool
    {
        return (float) $this->amount < 0;
    }

    public function absoluteAmount(): float
    {
        return abs((float) $this->amount);
    }

    public function direction(): string
    {
        return $this->isIncome() ? 'income' : 'expense';
    }

    public function isLinked(): bool
    {
        return in_array($this->status, [
            self::STATUS_AUTO_MATCHED,
            self::STATUS_CONFIRMED,
            self::STATUS_CREATED,
        ], true) && $this->matched_source_type && $this->matched_source_id;
    }

    public function scopeLinked($query)
    {
        return $query->whereIn('status', [
            self::STATUS_AUTO_MATCHED,
            self::STATUS_CONFIRMED,
            self::STATUS_CREATED,
        ])->whereNotNull('matched_source_type')->whereNotNull('matched_source_id');
    }

    public function scopePendingReview($query)
    {
        return $query->whereIn('status', [
            self::STATUS_UNMATCHED,
            self::STATUS_SUGGESTED,
        ]);
    }
}
