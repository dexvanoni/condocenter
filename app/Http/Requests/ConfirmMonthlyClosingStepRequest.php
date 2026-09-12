<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmMonthlyClosingStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage_transactions') ?? false;
    }

    public function rules(): array
    {
        return [
            'month' => ['required', 'date_format:Y-m'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
