<?php

namespace App\Http\Requests;

use App\Models\Condominium;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCondominiumRequest extends FormRequest
{
    public function authorize(): bool
    {
        $condominium = $this->route('condominium');

        if (!$condominium instanceof Condominium) {
            return false;
        }

        return $this->user()?->can('update', $condominium) ?? false;
    }

    public function rules(): array
    {
        $condominiumId = $this->route('condominium')?->id ?? $this->route('condominium');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'cnpj' => [
                'nullable',
                'string',
                'max:18',
                Rule::unique('condominiums', 'cnpj')->ignore($condominiumId),
            ],
            'address' => ['required', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'size:2'],
            'zip_code' => ['required', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'marketplace_allow_agregados' => ['nullable', 'boolean'],
        ];

        if ($this->user()?->isAdmin()) {
            $rules['financial_mode'] = ['required', Rule::in(['full', 'simplified'])];
            $rules['is_active'] = ['nullable', 'boolean'];
            $rules['units_limit'] = [
                'required',
                'integer',
                'min:1',
                'max:50000',
                function ($attribute, $value, $fail) {
                    $condominium = $this->route('condominium');
                    if (!$condominium instanceof Condominium) {
                        return;
                    }
                    $inUse = $condominium->units()->count();
                    if ((int) $value < $inUse) {
                        $fail("O limite não pode ser menor que o número de unidades já cadastradas ({$inUse}).");
                    }
                },
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return (new StoreCondominiumRequest())->messages();
    }
}
