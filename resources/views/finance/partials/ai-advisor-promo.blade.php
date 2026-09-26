@php
    $advisorUser = auth()->user();
    $canUseAdvisor = $advisorUser
        && $advisorUser->isSindico()
        && session('active_role') === 'Síndico'
        && !\App\Helpers\SidebarHelper::isFinancialSimplified($advisorUser)
        && \Illuminate\Support\Facades\Route::has('financial.ai-advisor.index');
@endphp

@if($canUseAdvisor)
<style>
    .finance-ai-promo {
        position: relative;
        overflow: hidden;
        border: 0;
        border-radius: 1rem;
        background: linear-gradient(135deg, #0b1f3a 0%, #123a6b 48%, #0e7490 100%);
        box-shadow: 0 12px 28px rgba(11, 31, 58, 0.28);
        color: #fff;
        margin-bottom: 1.5rem;
    }
    .finance-ai-promo::before {
        content: "";
        position: absolute;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
        top: -80px;
        right: -40px;
        pointer-events: none;
    }
    .finance-ai-promo::after {
        content: "";
        position: absolute;
        width: 140px;
        height: 140px;
        border-radius: 50%;
        background: rgba(34, 211, 238, 0.18);
        bottom: -50px;
        left: 28%;
        pointer-events: none;
    }
    .finance-ai-promo .card-body {
        position: relative;
        z-index: 1;
        padding: 1.35rem 1.5rem;
    }
    .finance-ai-promo__icon {
        width: 3.25rem;
        height: 3.25rem;
        border-radius: 0.9rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.22);
        font-size: 1.45rem;
        flex-shrink: 0;
    }
    .finance-ai-promo__badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        background: rgba(34, 211, 238, 0.2);
        border: 1px solid rgba(165, 243, 252, 0.45);
        color: #ecfeff;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        border-radius: 999px;
        padding: 0.28rem 0.7rem;
        margin-bottom: 0.55rem;
    }
    .finance-ai-promo__title {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
        color: #fff;
    }
    .finance-ai-promo__text {
        color: rgba(255, 255, 255, 0.82);
        margin-bottom: 0;
        max-width: 42rem;
        font-size: 0.95rem;
        line-height: 1.45;
    }
    .finance-ai-promo__cta {
        background: #fff;
        color: #0b1f3a;
        font-weight: 700;
        border: 0;
        border-radius: 0.65rem;
        padding: 0.65rem 1.15rem;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.18);
        white-space: nowrap;
    }
    .finance-ai-promo__cta:hover,
    .finance-ai-promo__cta:focus {
        background: #ecfeff;
        color: #0b1f3a;
    }
    .finance-ai-promo__hints {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.85rem;
    }
    .finance-ai-promo__hint {
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.9);
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.16);
        border-radius: 999px;
        padding: 0.25rem 0.65rem;
    }
    @media (max-width: 767.98px) {
        .finance-ai-promo__cta {
            width: 100%;
            text-align: center;
        }
    }
</style>

<div class="finance-ai-promo" role="region" aria-labelledby="financeAiPromoTitle">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-start gap-3 flex-grow-1">
                <div class="finance-ai-promo__icon" aria-hidden="true">
                    <i class="bi bi-robot"></i>
                </div>
                <div>
                    <span class="finance-ai-promo__badge">
                        <i class="bi bi-stars"></i> Exclusivo do síndico
                    </span>
                    <h3 class="finance-ai-promo__title" id="financeAiPromoTitle">
                        Consultor Financeiro SindCON
                    </h3>
                    <p class="finance-ai-promo__text">
                        Transforme os indicadores reais do condomínio em recomendações práticas:
                        onde cortar gastos, como melhorar a inadimplência e o que priorizar nos próximos 90 dias.
                    </p>
                    <div class="finance-ai-promo__hints" aria-hidden="true">
                        <span class="finance-ai-promo__hint">9 análises prontas</span>
                        <span class="finance-ai-promo__hint">Dados do seu caixa</span>
                        <span class="finance-ai-promo__hint">Modo completo</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('financial.ai-advisor.index') }}" class="btn finance-ai-promo__cta">
                Abrir consultor <i class="bi bi-arrow-right-short fs-5 align-middle"></i>
            </a>
        </div>
    </div>
</div>
@endif
