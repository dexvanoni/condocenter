<?php

namespace App\Models;

use App\Support\EmployeeEntryTypes;
use Illuminate\Database\Eloquent\Model;

class EmployeeFinancialEntry extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'condominium_id',
        'employee_id',
        'type',
        'status',
        'reference_date',
        'competence_month',
        'amount',
        'hours',
        'hourly_rate',
        'vacation_start',
        'vacation_end',
        'description',
        'tax_breakdown',
        'condominium_account_id',
        'created_by',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
    ];

    protected $casts = [
        'reference_date' => 'date',
        'competence_month' => 'date',
        'amount' => 'decimal:2',
        'hours' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'vacation_start' => 'date',
        'vacation_end' => 'date',
        'tax_breakdown' => 'array',
        'cancelled_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

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

    public function condominiumAccount()
    {
        return $this->belongsTo(CondominiumAccount::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByCondominium($query, int $condominiumId)
    {
        return $query->where('condominium_id', $condominiumId);
    }

    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('reference_date', [$startDate, $endDate]);
    }

    public function getTypeLabelAttribute(): string
    {
        return EmployeeEntryTypes::label($this->type);
    }
}
