@props([
    'variant' => 'default',
    'class' => '',
    'alt' => null,
])

@php
    $altText = $alt ?? config('brand.logo_alt', config('app.name', 'SindCON'));
    $onDark = asset(config('brand.logo_sidebar_path', config('brand.logo_path', 'images/logo_sindcon_br_fundo-removebg-preview.png')));
    $onLight = asset(config('brand.logo_on_light_path', 'images/logo_sindcon_sem_nome.png'));

    $src = match ($variant) {
        'sidebar', 'auth', 'footer' => $onDark,
        'navbar', 'inline' => $onLight,
        default => $onDark,
    };

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
