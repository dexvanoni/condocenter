<?php

namespace App\Http\Requests;

use App\Support\EmployeeEntryTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeFinancialEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage_employees') ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(EmployeeEntryTypes::all())],
            'reference_date' => ['required', 'date'],
            'competence_month' => ['nullable', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'hours' => ['nullable', 'numeric', 'min:0'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'vacation_start' => ['nullable', 'date', 'required_if:type,vacation'],
            'vacation_end' => ['nullable', 'date', 'after_or_equal:vacation_start', 'required_if:type,vacation'],
            'description' => ['nullable', 'string', 'max:1000'],
            'tax_breakdown' => ['nullable', 'array'],
            'tax_breakdown.*' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'vacation_start.required_if' => 'Informe o início das férias.',
            'vacation_end.required_if' => 'Informe o fim das férias.',
        ];
    }
}
