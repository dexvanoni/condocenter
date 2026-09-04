<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveOccurrenceBookCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $entry = $this->route('entry');

        return $entry && $this->user()?->can('comment', $entry);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'show_syndic_comment_publicly' => $this->boolean('show_syndic_comment_publicly'),
        ]);
    }

    public function rules(): array
    {
        return [
            'syndic_comment' => ['required', 'string', 'max:5000'],
            'show_syndic_comment_publicly' => ['nullable', 'boolean'],
        ];
    }
}
