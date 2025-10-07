@extends('layouts.app')

@section('title', 'Assembleias')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Assembleias</h2>
            @can('create_assemblies')
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#novaAssembleiaModal">
                <i class="bi bi-plus-circle"></i> Nova Assembleia
            </button>
            @endcan
        </div>
    </div>
</div>

<!-- Filtros -->
<ul class="nav nav-pills mb-4" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-status="scheduled" href="#" onclick="filterAssemblies('scheduled')">
            Agendadas
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-status="in_progress" href="#" onclick="filterAssemblies('in_progress')">
            Em Andamento
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-status="completed" href="#" onclick="filterAssemblies('completed')">
            Concluídas
        </a>
    </li>
</ul>

<!-- Lista de Assembleias -->
<div class="row g-4" id="assembliesContainer">
    <!-- Placeholder -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Assembleia Geral Ordinária - Novembro 2025</h5>
                    <span class="badge bg-light text-dark">Agendada</span>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    <i class="bi bi-calendar"></i> 15/11/2025 às 19:00<br>
                    <i class="bi bi-clock"></i> Duração: 2 horas<br>
                    <i class="bi bi-shield-check"></i> Votação: Aberta
                </p>

                <h6>Pauta:</h6>
                <ol class="mb-3">
                    <li>Aprovação das contas do mês anterior</li>
                    <li>Discussão sobre reforma da fachada</li>
                    <li>Eleição do novo síndico</li>
                    <li>Aumento da taxa condominial</li>
                </ol>

                <div class="d-grid gap-2">
                    @can('vote_assemblies')
                    <button class="btn btn-primary">
                        <i class="bi bi-hand-thumbs-up"></i> Votar
                    </button>
                    @endcan
                    <button class="btn btn-outline-primary">
                        <i class="bi bi-eye"></i> Ver Detalhes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Assembleia Extraordinária - Outubro 2025</h5>
                    <span class="badge bg-light text-dark">Concluída</span>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    <i class="bi bi-calendar"></i> Realizada em: 05/10/2025<br>
                    <i class="bi bi-people"></i> Participantes: 45 moradores<br>
                    <i class="bi bi-check-circle"></i> Status: Ata disponível
                </p>

                <h6>Resultados da Votação:</h6>
                <div class="mb-2">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Item 1: Aprovação</span>
                        <span class="badge bg-success">85% Sim</span>
                    </div>
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar bg-success" style="width: 85%"></div>
                    </div>
                </div>

                <div class="d-grid">
                    <button class="btn btn-outline-success">
                        <i class="bi bi-file-pdf"></i> Baixar Ata (PDF)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nova Assembleia -->
<div class="modal fade" id="novaAssembleiaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nova Assembleia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNovaAssembleia">
                    <div class="mb-3">
                        <label class="form-label">Título *</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Data e Hora *</label>
                                <input type="datetime-local" class="form-control" name="scheduled_at" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Duração (minutos) *</label>
                                <input type="number" class="form-control" name="duration_minutes" 
                                       value="120" min="30" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipo de Votação *</label>
                        <select class="form-select" name="voting_type" required>
                            <option value="open">Aberta (voto identificado)</option>
                            <option value="secret">Secreta (voto anônimo)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pauta *</label>
                        <div id="agendaItems">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" placeholder="Item da pauta" 
                                       data-agenda-item>
                                <button class="btn btn-outline-danger" type="button" onclick="removeAgendaItem(this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addAgendaItem()">
                            <i class="bi bi-plus"></i> Adicionar Item
                        </button>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="allow_delegation" id="allowDelegation">
                        <label class="form-check-label" for="allowDelegation">
                            Permitir delegação de voto
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="criarAssembleia()">
                    Criar e Convocar Moradores
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function addAgendaItem() {
        const container = document.getElementById('agendaItems');
        const div = document.createElement('div');
        div.className = 'input-group mb-2';
        div.innerHTML = `
            <input type="text" class="form-control" placeholder="Item da pauta" data-agenda-item>
            <button class="btn btn-outline-danger" type="button" onclick="removeAgendaItem(this)">
                <i class="bi bi-trash"></i>
            </button>
        `;
        container.appendChild(div);
    }

    function removeAgendaItem(btn) {
        btn.closest('.input-group').remove();
    }

    function criarAssembleia() {
        const form = document.getElementById('formNovaAssembleia');
        const formData = new FormData(form);
        
        // Coletar itens da pauta
        const agendaItems = [];
        document.querySelectorAll('[data-agenda-item]').forEach(input => {
            if (input.value.trim()) {
                agendaItems.push(input.value.trim());
            }
        });

        const data = {
            title: formData.get('title'),
            description: formData.get('description'),
            scheduled_at: formData.get('scheduled_at'),
            duration_minutes: formData.get('duration_minutes'),
            voting_type: formData.get('voting_type'),
            allow_delegation: formData.get('allow_delegation') === 'on',
            agenda: agendaItems,
        };

        fetch('/api/assemblies', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                alert(data.message);
                location.reload();
            }
        })
        .catch(error => console.error('Erro:', error));
    }

    function filterAssemblies(status) {
        // Implementar filtro via AJAX
        console.log('Filtrar por status:', status);
    }
</script>
@endpush
@endsection

