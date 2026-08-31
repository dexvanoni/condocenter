<?php

namespace App\Http\Requests;

use App\Models\CondominiumSubscription;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCondominiumSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'subscription_plan_id' => ['nullable', 'exists:subscription_plans,id'],
            'financial_responsible_user_id' => ['nullable', 'exists:users,id'],
            'billing_metric' => ['required', Rule::in([
                CondominiumSubscription::METRIC_UNIT,
                CondominiumSubscription::METRIC_USER,
            ])],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'user_price' => ['nullable', 'numeric', 'min:0'],
            'billing_cycle' => ['required', Rule::in([
                CondominiumSubscription::CYCLE_MONTHLY,
                CondominiumSubscription::CYCLE_QUARTERLY,
                CondominiumSubscription::CYCLE_SEMIANNUAL,
                CondominiumSubscription::CYCLE_ANNUAL,
            ])],
            'trial_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'payment_method' => ['required', Rule::in([
                CondominiumSubscription::PAYMENT_BOLETO,
                CondominiumSubscription::PAYMENT_CREDIT_CARD,
                CondominiumSubscription::PAYMENT_PIX_RECURRING,
                CondominiumSubscription::PAYMENT_BANK_DEPOSIT,
            ])],
            'financial_cnpj' => ['nullable', 'string', 'max:18'],
            'financial_contact_name' => ['nullable', 'string', 'max:255'],
            'financial_contact_email' => ['nullable', 'email', 'max:255'],
            'financial_contact_phone' => ['nullable', 'string', 'max:30'],
            'contract_starts_at' => ['nullable', 'date'],
            'contract_ends_at' => ['nullable', 'date', 'after_or_equal:contract_starts_at'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
