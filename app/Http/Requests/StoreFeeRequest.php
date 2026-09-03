<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage_charges') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'recurrence' => ['required', 'in:monthly,quarterly,yearly,one_time,custom'],
            'due_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'due_offset_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'billing_type' => ['required', 'in:condominium_fee,fine,extra,reservation'],
            'bank_account_id' => ['nullable', 'integer', 'exists:bank_accounts,id'],
            'auto_generate_charges' => ['sometimes', 'boolean'],
            'generate_charges_now' => ['sometimes', 'boolean'],
            'active' => ['sometimes', 'boolean'],
            'apply_all_units' => ['sometimes', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'custom_schedule' => ['required_if:recurrence,custom', 'array', 'min:1'],
            'custom_schedule.*' => ['date'],

            'unit_configurations' => ['nullable', 'array'],
            'unit_configurations.*.unit_id' => ['required', 'integer', 'exists:units,id'],
            'unit_configurations.*.payment_channel' => ['required', 'in:system,payroll'],
            'unit_configurations.*.custom_amount' => ['nullable', 'numeric', 'min:0'],
            'unit_configurations.*.starts_at' => ['nullable', 'date'],
            'unit_configurations.*.ends_at' => ['nullable', 'date', 'after_or_equal:unit_configurations.*.starts_at'],
            'unit_configurations.*.notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('auto_generate_charges')) {
            $this->merge([
                'auto_generate_charges' => filter_var($this->get('auto_generate_charges'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('generate_charges_now')) {
            $this->merge([
                'generate_charges_now' => filter_var($this->get('generate_charges_now'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('active')) {
            $this->merge([
                'active' => filter_var($this->get('active'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('apply_all_units')) {
            $this->merge([
                'apply_all_units' => filter_var($this->get('apply_all_units'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('unit_configurations')) {
            $this->merge([
                'unit_configurations' => collect($this->input('unit_configurations', []))
                    ->filter(fn ($config) => !empty($config['unit_id']))
                    ->values()
                    ->all(),
            ]);
        }

        if ($this->has('custom_schedule_text')) {
            $dates = collect(preg_split('/\r\n|[\r\n]+/', (string) $this->input('custom_schedule_text')))
                ->map(fn ($date) => trim($date))
                ->filter();

            $this->merge([
                'custom_schedule' => $dates->all(),
            ]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $applyAll = filter_var($this->input('apply_all_units'), FILTER_VALIDATE_BOOLEAN);
            if ($applyAll) {
                return;
            }

            $configs = collect($this->input('unit_configurations', []))
                ->filter(fn ($config) => !empty($config['unit_id']));

            if ($configs->isEmpty()) {
                $validator->errors()->add(
                    'unit_configurations',
                    'Selecione ao menos uma unidade ou mantenha o modo padrão (unidades habitadas com morador).'
                );
            }
        });
    }
}

