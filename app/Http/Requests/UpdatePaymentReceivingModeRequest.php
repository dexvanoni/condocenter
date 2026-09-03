<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentReceivingModeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->canManageReceiving();
    }

    public function rules(): array
    {
        return [
            'payment_receiving_mode' => ['required', Rule::in(['manual', 'platform'])],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_receiving_mode.required' => 'Selecione como o condomínio irá receber os pagamentos.',
            'payment_receiving_mode.in' => 'Modo de recebimento inválido.',
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
