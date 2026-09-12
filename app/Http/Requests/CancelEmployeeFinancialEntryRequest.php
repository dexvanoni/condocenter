<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelEmployeeFinancialEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage_employees') ?? false;
    }

    public function rules(): array
    {
        return [
            'cancellation_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }
}
