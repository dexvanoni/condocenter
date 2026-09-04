<script>
document.addEventListener('DOMContentLoaded', function () {
    const imagesInputId = @json($imagesInputId);
    const formId = @json($formId);
    const submitBtnId = @json($submitBtnId);
    const feedbackId = @json($feedbackId);
    const submitUrl = @json($submitUrl);
    const submitMethod = @json($submitMethod);
    const redirectUrl = @json($redirectUrl);
    const successFallback = @json($successFallback);
    const loadingLabel = @json($loadingLabel);

    if (typeof window.initMarketplaceImagesUpload === 'function') {
        window.initMarketplaceImagesUpload(imagesInputId);
    }

    const form = document.getElementById(formId);
    const submitBtn = document.getElementById(submitBtnId);
    const feedbackContainer = document.getElementById(feedbackId);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    function showFeedback(type, message) {
        if (!feedbackContainer) return;
        const icons = {
            success: 'bi bi-check-circle',
            danger: 'bi bi-exclamation-triangle',
            warning: 'bi bi-exclamation-circle',
            info: 'bi bi-info-circle',
        };
        feedbackContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="${icons[type] || icons.info} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        `;
        feedbackContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    window.marketplaceShowFeedback = showFeedback;

    document.querySelectorAll('.pet-type-option input, .pet-size-option input').forEach((input) => {
        input.addEventListener('change', function () {
            const group = this.closest('.pet-type-grid, .pet-size-grid');
            group?.querySelectorAll('label').forEach((label) => label.classList.remove('is-selected'));
            this.closest('label')?.classList.add('is-selected');
        });
    });

    form?.addEventListener('submit', async function (event) {
        event.preventDefault();

        const whatsappField = form.elements['whatsapp'];
        const digits = String(whatsappField?.value || '').replace(/\D/g, '');
        if (!/^\d{10,11}$/.test(digits)) {
            showFeedback('warning', 'Informe um WhatsApp válido com DDD (10 ou 11 dígitos).');
            whatsappField?.focus();
            return;
        }

        const categoryChecked = form.querySelector('input[name="category"]:checked');
        if (!categoryChecked) {
            showFeedback('warning', 'Selecione uma categoria.');
            return;
        }

        const formData = new FormData(form);
        formData.set('whatsapp', digits);

        if (typeof window.appendMarketplaceImagesToFormData === 'function') {
            window.appendMarketplaceImagesToFormData(formData, imagesInputId);
        }

        if (submitMethod.toUpperCase() === 'PUT') {
            formData.append('_method', 'PUT');
        }

        const originalHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${loadingLabel}`;

        try {
            const response = await fetch(submitUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                if (response.status === 422 && data?.errors) {
                    throw new Error(Object.values(data.errors).flat().join(' '));
                }
                throw new Error(data?.message || data?.error || 'Não foi possível salvar o anúncio.');
            }

            showFeedback('success', data?.message || successFallback);
            setTimeout(() => {
                window.location.href = redirectUrl;
            }, 900);
        } catch (error) {
            showFeedback('danger', error.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
        }
    });
});
</script>
