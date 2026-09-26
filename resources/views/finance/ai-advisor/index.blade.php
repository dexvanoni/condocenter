@extends('layouts.app')

@section('title', 'Consultor Financeiro SindCON')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
            <div>
                <span class="badge rounded-pill text-bg-info mb-2">Exclusivo do síndico</span>
                <h2 class="mb-1"><i class="bi bi-robot text-primary"></i> Consultor Financeiro SindCON</h2>
                <p class="text-muted mb-0">
                    Analise os dados financeiros do seu condomínio e receba recomendações baseadas nos indicadores reais do período.
                </p>
            </div>
            <div class="text-end">
                <a href="{{ route('financial.accounts.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="bi bi-arrow-left"></i> Voltar ao caixa
                </a>
                <div class="small" id="financeAiQuotaBox">
                    @if($quota['limit'] === null)
                        <span class="text-warning">Limite mensal não configurado pela plataforma.</span>
                    @else
                        <span class="text-muted">
                            Consultas em {{ $quota['period_label'] }}:
                            <strong id="financeAiQuotaUsed">{{ $quota['used'] }}</strong>/<strong id="financeAiQuotaLimit">{{ $quota['limit'] }}</strong>
                            @if($quota['shared'])
                                <span class="badge bg-light text-dark border">cota da administradora</span>
                            @endif
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <h3 class="h6 text-uppercase text-muted mb-3">Escolha uma pergunta</h3>
        <div class="row g-3" id="financeAiQuestions" role="list">
            @foreach($questions as $key => $question)
                <div class="col-md-6 col-xl-4" role="listitem">
                    <button
                        type="button"
                        class="btn btn-outline-primary w-100 h-100 text-start finance-ai-question"
                        data-question="{{ $key }}"
                        aria-label="{{ $question['title'] }}"
                    >
                        <span class="fw-semibold d-block">{{ $question['title'] }}</span>
                        <small class="text-muted">{{ $question['description'] ?? '' }}</small>
                    </button>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 d-none" id="financeAiLoading" aria-live="polite">
    <div class="card-body text-center py-5">
        <div class="spinner-border text-primary mb-3" role="status" aria-hidden="true"></div>
        <p class="mb-0">Analisando os indicadores financeiros do seu condomínio...</p>
    </div>
</div>

<div class="alert alert-warning d-none" id="financeAiError" role="alert" aria-live="assertive"></div>

<div class="card shadow-sm border-0 d-none" id="financeAiResult">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div>
                <h3 class="h5 mb-1" id="financeAiResultTitle">Análise do Consultor</h3>
                <p class="text-muted small mb-0" id="financeAiResultQuestion"></p>
            </div>
            <span class="badge bg-light text-dark border d-none" id="financeAiCacheBadge">Resposta em cache</span>
        </div>

        <section class="mb-4" aria-labelledby="financeAiResumoHeading">
            <h4 class="h6 text-uppercase text-muted" id="financeAiResumoHeading">Resumo</h4>
            <p id="financeAiResumo" class="mb-0"></p>
        </section>

        <section class="mb-4" aria-labelledby="financeAiPontosHeading">
            <h4 class="h6 text-uppercase text-muted" id="financeAiPontosHeading">Pontos de atenção</h4>
            <ul id="financeAiPontos" class="mb-0"></ul>
        </section>

        <section class="mb-4" aria-labelledby="financeAiRecsHeading">
            <h4 class="h6 text-uppercase text-muted" id="financeAiRecsHeading">Recomendações</h4>
            <div id="financeAiRecs"></div>
        </section>

        <section aria-labelledby="financeAiObsHeading">
            <h4 class="h6 text-uppercase text-muted" id="financeAiObsHeading">Observações</h4>
            <ul id="financeAiObs" class="mb-0"></ul>
        </section>
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
    let inFlight = false;

    function setBusy(busy) {
        inFlight = busy;
        buttons.forEach((btn) => {
            btn.disabled = busy;
            btn.setAttribute('aria-busy', busy ? 'true' : 'false');
        });
        loading.classList.toggle('d-none', !busy);
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
            li.textContent = item;
            pontos.appendChild(li);
        });

        const recs = document.getElementById('financeAiRecs');
        recs.innerHTML = '';
        (analysis.recomendacoes || []).forEach((rec, index) => {
            const card = document.createElement('div');
            card.className = 'border rounded p-3 mb-3';
            card.innerHTML = `
                <div class="d-flex justify-content-between gap-2 mb-2">
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
            li.textContent = item;
            obs.appendChild(li);
        });

        document.getElementById('financeAiCacheBadge').classList.toggle('d-none', !payload.from_cache);
        result.classList.remove('d-none');
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
