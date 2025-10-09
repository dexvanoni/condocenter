<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'space_id', 'unit_id', 'user_id', 'reservation_date',
        'start_time', 'end_time', 'status', 'approved_by',
        'approved_at', 'notes', 'rejection_reason',
        'cancelled_by', 'cancelled_at', 'cancellation_reason',
        'recurring_reservation_id', 'admin_action', 'admin_reason',
        'admin_action_by', 'admin_action_at',
        'prereservation_status', 'payment_deadline', 'payment_completed_at',
        'payment_reference', 'prereservation_amount'
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'approved_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'admin_action_at' => 'datetime',
        'payment_deadline' => 'datetime',
        'payment_completed_at' => 'datetime',
        'prereservation_amount' => 'decimal:2',
    ];

    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function recurringReservation()
    {
        return $this->belongsTo(RecurringReservation::class);
    }

    public function adminActionBy()
    {
        return $this->belongsTo(User::class, 'admin_action_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function approve($userId)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    public function reject($reason, $userId)
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    /**
     * Verificar se é uma pré-reserva
     */
    public function isPrereservation()
    {
        return !is_null($this->prereservation_status);
    }

    /**
     * Verificar se está aguardando pagamento
     */
    public function isPendingPayment()
    {
        return $this->prereservation_status === 'pending_payment';
    }

    /**
     * Verificar se o pagamento foi realizado
     */
    public function isPaid()
    {
        return $this->prereservation_status === 'paid';
    }

    /**
     * Verificar se o prazo de pagamento expirou
     */
    public function isPaymentExpired()
    {
        return $this->prereservation_status === 'expired' || 
               ($this->payment_deadline && now()->gt($this->payment_deadline));
    }

    /**
     * Marcar como pago
     */
    public function markAsPaid($paymentReference = null)
    {
        $this->update([
            'prereservation_status' => 'paid',
            'payment_completed_at' => now(),
            'payment_reference' => $paymentReference,
            'status' => 'approved', // Aprova automaticamente após pagamento
            'approved_at' => now(),
        ]);
    }

    /**
     * Marcar como expirado
     */
    public function markAsExpired()
    {
        $this->update([
            'prereservation_status' => 'expired',
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => 'Prazo de pagamento expirado',
        ]);
    }

    /**
     * Criar pré-reserva
     */
    public static function createPrereservation($data)
    {
        $space = Space::find($data['space_id']);
        $paymentDeadline = $space->getPaymentDeadline();
        
        return self::create([
            'space_id' => $data['space_id'],
            'unit_id' => $data['unit_id'],
            'user_id' => $data['user_id'],
            'reservation_date' => $data['reservation_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'status' => 'pending',
            'notes' => $data['notes'] ?? null,
            'prereservation_status' => 'pending_payment',
            'payment_deadline' => $paymentDeadline,
            'prereservation_amount' => $space->price_per_hour,
        ]);
    }
}
