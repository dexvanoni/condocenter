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
        'payment_date',
        'payment_method',
        'asaas_payment_id',
        'transaction_id',
        'notes',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'payment_date' => 'date',
    ];

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
