<?php

namespace App\Http\Requests;

use App\Models\OccurrenceBookEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportOccurrenceBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('export', OccurrenceBookEntry::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['nullable', Rule::in(array_keys(OccurrenceBookEntry::TYPES))],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', Rule::in(['pending', 'acknowledged'])],
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function filters(): array
    {
        return $this->only(['type', 'start_date', 'end_date', 'status', 'search']);
    }
}
