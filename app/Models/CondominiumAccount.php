<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class CondominiumAccount extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'condominium_id',
        'bank_account_id',
        'type',
        'status',
        'source_type',
        'source_id',
        'description',
        'amount',
        'transaction_date',
        'payment_method',
        'installments_total',
        'installment_number',
        'document_path',
        'captured_image_path',
        'notes',
        'created_by',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'reconciliation_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'installments_total' => 'integer',
        'installment_number' => 'integer',
        'cancelled_at' => 'datetime',
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isCancellableManualExpense(): bool
    {
        return $this->type === 'expense'
            && $this->isActive()
            && $this->source_type === null
            && $this->reconciliation_id === null;
    }

    public function source()
    {
        return $this->morphTo(null, 'source_type', 'source_id');
    }

    public function bankReconciliation()
    {
        return $this->belongsTo(BankAccountReconciliation::class, 'reconciliation_id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopeByCondominium($query, int $condominiumId)
    {
        return $query->where('condominium_id', $condominiumId);
    }

    public function scopeNotReconciled($query)
    {
        return $query->whereNull('reconciliation_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeCountsInBalance($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}

