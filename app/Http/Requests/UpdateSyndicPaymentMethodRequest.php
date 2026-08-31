<?php

namespace App\Http\Requests;

use App\Models\CondominiumSubscription;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSyndicPaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->isSindico() || $user->isAdmin());
    }

    public function rules(): array
    {
        return [
            'payment_method' => [
                'required',
                Rule::in([
                    CondominiumSubscription::PAYMENT_BOLETO,
                    CondominiumSubscription::PAYMENT_CREDIT_CARD,
                    CondominiumSubscription::PAYMENT_PIX_RECURRING,
                ]),
            ],
            'holder_name' => ['required_if:payment_method,credit_card', 'nullable', 'string', 'max:255'],
            'email' => ['required_if:payment_method,credit_card', 'nullable', 'email', 'max:255'],
            'cpf_cnpj' => ['required_if:payment_method,credit_card', 'nullable', 'string', 'max:18'],
            'postal_code' => ['required_if:payment_method,credit_card', 'nullable', 'string', 'max:10'],
            'address_number' => ['required_if:payment_method,credit_card', 'nullable', 'string', 'max:20'],
            'phone' => ['required_if:payment_method,credit_card', 'nullable', 'string', 'max:30'],
            'number' => ['required_if:payment_method,credit_card', 'nullable', 'string', 'max:19'],
            'expiry_month' => ['required_if:payment_method,credit_card', 'nullable', 'string', 'max:2'],
            'expiry_year' => ['required_if:payment_method,credit_card', 'nullable', 'string', 'max:4'],
            'ccv' => ['required_if:payment_method,credit_card', 'nullable', 'string', 'max:4'],
        ];
    }
}
