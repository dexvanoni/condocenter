@extends('layouts.app')

@section('title', 'Emergência Ativa')

@section('content')
<div class="container-fluid p-0">
    <div class="row g-0 min-vh-100">
        <!-- Conteúdo Principal -->
        <div class="col-12">
            <div class="alert-page bg-danger text-white" style="min-height: 100vh; display: flex; flex-direction: column;">
                <!-- Header -->
                <div class="alert-header bg-dark p-4 text-center">
                    <h1 class="display-4 mb-2">
                        <i class="bi bi-exclamation-octagon-fill"></i> EMERGÊNCIA ATIVA
                    </h1>
                    <p class="lead mb-0">Alerta de pânico em andamento</p>
                </div>

                <!-- Conteúdo do Alerta -->
                <div class="alert-content flex-grow-1 p-4">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-12 col-lg-10 col-xl-8">
                                <!-- Card Principal -->
                                <div class="card border-0 shadow-lg mb-4">
                                    <div class="card-header bg-danger text-white py-4">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h2 class="mb-0">
                                                    @php
                                                        $emergencyIcons = [
                                                            'fire' => '🔥',
                                                            'robbery' => '🔒',
                                                            'medical' => '🏥',
                                                            'flood' => '🌊',
                                                            'gas' => '⚠️',
                                                            'police' => '🚓',
                                                            'ambulance' => '🚑',
                                                            'domestic_violence' => '⚠️',
                                                            'lost_child' => '👶',
                                                            'other' => '🚨'
                                                        ];
                                                        $emergencyTypes = [
                                                            'fire' => 'INCÊNDIO',
                                                            'robbery' => 'ROUBO/ASSALTO',
                                                            'medical' => 'EMERGÊNCIA MÉDICA',
                                                            'flood' => 'ALAGAMENTO',
                                                            'gas' => 'VAZAMENTO DE GÁS',
                                                            'police' => 'CHAMEM A POLÍCIA',
                                                            'ambulance' => 'CHAMEM UMA AMBULÂNCIA',
                                                            'domestic_violence' => 'VIOLÊNCIA DOMÉSTICA',
                                                            'lost_child' => 'CRIANÇA PERDIDA',
                                                            'other' => 'OUTRA EMERGÊNCIA'
                                                        ];
                                                    @endphp
                                                    <span class="fs-1 me-3">{{ $emergencyIcons[$activeAlert->alert_type] ?? '🚨' }}</span>
                                                    {{ $emergencyTypes[$activeAlert->alert_type] ?? strtoupper($activeAlert->alert_type) }}
                                                </h2>
                                            </div>
                                            <div class="text-end">
                                                @php
                                                    $severityMap = [
                                                        'low' => ['text' => 'Baixa', 'class' => 'bg-success'],
                                                        'medium' => ['text' => 'Média', 'class' => 'bg-warning'],
                                                        'high' => ['text' => 'Alta', 'class' => 'bg-danger'],
                                                        'critical' => ['text' => 'Crítica', 'class' => 'bg-dark']
                                                    ];
                                                    $severity = $severityMap[$activeAlert->severity] ?? $severityMap['high'];
                                                @endphp
                                                <span class="badge {{ $severity['class'] }} fs-6 px-3 py-2">
                                                    Gravidade: {{ $severity['text'] }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body p-4">
                                        <!-- Informações do Alerta -->
                                        <div class="row mb-4">
                                            <div class="col-md-6 mb-3 mb-md-0">
                                                <h5 class="text-danger mb-3">
                                                    <i class="bi bi-info-circle-fill me-2"></i>Informações do Alerta
                                                </h5>
                                                <div class="mb-3">
                                                    <strong>Título:</strong>
                                                    <p class="mb-0">{{ $activeAlert->title }}</p>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Descrição:</strong>
                                                    <p class="mb-0">{{ $activeAlert->description ?: 'Sem descrição adicional' }}</p>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Local:</strong>
                                                    <p class="mb-0">{{ $activeAlert->location ?: 'Condomínio' }}</p>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Data/Hora:</strong>
                                                    <p class="mb-0">
                                                        {{ $activeAlert->created_at->format('d/m/Y') }} às {{ $activeAlert->created_at->format('H:i:s') }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h5 class="text-primary mb-3">
                                                    <i class="bi bi-person-fill me-2"></i>Reportado por
                                                </h5>
                                                <div class="d-flex align-items-center mb-3">
                                                    @if($activeAlert->user && $activeAlert->user->photo)
                                                        <img src="{{ Storage::url($activeAlert->user->photo) }}" 
                                                             class="rounded-circle me-3" 
                                                             width="60" height="60" 
                                                             alt="Foto do usuário">
                                                    @else
                                                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                             style="width: 60px; height: 60px;">
                                                            <i class="bi bi-person-fill text-white fs-4"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-bold fs-5">{{ $activeAlert->user->name ?? 'Usuário' }}</div>
                                                        <div class="text-muted">{{ $activeAlert->user->email ?? 'N/A' }}</div>
                                                        <div class="text-muted">{{ $activeAlert->user->phone ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Unidade:</strong>
                                                    <p class="mb-0">{{ $activeAlert->user->unit->full_identifier ?? 'N/A' }}</p>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>ID do Alerta:</strong>
                                                    <span class="badge bg-dark fs-6">#{{ $activeAlert->id }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <hr class="my-4">

                                        <!-- Ações -->
                                        <div class="text-center">
                                            <h4 class="mb-4">Como você deseja responder a esta emergência?</h4>
                                            <div class="row g-3">
                                                <div class="col-12 col-md-6">
                                                    <button type="button" 
                                                            class="btn btn-warning btn-lg w-100 py-3 fs-5" 
                                                            id="btnCiente"
                                                            onclick="handleCiente()">
                                                        <i class="bi bi-eye-fill me-2"></i>CIENTE
                                                    </button>
                                                    <small class="text-muted d-block mt-2">
                                                        Ao confirmar, você estará ciente da situação. O alerta continuará ativo para outros moradores.
                                                    </small>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <button type="button" 
                                                            class="btn btn-success btn-lg w-100 py-3 fs-5" 
                                                            id="btnTomareiProvidencia"
                                                            onclick="handleTomareiProvidencia()">
                                                        <i class="bi bi-check-circle-fill me-2"></i>TOMAREI PROVIDÊNCIA
                                                    </button>
                                                    <small class="text-muted d-block mt-2">
                                                        Ao confirmar, você assumirá a responsabilidade de resolver. O alerta será desativado para todos.
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botão Voltar -->
                                <div class="text-center">
                                    <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-lg">
                                        <i class="bi bi-arrow-left me-2"></i>Voltar ao Dashboard
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    @php
        $resolveUrl = route('panic.resolve', $activeAlert->id);
        $confirmUrl = route('panic.confirm', $activeAlert->id);
    @endphp
    
    const alertId = {{ $activeAlert->id }};
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const resolveUrl = '{{ $resolveUrl }}';
    const confirmUrl = '{{ $confirmUrl }}';
    
    console.log('URLs configuradas:', { resolveUrl, confirmUrl, alertId });

    function handleCiente() {
        const btn = document.getElementById('btnCiente');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processando...';

        fetch(confirmUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP ${response.status}: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.message) {
                alert('Você confirmou estar ciente do alerta. O alerta continua ativo para outros moradores.');
                window.location.href = '{{ route('dashboard') }}';
            } else {
                alert('Erro: ' + (data.error || 'Erro desconhecido'));
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-eye-fill me-2"></i>CIENTE';
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao processar confirmação: ' + error.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-eye-fill me-2"></i>CIENTE';
        });
    }

    function handleTomareiProvidencia() {
        if (!confirm('Tem certeza que deseja assumir a responsabilidade de resolver este alerta? O alerta será desativado para todos os moradores.')) {
            return;
        }

        const btn = document.getElementById('btnTomareiProvidencia');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processando...';

        console.log('URL da requisição:', resolveUrl);
        console.log('Alert ID:', alertId);
        console.log('CSRF Token:', csrfToken ? 'Presente' : 'Ausente');

        fetch(resolveUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Status da resposta:', response.status);
            console.log('Headers da resposta:', response.headers);
            
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Resposta de erro:', text);
                    throw new Error(`HTTP ${response.status}: ${text.substring(0, 200)}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Dados recebidos:', data);
            if (data.message) {
                alert('Alerta resolvido com sucesso!');
                window.location.href = '{{ route('dashboard') }}';
            } else {
                if (data.error && data.error.includes('já foi resolvido')) {
                    alert('Este alerta já foi resolvido por outro usuário.');
                } else {
                    alert('Erro ao resolver alerta: ' + (data.error || 'Erro desconhecido'));
                }
                window.location.href = '{{ route('dashboard') }}';
            }
        })
        .catch(error => {
            console.error('Erro completo:', error);
            alert('Erro ao resolver alerta: ' + error.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>TOMAREI PROVIDÊNCIA';
        });
    }
</script>

<style>
    .alert-page {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        animation: panicPulse 2s infinite;
    }
    
    @keyframes panicPulse {
        0% { filter: brightness(1); }
        50% { filter: brightness(1.1); }
        100% { filter: brightness(1); }
    }

    .alert-header {
        background: rgba(0, 0, 0, 0.3) !important;
    }

    .card {
        background: white;
    }

    @media (max-width: 768px) {
        .alert-content {
            padding: 1rem !important;
        }
        
        .card-body {
            padding: 1.5rem !important;
        }
        
        .btn-lg {
            font-size: 1rem !important;
            padding: 0.75rem 1rem !important;
        }
    }
</style>
@endpush
@endsection



