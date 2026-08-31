<?php

namespace App\Http\Requests;

use App\Models\Condominium;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SelfRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'registration_code' => ['required', 'string', 'max:20'],
            'registration_type' => ['required', Rule::in(['compossuidor', 'dependente'])],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'cpf' => ['required', 'string', 'size:14', 'unique:users,cpf', 'regex:/^\d{3}\.\d{3}\.\d{3}-\d{2}$/'],
            'telefone_celular' => ['required', 'string', 'max:20'],
            'data_nascimento' => ['nullable', 'date', 'before:today'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'morador_vinculado_id' => ['nullable', 'integer', 'exists:users,id'],
            'photo' => ['required', 'image', 'mimes:jpeg,jpg,png', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'registration_code.required' => 'Informe o código de cadastro do condomínio.',
            'registration_type.required' => 'Selecione se você é compossuidor ou dependente.',
            'registration_type.in' => 'Tipo de cadastro inválido.',
            'name.required' => 'Informe seu nome completo.',
            'email.required' => 'Informe seu e-mail.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'password.required' => 'Crie uma senha de acesso.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'cpf.required' => 'Informe seu CPF.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'cpf.regex' => 'CPF inválido. Use o formato 000.000.000-00.',
            'telefone_celular.required' => 'Informe seu celular para contato.',
            'data_nascimento.before' => 'A data de nascimento deve ser anterior a hoje.',
            'photo.required' => 'Tire uma foto (selfie) para concluir o cadastro.',
            'photo.image' => 'A foto deve ser uma imagem válida.',
            'photo.max' => 'A foto não pode ser maior que 4MB.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $condominium = Condominium::query()
                ->active()
                ->where('registration_code', strtoupper(trim((string) $this->input('registration_code'))))
                ->first();

            if (!$condominium) {
                $validator->errors()->add('registration_code', 'Código de cadastro inválido ou condomínio inativo.');
                return;
            }

            if ($this->input('registration_type') === 'compossuidor' && !$this->input('unit_id')) {
                $validator->errors()->add('unit_id', 'Selecione a unidade do condomínio.');
            }

            if ($this->input('registration_type') === 'dependente' && !$this->input('morador_vinculado_id')) {
                $validator->errors()->add('morador_vinculado_id', 'Selecione o morador responsável por você.');
            }

            if ($this->filled('unit_id')) {
                $unit = Unit::query()->find($this->input('unit_id'));
                if (!$unit || (int) $unit->condominium_id !== (int) $condominium->id || !$unit->is_active) {
                    $validator->errors()->add('unit_id', 'Unidade inválida para este condomínio.');
                }
            }

            if ($this->filled('morador_vinculado_id')) {
                $morador = User::query()->find($this->input('morador_vinculado_id'));
                $isMorador = $morador
                    && (int) $morador->condominium_id === (int) $condominium->id
                    && $morador->hasAssignedRole('Morador')
                    && $morador->is_active;

                if (!$isMorador) {
                    $validator->errors()->add('morador_vinculado_id', 'Morador responsável inválido.');
                }
            }
        });
    }

    public function condominium(): ?Condominium
    {
        return Condominium::query()
            ->active()
            ->where('registration_code', strtoupper(trim((string) $this->input('registration_code'))))
            ->first();
    }
}
