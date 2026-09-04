<?php

namespace App\Http\Requests;

use App\Models\CondominiumLandingItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCondominiumLandingItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage_landing_page') ?? false;
    }

    public function rules(): array
    {
        return $this->itemRules();
    }

    protected function itemRules(): array
    {
        return [
            'type' => ['required', Rule::in(array_keys(CondominiumLandingItem::TYPES))],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:10000'],
            'image' => ['nullable', 'image', 'max:5120'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'max:5120'],
            'event_starts_at' => ['nullable', 'date'],
            'event_ends_at' => ['nullable', 'date', 'after_or_equal:event_starts_at'],
            'is_popup' => ['nullable', 'boolean'],
            'popup_starts_at' => ['nullable', 'date'],
            'popup_ends_at' => ['nullable', 'date', 'after_or_equal:popup_starts_at'],
            'is_featured' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
