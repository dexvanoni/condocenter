<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOccurrenceBookSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user
            && $user->isSindico()
            && $user->can('manage_occurrence_book');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'occurrence_book_public_enabled' => $this->boolean('occurrence_book_public_enabled'),
        ]);
    }

    public function rules(): array
    {
        return [
            'occurrence_book_public_enabled' => ['required', 'boolean'],
        ];
    }
}
