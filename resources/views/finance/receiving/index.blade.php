@extends('layouts.app')

@section('title', 'Recebimentos — ' . $condominium->name)

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            @if(auth()->user()?->isAdmin())
                <a href="{{ route('condominiums.index') }}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Condomínios</a>
            @else
                <a href="{{ route('financial.settings.index') }}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Ambiente Financeiro</a>
            @endif
            <h1 class="mt-2 mb-1"><i class="bi bi-wallet2 text-primary"></i> Recebimentos do Condomínio</h1>
            <p class="text-muted mb-0">
                Configure como <strong>{{ $condominium->name }}</strong> recebe taxas, multas e reservas.
                Esta integração é independente da assinatura SaaS do SindCON.
            </p>
        </div>
        <div class="text-end">
            @if($config['setup_completed_at'])
                <span class="badge bg-success fs-6"><i class="bi bi-check-circle"></i> Recebimento online ativo</span>
            @elseif($config['receiving_mode'] === 'platform')
                <span class="badge bg-warning text-dark fs-6"><i class="bi bi-hourglass-split"></i> Configuração em andamento</span>
            @else
                <span class="badge bg-secondary fs-6"><i class="bi bi-journal-check"></i> Controle manual</span>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-semibold">Progresso da configuração</span>
                <span class="text-muted small">{{ $progress['percent'] }}%</span>
            </div>
            <div class="progress mb-3" style="height: 8px;">
                <div class="progress-bar bg-primary" style="width: {{ $progress['percent'] }}%"></div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($progress['steps'] as $step)
                    <span class="badge {{ $step['done'] ? 'bg-success' : 'bg-light text-muted border' }}">
                        @if($step['done'])<i class="bi bi-check2"></i>@else<i class="bi bi-circle"></i>@endif
                        {{ $step['label'] }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Passo 1: Modo --}}
            <div class="card shadow-sm mb-4" id="step-mode">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><span class="badge bg-primary me-2">1</span> Como deseja receber?</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('condominiums.settings.receiving.mode', $condominium) }}">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="d-block border rounded p-3 h-100 {{ $config['receiving_mode'] === 'manual' ? 'border-primary bg-primary bg-opacity-10' : '' }}">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_receiving_mode" id="modeManual" value="manual"
                                               {{ old('payment_receiving_mode', $config['receiving_mode']) === 'manual' ? 'checked' : '' }}>
                                        <span class="form-check-label fw-semibold">Somente registros (manual)</span>
                                    </div>
                                    <p class="small text-muted mb-0 mt-2">
                                        O síndico registra pagamentos recebidos fora do sistema (transferência, dinheiro, banco).
                                        Moradores não pagam online pelo SindCON.
                                    </p>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="d-block border rounded p-3 h-100 {{ $config['receiving_mode'] === 'platform' ? 'border-primary bg-primary bg-opacity-10' : '' }}">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_receiving_mode" id="modePlatform" value="platform"
                                               {{ old('payment_receiving_mode', $config['receiving_mode']) === 'platform' ? 'checked' : '' }}>
                                        <span class="form-check-label fw-semibold">Receber pelo SindCON (Asaas)</span>
                                    </div>
                                    <p class="small text-muted mb-0 mt-2">
                                        Moradores pagam taxas, multas e reservas online via <strong>PIX</strong> ou <strong>cartão</strong>.
                                        O valor cai na conta Asaas do condomínio.
                                    </p>
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">
                            <i class="bi bi-save"></i> Salvar modo de recebimento
                        </button>
                    </form>
                </div>
            </div>

            @if($config['receiving_mode'] === 'platform')
            {{-- Passo 2: Conta Asaas --}}
            <div class="card shadow-sm mb-4" id="step-account">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><span class="badge bg-primary me-2">2</span> Crie ou acesse sua conta Asaas</h5>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        O Asaas é o intermediador de pagamentos. Cada condomínio usa <strong>sua própria conta</strong>
                        — diferente da assinatura que você paga à plataforma SindCON.
                    </p>
                    <ol class="mb-4">
                        <li class="mb-2">Acesse o painel Asaas no ambiente escolhido (sandbox para testes ou produção).</li>
                        <li class="mb-2">Complete o cadastro da empresa/condomínio e a verificação de documentos.</li>
                        <li class="mb-2">Em <strong>Integrações → API</strong>, gere sua chave de API (copie para o passo 3).</li>
                    </ol>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ $asaasSignupUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary">
                            <i class="bi bi-box-arrow-up-right"></i> Criar conta Asaas
                        </a>
                        <a href="{{ $asaasPanelUrl }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
                            <i class="bi bi-box-arrow-up-right"></i> Abrir painel Asaas
                        </a>
                    </div>
                    <div class="alert alert-info mt-3 mb-0 small">
                        <i class="bi bi-lightbulb"></i>
                        <strong>Dica:</strong> use o ambiente <em>sandbox</em> primeiro para testar sem movimentar dinheiro real.
                    </div>
                </div>
            </div>

            {{-- Passo 3: Credenciais --}}
            <div class="card shadow-sm mb-4" id="step-credentials">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><span class="badge bg-primary me-2">3</span> Credenciais da API</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('condominiums.settings.receiving.credentials', $condominium) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="sandbox" value="1" id="asaasSandbox"
                                   @checked(old('sandbox', $config['sandbox']))>
                            <label class="form-check-label fw-semibold" for="asaasSandbox">Ambiente sandbox (homologação)</label>
                            <div class="form-text">Desmarque somente quando for usar a conta de produção.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">API Key do Asaas <span class="text-danger">*</span></label>
                            @if($maskedKey)
                                <div class="small text-muted mb-1">Atual: {{ $maskedKey }}</div>
                            @endif
                            <input type="password" name="api_key" class="form-control @error('api_key') is-invalid @enderror"
                                   placeholder="Informe para substituir a chave atual" autocomplete="new-password">
                            @error('api_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Painel Asaas → Integrações → API → Gerar nova chave.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">E-mail para alertas de webhook</label>
                            <input type="email" name="webhook_email" class="form-control"
                                   value="{{ old('webhook_email', $config['webhook_email'] ?? auth()->user()?->email) }}"
                                   placeholder="financeiro@condominio.com.br">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Salvar credenciais
                        </button>
                    </form>
                </div>
            </div>

            {{-- Passo 4: Webhook --}}
            <div class="card shadow-sm mb-4" id="step-webhook">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><span class="badge bg-primary me-2">4</span> Configure o webhook no Asaas</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        O webhook avisa o SindCON quando um morador paga. Sem ele, o pagamento não será confirmado automaticamente.
                    </p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">URL do webhook (copie para o painel Asaas)</label>
                        <div class="input-group">
                            <input type="text" class="form-control font-monospace small" id="webhookUrlInput" value="{{ $webhookUrl }}" readonly>
                            <button type="button" class="btn btn-outline-primary" id="btnCopyWebhookUrl"><i class="bi bi-clipboard"></i></button>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('condominiums.settings.receiving.credentials', $condominium) }}" class="mb-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="sandbox" value="{{ $config['sandbox'] ? '1' : '0' }}">
                        <label class="form-label fw-semibold">Token de autenticação do webhook</label>
                        @if($maskedToken)
                            <div class="small text-muted mb-1">Atual: {{ $maskedToken }}</div>
                        @endif
                        <div class="input-group mb-2">
                            <input type="password" name="webhook_token" class="form-control font-monospace"
                                   placeholder="Gerado automaticamente ao salvar, ou informe um novo">
                            <button type="submit" name="regenerate_webhook_token" value="1" class="btn btn-outline-secondary" title="Gerar novo token">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                        </div>
                        <div class="form-text mb-2">
                            No Asaas, em Integrações → Webhooks, use o mesmo token no campo de autenticação.
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Salvar token</button>
                    </form>

                    <div class="alert alert-warning small mb-0">
                        <strong>Eventos obrigatórios no Asaas:</strong>
                        PAYMENT_CONFIRMED, PAYMENT_RECEIVED, PAYMENT_OVERDUE, PAYMENT_DELETED.
                    </div>
                </div>
            </div>

            {{-- Passo 5: Testes --}}
            <div class="card shadow-sm mb-4 border-primary" id="step-test">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><span class="badge bg-white text-primary me-2">5</span> Testar e ativar</h5>
                    <button type="button" class="btn btn-sm btn-light" id="btnTestReceiving">
                        <i class="bi bi-lightning-charge"></i> Testar integração
                    </button>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        Execute os testes para validar a API Key e o webhook antes de liberar pagamentos aos moradores.
                    </p>

                    <div id="receivingTestResults" class="d-none mb-3">
                        <div id="receivingTestAsaas" class="alert py-2 small mb-2"></div>
                        <div id="receivingTestWebhook" class="alert py-2 small mb-0"></div>
                    </div>

                    @if($config['setup_completed_at'])
                        <div class="alert alert-success mb-3">
                            <i class="bi bi-check-circle"></i>
                            Recebimento online ativo desde {{ $config['setup_completed_at']->format('d/m/Y H:i') }}.
                            Moradores poderão pagar cobranças por PIX e cartão (quando disponibilizado nas telas de cobrança).
                        </div>
                    @endif

                    <form method="POST" action="{{ route('condominiums.settings.receiving.complete', $condominium) }}"
                          onsubmit="return confirm('Confirma a ativação do recebimento online para este condomínio?')">
                        @csrf
                        <button type="submit" class="btn btn-success" {{ !$config['configured_in_db'] ? 'disabled' : '' }}>
                            <i class="bi bi-check2-circle"></i> Concluir e ativar recebimento online
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="card shadow-sm border-secondary">
                <div class="card-body">
                    <h5 class="mb-2"><i class="bi bi-journal-text"></i> Modo manual ativo</h5>
                    <p class="text-muted mb-0">
                        As cobranças continuam sendo geradas no SindCON. O síndico registra os recebimentos manualmente
                        em <a href="{{ route('charges.index') }}">Cobranças</a>. Para habilitar PIX e cartão aos moradores,
                        selecione <strong>Receber pelo SindCON (Asaas)</strong> acima e siga o assistente.
                    </p>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light"><h6 class="mb-0">Status</h6></div>
                <div class="card-body small">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <span class="text-muted">Modo:</span>
                            @if($config['receiving_mode'] === 'platform')
                                <span class="badge bg-primary">SindCON + Asaas</span>
                            @else
                                <span class="badge bg-secondary">Manual</span>
                            @endif
                        </li>
                        <li class="mb-2">
                            <span class="text-muted">API Key:</span>
                            @if($config['configured_in_db'])
                                <span class="badge bg-success">Configurada</span>
                            @else
                                <span class="badge bg-warning text-dark">Pendente</span>
                            @endif
                        </li>
                        <li class="mb-2">
                            <span class="text-muted">Ambiente:</span>
                            <span class="badge bg-{{ $config['sandbox'] ? 'info' : 'dark' }}">
                                {{ $config['sandbox'] ? 'Sandbox' : 'Produção' }}
                            </span>
                        </li>
                        <li>
                            <span class="text-muted">Pagamentos online:</span>
                            @if($config['setup_completed_at'])
                                <span class="badge bg-success">Liberados</span>
                            @else
                                <span class="badge bg-secondary">Bloqueados</span>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-light"><h6 class="mb-0">O que os moradores poderão pagar</h6></div>
                <div class="card-body small">
                    <ul class="mb-0 ps-3">
                        <li>Taxas condominiais e extras</li>
                        <li>Multas aplicadas</li>
                        <li>Reservas de espaços com cobrança</li>
                    </ul>
                    <p class="text-muted mt-3 mb-0">
                        Formas: PIX e cartão de crédito (conforme habilitação na conta Asaas).
                    </p>
                </div>
            </div>

            <div class="card shadow-sm mt-4 border-info">
                <div class="card-body small">
                    <h6 class="text-info"><i class="bi bi-info-circle"></i> Diferente da assinatura SindCON</h6>
                    <p class="mb-0 text-muted">
                        A assinatura que o síndico paga à plataforma usa outra conta Asaas (configurada pelo administrador SaaS).
                        Aqui você configura a conta do <strong>condomínio</strong> para receber dos moradores.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('btnCopyWebhookUrl')?.addEventListener('click', function () {
        const input = document.getElementById('webhookUrlInput');
        if (!input) return;
        navigator.clipboard.writeText(input.value).then(() => {
            this.innerHTML = '<i class="bi bi-check2"></i>';
            setTimeout(() => { this.innerHTML = '<i class="bi bi-clipboard"></i>'; }, 1500);
        });
    });

    const btnTest = document.getElementById('btnTestReceiving');
    const panel = document.getElementById('receivingTestResults');
    const asaasBox = document.getElementById('receivingTestAsaas');
    const webhookBox = document.getElementById('receivingTestWebhook');

    btnTest?.addEventListener('click', async function () {
        const originalHtml = btnTest.innerHTML;
        btnTest.disabled = true;
        btnTest.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Testando…';
        panel?.classList.remove('d-none');

        try {
            const response = await fetch('{{ route('condominiums.settings.receiving.test', $condominium) }}', {
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
            asaasBox.className = 'alert alert-danger py-2 small mb-2';
            asaasBox.textContent = 'Falha ao executar o teste: ' + error.message;
        } finally {
            btnTest.disabled = false;
            btnTest.innerHTML = originalHtml;
        }
    });

    function renderResult(el, result, label) {
        if (!el || !result) return;
        el.className = 'alert ' + (result.warning ? 'alert-warning' : (result.ok ? 'alert-success' : 'alert-danger')) + ' py-2 small mb-2';
        let html = '<strong>' + label + ':</strong> ' + escapeHtml(result.message);
        if (result.details) {
            html += '<ul class="mb-0 mt-1 ps-3">';
            for (const [k, v] of Object.entries(result.details)) {
                if (v) html += '<li><span class="text-muted">' + escapeHtml(k) + ':</span> ' + escapeHtml(String(v)) + '</li>';
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

    @if($config['receiving_mode'] === 'platform')
    const step = @json($progress['current_step']);
    const targets = { mode: 'step-mode', account: 'step-account', credentials: 'step-credentials', webhook: 'step-webhook', test: 'step-test', done: 'step-test' };
    const el = document.getElementById(targets[step] || 'step-mode');
    el?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    @endif
});
</script>
@endpush
