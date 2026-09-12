<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelCondominiumAccountExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage_transactions') ?? false;
    }

    public function rules(): array
    {
        return [
            'cancellation_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'cancellation_reason.required' => 'Informe o motivo do cancelamento.',
            'cancellation_reason.min' => 'O motivo deve ter pelo menos :min caracteres.',
            'cancellation_reason.max' => 'O motivo não pode exceder :max caracteres.',
        ];
    }
}
