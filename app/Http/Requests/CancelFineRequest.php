<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelFineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage_fines') ?? false;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Informe o motivo do cancelamento.',
            'reason.min' => 'O motivo do cancelamento deve ter pelo menos 10 caracteres.',
        ];
    }
}
