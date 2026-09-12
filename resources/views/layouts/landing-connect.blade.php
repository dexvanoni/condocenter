<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $page->tagline ?? $page->hero_subtitle ?? $condominium->name }}">
    <title>{{ $page->hero_title ?? $condominium->name }} — Portal do Condomínio</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|sora:500,600,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    @vite(['resources/css/landing-connect.css', 'resources/js/landing-connect.js'])
    <style>
        :root {
            --cch-accent: {{ $page->accent_color ?? '#5b7fa8' }};
            --cch-primary: color-mix(in srgb, var(--cch-accent) 22%, #1a2332);
            --cch-primary-soft: color-mix(in srgb, var(--cch-accent) 12%, white);
        }
    </style>
</head>
<body class="cch-body">
    @yield('content')
    @stack('landing-modals')
    @stack('landing-popups')
</body>
</html>
