<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCondominiumWhatsAppSettingsRequest extends FormRequest
{
    public function authorize(): bool
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

    public function rules(): array
    {
        return [
            'enabled' => ['nullable', 'boolean'],
            'api_url' => ['required', 'url', 'max:500'],
            'api_key' => ['nullable', 'string', 'max:500'],
            'instance' => ['required', 'string', 'max:120'],
            'notify_groups' => ['nullable', 'array'],
            'notify_groups.*' => ['nullable', 'boolean'],
            'announcements_group' => ['nullable', 'string', 'max:120'],
            'test_phone' => ['nullable', 'string', 'max:30'],
        ];
    }
}
