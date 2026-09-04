<style>
.pet-form-page { max-width: 1180px; margin: 0 auto; }
.pet-form-hero { background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 45%, #fef3c7 100%); border: 1px solid #fed7aa; border-radius: 1.25rem; padding: 1.5rem 1.75rem; }
.pet-form-back { color: #9a3412; text-decoration: none; font-weight: 600; font-size: 0.9rem; }
.pet-form-title { font-size: 1.75rem; font-weight: 800; color: #7c2d12; }
.pet-form-subtitle { color: #9a3412; }
.pet-form-hero-badge { display: inline-flex; align-items: center; gap: 0.5rem; background: #fff; border: 1px solid #fdba74; color: #c2410c; padding: 0.65rem 1rem; border-radius: 999px; font-weight: 600; box-shadow: 0 8px 24px rgba(234, 88, 12, 0.12); }
.pet-form-section { background: #fff; border: 1px solid #e5e7eb; border-radius: 1.25rem; padding: 1.5rem; margin-bottom: 1.25rem; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04); }
.pet-form-section__header { display: flex; gap: 1rem; align-items: flex-start; margin-bottom: 1.25rem; }
.pet-form-section__header h2 { font-size: 1.15rem; font-weight: 700; margin: 0; }
.pet-form-section__header p { margin: 0.15rem 0 0; color: #6b7280; font-size: 0.92rem; }
.pet-form-step { width: 2.25rem; height: 2.25rem; border-radius: 0.85rem; display: inline-flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #fb923c, #f97316); color: #fff; font-weight: 800; flex-shrink: 0; }
.pet-info-chip { display: flex; gap: 0.85rem; align-items: center; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1rem 1.1rem; height: 100%; }
.pet-info-chip__icon { width: 2.75rem; height: 2.75rem; border-radius: 0.85rem; background: #fff7ed; color: #ea580c; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
.pet-info-chip small { display: block; color: #64748b; margin-bottom: 0.15rem; }
.pet-type-grid, .pet-size-grid { display: grid; gap: 0.75rem; }
.pet-type-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
.pet-size-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
.pet-type-option, .pet-size-option { border: 2px solid #e5e7eb; border-radius: 1rem; padding: 0.95rem 0.75rem; text-align: center; cursor: pointer; transition: all 0.2s ease; background: #fff; user-select: none; }
.pet-type-option i { display: block; font-size: 1.5rem; margin-bottom: 0.35rem; color: #f97316; }
.pet-type-option span, .pet-size-option span { font-weight: 600; color: #334155; }
.pet-type-option:hover, .pet-size-option:hover, .pet-type-option.is-selected, .pet-size-option.is-selected { border-color: #f97316; background: #fff7ed; box-shadow: 0 8px 20px rgba(249, 115, 22, 0.12); }
.pet-form-sidebar { top: 1rem; }
.pet-photo-card { text-align: center; }
.pet-photo-frame { position: relative; width: 100%; aspect-ratio: 1; border-radius: 1.25rem; overflow: hidden; background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%); border: 2px dashed #cbd5e1; margin-bottom: 1rem; }
.pet-photo-preview { width: 100%; height: 100%; object-fit: cover; display: none; }
.pet-photo-preview.is-visible { display: block; }
.pet-photo-placeholder { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem; color: #64748b; padding: 1rem; }
.pet-photo-placeholder i { font-size: 2.5rem; color: #f97316; }
.pet-photo-actions { display: grid; gap: 0.5rem; }
.pet-camera-viewport { background: #0f172a; border-radius: 1rem; overflow: hidden; min-height: 280px; display: flex; align-items: center; justify-content: center; }
.pet-camera-viewport video { width: 100%; max-height: 360px; object-fit: cover; }
.pet-camera-error { color: #fecaca; text-align: center; padding: 1.5rem; }
.pet-form-tip { display: flex; gap: 0.85rem; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1px solid #bfdbfe; border-radius: 1rem; padding: 1rem; color: #1e3a8a; }
.pet-form-tip i { font-size: 1.5rem; flex-shrink: 0; }
.pet-form-tip p { font-size: 0.9rem; color: #1d4ed8; }
@media (max-width: 991px) { .pet-form-sidebar { position: static !important; } }
@media (max-width: 768px) { .pet-type-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
</style>
