<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage_bank_statements') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'institution' => ['nullable', 'string', 'max:255'],
            'holder_name' => ['nullable', 'string', 'max:255'],
            'document_number' => ['nullable', 'string', 'max:20'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'agency' => ['nullable', 'string', 'max:50'],
            'account' => ['nullable', 'string', 'max:50'],
            'type' => ['required', 'in:checking,savings,payment,other'],
            'pix_key' => ['nullable', 'string', 'max:255'],
            'current_balance' => ['nullable', 'numeric'],
            'balance_updated_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'active' => $this->boolean('active'),
        ]);
    }
}

