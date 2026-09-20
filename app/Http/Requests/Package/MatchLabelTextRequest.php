<?php

namespace App\Http\Requests\Package;

use Illuminate\Foundation\Http\FormRequest;

class MatchLabelTextRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('register_packages') ?? false;
    }

    public function rules(): array
    {
        return [
            'ocr_text' => ['required', 'string', 'min:8', 'max:20000'],
            'ocr_confidence' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'barcode_value' => ['nullable', 'string', 'max:255'],
            'ocr_engine' => ['nullable', 'string', 'max:64'],
        ];
    }
}
