<style>
    .org-management {
        --org-accent: #1a5c45;
        --org-accent-dark: #0b2e1f;
        --org-accent-soft: rgba(26, 92, 69, 0.08);
    }

    .org-hero {
        background: linear-gradient(135deg, var(--org-accent) 0%, var(--org-accent-dark) 100%);
        border-radius: 0.75rem;
        color: #fff;
        padding: 1.5rem 1.75rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 0.35rem 1.25rem rgba(11, 46, 31, 0.18);
    }

    .org-hero .org-hero-back {
        color: rgba(255, 255, 255, 0.85);
        text-decoration: none;
        font-size: 0.875rem;
    }

    .org-hero .org-hero-back:hover {
        color: #fff;
    }

    .org-hero h1 {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 0.35rem;
    }

    .org-hero .org-hero-subtitle {
        color: rgba(255, 255, 255, 0.88);
        margin-bottom: 0;
        max-width: 42rem;
    }

    .org-hero .badge-org {
        background: rgba(255, 255, 255, 0.18);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.25);
    }

    .org-hero .btn-org-light {
        background: #fff;
        color: var(--org-accent-dark);
        border: none;
        font-weight: 500;
    }

    .org-hero .btn-org-light:hover {
        background: rgba(255, 255, 255, 0.92);
        color: var(--org-accent-dark);
    }

    .org-hero .btn-org-outline {
        border: 1px solid rgba(255, 255, 255, 0.55);
        color: #fff;
    }

    .org-hero .btn-org-outline:hover {
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        border-color: #fff;
    }

    .org-metric-card {
        border: 0;
        border-radius: 0.65rem;
        box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.06);
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .org-metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.35rem 1rem rgba(0, 0, 0, 0.1);
    }

    .org-metric-card .metric-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    .org-metric-card .metric-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6c757d;
    }

    .org-metric-card .metric-value {
        font-size: 1.35rem;
        font-weight: 600;
        line-height: 1.2;
    }

    .org-quota-bar {
        height: 0.5rem;
        border-radius: 999px;
        background: #e9ecef;
        overflow: hidden;
    }

    .org-quota-bar-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--org-accent), #2d8a66);
        transition: width 0.3s ease;
    }

    .org-section-card {
        border: 0;
        border-radius: 0.65rem;
        box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.06);
    }

    .org-section-card > .card-header {
        background: #fff;
        border-bottom: 1px solid #eef1f4;
        padding: 1rem 1.25rem;
        font-weight: 600;
    }

    .org-syndic-form {
        background: var(--org-accent-soft);
        border: 1px dashed rgba(26, 92, 69, 0.35);
        border-radius: 0.5rem;
        padding: 0.75rem;
    }

    .org-form-section-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: var(--org-accent-dark);
        margin-bottom: 0.25rem;
    }

    .org-form-section-title i {
        color: var(--org-accent);
    }

    .org-empty-state {
        padding: 3rem 1.5rem;
        text-align: center;
        color: #6c757d;
    }

    .org-empty-state i {
        font-size: 2.5rem;
        color: var(--org-accent);
        opacity: 0.5;
        margin-bottom: 0.75rem;
    }

    .org-condo-insights-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--org-accent-dark);
    }

    .org-condo-card {
        background: #fff;
        border: 1px solid #e7ece9;
        border-radius: 0.75rem;
        padding: 1rem 1.1rem 0.85rem;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }

    .org-condo-card__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .org-condo-card__name {
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
        color: #1c1c1c;
    }

    .org-condo-card__meta {
        font-size: 0.78rem;
        color: #6c757d;
    }

    .org-condo-card__status {
        flex-shrink: 0;
        font-size: 0.68rem;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        border-radius: 999px;
        padding: 0.2rem 0.5rem;
        background: #f1f3f5;
        color: #6c757d;
    }

    .org-condo-card__status.is-active {
        background: rgba(26, 92, 69, 0.1);
        color: var(--org-accent);
    }

    .org-condo-card__metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.5rem;
        margin: 0;
    }

    .org-condo-card__metrics div {
        min-width: 0;
    }

    .org-condo-card__metrics dt {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #8a9390;
        font-weight: 500;
        margin-bottom: 0.15rem;
    }

    .org-condo-card__metrics dd {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 600;
        line-height: 1.25;
        color: #1c1c1c;
    }

    .org-condo-card__metrics dd span {
        display: block;
        font-size: 0.72rem;
        font-weight: 500;
        color: #6c757d;
    }

    .org-condo-card__metrics dd.is-healthy { color: var(--org-accent); }
    .org-condo-card__metrics dd.is-attention { color: #9a6700; }
    .org-condo-card__metrics dd.is-critical { color: #b42318; }
    .org-condo-card__metrics dd.is-empty { color: #6c757d; }

    .org-condo-card__bar {
        height: 0.28rem;
        border-radius: 999px;
        background: #eef1f0;
        overflow: hidden;
    }

    .org-condo-card__bar span {
        display: block;
        height: 100%;
        border-radius: 999px;
    }

    .org-condo-card__bar span.is-healthy { background: var(--org-accent); }
    .org-condo-card__bar span.is-attention { background: #c48a12; }
    .org-condo-card__bar span.is-critical { background: #b42318; }
    .org-condo-card__bar span.is-empty { background: #ced4da; }

    .org-condo-card__hint {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: -0.35rem;
    }

    .org-condo-card__action .btn-link {
        color: var(--org-accent);
        font-weight: 500;
        text-decoration: none;
        font-size: 0.82rem;
    }

    .org-condo-card__action .btn-link:hover {
        color: var(--org-accent-dark);
    }

    @media (max-width: 575.98px) {
        .org-hero {
            padding: 1.15rem;
        }

        .org-hero h1 {
            font-size: 1.25rem;
        }
    }
</style>
