@extends('layouts.app')

@section('title', 'WhatsApp — ' . $condominium->name)

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <a href="{{ route('condominiums.index') }}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Condomínios</a>
            <h1 class="mt-2 mb-1"><i class="bi bi-whatsapp text-success"></i> WhatsApp do Condomínio</h1>
            <p class="text-muted mb-0">
                Configure a instância Evolution e o número usados para avisos de <strong>{{ $condominium->name }}</strong>.
                Somente administradores da plataforma podem alterar estas configurações.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Evolution API — {{ $condominium->name }}</h5>
                    <button type="button" class="btn btn-sm btn-outline-success" id="btnTestWhatsApp">
                        <i class="bi bi-lightning-charge"></i> Testar conexão
                    </button>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('condominiums.settings.whatsapp.update', $condominium) }}" id="whatsappSettingsForm">
                        @csrf
                        @method('PUT')

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="enabled" value="1" id="whatsappEnabled"
                                   @checked(old('enabled', $config['enabled']))>
                            <label class="form-check-label fw-semibold" for="whatsappEnabled">Ativar envio de notificações por WhatsApp</label>
                            <div class="form-text">Quando desligado, nenhuma mensagem será enviada neste condomínio.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">URL da Evolution API</label>
                            <input type="url" name="api_url" class="form-control @error('api_url') is-invalid @enderror"
                                   value="{{ old('api_url', $config['api_url']) }}"
                                   placeholder="http://localhost:8080" required>
                            @error('api_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nome da instância</label>
                            <input type="text" name="instance" class="form-control @error('instance') is-invalid @enderror"
                                   value="{{ old('instance', $config['instance']) }}"
                                   placeholder="condominio-exemplo" required>
                            @error('instance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Use um nome único por condomínio no servidor Evolution.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">API Key</label>
                            @if($maskedKey)
                                <div class="small text-muted mb-1">Atual: {{ $maskedKey }}</div>
                            @endif
                            <input type="password" name="api_key" class="form-control @error('api_key') is-invalid @enderror"
                                   placeholder="Informe para substituir a chave atual" autocomplete="new-password">
                            @error('api_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Telefone para teste (opcional)</label>
                            <input type="text" name="test_phone" id="testPhone" class="form-control"
                                   placeholder="Ex: 11999998888"
                                   value="{{ old('test_phone', auth()->user()?->phone) }}">
                        </div>

                        <hr>

                        <h6 class="mb-3">Tipos de aviso por WhatsApp</h6>
                        <p class="text-muted small">Marque os grupos que devem gerar mensagem. O envio usa o telefone cadastrado no perfil de cada destinatário.</p>

                        <div class="row g-2 mb-3">
                            @foreach($groups as $group)
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100 bg-light">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                   name="notify_groups[{{ $group['key'] }}]" value="1"
                                                   id="group_{{ $group['key'] }}"
                                                   @checked(old('notify_groups.'.$group['key'], $group['enabled']))>
                                            <label class="form-check-label fw-semibold" for="group_{{ $group['key'] }}">
                                                {{ $group['label'] }}
                                            </label>
                                        </div>
                                        <div class="small text-muted mt-1">{{ $group['description'] }}</div>
                                        @if(!empty($group['recipients']))
                                            <div class="small mt-2 pt-2 border-top">
                                                <span class="text-success"><i class="bi bi-people-fill"></i> Quem recebe:</span>
                                                <span class="text-muted">{{ $group['recipients'] }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Salvar configurações
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light"><h5 class="mb-0">Status</h5></div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2">
                            <span class="text-muted">Integração:</span>
                            @if($config['api_url'] && $config['instance'])
                                <span class="badge bg-success">Configurada</span>
                            @else
                                <span class="badge bg-warning text-dark">Pendente</span>
                            @endif
                        </li>
                        <li class="mb-2">
                            <span class="text-muted">Envio neste condomínio:</span>
                            <span class="badge {{ $config['enabled'] ? 'bg-success' : 'bg-secondary' }}">
                                {{ $config['enabled'] ? 'Ativo' : 'Inativo' }}
                            </span>
                        </li>
                        <li>
                            <span class="text-muted">Grupos ativos:</span>
                            <strong>{{ collect($groups)->where('enabled', true)->count() }}</strong> de {{ count($groups) }}
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-light"><h5 class="mb-0">Resultado do teste</h5></div>
                <div class="card-body">
                    <pre id="whatsappTestResult" class="small bg-light rounded p-3 mb-0" style="min-height:120px;white-space:pre-wrap;">Clique em "Testar conexão" para verificar a instância Evolution deste condomínio.</pre>
                </div>
            </div>

            <div class="alert alert-info small mt-4 mb-0">
                <strong>Importante:</strong> escaneie o QR Code na instância Evolution com o número do condomínio.
                Cobranças da assinatura SaaS continuam sendo enviadas pela instância global da plataforma.
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btnTestWhatsApp');
    const resultEl = document.getElementById('whatsappTestResult');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    btn?.addEventListener('click', async () => {
        btn.disabled = true;
        resultEl.textContent = 'Testando conexão...';

        try {
            const res = await fetch('{{ route('condominiums.settings.whatsapp.test', $condominium) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    test_phone: document.getElementById('testPhone')?.value || '',
                }),
            });

            const data = await res.json();
            resultEl.textContent = JSON.stringify(data, null, 2);
        } catch (e) {
            resultEl.textContent = 'Erro: ' + e.message;
        } finally {
            btn.disabled = false;
        }
    });
});
</script>
@endpush
