<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Transaction extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'condominium_id',
        'unit_id',
        'user_id',
        'type',
        'category',
        'subcategory',
        'description',
        'amount',
        'transaction_date',
        'due_date',
        'paid_date',
        'status',
        'payment_method',
        'store_location',
        'is_recurring',
        'recurrence_period',
        'parent_transaction_id',
        'tags',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'is_recurring' => 'boolean',
        'tags' => 'array',
    ];

    // Relacionamentos
    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    public function parentTransaction()
    {
        return $this->belongsTo(Transaction::class, 'parent_transaction_id');
    }

    public function childTransactions()
    {
        return $this->hasMany(Transaction::class, 'parent_transaction_id');
    }

    // Scopes
    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Métodos auxiliares
    public function markAsPaid($paymentDate = null, $paymentMethod = null)
    {
        $this->status = 'paid';
        $this->paid_date = $paymentDate ?? now();
        if ($paymentMethod) {
            $this->payment_method = $paymentMethod;
        }
        $this->save();
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending' 
            && $this->due_date 
            && $this->due_date->isPast();
    }
}
