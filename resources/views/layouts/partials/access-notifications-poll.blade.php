@auth
@if(auth()->user()->receivesAccessMovementAlerts())
<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const pollMs = 12000;
    let knownAlertIds = new Set();
    let polling = false;

    document.getElementById('accessAlertsContainer')?.querySelectorAll('[data-notification-id]').forEach(el => {
        knownAlertIds.add(String(el.dataset.notificationId));
    });

    document.addEventListener('click', async e => {
        const btn = e.target.closest('.btn-mark-access-alert');
        if (!btn) return;
        await markAccessAlertRead(btn.dataset.id);
        btn.closest('.access-alert-item')?.remove();
        updateAccessAlertsEmptyState();
    });

    async function markAccessAlertRead(id) {
        await fetch(`/api/notifications/access-alerts/${id}/read`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });
        knownAlertIds.delete(String(id));
        refreshNotificationBadge();
    }

    async function refreshNotificationBadge() {
        try {
            const res = await fetch('/api/notifications/unread-count', {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            if (!res.ok) return;
            const data = await res.json();
            document.querySelectorAll('[data-unread-notifications]').forEach(el => {
                const count = data.count || 0;
                el.textContent = count > 99 ? '99+' : String(count);
                el.classList.toggle('d-none', count < 1);
            });
        } catch {}
    }

    function updateAccessAlertsEmptyState() {
        const container = document.getElementById('accessAlertsContainer');
        if (!container) return;
        if (!container.querySelector('.access-alert-item') && !container.querySelector('.row')) {
            container.innerHTML = '';
        }
    }

    function renderAccessAlerts(alerts) {
        const container = document.getElementById('accessAlertsContainer');
        if (!container) return;

        if (!alerts.length) {
            container.innerHTML = '';
            return;
        }

        const html = alerts.map(alert => {
            const critical = alert.type === 'access_prohibition_critical';
            const denied = alert.type === 'access_denied';
            const widgetClass = critical ? 'critical' : (denied ? 'danger' : 'success');
            const icon = critical ? 'bi-exclamation-octagon-fill' : (denied ? 'bi-x-octagon-fill' : 'bi-door-open-fill');
            const when = alert.created_at ? new Date(alert.created_at).toLocaleString('pt-BR') : '';

            return `<div class="col-12">
                <div class="widget-notification ${widgetClass} fade-in access-alert-item" data-notification-id="${alert.id}">
                    <div class="d-flex align-items-center flex-wrap gap-3">
                        <i class="bi ${icon} fs-3"></i>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${esc(alert.title)}</h6>
                            <p class="mb-0">${esc(alert.message)}</p>
                            <small class="text-muted">${esc(when)}</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-mark-access-alert" data-id="${alert.id}">
                            <i class="bi bi-check2"></i> Ok
                        </button>
                    </div>
                </div>
            </div>`;
        }).join('');

        container.innerHTML = `<div class="row mb-4">${html}</div>`;
    }

    function showAccessToast(alert) {
        const critical = alert.type === 'access_prohibition_critical';
        const denied = alert.type === 'access_denied';
        const tone = critical ? 'danger' : (denied ? 'danger' : 'success');
        const toast = document.createElement('div');
        toast.className = `alert alert-${tone} alert-dismissible fade show position-fixed shadow`;
        if (critical) {
            toast.style.border = '2px solid #7f1d1d';
            toast.style.background = '#450a0a';
            toast.style.color = '#fecaca';
        }
        toast.style.cssText = 'top:1rem;right:1rem;z-index:1090;max-width:420px;';
        toast.innerHTML = `<strong>${esc(alert.title)}</strong><br>${esc(alert.message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 8000);
    }

    function esc(value) {
        return (value ?? '').toString().replace(/[&<>"']/g, m => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        }[m]));
    }

    async function pollAccessAlerts() {
        if (polling || document.hidden) return;
        polling = true;

        try {
            const res = await fetch('/api/notifications/access-alerts', {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            if (!res.ok) return;

            const payload = await res.json();
            const alerts = payload.data || [];

            alerts.forEach(alert => {
                const id = String(alert.id);
                if (!knownAlertIds.has(id)) {
                    knownAlertIds.add(id);
                    showAccessToast(alert);
                }
            });

            renderAccessAlerts(alerts);
            refreshNotificationBadge();
        } catch {} finally {
            polling = false;
        }
    }

    pollAccessAlerts();
    setInterval(pollAccessAlerts, pollMs);
});
</script>
@endif
@endauth
