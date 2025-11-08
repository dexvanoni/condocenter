<?php

namespace App\Http\Requests\Package;

use App\Models\Package;
use Illuminate\Foundation\Http\FormRequest;

class StorePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('register_packages') ?? false;
    }

    public function rules(): array
    {
        return [
            'unit_id' => ['required', 'exists:units,id'],
            'type' => ['required', 'in:' . implode(',', Package::TYPES)],
        ];
    }

    public function messages(): array
    {
        return [
            'unit_id.required' => 'Selecione a unidade que receberá a encomenda.',
            'unit_id.exists' => 'Unidade informada é inválida.',
            'type.required' => 'Selecione o tipo da encomenda.',
            'type.in' => 'Tipo de encomenda inválido.',
        ];
    }
}

