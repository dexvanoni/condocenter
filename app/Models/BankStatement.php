<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankStatement extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_READY = 'ready';

    public const STATUS_RECONCILED = 'reconciled';

    public const STATUS_FAILED = 'failed';

    public const FORMAT_CSV = 'csv';

    public const FORMAT_OFX = 'ofx';

    protected $fillable = [
        'condominium_id',
        'bank_account_id',
        'uploaded_by',
        'original_filename',
        'storage_path',
        'format',
        'file_hash',
        'statement_date',
        'period_start',
        'period_end',
        'opening_balance',
        'closing_balance',
        'status',
        'total_transactions',
        'reconciled_transactions',
        'unmatched_items',
        'notes',
    ];

    protected $casts = [
        'statement_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'unmatched_items' => 'array',
    ];

    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(BankStatementLine::class)->orderBy('line_order');
    }

    public function getReconciliationPercentageAttribute(): float
    {
        if ($this->total_transactions == 0) {
            return 0;
        }

        return round(($this->reconciled_transactions / $this->total_transactions) * 100, 2);
    }

    public function coversPeriod(\Carbon\Carbon $start, \Carbon\Carbon $end): bool
    {
        if (!$this->period_start || !$this->period_end) {
            return false;
        }

        return $this->period_start->lte($end) && $this->period_end->gte($start);
    }

    public function scopeReadyForAccount($query, int $bankAccountId)
    {
        return $query->where('bank_account_id', $bankAccountId)
            ->where('status', self::STATUS_READY);
    }
}
