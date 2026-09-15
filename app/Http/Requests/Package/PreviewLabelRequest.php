<?php

namespace App\Http\Requests\Package;

use Illuminate\Foundation\Http\FormRequest;

class PreviewLabelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('register_packages') ?? false;
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'max:8192'],
            'barcode_value' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'Capture ou envie a foto da etiqueta.',
            'image.image' => 'O arquivo deve ser uma imagem.',
            'image.max' => 'A imagem deve ter no máximo 8MB.',
        ];
    }
}
