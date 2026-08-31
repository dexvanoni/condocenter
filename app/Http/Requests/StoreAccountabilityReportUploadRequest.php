<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountabilityReportUploadRequest extends FormRequest
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
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
            'file' => 'required|file|mimes:pdf,xlsx,xls|max:15360',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'file.mimes' => 'O arquivo deve ser PDF, XLS ou XLSX.',
            'file.max' => 'O arquivo não pode exceder 15 MB.',
        ];
    }
}
