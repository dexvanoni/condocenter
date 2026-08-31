<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'billing_metric',
        'unit_price',
        'user_price',
        'billing_cycle',
        'trial_days',
        'payment_method',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'user_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SubscriptionPlan $plan) {
            if (!$plan->slug) {
                $plan->slug = Str::slug($plan->name);
            }
        });
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(CondominiumSubscription::class);
    }

    public function billingCycleLabel(): string
    {
        return match ($this->billing_cycle) {
            'quarterly' => 'Trimestral',
            'semiannual' => 'Semestral',
            'annual' => 'Anual',
            default => 'Mensal',
        };
    }

    public function toContractDefaults(): array
    {
        return [
            'billing_metric' => $this->billing_metric,
            'unit_price' => $this->unit_price,
            'user_price' => $this->user_price,
            'billing_cycle' => $this->billing_cycle,
            'trial_days' => $this->trial_days,
            'payment_method' => $this->payment_method,
        ];
    }
}
