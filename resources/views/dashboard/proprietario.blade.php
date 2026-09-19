@extends('layouts.app')

@section('title', 'Dashboard — Proprietário')

@section('content')
<div class="container-fluid">
    @include('dashboard.partials.profile-photo-alert')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-person-badge text-brand"></i> Área do proprietário</h1>
            <p class="text-muted mb-0">Acompanhe seus imóveis de aluguel e contratos com inquilinos.</p>
        </div>
    </div>

    @if($leaseAlerts->isNotEmpty())
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
                        O contrato <strong>já encerrou</strong>. O acesso do inquilino e dos agregados foi suspenso.
                        Para renovar, atualize a validade do contrato na ficha da unidade.
                    </p>
                @else
                    <p class="mb-2">
                        Faltam <strong>{{ $days }}</strong> dia(s) para o fim do contrato.
                        Após essa data, o acesso do inquilino e dos agregados será <strong>suspenso automaticamente</strong>.
                    </p>
                @endif
                <a href="{{ route('units.edit', $unit) }}" class="btn btn-sm {{ $expired ? 'btn-danger' : 'btn-warning' }}">
                    Atualizar contrato na unidade
                </a>
            </div>
        @endforeach
    @else
        <div class="alert alert-success border-0 mb-4">
            <i class="bi bi-check-circle"></i> Nenhum contrato de locação próximo do vencimento nos seus imóveis.
        </div>
    @endif

    @include('dashboard.partials.morador-quick-actions')
</div>
@endsection
