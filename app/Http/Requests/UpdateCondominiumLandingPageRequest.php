<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCondominiumLandingPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage_landing_page') ?? false;
    }

    public function rules(): array
    {
        $pageId = \App\Models\CondominiumLandingPage::query()
            ->where('condominium_id', $this->user()?->tenantCondominiumId())
            ->value('id');

        return [
            'slug' => [
                'nullable',
                'string',
                'max:120',
                'alpha_dash',
                Rule::unique('condominium_landing_pages', 'slug')->ignore($pageId),
            ],
            'custom_domain' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('condominium_landing_pages', 'custom_domain')->ignore($pageId),
            ],
            'is_published' => ['nullable', 'boolean'],
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_subtitle' => ['nullable', 'string', 'max:255'],
            'hero_image' => ['nullable', 'image', 'max:5120'],
            'hero_gallery' => ['nullable', 'array'],
            'hero_gallery.*' => ['image', 'max:5120'],
            'remove_hero_image' => ['nullable', 'boolean'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'about_title' => ['nullable', 'string', 'max:255'],
            'about_content' => ['nullable', 'string', 'max:10000'],
            'accent_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_whatsapp' => ['nullable', 'string', 'max:30'],
            'show_rides_feed' => ['nullable', 'boolean'],
            'show_marketplace_feed' => ['nullable', 'boolean'],
            'show_platform_news' => ['nullable', 'boolean'],
            'show_announcements_feed' => ['nullable', 'boolean'],
        ];
    }
}
