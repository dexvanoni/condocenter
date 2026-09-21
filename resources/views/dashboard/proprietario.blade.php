@extends('layouts.app')

@section('title', 'Dashboard — Proprietário')

@section('content')
<div class="container-fluid">
    @include('dashboard.partials.profile-photo-alert')

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="mb-1"><i class="bi bi-person-badge text-brand"></i> Área do proprietário</h1>
            <p class="text-muted mb-0">Panorama das unidades, moradores, cobranças e multas vinculadas a você.</p>
        </div>
        @if(isset($condominium))
            <span class="badge bg-light text-dark border">{{ $condominium->name }}</span>
        @endif
    </div>

    @include('dashboard.partials.proprietario-quick-actions')

    @if($leaseAlerts->isNotEmpty())
        <h5 class="mt-4 mb-3"><i class="bi bi-calendar-exclamation"></i> Contratos de locação</h5>
        @foreach($leaseAlerts as $alert)
            @php
                $unit = $alert['unit'];
                $days = $alert['days'];
                $expired = $alert['expired'];
            @endphp
            <div class="alert {{ $expired ? 'alert-danger' : 'alert-warning' }} border-0 shadow-sm mb-3">
                <h6 class="alert-heading mb-2">
                    <i class="bi bi-calendar-x"></i> Unidade {{ $unit->full_identifier }}
                </h6>
                <p class="mb-2">
                    Contrato do inquilino
                    <strong>{{ $alert['morador']?->name ?? '—' }}</strong>
                    @if($unit->lease_contract_ends_at)
                        — término em <strong>{{ $unit->lease_contract_ends_at->format('d/m/Y') }}</strong>.
                    @endif
                </p>
                @if($expired)
                    <p class="mb-2">
                        O contrato <strong>já encerrou</strong>. Atualize a validade na ficha da unidade para restabelecer o acesso do inquilino.
                    </p>
                @else
                    <p class="mb-2">
                        Faltam <strong>{{ $days }}</strong> dia(s) para o fim do contrato.
                    </p>
                @endif
                @can('update', $unit)
                    <a href="{{ route('units.edit', $unit) }}" class="btn btn-sm {{ $expired ? 'btn-danger' : 'btn-warning' }}">
                        Atualizar contrato na unidade
                    </a>
                @endcan
            </div>
        @endforeach
    @endif

    @include('dashboard.partials.proprietario-units-panorama', ['ownerPanorama' => $ownerPanorama ?? ['units' => collect(), 'summary' => []]])

    @if(($ownerPanorama['summary']['open_owner_charges'] ?? 0) > 0 && Route::has('my-charges.index'))
        <div class="alert alert-warning border-0 shadow-sm d-flex flex-wrap align-items-center justify-content-between gap-2">
            <span>
                <i class="bi bi-exclamation-circle me-1"></i>
                Você tem cobranças do condomínio em aberto no total de
                <strong>R$ {{ number_format($ownerPanorama['summary']['owner_pending_amount'] ?? 0, 2, ',', '.') }}</strong>.
            </span>
            <a href="{{ route('my-charges.index') }}" class="btn btn-sm btn-warning">Ir para Minhas cobranças</a>
        </div>
    @endif
</div>
@endsection
