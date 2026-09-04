<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $page->tagline ?? $page->hero_subtitle ?? $condominium->name }}">
    <title>{{ $page->hero_title ?? $condominium->name }} — Portal do Condomínio</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    @vite(['resources/css/landing.css', 'resources/js/landing.js'])
    <style>
        :root {
            --landing-accent: {{ $page->accent_color ?? '#3866d2' }};
            --landing-accent-soft: color-mix(in srgb, var(--landing-accent) 18%, white);
            --landing-accent-glow: color-mix(in srgb, var(--landing-accent) 35%, transparent);
        }
    </style>
</head>
<body class="landing-body" data-accent="{{ $page->accent_color ?? '#3866d2' }}">
    @yield('content')
    @stack('landing-modals')
    @stack('landing-popups')
</body>
</html>
