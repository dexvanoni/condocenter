<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnalyzeFinanceAiRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->isSindico()
            && session('active_role') === 'Síndico';
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'question' => [
                'required',
                'string',
                Rule::in(array_keys(config('finance_ai.questions', []))),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'question.required' => 'Selecione uma pergunta para análise.',
            'question.in' => 'Pergunta inválida.',
        ];
    }
}
