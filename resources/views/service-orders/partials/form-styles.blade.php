<style>
.os-form-page { max-width: 1180px; margin: 0 auto; }
.os-form-hero { background: linear-gradient(135deg, #ecfeff 0%, #cffafe 45%, #e0f2fe 100%); border: 1px solid #67e8f9; border-radius: 1.25rem; padding: 1.5rem 1.75rem; }
.os-form-back { color: #0e7490; text-decoration: none; font-weight: 600; font-size: 0.9rem; }
.os-form-title { font-size: 1.75rem; font-weight: 800; color: #155e75; }
.os-form-subtitle { color: #0e7490; }
.os-form-hero-badge { display: inline-flex; align-items: center; gap: 0.5rem; background: #fff; border: 1px solid #67e8f9; color: #0e7490; padding: 0.65rem 1rem; border-radius: 999px; font-weight: 600; box-shadow: 0 8px 24px rgba(6, 182, 212, 0.12); }
.os-form-section { background: #fff; border: 1px solid #e5e7eb; border-radius: 1.25rem; padding: 1.5rem; margin-bottom: 1.25rem; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04); }
.os-form-section__header { display: flex; gap: 1rem; align-items: flex-start; margin-bottom: 1.25rem; }
.os-form-section__header h2 { font-size: 1.15rem; font-weight: 700; margin: 0; }
.os-form-section__header p { margin: 0.15rem 0 0; color: #6b7280; font-size: 0.92rem; }
.os-form-step { width: 2.25rem; height: 2.25rem; border-radius: 0.85rem; display: inline-flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #22d3ee, #0891b2); color: #fff; font-weight: 800; flex-shrink: 0; }
.os-info-chip { display: flex; gap: 0.85rem; align-items: center; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1rem 1.1rem; height: 100%; }
.os-info-chip__icon { width: 2.75rem; height: 2.75rem; border-radius: 0.85rem; background: #ecfeff; color: #0891b2; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
.os-info-chip small { display: block; color: #64748b; margin-bottom: 0.15rem; }
.os-choice-grid { display: grid; gap: 0.75rem; grid-template-columns: repeat(3, minmax(0, 1fr)); }
.os-choice-option { border: 2px solid #e5e7eb; border-radius: 1rem; padding: 0.95rem 0.75rem; text-align: center; cursor: pointer; transition: all 0.2s ease; background: #fff; user-select: none; }
.os-choice-option i { display: block; font-size: 1.5rem; margin-bottom: 0.35rem; color: #0891b2; }
.os-choice-option span { font-weight: 600; color: #334155; font-size: 0.9rem; }
.os-choice-option:hover, .os-choice-option.is-selected { border-color: #0891b2; background: #ecfeff; box-shadow: 0 8px 20px rgba(8, 145, 178, 0.12); }
.os-form-sidebar { top: 1rem; }
.os-form-tip { display: flex; gap: 0.85rem; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #bbf7d0; border-radius: 1rem; padding: 1rem; color: #166534; }
.os-form-tip i { font-size: 1.5rem; flex-shrink: 0; }
.os-message-bubble { border-radius: 1rem; padding: 0.85rem 1rem; margin-bottom: 0.75rem; max-width: 85%; }
.os-message-bubble.is-mine { background: #ecfeff; border: 1px solid #a5f3fc; margin-left: auto; }
.os-message-bubble.is-other { background: #f8fafc; border: 1px solid #e2e8f0; }
.os-message-bubble.is-internal { background: #fff7ed; border: 1px dashed #fdba74; max-width: 100%; }
@media (max-width: 991px) { .os-form-sidebar { position: static !important; } }
@media (max-width: 768px) { .os-choice-grid { grid-template-columns: 1fr; } }
</style>
