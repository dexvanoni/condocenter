<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayChargeWithCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'holder_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'cpf_cnpj' => ['required', 'string', 'max:18'],
            'postal_code' => ['required', 'string', 'max:10'],
            'address_number' => ['required', 'string', 'max:20'],
            'phone' => ['required', 'string', 'max:30'],
            'number' => ['required', 'string', 'max:19'],
            'expiry_month' => ['required', 'string', 'max:2'],
            'expiry_year' => ['required', 'string', 'max:4'],
            'ccv' => ['required', 'string', 'max:4'],
        ];
    }
}
