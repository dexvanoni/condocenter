<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class MonthlyClosing extends Model
{
    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'condominium_id',
        'reference_month',
        'status',
        'completed_at',
        'completed_by',
        'closing_notes',
    ];

    protected $casts = [
        'reference_month' => 'date',
        'completed_at' => 'datetime',
    ];

    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class);
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function stepConfirmations(): HasMany
    {
        return $this->hasMany(MonthlyClosingStepConfirmation::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function referenceMonthValue(): string
    {
        return $this->reference_month->format('Y-m');
    }

    public static function referenceMonthFromCarbon(Carbon $month): string
    {
        return $month->copy()->startOfMonth()->toDateString();
    }
}
