<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCondominiumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'cnpj' => ['nullable', 'string', 'max:18', 'unique:condominiums,cnpj'],
            'address' => ['required', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'size:2'],
            'zip_code' => ['required', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'financial_mode' => ['required', Rule::in(['full', 'simplified'])],
            'marketplace_allow_agregados' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Informe o nome do condomínio.',
            'address.required' => 'Informe o endereço.',
            'city.required' => 'Informe a cidade.',
            'state.required' => 'Informe o estado (UF).',
            'state.size' => 'Use a sigla do estado com 2 letras.',
            'zip_code.required' => 'Informe o CEP.',
            'cnpj.unique' => 'Este CNPJ já está cadastrado.',
            'financial_mode.required' => 'Selecione o modo financeiro inicial.',
        ];
    }
}
