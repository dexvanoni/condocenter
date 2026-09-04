@extends('layouts.app')

@section('title', 'OS ' . $order->protocol)

@push('styles')
@include('service-orders.partials.form-styles')
@endpush

@section('content')
<div class="os-form-page">
    <div class="os-form-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <a href="{{ route('service-orders.index') }}" class="os-form-back">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                <h1 class="os-form-title mt-2 mb-1">{{ $order->title }}</h1>
                <p class="os-form-subtitle mb-0">{{ $order->protocol }} · {{ $order->type_label }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge {{ $order->status_badge_class }} fs-6">{{ $order->status_label }}</span>
                <span class="badge {{ $order->urgency_badge_class }} fs-6">{{ $order->urgency_label }}</span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show credits-wallet-card">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <section class="os-form-section">
                <h2 class="h5 mb-3">Detalhes</h2>
                <p class="mb-3">{{ $order->description }}</p>
                <div class="row g-3 small">
                    <div class="col-md-6"><strong>Local:</strong> {{ $order->location_type_label }}@if($order->location_detail) — {{ $order->location_detail }}@endif</div>
                    <div class="col-md-6"><strong>Unidade:</strong> {{ $order->unit?->full_identifier ?? '—' }}</div>
                    <div class="col-md-6"><strong>Data preferencial:</strong> {{ $order->preferred_date?->format('d/m/Y') ?? 'Não informada' }}</div>
                    <div class="col-md-6"><strong>Horário:</strong>
                        @if($order->preferred_time_start)
                            {{ substr($order->preferred_time_start, 0, 5) }}@if($order->preferred_time_end) — {{ substr($order->preferred_time_end, 0, 5) }}@endif
                        @else
                            Não informado
                        @endif
                    </div>
                    @if($order->availability_notes)
                        <div class="col-12"><strong>Disponibilidade:</strong> {{ $order->availability_notes }}</div>
                    @endif
                    @if($order->resolution_notes)
                        <div class="col-12"><strong>Resolução:</strong> {{ $order->resolution_notes }}</div>
                    @endif
                </div>
            </section>

            <section class="os-form-section">
                <h2 class="h5 mb-3"><i class="bi bi-chat-dots"></i> Conversa com a administração</h2>
                <div class="mb-3" style="max-height: 420px; overflow-y: auto;">
                    @forelse($order->messages as $message)
                        @php $isMine = $message->user_id === auth()->id(); @endphp
                        <div class="os-message-bubble {{ $isMine ? 'is-mine' : 'is-other' }}">
                            <div class="small text-muted mb-1">{{ $message->author->name }} · {{ $message->created_at->format('d/m/Y H:i') }}</div>
                            <div>{!! nl2br(e($message->message)) !!}</div>
                        </div>
                    @empty
                        <p class="text-muted small">Nenhuma mensagem ainda. Envie uma mensagem para a administração.</p>
                    @endforelse
                </div>

                @if($order->isOpen())
                <form method="POST" action="{{ route('service-orders.messages.store', $order) }}">
                    @csrf
                    <div class="mb-2">
                        <textarea name="message" class="form-control" rows="3" required placeholder="Escreva sua mensagem..."></textarea>
                    </div>
                    <button class="btn btn-primary btn-sm"><i class="bi bi-send"></i> Enviar mensagem</button>
                </form>
                @else
                    <div class="alert alert-secondary mb-0">Esta ordem de serviço está encerrada.</div>
                @endif
            </section>
        </div>

        <div class="col-lg-5">
            @if($order->items->isNotEmpty() || $order->charges->isNotEmpty())
            <section class="os-form-section">
                <h2 class="h5 mb-3"><i class="bi bi-cash-coin"></i> Ressarcimento</h2>
                @if($order->items->isNotEmpty())
                    <div class="table-responsive mb-3">
                        <table class="table table-sm">
                            <thead><tr><th>Item</th><th>Qtd</th><th class="text-end">Total</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td><span class="badge bg-light text-dark">{{ $item->type_label }}</span> {{ $item->description }}</td>
                                        <td>{{ number_format($item->quantity, 2, ',', '.') }}</td>
                                        <td class="text-end">R$ {{ number_format($item->total, 2, ',', '.') }}</td>
                                        <td>
                                            @if($item->charge_id)
                                                <span class="badge bg-success">Cobrado</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Pendente</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr><th colspan="2">Total geral</th><th class="text-end">R$ {{ number_format($order->reimbursement_total, 2, ',', '.') }}</th><th></th></tr>
                                @if($order->unbilled_total > 0)
                                <tr><th colspan="2">Pendente de cobrança</th><th class="text-end text-warning">R$ {{ number_format($order->unbilled_total, 2, ',', '.') }}</th><th></th></tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>
                @endif
                @if($order->charges->isNotEmpty())
                    @foreach($order->charges as $charge)
                        <div class="alert alert-{{ $charge->status === 'paid' ? 'success' : 'warning' }} {{ $loop->last ? 'mb-0' : 'mb-2' }}">
                            <i class="bi bi-receipt"></i>
                            <strong>{{ $charge->title }}</strong><br>
                            R$ {{ number_format($charge->amount, 2, ',', '.') }} — vencimento {{ $charge->due_date?->format('d/m/Y') }}
                            <span class="badge bg-light text-dark ms-1">{{ $charge->status }}</span>
                        </div>
                    @endforeach
                    @if(Route::has('my-charges.index'))
                        <div class="mt-2"><a href="{{ route('my-charges.index') }}" class="btn btn-sm btn-outline-primary">Ver em Minhas Cobranças</a></div>
                    @endif
                @endif
            </section>
            @endif

            <section class="os-form-section">
                <h2 class="h5 mb-3">Linha do tempo</h2>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2"><i class="bi bi-circle-fill text-primary" style="font-size:0.5rem"></i> Aberta em {{ $order->created_at->format('d/m/Y H:i') }}</li>
                    @if($order->dispatched_at)
                        <li class="mb-2"><i class="bi bi-circle-fill text-info" style="font-size:0.5rem"></i> Despachada em {{ $order->dispatched_at->format('d/m/Y H:i') }}</li>
                    @endif
                    @if($order->resolved_at)
                        <li class="mb-2"><i class="bi bi-circle-fill text-success" style="font-size:0.5rem"></i> Atualizada em {{ $order->resolved_at->format('d/m/Y H:i') }}</li>
                    @endif
                </ul>
            </section>
        </div>
    </div>
</div>
@endsection
