@extends('layouts.app')

@section('title', 'Multa ' . $fine->reference)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="mb-1">Multa {{ $fine->reference }}</h2>
        <p class="text-muted mb-0">
            Aplicada em {{ $fine->applied_at->format('d/m/Y H:i') }}
            por {{ $fine->appliedBy?->name ?? '—' }}
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @can('export', $fine)
            <a href="{{ route('fines.export-pdf', $fine) }}" class="btn btn-primary">
                <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
            </a>
        @endcan
        <a href="{{ route('fines.index') }}" class="btn btn-outline-secondary">Voltar</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Dados da multa</h5>
                <span class="badge bg-{{ $fine->isCancelled() ? 'secondary' : 'danger' }}">{{ $fine->status_label }}</span>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Enquadramento</dt>
                    <dd class="col-sm-9">{{ $fine->enquadramento }}</dd>

                    <dt class="col-sm-3">Valor</dt>
                    <dd class="col-sm-9 fw-semibold">R$ {{ number_format($fine->amount, 2, ',', '.') }}</dd>

                    <dt class="col-sm-3">Vencimento</dt>
                    <dd class="col-sm-9">{{ $fine->due_date->format('d/m/Y') }}</dd>

                    <dt class="col-sm-3">Motivo</dt>
                    <dd class="col-sm-9">{{ $fine->motivo }}</dd>

                    @if($fine->notes)
                        <dt class="col-sm-3">Observações</dt>
                        <dd class="col-sm-9">{{ $fine->notes }}</dd>
                    @endif

                    @if($fine->isCancelled())
                        <dt class="col-sm-3">Cancelada em</dt>
                        <dd class="col-sm-9">{{ optional($fine->cancelled_at)->format('d/m/Y H:i') ?? '—' }}</dd>

                        <dt class="col-sm-3">Motivo cancel.</dt>
                        <dd class="col-sm-9">{{ $fine->cancellation_reason }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">Destinatários</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Infrator</th>
                                <th>Unidade</th>
                                <th>Notificado</th>
                                <th>Cobrança</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fine->recipients as $recipient)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $recipient->user?->name }}</div>
                                        <small class="text-muted">
                                            {{ $recipient->user?->isAgregado() ? 'Agregado' : 'Morador' }}
                                        </small>
                                    </td>
                                    <td>{{ $recipient->unit?->full_identifier ?? '—' }}</td>
                                    <td>
                                        {{ $recipient->notifiedUser?->name ?? '—' }}
                                        @if($recipient->user_id !== $recipient->notified_user_id)
                                            <br><small class="text-muted">Responsável</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($recipient->charge)
                                            <a href="{{ route('charges.index') }}?search={{ urlencode($recipient->charge->title) }}">
                                                #{{ $recipient->charge->id }}
                                            </a>
                                            @php
                                                $chargeBadge = match($recipient->charge->status) {
                                                    'pending' => 'warning',
                                                    'overdue' => 'danger',
                                                    'paid' => 'success',
                                                    'cancelled' => 'secondary',
                                                    default => 'secondary',
                                                };
                                                $chargeLabel = match($recipient->charge->status) {
                                                    'pending' => 'Pendente',
                                                    'overdue' => 'Em atraso',
                                                    'paid' => 'Pago',
                                                    'cancelled' => 'Cancelada',
                                                    default => $recipient->charge->status,
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $chargeBadge }} ms-1">{{ $chargeLabel }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        @if(($isMoradorView ?? false) && $residentContext)
            <div class="card shadow-sm mb-3 border-{{ $residentContext['payment_status_color'] }}">
                <div class="card-header bg-{{ $residentContext['payment_status_color'] }} {{ in_array($residentContext['payment_status_color'], ['warning', 'success']) ? 'text-dark' : 'text-white' }}">
                    <h5 class="mb-0"><i class="bi bi-credit-card"></i> Pagamento</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <span class="text-muted">Status:</span>
                        <span class="badge bg-{{ $residentContext['payment_status_color'] }}">
                            {{ $residentContext['payment_status_label'] }}
                        </span>
                    </p>
                    @if(!empty($residentContext['paid_at']))
                        <p class="small text-muted mb-3">
                            Pago em {{ $residentContext['paid_at']->format('d/m/Y H:i') }}
                        </p>
                    @endif

                    @if($residentContext['can_pay_online'])
                        <button type="button"
                                class="btn btn-success w-100"
                                onclick="openChargeCheckout({{ $residentContext['charge_id'] }})">
                            <i class="bi bi-wallet2"></i> Pagar online
                        </button>
                        <p class="small text-muted mt-2 mb-0">
                            PIX, cartão ou boleto via Asaas.
                        </p>
                    @elseif(in_array($residentContext['charge_status'], ['pending', 'overdue']))
                        <a href="{{ route('my-charges.index') }}" class="btn btn-outline-primary w-100">
                            Ver em Cobranças
                        </a>
                        @if(!($onlinePaymentsEnabled ?? false))
                            <p class="small text-muted mt-2 mb-0">
                                Pagamento online ainda não está habilitado pelo condomínio.
                            </p>
                        @endif
                    @endif
                </div>
            </div>
        @endif

        @can('cancel', $fine)
            @if(!$fine->isCancelled())
                <div class="card shadow-sm border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">Cancelar multa</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('fines.cancel', $fine) }}"
                              onsubmit="return confirm('Confirma o cancelamento desta multa e das cobranças pendentes vinculadas?')">
                            @csrf
                            <div class="mb-3">
                                <label for="reason" class="form-label">Motivo do cancelamento</label>
                                <textarea name="reason" id="reason" rows="3" class="form-control @error('reason') is-invalid @enderror"
                                          required minlength="10">{{ old('reason') }}</textarea>
                                @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <button type="submit" class="btn btn-outline-danger w-100">Cancelar multa</button>
                        </form>
                    </div>
                </div>
            @endif
        @endcan
    </div>
</div>

@if(($isMoradorView ?? false) && ($onlinePaymentsEnabled ?? false))
    @include('charges.partials.payment-checkout')
@endif
@endsection
