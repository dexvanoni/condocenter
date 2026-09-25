<style>
    .platform-client-form-page {
        --pcf-accent: #1a5c45;
    }
    .platform-client-form-page .form-section-head {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8125rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        color: var(--bs-secondary);
        margin-bottom: 1.25rem;
    }
    .platform-client-form-page .form-section-head i {
        color: var(--pcf-accent);
        font-size: 1.1rem;
    }
    .platform-client-form-page .section-divider {
        border: 0;
        height: 1px;
        background: rgba(0, 0, 0, 0.06);
        margin: 2rem 0;
    }
    .platform-client-form-page .client-form-card {
        border: 0;
        border-radius: 0.75rem;
        box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.06);
    }
    .platform-client-form-page .client-aside {
        border-radius: 0.75rem;
        border: 1px solid rgba(0, 0, 0, 0.06);
        background: #f8faf9;
    }
    .platform-client-form-page .client-aside .step-item {
        display: flex;
        gap: 0.75rem;
        font-size: 0.875rem;
        color: var(--bs-secondary);
    }
    .platform-client-form-page .client-aside .step-num {
        flex-shrink: 0;
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 50%;
        background: rgba(26, 92, 69, 0.12);
        color: var(--pcf-accent);
        font-size: 0.75rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    @media (min-width: 1200px) {
        .platform-client-form-page .client-aside-sticky {
            position: sticky;
            top: 1rem;
        }
    }
</style>
