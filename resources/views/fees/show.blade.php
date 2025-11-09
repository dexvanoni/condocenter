@extends('layouts.app')

@section('title', 'Detalhes da Taxa')

@once
    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    @endpush
@endonce

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2 class="mb-0">{{ $fee->name }}</h2>
                <p class="text-muted mb-0">Resumo da taxa, unidades vinculadas e cobranças geradas.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('fees.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                @can('manage_charges')
                    <a href="{{ route('fees.edit', $fee) }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                    <form action="{{ route('fees.generate', $fee) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-lightning-charge"></i> Gerar próxima cobrança
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
</div>

@can('manage_transactions')
    @php
        $paymentMethods = [
            'cash' => 'Dinheiro',
            'pix' => 'PIX',
            'bank_transfer' => 'Transferência bancária',
            'credit_card' => 'Cartão de crédito',
            'debit_card' => 'Cartão de débito',
            'boleto' => 'Boleto',
            'payroll' => 'Desconto em folha',
            'other' => 'Outro',
        ];
    @endphp

    <div class="modal fade" id="markAllPaidModal" tabindex="-1" aria-labelledby="markAllPaidModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('fees.charges.mark-all-paid', $fee) }}">
                @csrf
                <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="markAllPaidModalLabel">Efetivar pagamentos (todos)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">Marcará todas as cobranças pendentes desta taxa como pagas.</p>
                        <div class="mb-3">
                            <label class="form-label">Data do pagamento</label>
                            <input type="date" name="paid_at" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Forma de pagamento</label>
                            <select name="payment_method" class="form-select" required>
                                @foreach($paymentMethods as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observações (opcional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Informações adicionais"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Confirmar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @foreach($charges as $charge)
        <div class="modal fade" id="markPaidModal-{{ $charge->id }}" tabindex="-1" aria-labelledby="markPaidModalLabel-{{ $charge->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('charges.mark-paid', $charge) }}">
                    @csrf
                    <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="markPaidModalLabel-{{ $charge->id }}">Efetivar pagamento</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">
                                <strong>{{ $charge->title }}</strong><br>
                                Valor: R$ {{ number_format($charge->amount, 2, ',', '.') }}
                            </p>
                            <div class="mb-3">
                                <label class="form-label">Data do pagamento</label>
                                <input type="date" name="paid_at" class="form-control" value="{{ optional($charge->due_date)->format('Y-m-d') ?? now()->format('Y-m-d') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Forma de pagamento</label>
                                @php
                                    $defaultPaymentMethod = $charge->metadata['payment_channel'] ?? 'system';
                                    if ($defaultPaymentMethod === 'system') {
                                        $defaultPaymentMethod = 'pix';
                                    }
                                @endphp
                                <select name="payment_method" class="form-select" required>
                                    @foreach($paymentMethods as $value => $label)
                                        <option value="{{ $value }}" {{ $defaultPaymentMethod === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Observações (opcional)</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Informações complementares sobre o pagamento"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success">Confirmar pagamento</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="revokePayrollModal-{{ $charge->id }}" tabindex="-1" aria-labelledby="revokePayrollModalLabel-{{ $charge->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('charges.revoke-payroll', $charge) }}">
                    @csrf
                    <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="revokePayrollModalLabel-{{ $charge->id }}">Revogar desconto em folha</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">Esta ação desfaz o lançamento automático via folha e reabre a cobrança como pendente.</p>
                            <div class="mb-3">
                                <label class="form-label">Motivo (opcional)</label>
                                <textarea name="reason" class="form-control" rows="3" placeholder="Explique o motivo da revogação, se necessário."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-warning">Revogar desconto</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endcan

<div class="row g-3">
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Informações gerais</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5">Status</dt>
                    <dd class="col-7">
                        <span class="badge {{ $fee->active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $fee->active ? 'Ativa' : 'Inativa' }}
                        </span>
                    </dd>

                    <dt class="col-5">Valor base</dt>
                    <dd class="col-7 fw-semibold text-success">
                        R$ {{ number_format($fee->amount, 2, ',', '.') }}
                    </dd>

                    <dt class="col-5">Recorrência</dt>
                    <dd class="col-7 text-capitalize">{{ $fee->recurrence }}</dd>

                    @if(in_array($fee->recurrence, ['monthly', 'quarterly', 'yearly']) && $fee->due_day)
                        <dt class="col-5">Dia de vencimento</dt>
                        <dd class="col-7">{{ $fee->due_day }}</dd>
                    @endif

                    @if($fee->due_offset_days)
                        <dt class="col-5">Antecedência</dt>
                        <dd class="col-7">{{ $fee->due_offset_days }} dia(s)</dd>
                    @endif

                    <dt class="col-5">Tipo</dt>
                    <dd class="col-7">
                        @switch($fee->billing_type)
                            @case('condominium_fee') Taxa condominial @break
                            @case('fine') Multa @break
                            @case('extra') Taxa extra @break
                            @case('reservation') Reserva de espaço @break
                            @default {{ $fee->billing_type }}
                        @endswitch
                    </dd>

                    @if($fee->bankAccount)
                        <dt class="col-5">Conta recebedora</dt>
                        <dd class="col-7">{{ $fee->bankAccount->name }}</dd>
                    @endif

                    <dt class="col-5">Geração automática</dt>
                    <dd class="col-7">
                        <span class="badge {{ $fee->auto_generate_charges ? 'bg-success' : 'bg-secondary' }}">
                            {{ $fee->auto_generate_charges ? 'Ativada' : 'Desativada' }}
                        </span>
                    </dd>

                    <dt class="col-5">Última geração</dt>
                    <dd class="col-7">
                        {{ $fee->last_generated_at ? $fee->last_generated_at->diffForHumans() : 'Nunca' }}
                    </dd>

                    <dt class="col-5">Vigência</dt>
                    <dd class="col-7">
                        {{ $fee->starts_at ? $fee->starts_at->format('d/m/Y') : 'Imediata' }}
                        @if($fee->ends_at)
                            <br>até {{ $fee->ends_at->format('d/m/Y') }}
                        @endif
                    </dd>
                </dl>

                @if($fee->description)
                    <hr>
                    <h6>Descrição</h6>
                    <p class="text-muted">{{ $fee->description }}</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-light">
                <h5 class="mb-0">Unidades vinculadas ({{ $fee->configurations->count() }})</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="fee-configurations-table" class="table table-sm table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Unidade</th>
                                <th>Morador</th>
                                <th>Forma de pagamento</th>
                                <th>Valor</th>
                                <th>Vigência</th>
                                <th>Notas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fee->configurations as $configuration)
                                <tr>
                                    <td class="fw-semibold">{{ $configuration->unit->full_identifier }}</td>
                                    <td>{{ optional($configuration->unit->morador)->name ?? 'Não cadastrado' }}</td>
                                    <td class="text-uppercase">
                                        {{ $configuration->payment_channel === 'system' ? 'Sistema' : 'Desconto em folha' }}
                                    </td>
                                    <td>
                                        @if($configuration->custom_amount)
                                            <span class="text-success fw-semibold">
                                                R$ {{ number_format($configuration->custom_amount, 2, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-muted">Valor padrão</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($configuration->starts_at || $configuration->ends_at)
                                            <span class="badge bg-light text-dark">
                                                {{ $configuration->starts_at ? $configuration->starts_at->format('d/m/Y') : 'Início' }}
                                                &rarr;
                                                {{ $configuration->ends_at ? $configuration->ends_at->format('d/m/Y') : 'Indef.' }}
                                            </span>
                                        @else
                                            <span class="text-muted">Padrão</span>
                                        @endif
                                    </td>
                                    <td>{{ $configuration->notes ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-0">Cobranças geradas</h5>
                    <span class="text-muted small">Listagem das cobranças associadas a esta taxa</span>
                </div>
                @can('manage_transactions')
                    <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#markAllPaidModal">
                        Efetivar pagamentos (todos)
                    </button>
                @endcan
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="fee-charges-table" class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Unidade</th>
                                <th>Título</th>
                                <th>Vencimento</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Período</th>
                                @can('manage_transactions')
                                    <th class="text-end">Ações</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($charges as $charge)
                                <tr>
                                    <td>{{ optional($charge->unit)->full_identifier ?? '—' }}</td>
                                    <td>{{ $charge->title }}</td>
                                    <td>{{ $charge->due_date?->format('d/m/Y') }}</td>
                                    <td>R$ {{ number_format($charge->amount, 2, ',', '.') }}</td>
                                    <td>
                                        <span class="badge
                                            @if($charge->status === 'paid') bg-success
                                            @elseif($charge->status === 'overdue') bg-danger
                                            @elseif($charge->status === 'cancelled') bg-secondary
                                            @else bg-warning text-dark
                                            @endif">
                                            {{ ucfirst($charge->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $charge->recurrence_period ?? '—' }}</td>
                                    @can('manage_transactions')
                                        @php
                                            $paymentChannel = $charge->metadata['payment_channel'] ?? 'system';
                                        @endphp
                                        <td class="text-end">
                                            @if($paymentChannel === 'payroll' && $charge->status === 'paid')
                                                <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#revokePayrollModal-{{ $charge->id }}">
                                                    Revogar desconto
                                                </button>
                                            @endif
                                            @if($charge->status !== 'paid')
                                                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#markPaidModal-{{ $charge->id }}">
                                                    Efetivar pagamento
                                                </button>
                                            @endif
                                        </td>
                                    @endcan
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@once
    @push('scripts')
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    @endpush
@endonce

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const languageUrl = 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json';

        const configurationsTable = $('#fee-configurations-table');
        if (configurationsTable.length) {
            configurationsTable.DataTable({
                paging: true,
                pageLength: 10,
                ordering: false,
                language: {
                    url: languageUrl
                }
            });
        }

        const chargesTable = $('#fee-charges-table');
        if (chargesTable.length) {
            chargesTable.DataTable({
                paging: true,
                pageLength: 15,
                order: [[2, 'desc']], // ordenar por vencimento
                language: {
                    url: languageUrl
                }
            });
        }
    });
</script>
@endpush

