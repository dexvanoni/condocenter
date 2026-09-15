<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'charge_id',
        'user_id',
        'amount_paid',
        'gross_amount',
        'net_amount',
        'gateway_fee',
        'installment_count',
        'asaas_billing_type',
        'payment_date',
        'payment_method',
        'asaas_payment_id',
        'transaction_id',
        'notes',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'gateway_fee' => 'decimal:2',
        'installment_count' => 'integer',
        'payment_date' => 'date',
    ];

    public function hasGatewayFee(): bool
    {
        return $this->gateway_fee !== null && (float) $this->gateway_fee > 0;
    }

    /**
     * Valor exibido ao morador/pagador — sempre o bruto efetivamente pago.
     */
    public function displayAmount(): float
    {
        if ($this->gross_amount !== null) {
            return (float) $this->gross_amount;
        }

        return (float) $this->amount_paid;
    }

    /**
     * Valor líquido creditado ao caixa do condomínio (após taxas do gateway).
     */
    public function settledNetAmount(): float
    {
        if ($this->net_amount !== null) {
            return (float) $this->net_amount;
        }

        return (float) $this->amount_paid;
    }

    // Relacionamentos
    public function charge()
    {
        return $this->belongsTo(Charge::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }
}
