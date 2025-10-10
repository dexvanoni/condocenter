<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit_units');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $unitId = $this->route('unit')->id;
        
        return [
            'condominium_id' => ['sometimes', 'required', 'exists:condominiums,id'],
            'number' => [
                'sometimes',
                'required', 
                'string', 
                'max:50',
                function ($attribute, $value, $fail) use ($unitId) {
                    $exists = \App\Models\Unit::where('condominium_id', $this->condominium_id)
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
            'situacao' => ['sometimes', 'required', 'in:habitado,fechado,indisponivel,em_obra'],
            'cep' => ['nullable', 'string', 'regex:/^\d{5}-?\d{3}$/'],
            'logradouro' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:100'],
            'bairro' => ['nullable', 'string', 'max:100'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'size:2'],
            'ideal_fraction' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'area' => ['nullable', 'numeric', 'min:0'],
            'floor' => ['nullable', 'integer'],
            'num_quartos' => ['nullable', 'integer', 'min:0'],
            'num_banheiros' => ['nullable', 'integer', 'min:0'],
            'foto' => ['nullable', 'image', 'max:2048'], // 2MB max
            'possui_dividas' => ['boolean'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'condominium_id.required' => 'O condomínio é obrigatório.',
            'condominium_id.exists' => 'O condomínio selecionado não existe.',
            'number.required' => 'O número da unidade é obrigatório.',
            'type.required' => 'O tipo da unidade é obrigatório.',
            'type.in' => 'O tipo deve ser residencial ou comercial.',
            'situacao.required' => 'A situação da unidade é obrigatória.',
            'situacao.in' => 'Situação inválida.',
            'cep.regex' => 'CEP inválido. Use o formato 00000-000.',
            'estado.size' => 'O estado deve ter 2 caracteres (UF).',
            'foto.image' => 'O arquivo deve ser uma imagem.',
            'foto.max' => 'A foto não pode ser maior que 2MB.',
        ];
    }
}

