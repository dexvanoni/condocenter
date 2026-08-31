<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlatformAsaasSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'api_key' => ['nullable', 'string', 'max:255'],
            'sandbox' => ['nullable', 'boolean'],
            'webhook_email' => ['nullable', 'email', 'max:255'],
            'webhook_token' => ['nullable', 'string', 'max:255'],
        ];
    }
}
