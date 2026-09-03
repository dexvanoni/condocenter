<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ResolvesTenantCondominium;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitRequest extends FormRequest
{
    use ResolvesTenantCondominium;

    public function authorize(): bool
    {
        return $this->user()->can('edit_units');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'condominium_id' => $this->tenantCondominiumId(),
        ]);
    }

    public function rules(): array
    {
        $unitId = $this->route('unit')->id;
        $condominiumId = $this->tenantCondominiumId();

        return [
            'condominium_id' => ['required', 'integer', 'in:' . $condominiumId],
            'number' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) use ($unitId, $condominiumId) {
                    $exists = \App\Models\Unit::where('condominium_id', $condominiumId)
                        ->where('number', $value)
                        ->where('block', $this->block)
                        ->where('id', '!=', $unitId)
                        ->exists();

                    if ($exists) {
                        $blockText = $this->block ? " e bloco '{$this->block}'" : '';
                        $fail("Já existe uma unidade com o número '{$value}'{$blockText} neste condomínio.");
                    }
                },
            ],
            'block' => ['nullable', 'string', 'max:50'],
            'type' => ['sometimes', 'required', 'in:residential,commercial'],
            'unit_model' => ['sometimes', 'required', \App\Support\UnitModels::validationRule()],
            'situacao' => ['sometimes', 'required', 'in:habitado,fechado,indisponivel,em_obra'],
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
            'unit_model.required' => 'Selecione o modelo da unidade.',
            'unit_model.in' => 'Modelo de unidade inválido.',
            'situacao.required' => 'A situação da unidade é obrigatória.',
            'situacao.in' => 'Situação inválida.',
        ];
    }
}
