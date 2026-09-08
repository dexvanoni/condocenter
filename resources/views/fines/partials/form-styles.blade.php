<style>
.fine-form-page { max-width: 1180px; margin: 0 auto; }
.fine-form-hero {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 45%, #ffedd5 100%);
    border: 1px solid #fecaca;
    border-radius: 1.25rem;
    padding: 1.5rem 1.75rem;
}
.fine-form-back { color: #b91c1c; text-decoration: none; font-weight: 600; font-size: 0.9rem; }
.fine-form-back:hover { color: #991b1b; }
.fine-form-title { font-size: 1.75rem; font-weight: 800; color: #991b1b; }
.fine-form-subtitle { color: #b45309; }
.fine-form-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #fff;
    border: 1px solid #fecaca;
    color: #b91c1c;
    padding: 0.65rem 1rem;
    border-radius: 999px;
    font-weight: 600;
    box-shadow: 0 8px 24px rgba(239, 68, 68, 0.12);
}
.fine-form-section {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 1.25rem;
    padding: 1.5rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
}
.fine-form-section__header {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
    margin-bottom: 1.25rem;
}
.fine-form-section__header h2 { font-size: 1.15rem; font-weight: 700; margin: 0; }
.fine-form-section__header p { margin: 0.15rem 0 0; color: #6b7280; font-size: 0.92rem; }
.fine-form-step {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 0.85rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f87171, #dc2626);
    color: #fff;
    font-weight: 800;
    flex-shrink: 0;
}
.fine-search-results .list-group-item { cursor: pointer; border-left: 0; border-right: 0; }
.fine-search-results .list-group-item:first-child { border-top: 0; }
.fine-search-results .list-group-item:hover { background: rgba(220, 38, 38, 0.06); }
.fine-selected-table { border: 1px solid #e5e7eb; border-radius: 1rem; overflow: hidden; }
.fine-selected-table thead th { background: #f8fafc; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.03em; }
.fine-empty-selection {
    border: 2px dashed #e5e7eb;
    border-radius: 1rem;
    padding: 2rem 1rem;
    text-align: center;
    color: #64748b;
}
.fine-form-sidebar { top: 1rem; }
.fine-form-tip {
    display: flex;
    gap: 0.85rem;
    background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
    border: 1px solid #fed7aa;
    border-radius: 1rem;
    padding: 1rem;
    color: #9a3412;
}
.fine-form-tip i { font-size: 1.5rem; flex-shrink: 0; }
@media (max-width: 991px) { .fine-form-sidebar { position: static !important; } }
</style>
