<?php

namespace App\Http\Requests\Package;

use App\Models\Package;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmLabelPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('register_packages') ?? false;
    }

    public function rules(): array
    {
        return [
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'resident_id' => ['nullable', 'integer', 'exists:users,id'],
            'type' => ['required', 'in:' . implode(',', Package::TYPES)],
            'sender' => ['nullable', 'string', 'max:255'],
            'tracking_code' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'label_image_path' => ['nullable', 'string', 'max:500'],
            'ocr_text' => ['nullable', 'string'],
            'ocr_confidence' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'identification_method' => ['nullable', 'in:' . implode(',', Package::IDENTIFICATION_METHODS)],
            'identification_confidence' => ['nullable', 'numeric', 'min:0', 'max:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'unit_id.required' => 'Selecione a unidade destinatária.',
            'type.required' => 'Selecione o tipo da encomenda.',
        ];
    }
}
