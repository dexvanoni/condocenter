<?php

namespace App\Http\Requests;

use App\Models\OccurrenceBookEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOccurrenceBookEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', OccurrenceBookEntry::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'notify_whatsapp' => $this->boolean('notify_whatsapp'),
        ]);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(array_keys(OccurrenceBookEntry::TYPES))],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'notify_whatsapp' => ['nullable', 'boolean'],
        ];
    }
}
