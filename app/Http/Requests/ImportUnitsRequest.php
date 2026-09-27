<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportUnitsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create_units') ?? false;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:5120',
                'mimes:csv,txt,xlsx',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Selecione um arquivo .xlsx ou .csv.',
            'file.mimes' => 'Formato inválido. Use .xlsx ou .csv (modelo do SindCON).',
            'file.max' => 'O arquivo deve ter no máximo 5 MB.',
        ];
    }
}
