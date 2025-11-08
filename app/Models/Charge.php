<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Charge extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'condominium_id',
        'unit_id',
        'fee_id',
        'title',
        'description',
        'amount',
        'due_date',
        'recurrence_period',
        'fine_percentage',
        'interest_rate',
        'status',
        'type',
        'generated_by',
        'asaas_payment_id',
        'boleto_url',
        'pix_code',
        'pix_qrcode',
        'metadata',
        'first_reminder_sent_at',
        'second_reminder_sent_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fine_percentage' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'due_date' => 'date',
        'first_reminder_sent_at' => 'datetime',
        'second_reminder_sent_at' => 'datetime',
        'metadata' => 'array',
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

    public function fee()
    {
        return $this->belongsTo(Fee::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
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
        return $query->whereBetween('due_date', [$startDate, $endDate]);
    }

    // Métodos auxiliares
    public function calculateTotal()
    {
        $total = $this->amount;
        
        if ($this->isOverdue()) {
            $daysLate = now()->diffInDays($this->due_date);
            $monthsLate = ceil($daysLate / 30);
            
            // Adiciona multa
            $fine = $this->amount * ($this->fine_percentage / 100);
            $total += $fine;
            
            // Adiciona juros
            $interest = $this->amount * ($this->interest_rate / 100) * $monthsLate;
            $total += $interest;
        }
        
        return round($total, 2);
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'paid' 
            && $this->due_date 
            && $this->due_date->isPast();
    }

    public function markAsPaid()
    {
        $this->status = 'paid';
        $this->save();
    }

    public function getTotalPaidAttribute()
    {
        return $this->payments()->sum('amount_paid');
    }

    public function getRemainingAmountAttribute()
    {
        return $this->calculateTotal() - $this->total_paid;
    }
}
