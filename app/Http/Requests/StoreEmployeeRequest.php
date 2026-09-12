<?php

namespace App\Http\Requests;

use App\Models\Employee;
use App\Support\EmployeeEntryTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage_employees') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'cpf' => ['nullable', 'string', 'max:14'],
            'rg' => ['nullable', 'string', 'max:20'],
            'position' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'admission_date' => ['required', 'date'],
            'termination_date' => ['nullable', 'date', 'after_or_equal:admission_date'],
            'base_salary' => ['required', 'numeric', 'min:0'],
            'work_schedule' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(array_keys(Employee::statusLabels()))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
