<style>
.ob-page { max-width: 1180px; margin: 0 auto; }
.ob-hero {
    background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 45%, #ede9fe 100%);
    border: 1px solid #ddd6fe;
    border-radius: 1.25rem;
    padding: 1.5rem 1.75rem;
}
.ob-hero--syndic {
    background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 45%, #fef3c7 100%);
    border-color: #fed7aa;
}
.ob-back { color: #6d28d9; text-decoration: none; font-weight: 600; font-size: 0.9rem; }
.ob-hero--syndic .ob-back { color: #c2410c; }
.ob-title { font-size: 1.75rem; font-weight: 800; color: #5b21b6; margin: 0; }
.ob-hero--syndic .ob-title { color: #9a3412; }
.ob-subtitle { color: #7c3aed; margin: 0; }
.ob-hero--syndic .ob-subtitle { color: #c2410c; }
.ob-privacy-badge {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: #fff; border: 1px solid #ddd6fe; color: #6d28d9;
    padding: 0.65rem 1rem; border-radius: 999px; font-weight: 600;
    box-shadow: 0 8px 24px rgba(109, 40, 217, 0.1);
}
.ob-section {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 1.25rem;
    padding: 1.5rem; margin-bottom: 1.25rem; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
}
.ob-section__header { display: flex; gap: 1rem; align-items: flex-start; margin-bottom: 1.25rem; }
.ob-section__header h2 { font-size: 1.15rem; font-weight: 700; margin: 0; }
.ob-section__header p { margin: 0.15rem 0 0; color: #6b7280; font-size: 0.92rem; }
.ob-step {
    width: 2.25rem; height: 2.25rem; border-radius: 0.85rem;
    display: inline-flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #a78bfa, #7c3aed); color: #fff; font-weight: 800; flex-shrink: 0;
}
.ob-choice-grid { display: grid; gap: 0.75rem; grid-template-columns: repeat(3, minmax(0, 1fr)); }
.ob-choice-option {
    border: 2px solid #e5e7eb; border-radius: 1rem; padding: 1rem 0.75rem;
    text-align: center; cursor: pointer; transition: all 0.2s ease; background: #fff; user-select: none;
}
.ob-choice-option i { display: block; font-size: 1.6rem; margin-bottom: 0.35rem; color: #7c3aed; }
.ob-choice-option span { font-weight: 600; color: #334155; font-size: 0.9rem; }
.ob-choice-option:hover, .ob-choice-option.is-selected {
    border-color: #7c3aed; background: #faf5ff; box-shadow: 0 8px 20px rgba(124, 58, 237, 0.12);
}
.ob-info-chip {
    display: flex; gap: 0.85rem; align-items: center; background: #f8fafc;
    border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1rem 1.1rem;
}
.ob-info-chip__icon {
    width: 2.75rem; height: 2.75rem; border-radius: 0.85rem; background: #faf5ff;
    color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
}
.ob-info-chip small { display: block; color: #64748b; margin-bottom: 0.15rem; }
.ob-tip {
    display: flex; gap: 0.85rem; background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
    border: 1px solid #ddd6fe; border-radius: 1rem; padding: 1rem; color: #5b21b6;
}
.ob-tip i { font-size: 1.5rem; flex-shrink: 0; }
.ob-entry-card {
    border: 1px solid #e5e7eb; border-radius: 1.15rem; padding: 1.25rem; height: 100%;
    transition: transform 0.2s ease, box-shadow 0.2s ease; background: #fff;
}
.ob-entry-card:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(91, 33, 182, 0.08); }
.ob-entry-card.is-pending { border-left: 4px solid #f59e0b; }
.ob-entry-card.is-done { border-left: 4px solid #22c55e; }
.ob-stat-card { border-radius: 1rem; text-align: center; padding: 1.25rem; }
.ob-stat-card .display-6 { font-weight: 800; }
.ob-export-panel { background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1px dashed #cbd5e1; }
.ob-photo-frame {
    border: 2px dashed #ddd6fe; border-radius: 1rem; background: #faf5ff;
    min-height: 180px; display: flex; align-items: center; justify-content: center; overflow: hidden;
}
.ob-photo-preview { width: 100%; max-height: 260px; object-fit: contain; }
.ob-photo-placeholder { text-align: center; color: #7c3aed; padding: 1.5rem; }
.ob-photo-placeholder i { font-size: 2rem; display: block; margin-bottom: 0.5rem; }
.ob-modal-photo { width: 100%; max-height: 320px; object-fit: contain; border-radius: 0.75rem; border: 1px solid #e5e7eb; }
@media (max-width: 768px) { .ob-choice-grid { grid-template-columns: 1fr; } }
</style>
