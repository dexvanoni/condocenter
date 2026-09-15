<?php

namespace App\Http\Requests\Package;

use Illuminate\Foundation\Http\FormRequest;

class CollectPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('register_packages') ?? false;
    }

    public function rules(): array
    {
        return [
            'pickup_code' => ['nullable', 'string', 'size:4', 'regex:/^\d{4}$/'],
            'picked_up_by_name' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'pickup_code.size' => 'A senha deve ter exatamente 4 dígitos.',
            'pickup_code.regex' => 'A senha deve conter apenas números.',
            'picked_up_by_name.max' => 'O nome de quem retirou deve ter no máximo 150 caracteres.',
        ];
    }
}
