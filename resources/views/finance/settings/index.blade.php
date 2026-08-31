@extends('layouts.app')

@section('title', 'Ambiente Financeiro')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-1">Ambiente Financeiro</h2>
        <p class="text-muted mb-0">
            Escolha como o módulo financeiro deve operar no condomínio <strong>{{ $condominium->name }}</strong>.
        </p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-sliders"></i> Modo de operação</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('financial.settings.mode') }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="d-block border rounded p-3 h-100 {{ $currentMode === 'full' ? 'border-primary bg-primary bg-opacity-10' : '' }}">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="financial_mode" id="modeFull" value="full"
                                           {{ old('financial_mode', $currentMode) === 'full' ? 'checked' : '' }}>
                                    <span class="form-check-label fw-semibold">Completo</span>
                                </div>
                                <p class="small text-muted mb-0 mt-2">
                                    Contabilidade integrada: transações, contas bancárias, conciliação, entradas/saídas e prestação de contas gerada pelo sistema.
                                </p>
                            </label>
                        </div>

                        <div class="col-md-6">
                            <label class="d-block border rounded p-3 h-100 {{ $currentMode === 'simplified' ? 'border-primary bg-primary bg-opacity-10' : '' }}">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="financial_mode" id="modeSimplified" value="simplified"
                                           {{ old('financial_mode', $currentMode) === 'simplified' ? 'checked' : '' }}>
                                    <span class="form-check-label fw-semibold">Simplificado</span>
                                </div>
                                <p class="small text-muted mb-0 mt-2">
                                    Prestação de contas por upload (PDF/XLSX). Taxas, cobranças e pagamentos de reservas continuam ativos.
                                </p>
                            </label>
                        </div>
                    </div>

                    @error('financial_mode')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror

                    <div class="alert alert-warning mt-4 mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        A alteração afeta imediatamente todos os usuários deste condomínio. Dados existentes não são apagados.
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Salvar configuração
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        @if($condominium->registration_code)
        <div class="card shadow-sm border-primary mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-key"></i> Código de autocadastro</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">Compartilhe com os moradores para o cadastro em <a href="{{ route('register') }}">{{ route('register') }}</a>.</p>
                <div class="input-group">
                    <input type="text" class="form-control fw-bold text-uppercase text-center" id="finRegistrationCode"
                           value="{{ $condominium->registration_code }}" readonly>
                    <button type="button" class="btn btn-outline-primary" id="finCopyCode" title="Copiar">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
            </div>
        </div>
        @endif

        <div class="card shadow-sm border-info">
            <div class="card-body">
                <h6 class="fw-semibold">Modo atual</h6>
                @if($currentMode === 'simplified')
                    <span class="badge bg-info text-dark fs-6">Simplificado</span>
                    <p class="small text-muted mt-3 mb-0">
                        Gerencie os arquivos de prestação de contas em
                        <a href="{{ route('accountability-uploads.index') }}">Prestação de Contas</a>.
                    </p>
                @else
                    <span class="badge bg-secondary fs-6">Completo</span>
                    <p class="small text-muted mt-3 mb-0">
                        Todos os recursos financeiros do sistema estão disponíveis no menu.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('finCopyCode')?.addEventListener('click', function () {
    const input = document.getElementById('finRegistrationCode');
    if (!input) return;
    navigator.clipboard.writeText(input.value).then(() => {
        this.innerHTML = '<i class="bi bi-check2"></i>';
        setTimeout(() => { this.innerHTML = '<i class="bi bi-clipboard"></i>'; }, 1500);
    });
});
</script>
@endpush
@endsection
