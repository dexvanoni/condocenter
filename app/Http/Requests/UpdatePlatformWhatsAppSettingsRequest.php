<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlatformWhatsAppSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'enabled' => ['nullable', 'boolean'],
            'api_url' => ['required', 'url', 'max:500'],
            'api_key' => ['nullable', 'string', 'max:500'],
            'instance' => ['required', 'string', 'max:120'],
            'notify_groups' => ['nullable', 'array'],
            'notify_groups.*' => ['nullable', 'boolean'],
            'test_phone' => ['nullable', 'string', 'max:30'],
        ];
    }
}
