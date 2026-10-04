@php
    $connectRoute = $connectRoute ?? null;
    $statusRoute = $statusRoute ?? null;
    $disconnectRoute = $disconnectRoute ?? null;
    $initialConnected = (bool) ($connection['ok'] ?? false);
@endphp
@if($connectRoute && $statusRoute)
<div class="card shadow-sm mb-4 border-success" id="whatsappQrConnectCard">
    <div class="card-header bg-success bg-opacity-10 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0"><i class="bi bi-qr-code-scan text-success"></i> Conectar WhatsApp</h5>
        <span class="badge {{ $initialConnected ? 'bg-success' : 'bg-secondary' }}" id="whatsappQrBadge">
            {{ $initialConnected ? 'Conectado' : 'Aguardando conexão' }}
        </span>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            Vincule o número do condomínio sem depender do administrador da plataforma.
            O SindCON cria (ou reutiliza) sua instância na Evolution e exibe o QR Code aqui.
        </p>
        <ol class="small text-muted mb-3">
            <li>No celular: WhatsApp → <strong>Aparelhos conectados</strong> → <strong>Conectar um aparelho</strong>.</li>
            <li>Clique em <strong>Gerar QR Code</strong> abaixo e escaneie antes de expirar (cerca de 40 segundos).</li>
            <li>Aguarde o status ficar <strong>Conectado</strong> e use <strong>Testar conexão</strong> para validar o envio.</li>
        </ol>
        <div class="d-flex flex-wrap gap-2 mb-3">
            <button type="button" class="btn btn-success" id="btnWhatsAppGenerateQr">
                <i class="bi bi-qr-code"></i> Gerar QR Code
            </button>
            <button type="button" class="btn btn-outline-secondary" id="btnWhatsAppDisconnect" @disabled(!($config['configured_in_db'] ?? false))>
                <i class="bi bi-phone-x"></i> Desconectar aparelho
            </button>
        </div>
        <div id="whatsappQrMessage" class="small text-muted mb-2"></div>
        <div class="text-center" id="whatsappQrImageWrap" style="display:none;">
            <img id="whatsappQrImage" alt="QR Code WhatsApp" class="img-fluid border rounded" style="max-width:280px;">
            <div class="small text-warning mt-2" id="whatsappQrExpiryHint">O QR expira em breve. Se falhar, clique em Gerar QR Code novamente.</div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const connectRoute = @json($connectRoute);
    const statusRoute = @json($statusRoute);
    const disconnectRoute = @json($disconnectRoute);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const btnGenerate = document.getElementById('btnWhatsAppGenerateQr');
    const btnDisconnect = document.getElementById('btnWhatsAppDisconnect');
    const msgEl = document.getElementById('whatsappQrMessage');
    const imgWrap = document.getElementById('whatsappQrImageWrap');
    const imgEl = document.getElementById('whatsappQrImage');
    const badgeEl = document.getElementById('whatsappQrBadge');

    let pollTimer = null;
    let pollCount = 0;
    const maxPolls = 72;

    function stopPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
        pollCount = 0;
    }

    function setBadge(connected) {
        if (!badgeEl) return;
        badgeEl.textContent = connected ? 'Conectado' : 'Aguardando conexão';
        badgeEl.className = 'badge ' + (connected ? 'bg-success' : 'bg-secondary');
    }

    function showQr(base64) {
        if (!base64 || !imgEl || !imgWrap) return;
        const src = base64.startsWith('data:') ? base64 : 'data:image/png;base64,' + base64;
        imgEl.src = src;
        imgWrap.style.display = 'block';
    }

    async function pollStatus() {
        try {
            const res = await fetch(statusRoute, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            const data = await res.json();
            const conn = data.connection || {};
            if (conn.ok) {
                stopPolling();
                setBadge(true);
                if (msgEl) msgEl.textContent = conn.message || 'WhatsApp conectado.';
                if (imgWrap) imgWrap.style.display = 'none';
                window.location.reload();
            }
        } catch (e) {
            /* ignore transient errors */
        }
        pollCount++;
        if (pollCount >= maxPolls) {
            stopPolling();
            if (msgEl) msgEl.textContent += ' Tempo de espera esgotado. Gere o QR Code novamente se ainda não conectou.';
        }
    }

    function startPolling() {
        stopPolling();
        pollTimer = setInterval(pollStatus, 5000);
        pollStatus();
    }

    btnGenerate?.addEventListener('click', async () => {
        btnGenerate.disabled = true;
        if (msgEl) msgEl.textContent = 'Preparando instância e QR Code...';
        stopPolling();

        try {
            const res = await fetch(connectRoute, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: '{}',
            });
            const data = await res.json();

            if (data.connected) {
                setBadge(true);
                if (msgEl) msgEl.textContent = data.message || 'Já conectado.';
                if (imgWrap) imgWrap.style.display = 'none';
                window.location.reload();
                return;
            }

            if (!data.ok) {
                if (msgEl) msgEl.textContent = data.message || 'Não foi possível gerar o QR Code.';
                return;
            }

            if (msgEl) msgEl.textContent = data.message || 'Escaneie o QR Code no WhatsApp.';
            if (data.qrcode_base64) {
                showQr(data.qrcode_base64);
                startPolling();
            }
        } catch (e) {
            if (msgEl) msgEl.textContent = 'Erro: ' + e.message;
        } finally {
            btnGenerate.disabled = false;
        }
    });

    btnDisconnect?.addEventListener('click', async () => {
        if (!disconnectRoute || !confirm('Desconectar o WhatsApp desta instância? Será necessário escanear o QR Code de novo.')) {
            return;
        }
        btnDisconnect.disabled = true;
        stopPolling();
        try {
            const res = await fetch(disconnectRoute, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: '{}',
            });
            const data = await res.json();
            if (msgEl) msgEl.textContent = data.message || '';
            setBadge(false);
            if (imgWrap) imgWrap.style.display = 'none';
            window.location.reload();
        } catch (e) {
            if (msgEl) msgEl.textContent = 'Erro: ' + e.message;
        } finally {
            btnDisconnect.disabled = false;
        }
    });
});
</script>
@endpush
@endif
