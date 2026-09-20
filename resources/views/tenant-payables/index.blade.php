@extends('layouts.app')

@section('title', 'Minhas pendências')

@php
    use App\Helpers\SidebarHelper;
    $user = auth()->user();
@endphp

@section('content')
<div class="container py-4">
    <h1 class="h3 mb-3"><i class="bi bi-wallet2"></i> Minhas pendências</h1>
    <p class="text-muted">
        Em imóveis de aluguel, taxas do condomínio são acompanhadas pelo proprietário.
        Aqui você vê <strong>multas</strong> e <strong>cobranças de reserva/serviço</strong> sob sua responsabilidade.
    </p>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <strong>Cobranças pendentes</strong>
        </div>
        <div class="card-body p-0">
            @if($charges->isEmpty())
                <p class="text-muted p-3 mb-0">Nenhuma cobrança pendente no momento.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($charges as $charge)
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <strong>{{ $charge->title }}</strong>
                                <div class="small text-muted">Vencimento: {{ $charge->due_date?->format('d/m/Y') }}</div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-semibold">R$ {{ number_format((float) $charge->amount, 2, ',', '.') }}</span>
                                @if($onlinePaymentsEnabled)
                                    <button type="button" class="btn btn-sm btn-success" onclick="window.openChargeCheckout({{ $charge->id }})">
                                        Pagar online
                                    </button>
                                @endif
                                <a href="{{ route('tenant-payables.charges.show', $charge) }}" class="btn btn-sm btn-outline-primary">Detalhes</a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <strong>Multas</strong>
        </div>
        <div class="card-body p-0">
            @if($fines->isEmpty())
                <p class="text-muted p-3 mb-0">Nenhuma multa registrada em seu nome.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($fines as $fine)
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <strong>{{ $fine->reference }}</strong> — {{ $fine->enquadramento }}
                                <div class="small text-muted">Vencimento: {{ $fine->due_date?->format('d/m/Y') }}</div>
                            </div>
                            <a href="{{ route('tenant-payables.fines.show', $fine) }}" class="btn btn-sm btn-outline-primary">Detalhes</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>

@if($onlinePaymentsEnabled)
    @include('charges.partials.payment-checkout', [
        'chargePaymentBaseUrl' => SidebarHelper::chargePaymentBaseUrl($user),
    ])
@endif
@endsection
