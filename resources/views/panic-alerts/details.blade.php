<div class="row">
    <div class="col-md-6">
        <h6 class="fw-bold text-danger mb-3">
            <i class="bi bi-exclamation-octagon-fill me-2"></i>Informações do Alerta
        </h6>
        
        <div class="mb-3">
            <strong>ID do Alerta:</strong>
            <span class="badge bg-dark fs-6 ms-2">#{{ $alert->id }}</span>
        </div>
        
        <div class="mb-3">
            <strong>Status:</strong>
            @if($alert->status === 'active')
                <span class="badge bg-danger fs-6 ms-2">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>ATIVO
                </span>
            @else
                <span class="badge bg-success fs-6 ms-2">
                    <i class="bi bi-check-circle-fill me-1"></i>RESOLVIDO
                </span>
            @endif
        </div>
        
        <div class="mb-3">
            <strong>Tipo de Emergência:</strong>
            <div class="mt-2">
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
                <span class="fs-3 me-2">{{ $emergencyIcons[$alert->alert_type] ?? '🚨' }}</span>
                <span class="fs-5 fw-bold text-danger">{{ $emergencyTypes[$alert->alert_type] ?? strtoupper($alert->alert_type) }}</span>
            </div>
        </div>
        
        <div class="mb-3">
            <strong>Título:</strong>
            <p class="mt-1">{{ $alert->title }}</p>
        </div>
        
        <div class="mb-3">
            <strong>Descrição:</strong>
            <p class="mt-1">{{ $alert->description ?: 'Sem descrição adicional' }}</p>
        </div>
        
        <div class="mb-3">
            <strong>Local:</strong>
            <p class="mt-1">{{ $alert->location ?: 'Condomínio' }}</p>
        </div>
        
        <div class="mb-3">
            <strong>Gravidade:</strong>
            @php
                $severityMap = [
                    'low' => ['text' => 'Baixa', 'class' => 'bg-success'],
                    'medium' => ['text' => 'Média', 'class' => 'bg-warning'],
                    'high' => ['text' => 'Alta', 'class' => 'bg-danger'],
                    'critical' => ['text' => 'Crítica', 'class' => 'bg-dark']
                ];
                $severity = $severityMap[$alert->severity] ?? $severityMap['high'];
            @endphp
            <span class="badge {{ $severity['class'] }} fs-6 ms-2">{{ $severity['text'] }}</span>
        </div>
    </div>
    
    <div class="col-md-6">
        <h6 class="fw-bold text-primary mb-3">
            <i class="bi bi-person-fill me-2"></i>Informações do Reportador
        </h6>
        
        <div class="d-flex align-items-center mb-3">
            @if($alert->user && $alert->user->photo)
                <img src="{{ Storage::url($alert->user->photo) }}" 
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
                <div class="fw-bold fs-5">{{ $alert->user->name ?? 'Usuário' }}</div>
                <div class="text-muted">{{ $alert->user->email ?? 'N/A' }}</div>
                <div class="text-muted">{{ $alert->user->phone ?? 'N/A' }}</div>
            </div>
        </div>
        
        <div class="mb-3">
            <strong>Unidade:</strong>
            <span class="ms-2">{{ $alert->user->unit->full_identifier ?? 'N/A' }}</span>
        </div>
        
        <div class="mb-3">
            <strong>Data/Hora do Alerta:</strong>
            <div class="mt-1">
                <div class="fw-bold">{{ $alert->created_at->format('d/m/Y') }}</div>
                <div class="text-muted">{{ $alert->created_at->format('H:i:s') }}</div>
            </div>
        </div>
        
        @if($alert->status === 'resolved')
        <hr class="my-4">
        
        <h6 class="fw-bold text-success mb-3">
            <i class="bi bi-check-circle-fill me-2"></i>Informações da Resolução
        </h6>
        
        <div class="d-flex align-items-center mb-3">
            @if($alert->resolvedBy && $alert->resolvedBy->photo)
                <img src="{{ Storage::url($alert->resolvedBy->photo) }}" 
                     class="rounded-circle me-3" 
                     width="50" height="50" 
                     alt="Foto do resolvedor">
            @else
                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center me-3" 
                     style="width: 50px; height: 50px;">
                    <i class="bi bi-person-fill text-white"></i>
                </div>
            @endif
            <div>
                <div class="fw-bold">{{ $alert->resolvedBy->name ?? 'Usuário' }}</div>
                <div class="text-muted">{{ $alert->resolvedBy->email ?? 'N/A' }}</div>
            </div>
        </div>
        
        <div class="mb-3">
            <strong>Data/Hora da Resolução:</strong>
            <div class="mt-1">
                <div class="fw-bold">{{ $alert->resolved_at->format('d/m/Y') }}</div>
                <div class="text-muted">{{ $alert->resolved_at->format('H:i:s') }}</div>
            </div>
        </div>
        
        <div class="mb-3">
            <strong>Tempo de Resolução:</strong>
            <span class="badge bg-info fs-6 ms-2">
                {{ $alert->created_at->diffForHumans($alert->resolved_at) }}
            </span>
        </div>
        @endif
    </div>
</div>

@if($alert->status === 'active')
<div class="row mt-4">
    <div class="col-12">
        <div class="alert alert-warning d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>
                <strong>Alerta Ativo!</strong> Este alerta ainda está ativo e requer atenção imediata.
                <button type="button" class="btn btn-success btn-sm ms-3" onclick="resolveAlert({{ $alert->id }})">
                    <i class="bi bi-check-circle me-1"></i>Resolver Agora
                </button>
            </div>
        </div>
    </div>
</div>
@endif
