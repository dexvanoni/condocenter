@can('manage_transactions')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-success btn-lg shadow-sm flex-fill flex-md-grow-0" data-bs-toggle="modal" data-bs-target="#modalRecebimento">
                <i class="bi bi-cash-coin"></i> RECEBER
            </button>
            <button type="button" class="btn btn-danger btn-lg shadow-sm flex-fill flex-md-grow-0" data-bs-toggle="modal" data-bs-target="#modalPagamento">
                <i class="bi bi-cart-check"></i> COMPRAR/PAGAR
            </button>
        </div>
    </div>
</div>
@endcan

<div class="row g-3 mb-4">
    <div class="col-xxl-2 col-lg-4 col-sm-6">
        <div class="sd-kpi sd-kpi--success">
            <span class="sd-kpi__label">Saldo do mês</span>
            <strong class="sd-kpi__value {{ $saldo >= 0 ? 'text-success' : 'text-danger' }}">R$ {{ number_format($saldo, 2, ',', '.') }}</strong>
            <small class="sd-kpi__hint">{{ $saldo >= 0 ? 'Superávit' : 'Déficit' }}</small>
        </div>
    </div>
    <div class="col-xxl-2 col-lg-4 col-sm-6">
        <div class="sd-kpi">
            <span class="sd-kpi__label">Receitas</span>
            <strong class="sd-kpi__value">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</strong>
            <small class="sd-kpi__hint {{ $variacaoReceitas >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($variacaoReceitas, 1) }}% vs mês anterior</small>
        </div>
    </div>
    <div class="col-xxl-2 col-lg-4 col-sm-6">
        <div class="sd-kpi">
            <span class="sd-kpi__label">Despesas</span>
            <strong class="sd-kpi__value">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</strong>
            <small class="sd-kpi__hint {{ $variacaoDespesas <= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($variacaoDespesas, 1) }}% vs mês anterior</small>
        </div>
    </div>
    <div class="col-xxl-2 col-lg-4 col-sm-6">
        <div class="sd-kpi">
            <span class="sd-kpi__label">Adimplência</span>
            <strong class="sd-kpi__value {{ $taxaAdimplencia >= 90 ? 'text-success' : 'text-warning' }}">{{ number_format($taxaAdimplencia, 1) }}%</strong>
            <small class="sd-kpi__hint">{{ $inadimplentes }} unidade(s) em atraso</small>
        </div>
    </div>
    <div class="col-xxl-2 col-lg-4 col-sm-6">
        <div class="sd-kpi">
            <span class="sd-kpi__label">A receber</span>
            <strong class="sd-kpi__value">R$ {{ number_format($totalAReceber, 2, ',', '.') }}</strong>
            <small class="sd-kpi__hint">Cobranças pendentes</small>
        </div>
    </div>
    <div class="col-xxl-2 col-lg-4 col-sm-6">
        <div class="sd-kpi sd-kpi--danger">
            <span class="sd-kpi__label">Em atraso</span>
            <strong class="sd-kpi__value text-danger">R$ {{ number_format($totalEmAtraso, 2, ',', '.') }}</strong>
            <small class="sd-kpi__hint">{{ $inadimplentes }} unidade(s)</small>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="sd-kpi">
            <span class="sd-kpi__label">Saldo consolidado</span>
            <strong class="sd-kpi__value text-primary">R$ {{ number_format($saldoConsolidado, 2, ',', '.') }}</strong>
            <small class="sd-kpi__hint">Contas bancárias</small>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="sd-kpi">
            <span class="sd-kpi__label">Entradas a conciliar</span>
            <strong class="sd-kpi__value text-success">R$ {{ number_format($entradasNaoConciliadas, 2, ',', '.') }}</strong>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="sd-kpi">
            <span class="sd-kpi__label">Saídas a conciliar</span>
            <strong class="sd-kpi__value text-danger">R$ {{ number_format($saidasNaoConciliadas, 2, ',', '.') }}</strong>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="sd-kpi">
            <span class="sd-kpi__label">Última conciliação</span>
            @if($ultimaConsolidacao)
                <strong class="sd-kpi__value" style="font-size:1.1rem;">{{ $ultimaConsolidacao->created_at->format('d/m/Y') }}</strong>
                <small class="sd-kpi__hint">{{ $ultimaConsolidacao->bankAccount->name ?? 'Conta' }}</small>
            @else
                <strong class="sd-kpi__value text-muted">—</strong>
                <small class="sd-kpi__hint">Nenhuma registrada</small>
            @endif
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="sd-panel h-100">
            <div class="sd-panel__head">
                <h3><i class="bi bi-bar-chart-line"></i> Evolução financeira (6 meses)</h3>
            </div>
            <div class="sd-panel__body">
                <canvas id="graficoFinanceiro" height="90"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="sd-panel h-100">
            <div class="sd-panel__head">
                <h3><i class="bi bi-pie-chart"></i> Adimplência</h3>
            </div>
            <div class="sd-panel__body">
                <canvas id="graficoAdimplencia" height="200"></canvas>
                <div class="d-flex justify-content-between mt-3 small">
                    <span class="text-success"><i class="bi bi-circle-fill"></i> {{ max($totalUnidades - $inadimplentes, 0) }} adimplentes</span>
                    <span class="text-danger"><i class="bi bi-circle-fill"></i> {{ $inadimplentes }} inadimplentes</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-7">
        <div class="sd-panel h-100">
            <div class="sd-panel__head">
                <h3><i class="bi bi-receipt"></i> Últimas transações</h3>
                @can('view_transactions')
                <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-primary">Ver todas</a>
                @endcan
            </div>
            <div class="sd-panel__body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle sd-table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Descrição</th>
                                <th>Tipo</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimasTransacoes as $transacao)
                            <tr>
                                <td class="text-nowrap">{{ $transacao->transaction_date->format('d/m/Y') }}</td>
                                <td>{{ Str::limit($transacao->description, 45) }}</td>
                                <td>
                                    <span class="badge rounded-pill bg-{{ $transacao->type === 'income' ? 'success' : 'danger' }}">
                                        {{ $transacao->type === 'income' ? 'Receita' : 'Despesa' }}
                                    </span>
                                </td>
                                <td class="text-end fw-semibold {{ $transacao->type === 'income' ? 'text-success' : 'text-danger' }}">
                                    {{ $transacao->type === 'income' ? '+' : '-' }} R$ {{ number_format($transacao->amount, 2, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Nenhuma transação registrada.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="sd-panel h-100">
            <div class="sd-panel__head">
                <h3><i class="bi bi-exclamation-octagon"></i> Inadimplência por unidade</h3>
            </div>
            <div class="sd-panel__body">
                @forelse($inadimplentesDetalhe as $item)
                <div class="sd-list-item">
                    <div>
                        <strong>{{ $item['unit']?->full_identifier ?? 'Unidade' }}</strong>
                        <small class="d-block text-muted">
                            {{ $item['count'] }} cobrança(s)
                            @if($item['oldest_due'])
                                · venc. {{ \Carbon\Carbon::parse($item['oldest_due'])->format('d/m/Y') }}
                            @endif
                        </small>
                    </div>
                    <span class="fw-bold text-danger">R$ {{ number_format($item['total'], 2, ',', '.') }}</span>
                </div>
                @empty
                <p class="text-muted mb-0 text-center py-3">Nenhuma unidade inadimplente no momento.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const moneyTick = value => 'R$ ' + Number(value).toLocaleString('pt-BR');

    const ctx = document.getElementById('graficoFinanceiro');
    if (ctx) {
        const data = @json($graficoFinanceiro);
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => d.mes),
                datasets: [
                    { label: 'Receitas', data: data.map(d => d.receitas), borderColor: '#11998e', backgroundColor: 'rgba(17,153,142,.1)', fill: true, tension: 0.35 },
                    { label: 'Despesas', data: data.map(d => d.despesas), borderColor: '#eb3349', backgroundColor: 'rgba(235,51,73,.08)', fill: true, tension: 0.35 },
                    { label: 'Saldo', data: data.map(d => d.saldo), borderColor: '#f4a261', borderDash: [6,6], tension: 0.35 },
                ],
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' } },
                scales: { y: { ticks: { callback: moneyTick } } },
            },
        });
    }

    const adimplenciaCanvas = document.getElementById('graficoAdimplencia');
    if (adimplenciaCanvas) {
        const dados = @json($graficoAdimplencia);
        new Chart(adimplenciaCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Adimplentes', 'Inadimplentes'],
                datasets: [{ data: [dados.adimplentes, dados.inadimplentes], backgroundColor: ['#11998e', '#eb3349'], borderWidth: 0 }],
            },
            options: { responsive: true, cutout: '68%', plugins: { legend: { display: false } } },
        });
    }
});
</script>
@endpush
