@extends('layouts.app')

@section('title', 'Gerenciar Reservas')

@push('styles')
<style>
    .bulk-actions {
        display: none;
        margin-bottom: 1rem;
    }
    .bulk-actions.show {
        display: block;
    }
    .selected-count {
        font-weight: bold;
        color: #dc3545;
    }
    .loading {
        text-align: center;
        padding: 2rem;
    }
    .table-responsive {
        max-height: 70vh;
        overflow-y: auto;
    }
    
    /* Estilos para filtros */
    .card-header.bg-light {
        background-color: #f8f9fa !important;
        border-bottom: 1px solid #dee2e6;
    }
    
    .form-label.small {
        font-size: 0.875rem;
        font-weight: 600;
        color: #495057;
    }
    
    .form-control-sm, .form-select-sm {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }
    
    .btn-group .btn-sm {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0"><i class="bi bi-gear"></i> Gerenciar Reservas</h3>
            <p class="text-muted mb-0">Gerencie todas as reservas do condomínio</p>
        </div>
        <a href="{{ route('reservations.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar ao Calendário
        </a>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-funnel"></i> Filtros Avançados</h6>
        </div>
        <div class="card-body">
            <form id="filtersForm">
                <div class="row g-3">
                    <!-- Período -->
                    <div class="col-md-3">
                        <label for="filterDateFrom" class="form-label small">Data Inicial</label>
                        <input type="date" class="form-control form-control-sm" id="filterDateFrom" name="date_from">
                    </div>
                    <div class="col-md-3">
                        <label for="filterDateTo" class="form-label small">Data Final</label>
                        <input type="date" class="form-control form-control-sm" id="filterDateTo" name="date_to">
                    </div>
                    
                    <!-- Espaço -->
                    <div class="col-md-3">
                        <label for="filterSpace" class="form-label small">Espaço</label>
                        <select class="form-select form-select-sm" id="filterSpace" name="space_id">
                            <option value="">Todos os espaços</option>
                        </select>
                    </div>
                    
                    <!-- Status -->
                    <div class="col-md-3">
                        <label for="filterStatus" class="form-label small">Status</label>
                        <select class="form-select form-select-sm" id="filterStatus" name="status">
                            <option value="">Todos os status</option>
                            <option value="pending">Pendente</option>
                            <option value="approved">Aprovada</option>
                            <option value="rejected">Rejeitada</option>
                            <option value="cancelled">Cancelada</option>
                        </select>
                    </div>
                    
                    <!-- Tipo -->
                    <div class="col-md-3">
                        <label for="filterType" class="form-label small">Tipo</label>
                        <select class="form-select form-select-sm" id="filterType" name="type">
                            <option value="">Todos os tipos</option>
                            <option value="individual">Individual</option>
                            <option value="recurring">Recorrente</option>
                        </select>
                    </div>
                    
                    <!-- Morador -->
                    <div class="col-md-3">
                        <label for="filterUser" class="form-label small">Morador</label>
                        <input type="text" class="form-control form-control-sm" id="filterUser" name="user_name" placeholder="Nome do morador">
                    </div>
                    
                    <!-- Unidade -->
                    <div class="col-md-3">
                        <label for="filterUnit" class="form-label small">Unidade</label>
                        <input type="text" class="form-control form-control-sm" id="filterUnit" name="unit_number" placeholder="Ex: 101, A-202">
                    </div>
                    
                    <!-- Horário -->
                    <div class="col-md-3">
                        <label for="filterTime" class="form-label small">Horário</label>
                        <select class="form-select form-select-sm" id="filterTime" name="time_period">
                            <option value="">Todos os horários</option>
                            <option value="morning">Manhã (06:00 - 12:00)</option>
                            <option value="afternoon">Tarde (12:00 - 18:00)</option>
                            <option value="evening">Noite (18:00 - 24:00)</option>
                            <option value="night">Madrugada (00:00 - 06:00)</option>
                        </select>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-sm" onclick="applyFilters()">
                                <i class="bi bi-search"></i> Aplicar Filtros
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
                                <i class="bi bi-x-circle"></i> Limpar Filtros
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="exportToExcel()">
                                <i class="bi bi-file-excel"></i> Exportar Excel
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Ações em Massa -->
    <div id="bulkActions" class="bulk-actions">
        <div class="alert alert-info d-flex justify-content-between align-items-center">
            <div>
                <strong><span id="selectedCount" class="selected-count">0</span> reserva(s) selecionada(s)</strong>
            </div>
            <div class="btn-group">
                <button class="btn btn-success btn-sm" onclick="bulkAction('approve')">
                    <i class="bi bi-check-circle"></i> Aprovar
                </button>
                <button class="btn btn-warning btn-sm" onclick="bulkAction('reject')">
                    <i class="bi bi-x-circle"></i> Rejeitar
                </button>
                <button class="btn btn-danger btn-sm" onclick="bulkAction('cancel')">
                    <i class="bi bi-trash"></i> Cancelar
                </button>
                <button class="btn btn-secondary btn-sm" onclick="clearSelection()">
                    <i class="bi bi-x"></i> Limpar
                </button>
            </div>
        </div>
    </div>

    <!-- Tabela de Reservas -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-calendar3"></i> Todas as Reservas do Condomínio</h5>
            <div>
                <button class="btn btn-light btn-sm" onclick="loadReservations()">
                    <i class="bi bi-arrow-clockwise"></i> Atualizar
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="reservationsTable" class="table table-hover table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>Morador</th>
                            <th>Unidade</th>
                            <th>Espaço</th>
                            <th>Data</th>
                            <th>Horário</th>
                            <th>Status</th>
                            <th>Tipo</th>
                            <th>Observações</th>
                            <th width="120">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="reservationsTableBody">
                        <tr>
                            <td colspan="10" class="loading">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2">Carregando reservas...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Detalhes -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewModalLabel"><i class="bi bi-eye"></i> Detalhes da Reserva</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <!-- Conteúdo será carregado via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editForm">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="editModalLabel"><i class="bi bi-pencil"></i> Editar Reserva</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editReservationId">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editSpace" class="form-label">Espaço *</label>
                            <select class="form-select" id="editSpace" name="space_id" required>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editDate" class="form-label">Data *</label>
                            <input type="date" class="form-control" id="editDate" name="reservation_date" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editStartTime" class="form-label">Horário Início *</label>
                            <input type="time" class="form-control" id="editStartTime" name="start_time" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editEndTime" class="form-label">Horário Término *</label>
                            <input type="time" class="form-control" id="editEndTime" name="end_time" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editStatus" class="form-label">Status *</label>
                            <select class="form-select" id="editStatus" name="status" required>
                                <option value="pending">Pendente</option>
                                <option value="approved">Aprovada</option>
                                <option value="rejected">Rejeitada</option>
                                <option value="cancelled">Cancelada</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editNotes" class="form-label">Observações</label>
                            <textarea class="form-control" id="editNotes" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editAdminReason" class="form-label">Motivo da Alteração</label>
                        <textarea class="form-control" id="editAdminReason" name="admin_reason" rows="2" placeholder="Explique o motivo das alterações (obrigatório se houver mudanças significativas)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Excluir -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="deleteForm">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel"><i class="bi bi-trash"></i> Cancelar Reserva</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="deleteReservationId">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> Tem certeza que deseja cancelar esta reserva? O morador será notificado.
                    </div>
                    <div class="mb-3">
                        <label for="deleteAdminReason" class="form-label">Motivo do Cancelamento *</label>
                        <textarea class="form-control" id="deleteAdminReason" name="admin_reason" rows="3" required placeholder="Explique o motivo do cancelamento (mínimo 10 caracteres)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Confirmar Cancelamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ação em Massa -->
<div class="modal fade" id="bulkActionModal" tabindex="-1" aria-labelledby="bulkActionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="bulkActionForm">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="bulkActionModalLabel"><i class="bi bi-check2-all"></i> Ação em Massa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="bulkActionType">
                    <p id="bulkActionMessage">Tem certeza que deseja realizar esta ação?</p>
                    <div class="mb-3">
                        <label for="bulkAdminReason" class="form-label">Motivo da Ação (Obrigatório)</label>
                        <textarea class="form-control" id="bulkAdminReason" name="admin_reason" rows="3" required placeholder="Explique o motivo da ação..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Confirmar Ação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Definir funções globais primeiro
var updateBulkActions, loadSpaces, loadReservations;

$(document).ready(function() {
    // Atualizar ações em massa
    updateBulkActions = function() {
        var selectedCount = $('.reservation-checkbox:checked').length;
        $('#selectedCount').text(selectedCount);
        
        if (selectedCount > 0) {
            $('#bulkActions').addClass('show');
        } else {
            $('#bulkActions').removeClass('show');
        }
    };

    // Carregar espaços para filtro
    loadSpaces = function() {
        $.get('{{ route("reservations.manage.spaces") }}')
            .done(function(spaces) {
                var spaceSelect = $('#filterSpace, #editSpace');
                $('#filterSpace').empty().append('<option value="">Todos os espaços</option>');
                spaces.forEach(function(space) {
                    $('#filterSpace').append(`<option value="${space.id}">${space.name}</option>`);
                });
            })
            .fail(function() {
                console.error('Erro ao carregar espaços');
            });
    };

    // Carregar reservas
    loadReservations = function() {
        $('#reservationsTableBody').html(`
            <tr>
                <td colspan="10" class="loading">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Carregando reservas...</p>
                </td>
            </tr>
        `);

        // Coletar filtros
        var filters = {
            date_from: $('#filterDateFrom').val(),
            date_to: $('#filterDateTo').val(),
            space_id: $('#filterSpace').val(),
            status: $('#filterStatus').val(),
            type: $('#filterType').val(),
            user_name: $('#filterUser').val(),
            unit_number: $('#filterUnit').val(),
            time_period: $('#filterTime').val()
        };

        $.get("{{ route('reservations.manage') }}", filters)
            .done(function(response) {
                var html = '';
                if (response.data && response.data.length > 0) {
                    response.data.forEach(function(reservation) {
                        html += `
                            <tr>
                                <td>${reservation.checkbox}</td>
                                <td>${reservation.user_name}</td>
                                <td>${reservation.unit_info}</td>
                                <td>${reservation.space_name}</td>
                                <td>${reservation.formatted_date}</td>
                                <td>${reservation.formatted_time}</td>
                                <td>${reservation.status_badge}</td>
                                <td>${reservation.is_recurring}</td>
                                <td>${reservation.notes}</td>
                                <td>${reservation.actions}</td>
                            </tr>
                        `;
                    });
                } else {
                    html = `
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="bi bi-inbox display-4 text-muted"></i>
                                <p class="text-muted mt-2">Nenhuma reserva encontrada</p>
                            </td>
                        </tr>
                    `;
                }
                $('#reservationsTableBody').html(html);
            })
            .fail(function() {
                $('#reservationsTableBody').html(`
                    <tr>
                        <td colspan="10" class="text-center py-4 text-danger">
                            <i class="bi bi-exclamation-triangle display-4"></i>
                            <p class="mt-2">Erro ao carregar reservas</p>
                        </td>
                    </tr>
                `);
            });
    };

    // Inicializar - carregar dados
    loadSpaces();
    loadReservations();

    // Controlar seleção de todas as linhas
    $('#selectAll').on('change', function() {
        $('.reservation-checkbox').prop('checked', this.checked);
        updateBulkActions();
    });

    // Controlar seleção individual
    $(document).on('change', '.reservation-checkbox', function() {
        updateBulkActions();
        
        var totalCheckboxes = $('.reservation-checkbox').length;
        var checkedCheckboxes = $('.reservation-checkbox:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes && totalCheckboxes > 0);
    });

    // Limpar seleção
    window.clearSelection = function() {
        $('.reservation-checkbox').prop('checked', false);
        $('#selectAll').prop('checked', false);
        updateBulkActions();
    };

    // Ação em massa
    window.bulkAction = function(action) {
        var selectedIds = [];
        $('.reservation-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            alert('Selecione pelo menos uma reserva.');
            return;
        }

        $('#bulkActionType').val(action);
        
        var actionText = {
            'approve': 'aprovar',
            'reject': 'rejeitar',
            'cancel': 'cancelar'
        };
        
        $('#bulkActionMessage').text(`Tem certeza que deseja ${actionText[action]} ${selectedIds.length} reserva(s) selecionada(s)?`);
        $('#bulkAdminReason').val('');
        
        var modal = new bootstrap.Modal(document.getElementById('bulkActionModal'));
        modal.show();
    };

    // Submeter ação em massa
    $('#bulkActionForm').on('submit', function(e) {
        e.preventDefault();
        
        var selectedIds = [];
        $('.reservation-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        $.ajax({
            url: "{{ route('reservations.manage.bulk-action') }}",
            type: 'POST',
            data: {
                action: $('#bulkActionType').val(),
                reservation_ids: selectedIds,
                admin_reason: $('#bulkAdminReason').val(),
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                alert('✅ ' + response.message);
                loadReservations();
                clearSelection();
                bootstrap.Modal.getInstance(document.getElementById('bulkActionModal')).hide();
            },
            error: function(xhr) {
                var errors = xhr.responseJSON?.errors;
                if (errors) {
                    alert('❌ Erro: ' + Object.values(errors).flat().join('\n'));
                } else {
                    alert('❌ Erro ao realizar ação em massa.');
                }
            }
        });
    });

    // Visualizar reserva
    window.viewReservation = function(id) {
        $.get(`/reservations/manage/${id}`)
            .done(function(data) {
                var html = `
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Morador:</strong> ${data.user ? data.user.name : 'N/A'}</p>
                            <p><strong>Unidade:</strong> ${data.unit ? data.unit.number + ' - ' + data.unit.block : 'N/A'}</p>
                            <p><strong>Espaço:</strong> ${data.space ? data.space.name : 'N/A'}</p>
                            <p><strong>Data:</strong> ${new Date(data.reservation_date).toLocaleDateString('pt-BR')}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Horário:</strong> ${data.start_time.substring(0, 5)} - ${data.end_time.substring(0, 5)}</p>
                            <p><strong>Status:</strong> <span class="badge bg-${getStatusColor(data.status)}">${getStatusText(data.status)}</span></p>
                            <p><strong>Tipo:</strong> ${data.recurring_reservation_id ? '<span class="badge bg-info">Recorrente</span>' : '<span class="badge bg-light text-dark">Individual</span>'}</p>
                            <p><strong>Criada em:</strong> ${new Date(data.created_at).toLocaleDateString('pt-BR')}</p>
                        </div>
                    </div>
                `;
                
                if (data.notes) {
                    html += `<div class="mt-3"><strong>Observações:</strong><br><p class="text-muted">${data.notes}</p></div>`;
                }
                
                if (data.admin_action) {
                    html += `<div class="mt-3 alert alert-info"><strong>Ação Administrativa:</strong> ${getActionText(data.admin_action)}<br><strong>Motivo:</strong> ${data.admin_reason || 'N/A'}</div>`;
                }
                
                $('#viewModalBody').html(html);
                var modal = new bootstrap.Modal(document.getElementById('viewModal'));
                modal.show();
            })
            .fail(function() {
                alert('❌ Erro ao carregar detalhes da reserva.');
            });
    };

    // Editar reserva
    window.editReservation = function(id) {
        $.get(`/reservations/manage/${id}/edit`)
            .done(function(response) {
                var data = response.reservation;
                var spaces = response.spaces;
                
                $('#editReservationId').val(data.id);
                
                // Formatar a data corretamente para o input tipo date (YYYY-MM-DD)
                var reservationDate = data.reservation_date;
                if (reservationDate) {
                    // Se vier como objeto Date ou string ISO, extrair apenas a parte da data
                    reservationDate = reservationDate.split('T')[0];
                }
                $('#editDate').val(reservationDate);
                
                $('#editStartTime').val(data.start_time.substring(0, 5));
                $('#editEndTime').val(data.end_time.substring(0, 5));
                $('#editStatus').val(data.status);
                $('#editNotes').val(data.notes);
                $('#editAdminReason').val('');
                
                // Carregar espaços
                var spaceSelect = $('#editSpace');
                spaceSelect.empty();
                spaces.forEach(function(space) {
                    spaceSelect.append(`<option value="${space.id}" ${space.id == data.space_id ? 'selected' : ''}>${space.name}</option>`);
                });
                
                var modal = new bootstrap.Modal(document.getElementById('editModal'));
                modal.show();
            })
            .fail(function() {
                alert('❌ Erro ao carregar dados da reserva.');
            });
    };

    // Submeter edição
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        
        var id = $('#editReservationId').val();
        var formData = {
            space_id: $('#editSpace').val(),
            reservation_date: $('#editDate').val(),
            start_time: $('#editStartTime').val(),
            end_time: $('#editEndTime').val(),
            status: $('#editStatus').val(),
            notes: $('#editNotes').val(),
            admin_reason: $('#editAdminReason').val(),
            _token: '{{ csrf_token() }}'
        };
        
        $.ajax({
            url: `/reservations/manage/${id}`,
            type: 'PUT',
            data: formData,
            success: function(response) {
                alert('✅ ' + response.message);
                loadReservations();
                bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
            },
            error: function(xhr) {
                var errors = xhr.responseJSON?.errors;
                if (errors) {
                    alert('❌ Erro: ' + Object.values(errors).flat().join('\n'));
                } else {
                    alert('❌ Erro ao atualizar reserva.');
                }
            }
        });
    });

    // Excluir reserva
    window.deleteReservation = function(id) {
        $('#deleteReservationId').val(id);
        $('#deleteAdminReason').val('');
        var modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    };

    // Submeter exclusão
    $('#deleteForm').on('submit', function(e) {
        e.preventDefault();
        
        var id = $('#deleteReservationId').val();
        
        $.ajax({
            url: `/reservations/manage/${id}`,
            type: 'DELETE',
            data: {
                admin_reason: $('#deleteAdminReason').val(),
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                alert('✅ ' + response.message);
                loadReservations();
                bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
            },
            error: function(xhr) {
                var errors = xhr.responseJSON?.errors;
                if (errors) {
                    alert('❌ Erro: ' + Object.values(errors).flat().join('\n'));
                } else {
                    alert('❌ Erro ao cancelar reserva.');
                }
            }
        });
    });

    // Aplicar filtros (expor globalmente)
    window.applyFilters = function() {
        loadReservations();
    };

    // Limpar filtros (expor globalmente)
    window.clearFilters = function() {
        $('#filtersForm')[0].reset();
        loadReservations();
    };

    // Expor funções globalmente para uso em outros contextos
    window.loadReservations = loadReservations;
    window.loadSpaces = loadSpaces;
    window.updateBulkActions = updateBulkActions;

    // Exportar para Excel
    window.exportToExcel = function() {
        var table = document.getElementById('reservationsTable');
        var rows = [];
        
        // Cabeçalho
        var header = [];
        for (var i = 1; i < table.rows[0].cells.length; i++) {
            header.push(table.rows[0].cells[i].innerText);
        }
        rows.push(header.join('\t'));
        
        // Dados
        for (var i = 1; i < table.rows.length; i++) {
            var row = [];
            for (var j = 1; j < table.rows[i].cells.length; j++) {
                var cellText = table.rows[i].cells[j].innerText.replace(/\n/g, ' ').replace(/\t/g, ' ');
                row.push(cellText);
            }
            rows.push(row.join('\t'));
        }
        
        var csvContent = rows.join('\n');
        var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'reservas_' + new Date().toISOString().split('T')[0] + '.csv';
        link.click();
    };

    // Funções auxiliares
    function getStatusColor(status) {
        switch(status) {
            case 'approved': return 'success';
            case 'pending': return 'warning';
            case 'rejected': return 'danger';
            case 'cancelled': return 'secondary';
            default: return 'secondary';
        }
    }

    function getStatusText(status) {
        switch(status) {
            case 'approved': return 'Aprovada';
            case 'pending': return 'Pendente';
            case 'rejected': return 'Rejeitada';
            case 'cancelled': return 'Cancelada';
            default: return status;
        }
    }

    function getActionText(action) {
        switch(action) {
            case 'edited': return 'Editada';
            case 'cancelled': return 'Cancelada';
            case 'approved': return 'Aprovada';
            case 'rejected': return 'Rejeitada';
            default: return action;
        }
    }
});
</script>
@endpush
