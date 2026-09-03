<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCondominiumAsaasSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->canManageReceiving();
    }

    public function rules(): array
    {
        return [
            'sandbox' => ['nullable', 'boolean'],
            'api_key' => ['nullable', 'string', 'max:500'],
            'webhook_email' => ['nullable', 'email', 'max:255'],
            'webhook_token' => ['nullable', 'string', 'max:500'],
            'regenerate_webhook_token' => ['nullable', 'boolean'],
        ];
    }

    protected function canManageReceiving(): bool
    {
        $user = $this->user();
        $condominium = $this->route('condominium');

        if (!$user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->isSindico()
            && $condominium instanceof \App\Models\Condominium
            && (int) $user->tenantCondominiumId() === (int) $condominium->id;
    }
}
