<?php

namespace App\Http\Requests\Package;

use Illuminate\Foundation\Http\FormRequest;

class FindPackageByPickupCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('register_packages') ?? false;
    }

    public function rules(): array
    {
        return [
            'pickup_code' => ['required', 'string', 'size:4', 'regex:/^\d{4}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'pickup_code.required' => 'Informe a senha de retirada.',
            'pickup_code.size' => 'A senha deve ter exatamente 4 dígitos.',
            'pickup_code.regex' => 'A senha deve conter apenas números.',
        ];
    }
}
