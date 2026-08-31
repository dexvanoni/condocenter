<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ResolvesTenantCondominium;
use Illuminate\Foundation\Http\FormRequest;

class StoreUnitRequest extends FormRequest
{
    use ResolvesTenantCondominium;

    public function authorize(): bool
    {
        return $this->user()->can('create_units');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'condominium_id' => $this->tenantCondominiumId(),
        ]);
    }

    public function rules(): array
    {
        $condominiumId = $this->tenantCondominiumId();

        return [
            'condominium_id' => ['required', 'integer', 'in:' . $condominiumId],
            'number' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) use ($condominiumId) {
                    $exists = \App\Models\Unit::where('condominium_id', $condominiumId)
                        ->where('number', $value)
                        ->where('block', $this->block)
                        ->exists();

                    if ($exists) {
                        $blockText = $this->block ? " e bloco '{$this->block}'" : '';
                        $fail("Já existe uma unidade com o número '{$value}'{$blockText} neste condomínio.");
                    }
                },
            ],
            'block' => ['nullable', 'string', 'max:50'],
            'type' => ['required', 'in:residential,commercial'],
            'situacao' => ['required', 'in:habitado,fechado,indisponivel,em_obra'],
            'floor' => ['nullable', 'integer'],
            'morador_id' => [
                'nullable',
                'integer',
                'exists:users,id',
                function ($attribute, $value, $fail) use ($condominiumId) {
                    if (!$value) {
                        return;
                    }

                    $exists = \App\Models\User::query()
                        ->where('id', $value)
                        ->where('condominium_id', $condominiumId)
                        ->whereHas('roles', fn ($q) => $q->where('name', 'Morador'))
                        ->exists();

                    if (!$exists) {
                        $fail('Selecione um morador válido deste condomínio.');
                    }
                },
            ],
            'possui_dividas' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'condominium_id.required' => 'Selecione um condomínio para continuar.',
            'condominium_id.in' => 'A unidade deve pertencer ao condomínio selecionado.',
            'number.required' => 'O número da unidade é obrigatório.',
            'type.required' => 'O tipo da unidade é obrigatório.',
            'type.in' => 'O tipo deve ser residencial ou comercial.',
            'situacao.required' => 'A situação da unidade é obrigatória.',
            'situacao.in' => 'Situação inválida.',
        ];
    }
}
