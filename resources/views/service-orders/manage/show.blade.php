@extends('layouts.app')

@section('title', 'Gerenciar OS ' . $order->protocol)

@push('styles')
@include('service-orders.partials.form-styles')
@endpush

@section('content')
<div class="os-form-page">
    <div class="os-form-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <a href="{{ route('service-orders.manage.index') }}" class="os-form-back">
                    <i class="bi bi-arrow-left"></i> Voltar à gestão
                </a>
                <h1 class="os-form-title mt-2 mb-1">{{ $order->title }}</h1>
                <p class="os-form-subtitle mb-0">
                    {{ $order->protocol }} · {{ $order->requester->name }}
                    @if($order->unit) ({{ $order->unit->full_identifier }}) @endif
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge {{ $order->status_badge_class }} fs-6">{{ $order->status_label }}</span>
                <span class="badge {{ $order->urgency_badge_class }} fs-6">{{ $order->urgency_label }}</span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success credits-wallet-card">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <section class="os-form-section">
                <h2 class="h5 mb-3">Solicitação</h2>
                <p>{{ $order->description }}</p>
                <div class="row g-2 small text-muted">
                    <div class="col-md-4"><strong>Tipo:</strong> {{ $order->type_label }}</div>
                    <div class="col-md-4"><strong>Local:</strong> {{ $order->location_type_label }}@if($order->location_detail) — {{ $order->location_detail }}@endif</div>
                    <div class="col-md-4"><strong>WhatsApp:</strong> {{ $order->whatsapp_notify ? 'Sim' : 'Não' }}</div>
                    <div class="col-md-4"><strong>Data pref.:</strong> {{ $order->preferred_date?->format('d/m/Y') ?? '—' }}</div>
                    <div class="col-md-4"><strong>Horário:</strong>
                        @if($order->preferred_time_start){{ substr($order->preferred_time_start, 0, 5) }}@if($order->preferred_time_end) — {{ substr($order->preferred_time_end, 0, 5) }}@endif @else — @endif
                    </div>
                    @if($order->availability_notes)
                        <div class="col-12"><strong>Disponibilidade:</strong> {{ $order->availability_notes }}</div>
                    @endif
                </div>
            </section>

            <section class="os-form-section">
                <h2 class="h5 mb-3"><i class="bi bi-chat-left-text"></i> Interação com solicitante</h2>
                <div class="mb-3" style="max-height: 360px; overflow-y: auto;">
                    @foreach($order->messages as $message)
                        @php
                            $isRequester = $message->user_id === $order->user_id;
                            $classes = $message->is_internal ? 'is-internal' : ($isRequester ? 'is-other' : 'is-mine');
                        @endphp
                        <div class="os-message-bubble {{ $classes }}">
                            <div class="small text-muted mb-1">
                                {{ $message->author->name }}
                                @if($message->is_internal) <span class="badge bg-warning text-dark">Interna</span> @endif
                                · {{ $message->created_at->format('d/m/Y H:i') }}
                            </div>
                            <div>{!! nl2br(e($message->message)) !!}</div>
                        </div>
                    @endforeach
                </div>

                <form method="POST" action="{{ route('service-orders.manage.messages.store', $order) }}">
                    @csrf
                    <textarea name="message" class="form-control mb-2" rows="3" required placeholder="Responder ao morador ou registrar nota..."></textarea>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <button class="btn btn-primary btn-sm"><i class="bi bi-send"></i> Enviar ao solicitante</button>
                        <div class="form-check ms-2">
                            <input class="form-check-input" type="checkbox" name="is_internal" value="1" id="is_internal">
                            <label class="form-check-label" for="is_internal">Nota interna (só administração)</label>
                        </div>
                    </div>
                </form>
            </section>

            <section class="os-form-section">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0"><i class="bi bi-box-seam"></i> Materiais e serviços (ressarcimento)</h2>
                    <div class="d-flex gap-2">
                        <span class="badge bg-secondary">Total geral: R$ {{ number_format($order->reimbursement_total, 2, ',', '.') }}</span>
                        @if($order->unbilled_total > 0)
                            <span class="badge bg-warning text-dark">Pendente: R$ {{ number_format($order->unbilled_total, 2, ',', '.') }}</span>
                        @endif
                    </div>
                </div>

                @if($order->items->isNotEmpty())
                    <div class="table-responsive mb-3">
                        <table class="table table-sm align-middle">
                            <thead><tr><th>Tipo</th><th>Descrição</th><th>Qtd</th><th>Unit.</th><th>Total</th><th>Cobrança</th><th></th></tr></thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>{{ $item->type_label }}</td>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ number_format($item->quantity, 2, ',', '.') }}</td>
                                        <td>R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                                        <td>R$ {{ number_format($item->total, 2, ',', '.') }}</td>
                                        <td>
                                            @if($item->charge_id)
                                                <span class="badge bg-success">#{{ $item->charge_id }}</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Pendente</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if(!$item->charge_id)
                                            <form method="POST" action="{{ route('service-orders.manage.items.destroy', [$order, $item]) }}" onsubmit="return confirm('Remover este item?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted small">Nenhum item de ressarcimento adicionado.</p>
                @endif

                <form method="POST" action="{{ route('service-orders.manage.items.store', $order) }}" class="row g-2 align-items-end border-top pt-3">
                    @csrf
                    <div class="col-md-2">
                        <label class="form-label small">Tipo</label>
                        <select name="type" class="form-select form-select-sm" required>
                            @foreach(\App\Models\ServiceOrderItem::TYPES as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Descrição</label>
                        <input type="text" name="description" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Qtd</label>
                        <input type="number" name="quantity" class="form-control form-control-sm" value="1" min="0.01" step="0.01" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Valor unit.</label>
                        <input type="number" name="unit_price" class="form-control form-control-sm" min="0.01" step="0.01" required>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-sm btn-outline-primary w-100"><i class="bi bi-plus"></i> Adicionar</button>
                    </div>
                </form>

                @if($order->charges->isNotEmpty())
                    <div class="mt-3">
                        <h3 class="h6">Cobranças geradas</h3>
                        @foreach($order->charges as $charge)
                            <div class="alert alert-{{ $charge->status === 'paid' ? 'success' : 'light border' }} py-2 mb-2">
                                <i class="bi bi-receipt-cutoff"></i>
                                <strong>#{{ $charge->id }}</strong> — {{ $charge->title }}<br>
                                <span class="small">R$ {{ number_format($charge->amount, 2, ',', '.') }} · venc. {{ $charge->due_date?->format('d/m/Y') }} · {{ $charge->status }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($order->unbilled_total > 0)
                    <form method="POST" action="{{ route('service-orders.manage.charge.generate', $order) }}" class="row g-2 align-items-end border-top pt-3 mt-3">
                        @csrf
                        <div class="col-md-4">
                            <label class="form-label small">Vencimento da cobrança</label>
                            <input type="date" name="due_date" class="form-control form-control-sm" value="{{ now()->addDays(10)->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-5">
                            <button class="btn btn-warning w-100">
                                <i class="bi bi-cash-stack"></i>
                                {{ $order->charges->isNotEmpty() ? 'Gerar cobrança adicional' : 'Gerar cobrança ao morador' }}
                                (R$ {{ number_format($order->unbilled_total, 2, ',', '.') }})
                            </button>
                        </div>
                    </form>
                @endif
            </section>
        </div>

        <div class="col-lg-4">
            <section class="os-form-section">
                <h2 class="h5 mb-3"><i class="bi bi-kanban"></i> Fluxo da OS</h2>
                <form method="POST" action="{{ route('service-orders.manage.status.update', $order) }}">
                    @csrf @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            @foreach(\App\Models\ServiceOrder::STATUSES as $value => $label)
                                <option value="{{ $value }}" @selected($order->status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Responsável / despachado para</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Não atribuído</option>
                            @foreach($assignees as $assignee)
                                <option value="{{ $assignee->id }}" @selected($order->assigned_to === $assignee->id)>{{ $assignee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notas de resolução</label>
                        <textarea name="resolution_notes" class="form-control" rows="4">{{ old('resolution_notes', $order->resolution_notes) }}</textarea>
                    </div>
                    <button class="btn btn-primary w-100"><i class="bi bi-check2-circle"></i> Atualizar status</button>
                </form>
            </section>

            <section class="os-form-section">
                <h2 class="h5 mb-3">Histórico</h2>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2">Criada: {{ $order->created_at->format('d/m/Y H:i') }}</li>
                    @if($order->dispatched_at)<li class="mb-2">Despachada: {{ $order->dispatched_at->format('d/m/Y H:i') }}</li>@endif
                    @if($order->resolved_at)<li class="mb-2">Resolvida/encerrada: {{ $order->resolved_at->format('d/m/Y H:i') }}</li>@endif
                    @if($order->assignee)<li class="mb-2">Atribuída a: {{ $order->assignee->name }}</li>@endif
                </ul>
            </section>
        </div>
    </div>
</div>
@endsection
