@extends('layouts.app')

@section('title', 'Configurações Asaas — Plataforma')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <a href="{{ route('platform.dashboard') }}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Dashboard SaaS</a>
            <h1 class="mt-2 mb-1"><i class="bi bi-credit-card-2-front"></i> Integração Asaas (SaaS)</h1>
            <p class="text-muted mb-0">Credenciais usadas para cobrar assinaturas dos condomínios contratantes.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Credenciais</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('platform.settings.asaas.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">API Key Asaas</label>
                            @if($maskedKey)
                                <div class="small text-muted mb-1">Atual: {{ $maskedKey }}</div>
                            @endif
                            <input type="password" name="api_key" class="form-control @error('api_key') is-invalid @enderror"
                                   placeholder="Informe para substituir a chave atual" autocomplete="new-password">
                            @error('api_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Prioridade: banco de dados → `.env` (`ASAAS_API_KEY`).</div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="sandbox" value="1" id="asaasSandbox"
                                   @checked(old('sandbox', $config['sandbox']))>
                            <label class="form-check-label" for="asaasSandbox">Ambiente sandbox (homologação)</label>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">E-mail para alertas de webhook</label>
                            <input type="email" name="webhook_email" class="form-control"
                                   value="{{ old('webhook_email', $config['webhook_email']) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Token de validação do webhook (opcional)</label>
                            <input type="password" name="webhook_token" class="form-control"
                                   placeholder="Deixe em branco para manter o atual" autocomplete="new-password">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Salvar configurações
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Webhook da plataforma</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnTestAsaasIntegration">
                        <i class="bi bi-lightning-charge"></i> Testar Asaas e Webhook
                    </button>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Configure esta URL no painel Asaas para assinaturas SaaS:</p>
                    <code class="d-block p-2 bg-light rounded small mb-3">{{ $webhookUrl }}</code>
                    <ul class="small mb-3">
                        <li>Eventos de pagamento e assinatura</li>
                        <li>Separado do webhook de cobranças internas do condomínio</li>
                    </ul>

                    <div id="asaasTestResults" class="d-none">
                        <hr>
                        <h6 class="mb-2">Resultado do teste</h6>
                        <div id="asaasTestAsaas" class="alert mb-2 py-2 small"></div>
                        <div id="asaasTestWebhook" class="alert mb-0 py-2 small"></div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-{{ $config['api_key'] ? 'success' : 'warning' }}">
                <div class="card-body">
                    <h6 class="mb-2">Status</h6>
                    @if($config['api_key'])
                        <span class="badge bg-success">Asaas configurado</span>
                        @if($config['configured_in_db'])
                            <span class="badge bg-info text-dark ms-1">Chave no banco</span>
                        @else
                            <span class="badge bg-secondary ms-1">Chave via .env</span>
                        @endif
                    @else
                        <span class="badge bg-warning text-dark">Aguardando API Key</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('btnTestAsaasIntegration');
    const panel = document.getElementById('asaasTestResults');
    const asaasBox = document.getElementById('asaasTestAsaas');
    const webhookBox = document.getElementById('asaasTestWebhook');

    if (!btn) return;

    btn.addEventListener('click', async function () {
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Testando…';
        panel.classList.remove('d-none');
        asaasBox.className = 'alert alert-secondary mb-2 py-2 small';
        asaasBox.textContent = 'Testando conexão com a API Asaas…';
        webhookBox.className = 'alert alert-secondary mb-0 py-2 small';
        webhookBox.textContent = 'Aguardando…';

        try {
            const response = await fetch('{{ route('platform.settings.asaas.test') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
            });

            const data = await response.json();
            renderResult(asaasBox, data.asaas, 'API Asaas');
            renderResult(webhookBox, data.webhook, 'Webhook');
        } catch (error) {
            asaasBox.className = 'alert alert-danger mb-2 py-2 small';
            asaasBox.textContent = 'Falha ao executar o teste: ' + error.message;
            webhookBox.className = 'alert alert-danger mb-0 py-2 small';
            webhookBox.textContent = '—';
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    });

    function renderResult(el, result, label) {
        if (!result) {
            el.className = 'alert alert-warning mb-2 py-2 small';
            el.textContent = label + ': sem resposta.';
            return;
        }

        el.className = 'alert ' + (
            result.warning ? 'alert-warning' :
            (result.ok ? 'alert-success' : 'alert-danger')
        ) + ' mb-2 py-2 small';
        let html = '<strong>' + label + ':</strong> ' + escapeHtml(result.message);

        if (result.details && Object.keys(result.details).length) {
            html += '<ul class="mb-0 mt-1 ps-3">';
            for (const [key, value] of Object.entries(result.details)) {
                if (value === null || value === undefined || value === '') continue;
                html += '<li><span class="text-muted">' + escapeHtml(key) + ':</span> ' + escapeHtml(String(value)) + '</li>';
            }
            html += '</ul>';
        }

        el.innerHTML = html;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
@endpush
