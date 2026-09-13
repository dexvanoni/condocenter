@props([
    'variant' => 'default',
    'class' => '',
    'alt' => null,
])

@php
    $altText = $alt ?? config('brand.logo_alt', config('app.name', 'SindCON'));
    $src = asset(config('brand.logo_path', 'images/logo_sindcon.png'));

    $variantClass = match ($variant) {
        'sidebar' => 'sindcon-logo sindcon-logo--sidebar',
        'auth' => 'sindcon-logo sindcon-logo--auth',
        'navbar' => 'sindcon-logo sindcon-logo--navbar',
        'footer' => 'sindcon-logo sindcon-logo--footer',
        'inline' => 'sindcon-logo sindcon-logo--inline',
        default => 'sindcon-logo',
    };
@endphp

<img
    src="{{ $src }}"
    alt="{{ $altText }}"
    class="{{ trim("{$variantClass} {$class}") }}"
    loading="lazy"
    decoding="async"
    {{ $attributes }}
>
