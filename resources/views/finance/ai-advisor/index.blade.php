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

    .finance-ai-thinking {
        position: fixed;
        inset: 0;
        z-index: 1080;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.25rem;
    }
    .finance-ai-thinking.d-none {
        display: none !important;
    }
    .finance-ai-thinking__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(11, 31, 58, 0.68);
        backdrop-filter: blur(7px);
        -webkit-backdrop-filter: blur(7px);
    }
    .finance-ai-thinking__panel {
        position: relative;
        z-index: 1;
        width: min(28rem, 100%);
        border-radius: 1.35rem;
        padding: 2rem 1.65rem 1.65rem;
        text-align: center;
        color: #ecfeff;
        background: linear-gradient(165deg, #0b1f3a 0%, #123a6b 55%, #0e7490 140%);
        border: 1px solid rgba(165, 243, 252, 0.28);
        box-shadow: 0 28px 70px rgba(11, 31, 58, 0.45);
        animation: financeAiModalIn 0.35s ease;
    }
    .finance-ai-thinking__panel:focus {
        outline: none;
    }
    .finance-ai-thinking__panel:focus-visible {
        outline: 2px solid #a5f3fc;
        outline-offset: 3px;
    }
    .finance-ai-orb {
        position: relative;
        width: 8.5rem;
        height: 8.5rem;
        margin: 0 auto 1.35rem;
    }
    .finance-ai-orb__ring,
    .finance-ai-orb__ring--slow {
        position: absolute;
        border-radius: 50%;
        border: 2px solid rgba(34, 211, 238, 0.18);
    }
    .finance-ai-orb__ring {
        inset: 0;
        border-top-color: #22d3ee;
        animation: financeAiSpin 2.1s linear infinite;
    }
    .finance-ai-orb__ring--slow {
        inset: 0.85rem;
        border-right-color: #67e8f9;
        animation: financeAiSpin 3.4s linear infinite reverse;
    }
    .finance-ai-orb__core {
        position: absolute;
        inset: 1.7rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: #fff;
        background: radial-gradient(circle at 32% 28%, #67e8f9, #0e7490 58%, #08233f);
        box-shadow: 0 0 28px rgba(34, 211, 238, 0.55);
        animation: financeAiPulse 1.7s ease-in-out infinite;
    }
    .finance-ai-orb__spark {
        position: absolute;
        width: 0.55rem;
        height: 0.55rem;
        border-radius: 50%;
        background: #ecfeff;
        box-shadow: 0 0 10px #67e8f9;
        top: 50%;
        left: 50%;
        animation: financeAiOrbit 2.6s linear infinite;
    }
    .finance-ai-orb__spark:nth-child(4) {
        animation-delay: -0.85s;
        width: 0.4rem;
        height: 0.4rem;
    }
    .finance-ai-thinking__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #a5f3fc;
        margin-bottom: 0.45rem;
    }
    .finance-ai-thinking__title {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 0.4rem;
        color: #fff;
    }
    .finance-ai-thinking__copy {
        color: rgba(236, 254, 255, 0.82);
        font-size: 0.9rem;
        margin-bottom: 1.15rem;
        line-height: 1.45;
    }
    .finance-ai-thinking__bar {
        height: 7px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.14);
        overflow: hidden;
        margin-bottom: 1.1rem;
    }
    .finance-ai-thinking__bar span {
        display: block;
        height: 100%;
        width: 28%;
        border-radius: inherit;
        background: linear-gradient(90deg, #67e8f9, #22d3ee, #fff);
        animation: financeAiBar 1.6s ease-in-out infinite;
    }
    .finance-ai-thinking__steps {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.4rem 0.85rem;
        font-size: 0.78rem;
        color: rgba(236, 254, 255, 0.55);
    }
    .finance-ai-thinking__step.is-active {
        color: #a5f3fc;
        font-weight: 700;
    }

    body.finance-ai-thinking-open {
        overflow: hidden;
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
    @keyframes financeAiModalIn {
        from {
            opacity: 0;
            transform: translateY(12px) scale(0.96);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    @keyframes financeAiSpin {
        to { transform: rotate(360deg); }
    }
    @keyframes financeAiPulse {
        0%, 100% { transform: scale(1); box-shadow: 0 0 22px rgba(34, 211, 238, 0.4); }
        50% { transform: scale(1.06); box-shadow: 0 0 36px rgba(34, 211, 238, 0.75); }
    }
    @keyframes financeAiOrbit {
        from { transform: rotate(0deg) translateX(3.95rem) rotate(0deg); }
        to { transform: rotate(360deg) translateX(3.95rem) rotate(-360deg); }
    }
    @keyframes financeAiBar {
        0% { transform: translateX(-120%); }
        100% { transform: translateX(380%); }
    }

    @media (prefers-reduced-motion: reduce) {
        .finance-ai-kpi,
        .finance-ai-question,
        .finance-ai-result,
        .finance-ai-thinking__panel {
            animation: none;
            opacity: 1;
            transform: none;
        }
        .finance-ai-question:hover:not(:disabled) {
            transform: none;
        }
        .finance-ai-orb__ring,
        .finance-ai-orb__ring--slow,
        .finance-ai-orb__core,
        .finance-ai-orb__spark,
        .finance-ai-thinking__bar span {
            animation: none;
        }
        .finance-ai-thinking__bar span {
            width: 70%;
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

    <div
        class="finance-ai-thinking d-none"
        id="financeAiThinking"
        role="dialog"
        aria-modal="true"
        aria-labelledby="financeAiLoadingTitle"
        aria-describedby="financeAiLoadingCopy"
        aria-live="polite"
        aria-hidden="true"
    >
        <div class="finance-ai-thinking__backdrop" aria-hidden="true"></div>
        <div class="finance-ai-thinking__panel" tabindex="-1">
            <div class="finance-ai-orb" aria-hidden="true">
                <span class="finance-ai-orb__ring"></span>
                <span class="finance-ai-orb__ring--slow"></span>
                <span class="finance-ai-orb__spark"></span>
                <span class="finance-ai-orb__spark"></span>
                <span class="finance-ai-orb__core">
                    <i class="bi bi-stars"></i>
                </span>
            </div>
            <div class="finance-ai-thinking__eyebrow">
                <i class="bi bi-robot"></i> Consultor SindCON
            </div>
            <h3 class="finance-ai-thinking__title" id="financeAiLoadingTitle">
                A inteligência artificial está pensando
            </h3>
            <p class="finance-ai-thinking__copy mb-0" id="financeAiLoadingCopy">
                Analisando os indicadores financeiros... O retorno chega em instantes.
            </p>
            <div class="finance-ai-thinking__bar mt-3" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuetext="Analisando">
                <span></span>
            </div>
            <div class="finance-ai-thinking__steps" aria-hidden="true">
                <span class="finance-ai-thinking__step is-active" data-step="1">Indicadores</span>
                <span class="finance-ai-thinking__step" data-step="2">Comparativos</span>
                <span class="finance-ai-thinking__step" data-step="3">Recomendações</span>
            </div>
            <p class="small mb-0 mt-3" style="color: rgba(236, 254, 255, 0.55);">
                Seus dados no caixa não são alterados.
            </p>
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
    const thinking = document.getElementById('financeAiThinking');
    const errorBox = document.getElementById('financeAiError');
    const result = document.getElementById('financeAiResult');
    const thinkingCopy = document.getElementById('financeAiLoadingCopy');
    const thinkingSteps = thinking?.querySelectorAll('.finance-ai-thinking__step') || [];
    const thinkingMessages = [
        'Analisando os indicadores financeiros... O retorno chega em instantes.',
        'Comparando o período atual com o anterior...',
        'A inteligência artificial está elaborando as recomendações...',
    ];
    let inFlight = false;
    let stepTimer = null;
    let lastTrigger = null;

    if (thinking && thinking.parentElement !== document.body) {
        document.body.appendChild(thinking);
    }

    function setBusy(busy) {
        inFlight = busy;
        buttons.forEach((btn) => {
            btn.disabled = busy;
            btn.setAttribute('aria-busy', busy ? 'true' : 'false');
        });

        document.body.classList.toggle('finance-ai-thinking-open', busy);
        thinking?.classList.toggle('d-none', !busy);
        thinking?.setAttribute('aria-hidden', busy ? 'false' : 'true');

        if (stepTimer) {
            window.clearInterval(stepTimer);
            stepTimer = null;
        }

        if (busy) {
            lastTrigger = document.activeElement;
            let step = 1;
            thinkingSteps.forEach((el) => el.classList.toggle('is-active', el.dataset.step === '1'));
            if (thinkingCopy) thinkingCopy.textContent = thinkingMessages[0];
            thinking?.querySelector('.finance-ai-thinking__panel')?.focus();

            stepTimer = window.setInterval(() => {
                step = Math.min(thinkingMessages.length, step + 1);
                thinkingSteps.forEach((el) => el.classList.toggle('is-active', Number(el.dataset.step) <= step));
                if (thinkingCopy) {
                    thinkingCopy.textContent = thinkingMessages[step - 1] || thinkingMessages[thinkingMessages.length - 1];
                }
            }, 2200);
        } else if (lastTrigger && typeof lastTrigger.focus === 'function') {
            lastTrigger.focus();
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

            setBusy(false);
            renderAnalysis(data);
        } catch (e) {
            errorBox.textContent = 'Não foi possível gerar a análise financeira neste momento. Seus dados financeiros continuam disponíveis normalmente.';
            errorBox.classList.remove('d-none');
        } finally {
            if (inFlight) {
                setBusy(false);
            }
        }
    }

    buttons.forEach((btn) => {
        btn.addEventListener('click', () => runAnalysis(btn.dataset.question));
    });
})();
</script>
@endpush
