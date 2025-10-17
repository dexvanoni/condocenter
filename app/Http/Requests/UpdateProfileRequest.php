<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->route('user');
        
        // Usuário só pode editar a si mesmo
        return $this->user()->id === $user->id;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id;
        
        return [
            // Dados pessoais básicos
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:20'],
            'data_nascimento' => ['nullable', 'date', 'before:today'],
            
            // Contatos adicionais
            'telefone_residencial' => ['nullable', 'string', 'max:20'],
            'telefone_celular' => ['nullable', 'string', 'max:20'],
            'telefone_comercial' => ['nullable', 'string', 'max:20'],
            
            // Informações profissionais
            'local_trabalho' => ['nullable', 'string', 'max:255'],
            'contato_comercial' => ['nullable', 'string', 'max:20'],
            
            // Foto
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Por favor, insira um e-mail válido.',
            'email.unique' => 'Este e-mail já está sendo usado por outro usuário.',
            'phone.max' => 'O telefone não pode ter mais de 20 caracteres.',
            'data_nascimento.date' => 'A data de nascimento deve ser uma data válida.',
            'data_nascimento.before' => 'A data de nascimento deve ser anterior a hoje.',
            'telefone_residencial.max' => 'O telefone residencial não pode ter mais de 20 caracteres.',
            'telefone_celular.max' => 'O telefone celular não pode ter mais de 20 caracteres.',
            'telefone_comercial.max' => 'O telefone comercial não pode ter mais de 20 caracteres.',
            'local_trabalho.max' => 'O local de trabalho não pode ter mais de 255 caracteres.',
            'contato_comercial.max' => 'O contato comercial não pode ter mais de 20 caracteres.',
            'photo.image' => 'A foto deve ser uma imagem válida.',
            'photo.max' => 'A foto não pode ter mais de 2MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'e-mail',
            'phone' => 'telefone',
            'data_nascimento' => 'data de nascimento',
            'telefone_residencial' => 'telefone residencial',
            'telefone_celular' => 'telefone celular',
            'telefone_comercial' => 'telefone comercial',
            'local_trabalho' => 'local de trabalho',
            'contato_comercial' => 'contato comercial',
            'photo' => 'foto',
        ];
    }
}
