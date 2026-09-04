<style>
/* Base compartilhada com o cadastro de pets */
.pet-form-page { max-width: 1180px; margin: 0 auto; }
.pet-form-hero { background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 45%, #ccfbf1 100%); border: 1px solid #6ee7b7; border-radius: 1.25rem; padding: 1.5rem 1.75rem; }
.pet-form-back { color: #047857; text-decoration: none; font-weight: 600; font-size: 0.9rem; }
.pet-form-title { font-size: 1.75rem; font-weight: 800; color: #065f46; }
.pet-form-subtitle { color: #047857; }
.pet-form-hero-badge { display: inline-flex; align-items: center; gap: 0.5rem; background: #fff; border: 1px solid #6ee7b7; color: #047857; padding: 0.65rem 1rem; border-radius: 999px; font-weight: 600; box-shadow: 0 8px 24px rgba(16, 185, 129, 0.12); }
.pet-form-section { background: #fff; border: 1px solid #e5e7eb; border-radius: 1.25rem; padding: 1.5rem; margin-bottom: 1.25rem; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04); }
.pet-form-section__header { display: flex; gap: 1rem; align-items: flex-start; margin-bottom: 1.25rem; }
.pet-form-section__header h2 { font-size: 1.15rem; font-weight: 700; margin: 0; }
.pet-form-section__header p { margin: 0.15rem 0 0; color: #6b7280; font-size: 0.92rem; }
.pet-form-step { width: 2.25rem; height: 2.25rem; border-radius: 0.85rem; display: inline-flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #34d399, #10b981); color: #fff; font-weight: 800; flex-shrink: 0; }
.pet-info-chip { display: flex; gap: 0.85rem; align-items: center; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1rem 1.1rem; height: 100%; }
.pet-info-chip__icon { width: 2.75rem; height: 2.75rem; border-radius: 0.85rem; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
.pet-info-chip small { display: block; color: #64748b; margin-bottom: 0.15rem; }
.pet-type-grid, .pet-size-grid { display: grid; gap: 0.75rem; }
.pet-type-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
.pet-size-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.pet-type-option, .pet-size-option { border: 2px solid #e5e7eb; border-radius: 1rem; padding: 0.95rem 0.75rem; text-align: center; cursor: pointer; transition: all 0.2s ease; background: #fff; user-select: none; }
.pet-type-option i { display: block; font-size: 1.5rem; margin-bottom: 0.35rem; color: #10b981; }
.pet-type-option span, .pet-size-option span { font-weight: 600; color: #334155; font-size: 0.92rem; }
.pet-type-option:hover, .pet-size-option:hover, .pet-type-option.is-selected, .pet-size-option.is-selected { border-color: #10b981; background: #ecfdf5; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.12); }
.pet-form-sidebar { top: 1rem; }
.pet-form-tip { display: flex; gap: 0.85rem; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1px solid #bfdbfe; border-radius: 1rem; padding: 1rem; color: #1e3a8a; }
.pet-form-tip i { font-size: 1.5rem; flex-shrink: 0; }
.pet-form-tip p { font-size: 0.9rem; color: #1d4ed8; }
.marketplace-images-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.5rem; margin-bottom: 1rem; }
.marketplace-image-slot { position: relative; aspect-ratio: 1; border-radius: 0.85rem; overflow: hidden; background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%); border: 2px dashed #cbd5e1; }
.marketplace-image-slot img { width: 100%; height: 100%; object-fit: cover; display: block; }
.marketplace-image-slot.is-empty { display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 1.5rem; }
.marketplace-image-slot button { position: absolute; top: 0.25rem; right: 0.25rem; width: 1.5rem; height: 1.5rem; border: none; border-radius: 999px; background: rgba(15, 23, 42, 0.75); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; }
.marketplace-photo-actions { display: grid; gap: 0.5rem; }
@media (max-width: 991px) { .pet-form-sidebar { position: static !important; } }
@media (max-width: 768px) { .pet-type-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .marketplace-images-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
</style>
