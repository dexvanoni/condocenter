<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ResolvesTenantCondominium;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    use ResolvesTenantCondominium;

    public function authorize(): bool
    {
        return $this->user()->can('manage_users');
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
            'unit_id' => [
                'nullable',
                'exists:units,id',
                function ($attribute, $value, $fail) use ($condominiumId) {
                    if (!$value) {
                        return;
                    }

                    $belongsToTenant = \App\Models\Unit::query()
                        ->where('id', $value)
                        ->where('condominium_id', $condominiumId)
                        ->exists();

                    if (!$belongsToTenant) {
                        $fail('Selecione uma unidade válida deste condomínio.');
                    }
                },
            ],
            'morador_vinculado_id' => ['nullable', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'telefone_residencial' => ['nullable', 'string', 'max:20'],
            'telefone_celular' => ['nullable', 'string', 'max:20'],
            'telefone_comercial' => ['nullable', 'string', 'max:20'],
            'cpf' => ['required', 'string', 'size:14', 'unique:users,cpf', 'regex:/^\d{3}\.\d{3}\.\d{3}-\d{2}$/'],
            'cnh' => ['nullable', 'string', 'max:20'],
            'data_nascimento' => ['nullable', 'date', 'before:today'],
            'data_entrada' => ['nullable', 'date'],
            'data_saida' => ['nullable', 'date', 'after:data_entrada'],
            'necessita_cuidados_especiais' => ['boolean'],
            'descricao_cuidados_especiais' => ['nullable', 'string', 'required_if:necessita_cuidados_especiais,true'],
            'local_trabalho' => ['nullable', 'string', 'max:255'],
            'contato_comercial' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,name'],
            'agregado_permissions' => ['array', 'nullable'],
            'agregado_permissions.*.module' => ['string', 'in:' . implode(',', array_keys(\App\Models\AgregadoPermission::getAvailablePermissions()))],
            'agregado_permissions.*.level' => ['string', 'in:view,crud'],
            'possui_dividas' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'condominium_id.required' => 'Selecione um condomínio para continuar.',
            'condominium_id.in' => 'O usuário deve pertencer ao condomínio selecionado.',
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Digite um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'cpf.regex' => 'CPF inválido. Use o formato 000.000.000-00.',
            'data_nascimento.before' => 'A data de nascimento deve ser anterior a hoje.',
            'data_saida.after' => 'A data de saída deve ser posterior à data de entrada.',
            'descricao_cuidados_especiais.required_if' => 'Descreva os cuidados especiais necessários.',
            'photo.image' => 'O arquivo deve ser uma imagem.',
            'photo.max' => 'A foto não pode ser maior que 2MB.',
            'roles.required' => 'Selecione pelo menos um perfil.',
            'roles.min' => 'Selecione pelo menos um perfil.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('roles')) {
                $restrictedRoles = ['Síndico', 'Conselho Fiscal'];
                $requestedRoles = $this->input('roles', []);

                if (array_intersect($restrictedRoles, $requestedRoles) && !$this->user()->hasRole('Administrador')) {
                    $validator->errors()->add('roles', 'Apenas administradores podem atribuir os perfis de Síndico ou Conselho Fiscal.');
                }
            }

            if ($this->has('roles') && in_array('Agregado', $this->input('roles', []))) {
                if (!$this->input('morador_vinculado_id')) {
                    $validator->errors()->add('morador_vinculado_id', 'Agregados devem estar vinculados a um morador.');
                }
            }

            if ($this->has('roles')) {
                $rolesWithoutUnit = ['Administrador', 'Porteiro'];
                $requestedRoles = $this->input('roles', []);
                $needsUnit = !array_intersect($rolesWithoutUnit, $requestedRoles);

                if ($needsUnit && !$this->input('unit_id')) {
                    $validator->errors()->add('unit_id', 'Este perfil requer que uma unidade seja vinculada.');
                }
            }
        });
    }
}
