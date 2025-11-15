@extends('layouts.app')

@section('title', 'Gestão de Taxas')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0">Gestão Financeira - Taxas do Condomínio</h2>
                <p class="text-muted mb-0">Configure e monitore todas as taxas recorrentes e avulsas das unidades</p>
            </div>
            @can('manage_charges')
                <a href="{{ route('fees.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Nova Taxa
                </a>
            @endcan
        </div>
    </div>
</div>

<!-- Cards de Resumo -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <span class="text-muted small">Taxas Ativas</span>
                <h3 class="mb-0 mt-2 text-success">{{ $summary['active'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <span class="text-muted small">Taxas Inativas</span>
                <h3 class="mb-0 mt-2 text-secondary">{{ $summary['inactive'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <span class="text-muted small">Unidades Vinculadas</span>
                <h3 class="mb-0 mt-2">{{ $summary['total_configurations'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <span class="text-muted small">Cobranças Pendentes / Vencidas</span>
                <h3 class="mb-0 mt-2">
                    <span class="text-warning">{{ $summary['pending_charges'] }}</span>
                    <span class="text-muted">/</span>
                    <span class="text-danger">{{ $summary['overdue_charges'] }}</span>
                </h3>
            </div>
        </div>
    </div>
</div>

<!-- Filtros e Exportação -->
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-12">
                <label class="form-label fw-semibold">Filtros Rápidos:</label>
                <div class="d-flex flex-wrap gap-2" id="filterControls">
                    <select class="form-select form-select-sm" id="filterType" style="width: auto; min-width: 180px;">
                        <option value="">Todos os Tipos</option>
                    </select>
                    <select class="form-select form-select-sm" id="filterStatus" style="width: auto; min-width: 150px;">
                        <option value="">Todos os Status</option>
                    </select>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
                        <i class="bi bi-x-circle"></i> Limpar Filtros
                    </button>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label class="form-label fw-semibold">Exportar Dados:</label>
                <div class="btn-group flex-wrap gap-2" id="exportButtons">
                    <!-- Botões serão adicionados via JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabela de Taxas -->
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table id="feesTable" class="table table-hover table-striped align-middle" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th style="width: 30%;">Nome da Taxa</th>
                        <th style="width: 15%;">Tipo</th>
                        <th style="width: 12%;" class="text-end">Valor Base</th>
                        <th style="width: 15%;" class="text-center">Unidades</th>
                        <th style="width: 13%;" class="text-center">Status</th>
                        <th style="width: 15%;" class="text-center no-export">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fees as $fee)
                    <tr>
                        <td>
                            <strong>{{ $fee->name }}</strong>
                            @if($fee->description)
                                <br><small class="text-muted">{{ Str::limit($fee->description, 50) }}</small>
                            @endif
                        </td>
                        <td>
                            @switch($fee->billing_type)
                                @case('condominium_fee')
                                    <span class="badge bg-primary">Taxa Condominial</span>
                                    @break
                                @case('fine')
                                    <span class="badge bg-danger">Multa</span>
                                    @break
                                @case('extra')
                                    <span class="badge bg-warning text-dark">Taxa Extra</span>
                                    @break
                                @case('reservation')
                                    <span class="badge bg-info">Reserva de Espaço</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ $fee->billing_type }}</span>
                            @endswitch
                            @if($fee->recurrence)
                                <br><small class="text-muted">
                                    @switch($fee->recurrence)
                                        @case('monthly') <i class="bi bi-calendar-month"></i> Mensal @break
                                        @case('quarterly') <i class="bi bi-calendar3"></i> Trimestral @break
                                        @case('yearly') <i class="bi bi-calendar4"></i> Anual @break
                                        @case('one_time') <i class="bi bi-calendar-x"></i> Única @break
                                        @case('custom') <i class="bi bi-calendar-check"></i> Personalizada @break
                                        @default {{ $fee->recurrence }}
                                    @endswitch
                                </small>
                            @endif
                        </td>
                        <td class="text-end">
                            <strong class="text-success fs-6">R$ {{ number_format($fee->amount, 2, ',', '.') }}</strong>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info fs-6">{{ $fee->configurations_count }}</span> unidades
                            @if($fee->pending_charges_count > 0 || $fee->overdue_charges_count > 0)
                                <br><small class="text-muted d-block mt-1">
                                    <span class="badge bg-warning text-dark">{{ $fee->pending_charges_count }}</span> pendente(s)
                                    @if($fee->overdue_charges_count > 0)
                                        / <span class="badge bg-danger">{{ $fee->overdue_charges_count }}</span> vencida(s)
                                    @endif
                                </small>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($fee->isInvalidated())
                                <span class="badge bg-danger">Invalidada</span>
                                <br><small class="text-muted d-block mt-1"><i class="bi bi-x-circle"></i> Não editável</small>
                            @elseif($fee->active)
                                <span class="badge bg-success">Ativa</span>
                                @if($fee->hasPaidCharges())
                                    <br><small class="text-warning d-block mt-1"><i class="bi bi-lock"></i> Bloqueada</small>
                                @endif
                            @else
                                <span class="badge bg-secondary">Inativa</span>
                            @endif
                            @if($fee->auto_generate_charges && !$fee->isInvalidated())
                                <br><small class="text-muted d-block mt-1"><i class="bi bi-arrow-repeat"></i> Auto-geração</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('fees.show', $fee) }}" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Ver detalhes">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('manage_charges')
                                    @if($fee->canBeModified())
                                        <a href="{{ route('fees.edit', $fee) }}" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Editar taxa">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-outline-secondary" disabled data-bs-toggle="tooltip" title="Não editável - possui cobranças pagas">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endif
                                    @if($fee->recurrence === 'monthly' && $fee->canBeModified())
                                        <form action="{{ route('fees.clone', $fee) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja clonar esta taxa para o próximo mês?');">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" data-bs-toggle="tooltip" title="Clonar para próximo mês">
                                                <i class="bi bi-files"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-cash-stack display-1 text-muted d-block mb-3"></i>
                            <h5 class="text-muted">Nenhuma taxa cadastrada</h5>
                            <p class="text-muted">Crie a primeira taxa para começar a administrar o financeiro do condomínio.</p>
                            @can('manage_charges')
                                <a href="{{ route('fees.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Criar primeira taxa
                                </a>
                            @endcan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

<script>
$(document).ready(function() {
    const languageUrl = 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json';

    const table = $('#feesTable').DataTable({
        language: {
            url: languageUrl
        },
        order: [[0, 'asc']], // Ordenar por Nome da Taxa
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
        responsive: true,
        dom: '<"row mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: 'Taxas do Condomínio - ' + new Date().toLocaleDateString('pt-BR'),
                exportOptions: {
                    columns: ':not(.no-export)',
                    format: {
                        body: function(data, row, column, node) {
                            const $node = $(node);
                            
                            // Coluna de Valor (2)
                            if (column === 2) {
                                return $node.find('strong').text().trim() || data.replace(/[^\d,.-]/g, '');
                            }
                            
                            // Colunas com badges (1, 3, 4)
                            if (column === 1 || column === 3 || column === 4) {
                                const badgeText = $node.find('.badge').first().text().trim();
                                return badgeText || $node.text().trim() || data;
                            }
                            
                            // Outras colunas - remover HTML
                            return $node.text().trim() || data.replace(/<[^>]*>/g, '').trim();
                        }
                    }
                },
                customize: function(xlsx) {
                    const sheet = xlsx.xl.worksheets['sheet1.xml'];
                    $('row c', sheet).attr('s', '2');
                }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm',
                title: 'Taxas do Condomínio - ' + new Date().toLocaleDateString('pt-BR'),
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: {
                    columns: ':not(.no-export)',
                    format: {
                        body: function(data, row, column, node) {
                            const $node = $(node);
                            
                            if (column === 2) {
                                return $node.find('strong').text().trim() || data.replace(/[^\d,.-]/g, '');
                            }
                            
                            if (column === 1 || column === 3 || column === 4) {
                                return $node.find('.badge').first().text().trim() || $node.text().trim() || data;
                            }
                            
                            return $node.text().trim() || data.replace(/<[^>]*>/g, '').trim();
                        }
                    }
                },
                customize: function(doc) {
                    doc.defaultStyle.fontSize = 8;
                    doc.styles.tableHeader.fontSize = 9;
                    doc.styles.tableHeader.fontWeight = 'bold';
                    doc.pageMargins = [10, 10, 10, 10];
                }
            },
            {
                extend: 'csvHtml5',
                text: '<i class="bi bi-filetype-csv"></i> CSV',
                className: 'btn btn-info btn-sm',
                title: 'Taxas do Condomínio - ' + new Date().toLocaleDateString('pt-BR'),
                exportOptions: {
                    columns: ':not(.no-export)',
                    format: {
                        body: function(data, row, column, node) {
                            const $node = $(node);
                            
                            if (column === 2) {
                                const value = $node.find('strong').text().trim() || data.replace(/[^\d,.-]/g, '');
                                return value.replace(',', '.');
                            }
                            
                            if (column === 1 || column === 3 || column === 4) {
                                return $node.find('.badge').first().text().trim() || $node.text().trim() || data;
                            }
                            
                            return $node.text().trim() || data.replace(/<[^>]*>/g, '').trim();
                        }
                    }
                }
            },
            {
                extend: 'print',
                text: '<i class="bi bi-printer"></i> Imprimir',
                className: 'btn btn-secondary btn-sm',
                title: 'Taxas do Condomínio - ' + new Date().toLocaleDateString('pt-BR'),
                exportOptions: {
                    columns: ':not(.no-export)',
                    stripHtml: false
                },
                customize: function(win) {
                    $(win.document.body).find('table').addClass('table table-bordered');
                    $(win.document.body).find('h1').css('text-align', 'center');
                    $(win.document.body).find('h1').css('margin-bottom', '20px');
                }
            },
            {
                text: '<i class="bi bi-arrow-clockwise"></i> Atualizar',
                className: 'btn btn-outline-primary btn-sm',
                action: function(e, dt, node, config) {
                    location.reload();
                }
            }
        ],
        columnDefs: [
            {
                targets: [2], // Coluna de Valor
                type: 'num',
                render: function(data, type, row) {
                    if (type === 'sort' || type === 'type') {
                        // Para ordenação, extrair apenas o número
                        return parseFloat(data.replace(/[^\d,.-]/g, '').replace(',', '.'));
                    }
                    return data;
                }
            },
            {
                targets: 'no-export',
                orderable: false,
                searchable: false
            }
        ],
        initComplete: function() {
            // Mover botões de exportação para o container acima da tabela
            const buttonsContainer = $('#exportButtons');
            if (buttonsContainer.length) {
                $('.dt-buttons').detach().appendTo(buttonsContainer);
            }

            // Popular filtros de Tipo
            const tipoColumn = this.api().column(1);
            const tipoSelect = $('#filterType');
            tipoColumn.data().unique().each(function(d) {
                const $d = $(d);
                const text = $d.find('.badge').first().text().trim();
                if (text && !tipoSelect.find('option[value="' + text + '"]').length) {
                    tipoSelect.append('<option value="' + text + '">' + text + '</option>');
                }
            });

            tipoSelect.on('change', function() {
                const val = $(this).val();
                tipoColumn.search(val ? val : '', true, false).draw();
            });

            // Popular filtros de Status
            const statusColumn = this.api().column(4);
            const statusSelect = $('#filterStatus');
            statusColumn.data().unique().each(function(d) {
                const $d = $(d);
                const text = $d.find('.badge').first().text().trim();
                if (text && !statusSelect.find('option[value="' + text + '"]').length) {
                    statusSelect.append('<option value="' + text + '">' + text + '</option>');
                }
            });

            statusSelect.on('change', function() {
                const val = $(this).val();
                statusColumn.search(val ? val : '', true, false).draw();
            });
        }
    });

    // Melhorar responsividade dos filtros
    $('.dataTables_filter input').addClass('form-control form-control-sm');
    $('.dataTables_length select').addClass('form-select form-select-sm');
    
    // Adicionar tooltips aos botões de ação
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Melhorar estilo da tabela
    $('#feesTable').on('draw.dt', function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
});

// Função para limpar filtros
function clearFilters() {
    $('#filterType').val('').trigger('change');
    $('#filterStatus').val('').trigger('change');
    $('#feesTable').DataTable().search('').draw();
}
</script>
@endpush

