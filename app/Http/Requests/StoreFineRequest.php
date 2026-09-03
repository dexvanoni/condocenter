<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage_fines') ?? false;
    }

    public function rules(): array
    {
        return [
            'motivo' => ['required', 'string', 'min:10', 'max:5000'],
            'enquadramento' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'motivo.required' => 'Informe o motivo da multa.',
            'motivo.min' => 'O motivo deve ter pelo menos 10 caracteres.',
            'enquadramento.required' => 'Informe o enquadramento da multa.',
            'user_ids.required' => 'Selecione ao menos um morador ou agregado.',
            'user_ids.min' => 'Selecione ao menos um morador ou agregado.',
            'due_date.after_or_equal' => 'A data de vencimento não pode ser anterior a hoje.',
        ];
    }
}
