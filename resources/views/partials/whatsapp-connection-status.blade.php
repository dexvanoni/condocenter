@php
    $connection = $connection ?? ['ok' => false, 'state' => null, 'message' => 'Não foi possível consultar a Evolution.'];
    $managerUrl = filled($config['api_url'] ?? null)
        ? rtrim((string) $config['api_url'], '/') . '/manager'
        : null;
@endphp
@if($connection['ok'] ?? false)
    <div class="alert alert-success small mb-4">
        <i class="bi bi-check-circle"></i>
        {{ $connection['message'] }}
        @if(!empty($connection['state']))
            <span class="badge text-bg-success ms-1">{{ $connection['state'] }}</span>
        @endif
    </div>
@else
    <div class="alert alert-warning small mb-4">
        <i class="bi bi-exclamation-triangle"></i>
        <strong>A Evolution está configurada, mas a sessão do WhatsApp não está aberta.</strong>
        <div class="mt-1">{{ $connection['message'] ?? '' }}</div>
        @if(!empty($connection['state']))
            <span class="badge text-bg-warning mt-2">estado: {{ $connection['state'] }}</span>
        @endif
        @if($managerUrl)
            <div class="mt-2">
                <a href="{{ $managerUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-success">
                    <i class="bi bi-box-arrow-up-right"></i> Abrir Evolution Manager e escanear o QR
                </a>
            </div>
        @endif
    </div>
@endif
