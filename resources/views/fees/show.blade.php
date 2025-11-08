@extends('layouts.app')

@section('title', 'Detalhes da Taxa')

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
                    <table class="table table-sm table-hover mb-0 align-middle">
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
                            @forelse($fee->configurations as $configuration)
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
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Nenhuma unidade vinculada. Edite a taxa para selecionar as unidades.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Cobranças geradas</h5>
                <span class="text-muted small">Listagem das cobranças associadas a esta taxa</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Unidade</th>
                                <th>Título</th>
                                <th>Vencimento</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Período</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($charges as $charge)
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
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Ainda não há cobranças geradas para esta taxa.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($charges->hasPages())
                <div class="card-footer">
                    {{ $charges->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

