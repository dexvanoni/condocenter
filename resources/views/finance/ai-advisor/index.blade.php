@extends('layouts.app')

@section('title', 'Consultor Financeiro SindCON')

@php
    $questionVisuals = [
        'where_spending' => ['icon' => 'bi-pie-chart-fill', 'tone' => 'sky'],
        'reduce_energy' => ['icon' => 'bi-lightning-charge-fill', 'tone' => 'amber'],
        'expense_attention' => ['icon' => 'bi-exclamation-triangle-fill', 'tone' => 'rose'],
        'increase_revenue' => ['icon' => 'bi-graph-up-arrow', 'tone' => 'emerald'],
        'contracts_review' => ['icon' => 'bi-file-earmark-text-fill', 'tone' => 'indigo'],
        'default_analysis' => ['icon' => 'bi-hourglass-split', 'tone' => 'orange'],
        'expense_evolution' => ['icon' => 'bi-bar-chart-line-fill', 'tone' => 'violet'],
        'financial_health' => ['icon' => 'bi-heart-pulse-fill', 'tone' => 'teal'],
        'ninety_day_savings' => ['icon' => 'bi-piggy-bank-fill', 'tone' => 'cyan'],
    ];

    $formatTrend = function (?float $value, bool $positiveIsGood, ?string $suffix = null): ?array {
        if ($value === null) {
            return null;
        }

        $isUp = $value >= 0;
        $isGood = $positiveIsGood ? $isUp : ! $isUp;

        $formatted = number_format(abs($value), 1, ',', '.');
        if ($suffix === 'margem') {
            $text = $formatted.'% margem';
        } else {
            $text = ($isUp ? '+' : '−').$formatted.'% vs período anterior';
        }

        return [
            'text' => $text,
            'class' => $isGood ? 'text-success' : 'text-danger',
            'icon' => $isUp ? 'bi-arrow-up-right' : 'bi-arrow-down-right',
        ];
    };
@endphp

@push('styles')
<style>
    .finance-ai-page {
        --finance-ai-deep: #0b1f3a;
        --finance-ai-mid: #123a6b;
        --finance-ai-accent: #0e7490;
    }

    .finance-ai-hero {
        position: relative;
        overflow: hidden;
        border-radius: 1.15rem;
        background: linear-gradient(135deg, var(--finance-ai-deep) 0%, var(--finance-ai-mid) 48%, var(--finance-ai-accent) 100%);
        color: #fff;
        box-shadow: 0 16px 40px rgba(11, 31, 58, 0.22);
        margin-bottom: 1.5rem;
    }
    .finance-ai-hero::before,
    .finance-ai-hero::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
    }
    .finance-ai-hero::before {
        width: 260px;
        height: 260px;
        background: rgba(255, 255, 255, 0.07);
        top: -90px;
        right: -30px;
    }
    .finance-ai-hero::after {
        width: 160px;
        height: 160px;
        background: rgba(34, 211, 238, 0.16);
        bottom: -60px;
        left: 18%;
    }
    .finance-ai-hero__body {
        position: relative;
        z-index: 1;
        padding: 1.5rem 1.65rem;
    }
    .finance-ai-hero__icon {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.22);
        font-size: 1.55rem;
    }
    .finance-ai-hero__badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        background: rgba(34, 211, 238, 0.2);
        border: 1px solid rgba(165, 243, 252, 0.45);
        color: #ecfeff;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        border-radius: 999px;
        padding: 0.28rem 0.75rem;
    }
    .finance-ai-hero__subtitle {
        color: rgba(255, 255, 255, 0.84);
        max-width: 40rem;
        line-height: 1.5;
    }
    .finance-ai-quota {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 0.85rem;
        padding: 0.65rem 0.85rem;
        min-width: 12rem;
    }
    .finance-ai-quota__bar {
        height: 6px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.18);
        overflow: hidden;
        margin-top: 0.45rem;
    }
    .finance-ai-quota__bar span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #67e8f9, #22d3ee);
        transition: width 0.6s ease;
    }

    .finance-ai-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1.75rem;
    }
    @media (max-width: 991.98px) {
        .finance-ai-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 575.98px) {
        .finance-ai-kpi-grid { grid-template-columns: 1fr; }
    }

    .finance-ai-kpi {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 1rem;
        padding: 1.1rem 1.15rem;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        opacity: 0;
        transform: translateY(12px);
        animation: financeAiFadeUp 0.55s ease forwards;
    }
    .finance-ai-kpi:nth-child(1) { animation-delay: 0.05s; }
    .finance-ai-kpi:nth-child(2) { animation-delay: 0.12s; }
    .finance-ai-kpi:nth-child(3) { animation-delay: 0.19s; }
    .finance-ai-kpi:nth-child(4) { animation-delay: 0.26s; }

    .finance-ai-kpi__label {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        margin-bottom: 0.35rem;
    }
    .finance-ai-kpi__value {
        font-size: 1.35rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
    }
    .finance-ai-kpi__hint {
        font-size: 0.78rem;
        color: #94a3b8;
        margin-top: 0.35rem;
    }
    .finance-ai-kpi__extra {
        font-size: 0.82rem;
        color: #475569;
        margin-top: 0.25rem;
    }

    .finance-ai-section-title {
        font-size: 0.8rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 700;
        margin-bottom: 0.85rem;
    }

    .finance-ai-questions {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }
    @media (max-width: 1199.98px) {
        .finance-ai-questions { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 767.98px) {
        .finance-ai-questions { grid-template-columns: 1fr; }
    }

    .finance-ai-question {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: stretch;
        text-align: left;
        width: 100%;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 1rem;
        background: #fff;
        padding: 1.15rem 1.15rem 1rem;
        box-shadow: 0 6px 20px rgba(15, 23, 42, 0.05);
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        opacity: 0;
        transform: translateY(16px);
        animation: financeAiFadeUp 0.5s ease forwards;
        overflow: hidden;
    }
    .finance-ai-question::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, transparent 60%, rgba(14, 116, 144, 0.06));
        opacity: 0;
        transition: opacity 0.25s ease;
        pointer-events: none;
    }
    .finance-ai-question:hover:not(:disabled),
    .finance-ai-question:focus-visible:not(:disabled) {
        transform: translateY(-4px);
        box-shadow: 0 14px 32px rgba(14, 116, 144, 0.14);
        border-color: rgba(14, 116, 144, 0.35);
    }
    .finance-ai-question:hover::after,
    .finance-ai-question:focus-visible::after {
        opacity: 1;
    }
    .finance-ai-question:disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }
    .finance-ai-question__head {
        display: flex;
        gap: 0.85rem;
        align-items: flex-start;
        margin-bottom: 0.75rem;
    }
    .finance-ai-question__icon {
        width: 2.65rem;
        height: 2.65rem;
        border-radius: 0.75rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .finance-ai-tone-sky { background: #e0f2fe; color: #0369a1; }
    .finance-ai-tone-amber { background: #fef3c7; color: #b45309; }
    .finance-ai-tone-rose { background: #ffe4e6; color: #be123c; }
    .finance-ai-tone-emerald { background: #d1fae5; color: #047857; }
    .finance-ai-tone-indigo { background: #e0e7ff; color: #4338ca; }
    .finance-ai-tone-orange { background: #ffedd5; color: #c2410c; }
    .finance-ai-tone-violet { background: #ede9fe; color: #6d28d9; }
    .finance-ai-tone-teal { background: #ccfbf1; color: #0f766e; }
    .finance-ai-tone-cyan { background: #cffafe; color: #0e7490; }

    .finance-ai-question__title {
        font-weight: 700;
        color: #0f172a;
        font-size: 0.98rem;
        line-height: 1.35;
        margin-bottom: 0.2rem;
    }
    .finance-ai-question__desc {
        font-size: 0.82rem;
        color: #64748b;
        margin: 0;
    }
    .finance-ai-question__preview {
        margin-top: auto;
        padding-top: 0.75rem;
        border-top: 1px dashed rgba(100, 116, 139, 0.25);
    }
    .finance-ai-question__preview-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #94a3b8;
        margin-bottom: 0.15rem;
    }
    .finance-ai-question__preview-detail {
        font-size: 0.84rem;
        color: #334155;
        line-height: 1.4;
    }
    .finance-ai-question__preview-amount {
        font-size: 1.05rem;
        font-weight: 700;
        color: #0e7490;
        margin-top: 0.2rem;
    }
    .finance-ai-question__cta {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        margin-top: 0.65rem;
        font-size: 0.78rem;
        font-weight: 600;
        color: #0e7490;
    }

    .finance-ai-loading {
        border-radius: 1rem;
        border: 1px solid rgba(14, 116, 144, 0.15);
        background: linear-gradient(180deg, #f8fafc, #fff);
    }
    .finance-ai-loading__steps {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.5rem 1rem;
        margin-top: 1rem;
        font-size: 0.85rem;
        color: #64748b;
    }
    .finance-ai-loading__step.is-active {
        color: #0e7490;
        font-weight: 600;
    }

    .finance-ai-result {
        border-radius: 1rem;
        border: 0;
        box-shadow: 0 12px 36px rgba(15, 23, 42, 0.08);
        animation: financeAiFadeUp 0.45s ease;
    }
    .finance-ai-result__block {
        background: #f8fafc;
        border-radius: 0.85rem;
        padding: 1rem 1.1rem;
        margin-bottom: 1rem;
    }
    .finance-ai-rec {
        border-left: 4px solid #0e7490;
        background: #fff;
        border-radius: 0.75rem;
        padding: 1rem 1.1rem;
        margin-bottom: 0.85rem;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    }

    @keyframes financeAiFadeUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .finance-ai-kpi,
        .finance-ai-question,
        .finance-ai-result {
            animation: none;
            opacity: 1;
            transform: none;
        }
        .finance-ai-question:hover:not(:disabled) {
            transform: none;
        }
    }
</style>
@endpush

@section('content')
<div class="finance-ai-page">
    <div class="finance-ai-hero">
        <div class="finance-ai-hero__body">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                <div class="d-flex align-items-start gap-3 flex-grow-1">
                    <div class="finance-ai-hero__icon" aria-hidden="true">
                        <i class="bi bi-robot"></i>
                    </div>
                    <div>
                        <span class="finance-ai-hero__badge mb-2">
                            <i class="bi bi-stars"></i> Exclusivo do síndico
                        </span>
                        <h2 class="h3 mb-2 text-white">Consultor Financeiro SindCON</h2>
                        <p class="finance-ai-hero__subtitle mb-2">
                            Indicadores reais do caixa ({{ $dashboard['period_label'] ?? 'período atual' }})
                            @if(!empty($dashboard['period_start']) && !empty($dashboard['period_end']))
                                · {{ \Carbon\Carbon::parse($dashboard['period_start'])->format('d/m/Y') }}
                                a {{ \Carbon\Carbon::parse($dashboard['period_end'])->format('d/m/Y') }}
                            @endif
                            — escolha uma análise para receber recomendações objetivas.
                        </p>
                        <span class="small text-white-50">
                            <i class="bi bi-building"></i> {{ $dashboard['units'] ?? 0 }} unidade(s) no condomínio ativo
                        </span>
                    </div>
                </div>
                <div class="d-flex flex-column align-items-stretch align-items-md-end gap-2">
                    <a href="{{ route('financial.accounts.index') }}" class="btn btn-light btn-sm fw-semibold">
                        <i class="bi bi-arrow-left"></i> Voltar ao caixa
                    </a>
                    <div class="finance-ai-quota text-white" id="financeAiQuotaBox">
                        @if($quota['limit'] === null)
                            <span class="small text-warning">Limite mensal não configurado pela plataforma.</span>
                        @else
                            @php
                                $quotaPct = $quota['limit'] > 0 ? min(100, round(($quota['used'] / $quota['limit']) * 100)) : 0;
                            @endphp
                            <div class="small">
                                Consultas em {{ $quota['period_label'] }}
                                @if($quota['shared'])
                                    <span class="badge bg-dark bg-opacity-25 border border-light border-opacity-25 ms-1">cota da administradora</span>
                                @endif
                            </div>
                            <div class="fw-bold">
                                <span id="financeAiQuotaUsed">{{ $quota['used'] }}</span>
                                /
                                <span id="financeAiQuotaLimit">{{ $quota['limit'] }}</span>
                            </div>
                            <div class="finance-ai-quota__bar" aria-hidden="true">
                                <span style="width: {{ $quotaPct }}%;"></span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="finance-ai-kpi-grid" aria-label="Indicadores financeiros do período">
        @foreach($dashboard['kpis'] ?? [] as $kpi)
            @php
                $trend = $formatTrend(
                    $kpi['trend'] ?? null,
                    (bool) ($kpi['positive_is_good'] ?? true),
                    $kpi['trend_suffix'] ?? null
                );
                $valueClass = ($kpi['key'] ?? '') === 'saldo' && ($kpi['value'] ?? 0) < 0 ? 'text-danger' : '';
            @endphp
            <article class="finance-ai-kpi">
                <div class="finance-ai-kpi__label">{{ $kpi['label'] }}</div>
                <div class="finance-ai-kpi__value {{ $valueClass }}">
                    R$ {{ number_format((float) ($kpi['value'] ?? 0), 2, ',', '.') }}
                </div>
                @if(!empty($kpi['extra']))
                    <div class="finance-ai-kpi__extra">{{ $kpi['extra'] }}</div>
                @endif
                @if($trend)
                    <div class="small mt-1 {{ $trend['class'] }}">
                        <i class="bi {{ $trend['icon'] }}"></i> {{ $trend['text'] }}
                    </div>
                @endif
                <div class="finance-ai-kpi__hint">{{ $kpi['hint'] ?? '' }}</div>
            </article>
        @endforeach
    </div>

    <p class="finance-ai-section-title mb-0">Escolha uma pergunta</p>
    <p class="text-muted small mb-3">
        Cada card mostra um recorte dos seus números antes da análise com IA. Os valores vêm do caixa do condomínio, sem estimativas.
    </p>

    <div class="finance-ai-questions mb-4" id="financeAiQuestions" role="list">
        @foreach($questions as $key => $question)
            @php
                $visual = $questionVisuals[$key] ?? ['icon' => 'bi-chat-dots-fill', 'tone' => 'sky'];
                $preview = $dashboard['question_previews'][$key] ?? null;
                $delay = 0.08 + ($loop->index * 0.05);
            @endphp
            <div role="listitem" style="animation-delay: {{ $delay }}s;">
                <button
                    type="button"
                    class="finance-ai-question finance-ai-question"
                    data-question="{{ $key }}"
                    aria-label="{{ $question['title'] }}"
                    style="animation-delay: {{ $delay }}s;"
                >
                    <div class="finance-ai-question__head">
                        <span class="finance-ai-question__icon finance-ai-tone-{{ $visual['tone'] }}" aria-hidden="true">
                            <i class="bi {{ $visual['icon'] }}"></i>
                        </span>
                        <div>
                            <div class="finance-ai-question__title">{{ $question['title'] }}</div>
                            <p class="finance-ai-question__desc">{{ $question['description'] ?? '' }}</p>
                        </div>
                    </div>
                    @if($preview)
                        <div class="finance-ai-question__preview">
                            <div class="finance-ai-question__preview-label">{{ $preview['label'] }}</div>
                            <div class="finance-ai-question__preview-detail">{{ $preview['detail'] }}</div>
                            @if(!empty($preview['amount']))
                                <div class="finance-ai-question__preview-amount">{{ $preview['amount'] }}</div>
                            @endif
                        </div>
                    @endif
                    <span class="finance-ai-question__cta">
                        Gerar análise <i class="bi bi-arrow-right-short fs-5 align-middle"></i>
                    </span>
                </button>
            </div>
        @endforeach
    </div>

    <div class="card finance-ai-loading border-0 d-none mb-4" id="financeAiLoading" aria-live="polite">
        <div class="card-body text-center py-5">
            <div class="spinner-border text-primary mb-3" role="status" aria-hidden="true"></div>
            <p class="mb-1 fw-semibold" id="financeAiLoadingTitle">Analisando os indicadores financeiros...</p>
            <p class="text-muted small mb-0">Isso leva alguns segundos. Seus dados no caixa não são alterados.</p>
            <div class="finance-ai-loading__steps" aria-hidden="true">
                <span class="finance-ai-loading__step is-active" data-step="1">Indicadores</span>
                <span class="finance-ai-loading__step" data-step="2">Comparativos</span>
                <span class="finance-ai-loading__step" data-step="3">Recomendações</span>
            </div>
        </div>
    </div>

    <div class="alert alert-warning d-none" id="financeAiError" role="alert" aria-live="assertive"></div>

    <div class="card finance-ai-result d-none mb-4" id="financeAiResult">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                <div>
                    <h3 class="h5 mb-1" id="financeAiResultTitle">Análise do Consultor</h3>
                    <p class="text-muted small mb-0" id="financeAiResultQuestion"></p>
                </div>
                <span class="badge bg-light text-dark border d-none" id="financeAiCacheBadge">Resposta em cache</span>
            </div>

            <section class="finance-ai-result__block mb-3" aria-labelledby="financeAiResumoHeading">
                <h4 class="h6 text-uppercase text-muted mb-2" id="financeAiResumoHeading">Resumo</h4>
                <p id="financeAiResumo" class="mb-0 lead fs-6"></p>
            </section>

            <section class="finance-ai-result__block mb-3" aria-labelledby="financeAiPontosHeading">
                <h4 class="h6 text-uppercase text-muted mb-2" id="financeAiPontosHeading">Pontos de atenção</h4>
                <ul id="financeAiPontos" class="mb-0 ps-3"></ul>
            </section>

            <section class="mb-3" aria-labelledby="financeAiRecsHeading">
                <h4 class="h6 text-uppercase text-muted mb-2" id="financeAiRecsHeading">Recomendações</h4>
                <div id="financeAiRecs"></div>
            </section>

            <section class="finance-ai-result__block mb-0" aria-labelledby="financeAiObsHeading">
                <h4 class="h6 text-uppercase text-muted mb-2" id="financeAiObsHeading">Observações</h4>
                <ul id="financeAiObs" class="mb-0 ps-3"></ul>
            </section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const analyzeUrl = @json($analyzeUrl);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const buttons = document.querySelectorAll('.finance-ai-question');
    const loading = document.getElementById('financeAiLoading');
    const errorBox = document.getElementById('financeAiError');
    const result = document.getElementById('financeAiResult');
    const loadingSteps = loading?.querySelectorAll('.finance-ai-loading__step') || [];
    let inFlight = false;
    let stepTimer = null;

    function setBusy(busy) {
        inFlight = busy;
        buttons.forEach((btn) => {
            btn.disabled = busy;
            btn.setAttribute('aria-busy', busy ? 'true' : 'false');
        });
        loading.classList.toggle('d-none', !busy);

        if (busy) {
            let step = 1;
            loadingSteps.forEach((el) => el.classList.toggle('is-active', el.dataset.step === '1'));
            stepTimer = window.setInterval(() => {
                step = Math.min(3, step + 1);
                loadingSteps.forEach((el) => el.classList.toggle('is-active', Number(el.dataset.step) <= step));
            }, 2200);
        } else if (stepTimer) {
            window.clearInterval(stepTimer);
            stepTimer = null;
        }
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function priorityLabel(value) {
        if (value === 'alta') return 'Alta';
        if (value === 'baixa') return 'Baixa';
        return 'Média';
    }

    function priorityClass(value) {
        if (value === 'alta') return 'bg-danger';
        if (value === 'baixa') return 'bg-secondary';
        return 'bg-warning text-dark';
    }

    function renderAnalysis(payload) {
        const analysis = payload.analysis || {};
        document.getElementById('financeAiResultTitle').textContent = analysis.titulo || 'Análise do Consultor';
        document.getElementById('financeAiResultQuestion').textContent = payload.question_title || '';
        document.getElementById('financeAiResumo').textContent = analysis.resumo || '';

        const pontos = document.getElementById('financeAiPontos');
        pontos.innerHTML = '';
        (analysis.pontos_atencao || []).forEach((item) => {
            const li = document.createElement('li');
            li.className = 'mb-1';
            li.textContent = item;
            pontos.appendChild(li);
        });

        const recs = document.getElementById('financeAiRecs');
        recs.innerHTML = '';
        (analysis.recomendacoes || []).forEach((rec, index) => {
            const card = document.createElement('div');
            card.className = 'finance-ai-rec';
            card.innerHTML = `
                <div class="d-flex justify-content-between gap-2 mb-2 flex-wrap">
                    <strong>${index + 1}. ${escapeHtml(rec.titulo || 'Recomendação')}</strong>
                    <span class="badge ${priorityClass(rec.prioridade)}">Prioridade: ${priorityLabel(rec.prioridade)}</span>
                </div>
                <p class="mb-1"><strong>Ação:</strong> ${escapeHtml(rec.acao || '—')}</p>
                <p class="mb-1"><strong>Motivo:</strong> ${escapeHtml(rec.motivo || '—')}</p>
                <p class="mb-0"><strong>Impacto:</strong> ${escapeHtml(rec.impacto || '—')}</p>
            `;
            recs.appendChild(card);
        });

        const obs = document.getElementById('financeAiObs');
        obs.innerHTML = '';
        (analysis.observacoes || []).forEach((item) => {
            const li = document.createElement('li');
            li.className = 'mb-1';
            li.textContent = item;
            obs.appendChild(li);
        });

        document.getElementById('financeAiCacheBadge').classList.toggle('d-none', !payload.from_cache);
        result.classList.remove('d-none');
        result.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    async function runAnalysis(questionKey) {
        if (inFlight) return;

        errorBox.classList.add('d-none');
        result.classList.add('d-none');
        setBusy(true);

        try {
            const response = await fetch(analyzeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ question: questionKey }),
            });

            const data = await response.json().catch(() => ({}));

            if (data.quota && data.quota.limit !== null) {
                const usedEl = document.getElementById('financeAiQuotaUsed');
                const limitEl = document.getElementById('financeAiQuotaLimit');
                if (usedEl) usedEl.textContent = data.quota.used;
                if (limitEl) limitEl.textContent = data.quota.limit;
            }

            if (!response.ok || !data.ok) {
                errorBox.textContent = data.message
                    || 'Não foi possível gerar a análise financeira neste momento. Seus dados financeiros continuam disponíveis normalmente.';
                errorBox.classList.remove('d-none');
                return;
            }

            renderAnalysis(data);
        } catch (e) {
            errorBox.textContent = 'Não foi possível gerar a análise financeira neste momento. Seus dados financeiros continuam disponíveis normalmente.';
            errorBox.classList.remove('d-none');
        } finally {
            setBusy(false);
        }
    }

    buttons.forEach((btn) => {
        btn.addEventListener('click', () => runAnalysis(btn.dataset.question));
    });
})();
</script>
@endpush
