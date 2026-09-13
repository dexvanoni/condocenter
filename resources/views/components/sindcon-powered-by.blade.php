@props(['text' => 'Portal oficial do condomínio powered by'])

<div {{ $attributes->merge(['class' => 'sindcon-powered-by']) }}>
    <span>{{ $text }}</span>
    <span class="sindcon-logo-wrap--footer">
        <x-sindcon-logo variant="footer" />
    </span>
</div>
