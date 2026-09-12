<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\PaymentCancellation;

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
        'service_order_id',
        'asaas_payment_id',
        'boleto_url',
        'pix_code',
        'pix_qrcode',
        'metadata',
        'first_reminder_sent_at',
        'second_reminder_sent_at',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fine_percentage' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'due_date' => 'date',
        'first_reminder_sent_at' => 'datetime',
        'second_reminder_sent_at' => 'datetime',
        'metadata' => 'array',
        'paid_at' => 'datetime',
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

    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function serviceOrderItems()
    {
        return $this->hasMany(ServiceOrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function cancellations()
    {
        return $this->hasMany(PaymentCancellation::class);
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

    public function paymentChannel(): string
    {
        $channel = $this->metadata['payment_channel'] ?? 'system';

        return in_array($channel, ['system', 'payroll'], true) ? $channel : 'system';
    }

    public function isPayrollChannel(): bool
    {
        return $this->paymentChannel() === 'payroll';
    }

    public function isPayrollAutoSettled(): bool
    {
        return ($this->metadata['payroll_auto_settled'] ?? false) === true;
    }

    public function competencePeriod(): ?string
    {
        return $this->metadata['competence_period'] ?? $this->recurrence_period;
    }

    public function competenceLabel(): string
    {
        $period = $this->competencePeriod();

        if ($period && preg_match('/^\d{4}-\d{2}$/', $period)) {
            return Carbon::createFromFormat('Y-m', $period)->translatedFormat('M/Y');
        }

        if ($period) {
            return $period;
        }

        return '—';
    }

    public function receivedAt(): ?Carbon
    {
        if ($this->status !== 'paid') {
            return null;
        }

        return $this->paid_at;
    }

    /**
     * @return array{key: string, label: string, color: string}
     */
    public function displayStatus(): array
    {
        if ($this->status === 'cancelled') {
            return ['key' => 'cancelled', 'label' => 'Cancelada', 'color' => 'secondary'];
        }

        if ($this->status === 'paid') {
            if ($this->isPayrollAutoSettled()) {
                return ['key' => 'paid_payroll', 'label' => 'Paga (folha)', 'color' => 'success'];
            }

            return ['key' => 'paid', 'label' => 'Paga', 'color' => 'success'];
        }

        if ($this->isPayrollChannel() && in_array($this->status, ['pending', 'overdue'], true)) {
            if ($this->due_date?->isFuture()) {
                return ['key' => 'payroll_scheduled', 'label' => 'Aguardando folha', 'color' => 'info'];
            }

            return ['key' => 'payroll_due', 'label' => 'Folha pendente', 'color' => 'warning'];
        }

        if ($this->status === 'overdue') {
            return ['key' => 'overdue', 'label' => 'Em atraso', 'color' => 'danger'];
        }

        return ['key' => 'pending', 'label' => 'Pendente', 'color' => 'warning'];
    }
}
