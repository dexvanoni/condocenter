<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CondominiumSubscription extends Model
{
    public const METRIC_UNIT = 'unit';
    public const METRIC_USER = 'user';
    public const METRIC_FIXED = 'fixed';

    public const CYCLE_MONTHLY = 'monthly';
    public const CYCLE_QUARTERLY = 'quarterly';
    public const CYCLE_SEMIANNUAL = 'semiannual';
    public const CYCLE_ANNUAL = 'annual';

    public const PAYMENT_BOLETO = 'boleto';
    public const PAYMENT_CREDIT_CARD = 'credit_card';
    public const PAYMENT_PIX_RECURRING = 'pix_recurring';
    public const PAYMENT_BANK_DEPOSIT = 'bank_deposit';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_TRIAL = 'trial';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAST_DUE = 'past_due';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'condominium_id',
        'subscription_plan_id',
        'financial_responsible_user_id',
        'created_by',
        'billing_metric',
        'unit_price',
        'user_price',
        'fixed_price',
        'billing_cycle',
        'trial_days',
        'payment_method',
        'financial_cnpj',
        'financial_contact_name',
        'financial_contact_email',
        'financial_contact_phone',
        'contract_starts_at',
        'contract_ends_at',
        'trial_starts_at',
        'trial_ends_at',
        'extended_until',
        'billable_quantity',
        'recurring_amount',
        'status',
        'asaas_customer_id',
        'asaas_subscription_id',
        'admin_notes',
        'activated_at',
        'past_due_at',
        'suspended_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'user_price' => 'decimal:2',
            'fixed_price' => 'decimal:2',
            'recurring_amount' => 'decimal:2',
            'contract_starts_at' => 'date',
            'contract_ends_at' => 'date',
            'trial_starts_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'extended_until' => 'date',
            'activated_at' => 'datetime',
            'past_due_at' => 'datetime',
            'suspended_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function financialResponsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'financial_responsible_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CondominiumSubscriptionDocument::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(CondominiumSubscriptionLog::class)->latest();
    }

    public function isAccessAllowed(): bool
    {
        if ($this->isContractExpired()) {
            return false;
        }

        if ($this->status === self::STATUS_DRAFT) {
            return true;
        }

        if (in_array($this->status, [
            self::STATUS_TRIAL,
            self::STATUS_ACTIVE,
        ], true)) {
            return true;
        }

        if ($this->status === self::STATUS_PAST_DUE) {
            if (!$this->past_due_at) {
                return false;
            }

            $graceDays = max(0, (int) config('saas.grace_days', 0));

            return now()->lte($this->past_due_at->copy()->addDays($graceDays));
        }

        return false;
    }

    public function isContractExpired(): bool
    {
        $effectiveEnd = $this->extended_until ?? $this->contract_ends_at;

        return $effectiveEnd && $effectiveEnd->isPast();
    }

    public function usesAsaas(): bool
    {
        return $this->payment_method !== self::PAYMENT_BANK_DEPOSIT;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Rascunho',
            self::STATUS_TRIAL => 'Período de teste',
            self::STATUS_ACTIVE => 'Ativa',
            self::STATUS_PAST_DUE => 'Inadimplente',
            self::STATUS_SUSPENDED => 'Suspensa',
            self::STATUS_CANCELLED => 'Cancelada',
            self::STATUS_EXPIRED => 'Expirada',
            default => $this->status,
        };
    }

    public function billingMetricLabel(): string
    {
        return match ($this->billing_metric) {
            self::METRIC_USER => 'Por usuário',
            self::METRIC_FIXED => 'Preço fixo',
            default => 'Por unidade',
        };
    }

    public function billingCycleLabel(): string
    {
        return match ($this->billing_cycle) {
            self::CYCLE_QUARTERLY => 'Trimestral',
            self::CYCLE_SEMIANNUAL => 'Semestral',
            self::CYCLE_ANNUAL => 'Anual',
            default => 'Mensal',
        };
    }

    public function paymentMethodLabel(): string
    {
        return match ($this->payment_method) {
            self::PAYMENT_CREDIT_CARD => 'Cartão de crédito',
            self::PAYMENT_PIX_RECURRING => 'PIX recorrente',
            self::PAYMENT_BANK_DEPOSIT => 'Depósito bancário',
            default => 'Boleto',
        };
    }
}
