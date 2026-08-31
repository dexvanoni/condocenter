<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFinancialModeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user
            && $user->getActiveCondominiumId()
            && \App\Helpers\SidebarHelper::isAdminOrSindico($user);
    }

    public function rules(): array
    {
        return [
            'financial_mode' => ['required', Rule::in(['full', 'simplified'])],
        ];
    }
}
