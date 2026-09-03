@php
    use Illuminate\Support\Arr;

    $recurrenceOptions = [
        'monthly' => 'Mensal',
        'quarterly' => 'Trimestral',
        'yearly' => 'Anual',
        'one_time' => 'Pontual',
        'custom' => 'Customizada',
    ];

    $billingTypeOptions = [
        'condominium_fee' => 'Taxa Condominial',
        'fine' => 'Multa',
        'extra' => 'Taxa Extra',
        'reservation' => 'Reserva de Espaço',
    ];

    $existingConfigurations = isset($fee) && $fee->relationLoaded('configurations')
        ? $fee->configurations->map(fn($config) => [
            'id' => $config->id,
            'unit_id' => $config->unit_id,
            'payment_channel' => $config->payment_channel,
            'custom_amount' => $config->custom_amount,
            'starts_at' => optional($config->starts_at)->format('Y-m-d'),
            'ends_at' => optional($config->ends_at)->format('Y-m-d'),
            'notes' => $config->notes,
        ])->keyBy('unit_id')->toArray()
        : [];

    $unitConfigurations = collect(old('unit_configurations', $existingConfigurations));

    $selectedRecurrence = old('recurrence', $fee->recurrence);
    $customScheduleVisible = $selectedRecurrence === 'custom';

    $customScheduleText = old(
        'custom_schedule_text',
        collect(old('custom_schedule', $fee->custom_schedule ?? []))->implode(PHP_EOL)
    );

    $wizardMode = $wizardMode ?? false;
    $recurrenceLabels = $recurrenceOptions;
    $unitModelOptions = $unitModelOptions ?? \App\Support\UnitModels::labels();
    $selectedUnitModels = old('unit_models', $fee->unit_models ?? []);
@endphp

@once
    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
        <style>
            .fee-units-table-container {
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                border-radius: .375rem;
                border: 1px solid #dee2e6;
                background: #fff;
            }
            .fee-units-table-container .dataTables_wrapper {
                width: 100%;
                padding: .75rem;
            }
            .fee-units-table-container table.dataTable {
                width: 100% !important;
                margin: 0 !important;
            }
            #units-table { min-width: 1080px; }
            #units-table th, #units-table td { vertical-align: middle; }
            #units-table .unit-col-payment { min-width: 130px; }
            #units-table .unit-col-amount { min-width: 140px; }
            #units-table .unit-col-dates { min-width: 150px; }
            #units-table .unit-col-notes { min-width: 160px; max-width: 220px; }
            #units-table .unit-col-unit { min-width: 200px; }
            .fee-preview-card .preview-value { font-size: 1.35rem; font-weight: 700; }
            .fee-hint { font-size: .85rem; color: #6c757d; }
        </style>
    @endpush
@endonce

@once
    @push('scripts')
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
        @include('partials.datatables-helper')
    @endpush
@endonce

@php
    $totalUnits = $units->count();
    $autoEligibleUnitsCount = $autoEligibleUnitsCount ?? $units->filter(fn ($u) => $u->isEligibleForAutomaticFee())->count();
    $defaultApplyAll = old('apply_all_units', ($wizardMode ?? false) ? '1' : (empty($existingConfigurations) ? '1' : '0'));
@endphp

<input type="hidden" name="apply_all_units" id="apply_all_units" value="{{ $defaultApplyAll }}">
<span id="fee-units-auto-total" data-total="{{ $autoEligibleUnitsCount }}" class="d-none"></span>
<span id="fee-units-all-total" data-total="{{ $totalUnits }}" class="d-none"></span>

@if($wizardMode)
<div class="fee-wizard-pane active" data-pane="1">
@endif

<div class="alert alert-info border-0 bg-info bg-opacity-10 mb-4">
    <i class="bi bi-info-circle me-1"></i>
    <strong>Como funciona:</strong> no modo padrão, a taxa é aplicada automaticamente apenas às unidades <strong>habitadas com morador vinculado</strong>.
    Para incluir unidades vazias ou com outro status, use <strong>Mostrar unidades</strong> e selecione manualmente.
</div>

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nome da Taxa *</label>
        <input type="text"
               name="name"
               class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $fee->name) }}"
               required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Conta Bancária Recebedora</label>
        <select name="bank_account_id" class="form-select @error('bank_account_id') is-invalid @enderror">
            <option value="">Selecionar conta</option>
            @foreach($bankAccounts as $bankAccount)
                <option value="{{ $bankAccount->id }}"
                    {{ (string) old('bank_account_id', $fee->bank_account_id) === (string) $bankAccount->id ? 'selected' : '' }}>
                    {{ $bankAccount->name }} @if($bankAccount->bank_name) - {{ $bankAccount->bank_name }}@endif
                </option>
            @endforeach
        </select>
        @error('bank_account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Valor base (R$) *</label>
        <div class="input-group">
            <span class="input-group-text">R$</span>
            <input type="number"
                   step="0.01"
                   min="0"
                   class="form-control @error('amount') is-invalid @enderror"
                   name="amount"
                   value="{{ old('amount', $fee->amount) }}"
                   required>
        </div>
        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Recorrência *</label>
        <select name="recurrence" id="recurrence"
                class="form-select @error('recurrence') is-invalid @enderror" required>
            @foreach($recurrenceOptions as $value => $label)
                <option value="{{ $value }}"
                    {{ old('recurrence', $fee->recurrence) === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('recurrence')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Tipo de Cobrança *</label>
        <select name="billing_type"
                class="form-select @error('billing_type') is-invalid @enderror"
                required>
            @foreach($billingTypeOptions as $value => $label)
                <option value="{{ $value }}"
                    {{ old('billing_type', $fee->billing_type) === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('billing_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">Modelos de unidade</label>
        <div class="d-flex flex-wrap gap-3" id="fee-unit-models">
            @foreach($unitModelOptions as $value => $label)
                <div class="form-check">
                    <input class="form-check-input fee-unit-model-checkbox"
                           type="checkbox"
                           name="unit_models[]"
                           id="unit_model_{{ $value }}"
                           value="{{ $value }}"
                           {{ in_array($value, $selectedUnitModels ?? [], true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="unit_model_{{ $value }}">{{ $label }}</label>
                </div>
            @endforeach
        </div>
        <small class="text-muted d-block mt-1">
            Deixe em branco para aplicar a todos os modelos. Selecione um ou mais para restringir a taxa.
        </small>
        @error('unit_models')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3 recurrence-dependent">
        <label class="form-label fw-semibold">Dia do vencimento</label>
        <input type="number"
               min="1"
               max="31"
               name="due_day"
               class="form-control @error('due_day') is-invalid @enderror"
               value="{{ old('due_day', $fee->due_day) }}">
        <small class="text-muted">Use 1-28 para evitar meses curtos</small>
        @error('due_day')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3 recurrence-dependent">
        <label class="form-label fw-semibold">Ajuste de vencimento (dias)</label>
        <input type="number"
               min="0"
               max="365"
               name="due_offset_days"
               id="due_offset_days"
               class="form-control @error('due_offset_days') is-invalid @enderror"
               value="{{ old('due_offset_days', $fee->due_offset_days) }}">
        <small class="text-muted">Antecipa o vencimento em X dias (opcional)</small>
        @error('due_offset_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Início da vigência</label>
        <input type="date"
               name="starts_at"
               class="form-control @error('starts_at') is-invalid @enderror"
               value="{{ old('starts_at', optional($fee->starts_at)->format('Y-m-d')) }}">
        @error('starts_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Término da vigência</label>
        <input type="date"
               name="ends_at"
               class="form-control @error('ends_at') is-invalid @enderror"
               value="{{ old('ends_at', optional($fee->ends_at)->format('Y-m-d')) }}">
        @error('ends_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12" id="custom-schedule-section" style="{{ $customScheduleVisible ? '' : 'display:none;' }}">
        <label class="form-label fw-semibold">Datas personalizadas</label>
        <textarea name="custom_schedule_text"
                  id="custom_schedule_text"
                  class="form-control @error('custom_schedule') is-invalid @enderror"
                  rows="3"
                  placeholder="Informe uma data por linha (formato AAAA-MM-DD)"
                  {{ $customScheduleVisible ? '' : 'disabled' }}>{{ $customScheduleText }}</textarea>
        <small class="text-muted">Utilize este campo apenas para recorrência customizada.</small>
        @error('custom_schedule')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">Descrição / Observações</label>
        <textarea name="description"
                  rows="3"
                  class="form-control @error('description') is-invalid @enderror"
                  placeholder="Detalhes da taxa, regras de cobrança, etc.">{{ old('description', $fee->description) }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <div class="card bg-light border-0">
            <div class="card-body">
                <h6 class="fw-semibold mb-3"><i class="bi bi-lightning-charge me-1"></i> Geração de cobranças</h6>
                <div class="row g-3">
                    @if(!($fee->exists ?? false))
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input type="checkbox"
                                   name="generate_charges_now"
                                   id="generate_charges_now"
                                   class="form-check-input"
                                   value="1"
                                   {{ old('generate_charges_now', true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="generate_charges_now">
                                Gerar cobranças do próximo período agora
                            </label>
                            <div class="fee-hint">Cria as cobranças imediatamente ao salvar a taxa.</div>
                        </div>
                    </div>
                    @endif
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input type="checkbox"
                                   name="auto_generate_charges"
                                   id="auto_generate_charges"
                                   class="form-check-input"
                                   value="1"
                                   {{ old('auto_generate_charges', $fee->auto_generate_charges) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="auto_generate_charges">
                                Manter geração automática nos próximos períodos
                            </label>
                            <div class="fee-hint">Permite gerar novos períodos manualmente ou em rotinas futuras.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input type="checkbox"
                                   name="active"
                                   id="active"
                                   class="form-check-input"
                                   value="1"
                                   {{ old('active', $fee->active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="active">Taxa ativa</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($wizardMode)
</div>
<div class="fee-wizard-pane" data-pane="2">
@endif

@error('unit_configurations')
    <div class="alert alert-danger">{{ $message }}</div>
@enderror

<div id="units-simple-panel" class="mb-4">
    <div class="card border-success border-opacity-50 bg-success bg-opacity-10">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <h5 class="mb-2"><i class="bi bi-buildings me-1"></i> Unidades habitadas com morador</h5>
                    <p class="text-muted mb-2">
                        Esta taxa será aplicada automaticamente às <strong id="units-total-label">{{ $autoEligibleUnitsCount }}</strong> unidades elegíveis
                        (habitadas com morador ativo<span id="fee-models-filter-hint">{{ empty($selectedUnitModels) ? '' : ' nos modelos selecionados' }}</span>), usando a forma de pagamento padrão de cada uma (folha ou sistema).
                        @if($totalUnits > $autoEligibleUnitsCount)
                            <span class="d-block mt-1 small">
                                O condomínio possui {{ $totalUnits }} unidades no total; as demais podem ser incluídas manualmente.
                            </span>
                        @endif
                    </p>
                    <span class="badge bg-success" id="apply-all-status-badge">
                        <i class="bi bi-check-circle me-1"></i> Modo padrão: automação (habitadas + morador)
                    </span>
                </div>
                <button type="button" class="btn btn-outline-primary" id="btn-show-units">
                    <i class="bi bi-list-ul"></i> Mostrar unidades
                </button>
            </div>
        </div>
    </div>
</div>

<div id="units-custom-panel" class="d-none mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h5 class="mb-1">Personalizar unidades</h5>
            <p class="text-muted small mb-0">
                Por padrão, todas as unidades habitadas com morador ativo recebem a taxa.
                Marque unidades adicionais (vazias ou com outro status) ou ajuste valor, pagamento e vigência por unidade.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-use-all-units">
                <i class="bi bi-arrow-counterclockwise"></i> Voltar ao resumo
            </button>
            <div class="form-check form-switch mb-0 ms-1">
                <input class="form-check-input" type="checkbox" id="only_selected_units" value="1">
                <label class="form-check-label small" for="only_selected_units">Somente unidades marcadas</label>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="select-all-units">Selecionar todas</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-all-units">Limpar seleção</button>
            <span class="badge bg-primary align-self-center" id="selected-units-count">0 selecionadas</span>
        </div>
    </div>

    <div class="input-group input-group-sm mb-3" style="max-width: 360px;">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" id="unit-filter" class="form-control" placeholder="Filtrar por bloco, número ou morador">
    </div>

    <div class="mb-3" style="max-width: 360px;">
        <select id="unit-model-filter" class="form-select form-select-sm">
            <option value="">Todos os modelos</option>
            @foreach($unitModelOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="fee-units-table-container">
        <table id="units-table" class="table table-hover mb-0 align-middle w-100">
        <thead class="table-light">
            <tr>
                <th class="text-center" style="width: 44px;"></th>
                <th class="unit-col-unit">Unidade</th>
                <th class="unit-col-payment">Pagamento</th>
                <th class="unit-col-amount">Valor</th>
                <th class="unit-col-dates">Vigência</th>
                <th class="unit-col-notes">Notas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($units as $unit)
                @php
                    $config = $unitConfigurations->get($unit->id, []);
                    $isSelected = !empty($config);
                    $paymentChannel = Arr::get($config, 'payment_channel', $unit->default_payment_channel ?? 'system');
                    $autoEligible = $unit->isEligibleForAutomaticFee();
                    $searchBlob = strtolower(trim(($unit->full_identifier ?? '') . ' ' . (optional($unit->morador)->name ?? '') . ' ' . $unit->situacao_label));
                @endphp
                <tr class="unit-row {{ $autoEligible ? '' : 'table-light' }}"
                    data-search="{{ $searchBlob }}"
                    data-unit-id="{{ $unit->id }}"
                    data-unit-model="{{ $unit->unit_model }}"
                    data-auto-eligible="{{ $autoEligible ? '1' : '0' }}">
                    <td>
                        <div class="form-check">
                            <input class="form-check-input unit-toggle"
                                   type="checkbox"
                                   value="1"
                                   data-target="unit-config-{{ $unit->id }}"
                                   data-unit-id="{{ $unit->id }}"
                                   {{ $isSelected ? 'checked' : '' }}>
                            <input type="hidden"
                                   name="unit_configurations[{{ $unit->id }}][unit_id]"
                                   value="{{ $unit->id }}"
                                   class="unit-config-{{ $unit->id }} unit-config-input"
                                   data-unit-id="{{ $unit->id }}"
                                   {{ $isSelected ? '' : 'disabled' }}>
                            @if(isset($config['id']))
                                <input type="hidden"
                                       name="unit_configurations[{{ $unit->id }}][id]"
                                       value="{{ $config['id'] }}"
                                       class="unit-config-{{ $unit->id }} unit-config-input"
                                       data-unit-id="{{ $unit->id }}"
                                       {{ $isSelected ? '' : 'disabled' }}>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $unit->full_identifier }}</div>
                        <div class="d-flex flex-wrap gap-1 mt-1 mb-1">
                            <span class="badge bg-secondary">{{ $unit->unit_model_label }}</span>
                            <span class="badge {{ $unit->situacao === 'habitado' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $unit->situacao_label }}
                            </span>
                            @if($autoEligible)
                                <span class="badge bg-primary">Automação</span>
                            @else
                                <span class="badge bg-warning text-dark">Manual</span>
                            @endif
                        </div>
                        <small class="text-muted d-block">
                            Morador atual: {{ optional($unit->morador)->name ?? 'Não cadastrado' }}
                        </small>
                        @if($unit->default_payment_channel)
                            <small class="text-muted">Preferência padrão: {{ strtoupper($unit->default_payment_channel) }}</small>
                        @endif
                    </td>
                    <td class="unit-col-payment">
                        <select name="unit_configurations[{{ $unit->id }}][payment_channel]"
                                class="form-select form-select-sm unit-config-{{ $unit->id }} unit-config-input"
                                data-unit-id="{{ $unit->id }}"
                                {{ $isSelected ? '' : 'disabled' }}>
                            <option value="payroll" {{ $paymentChannel === 'payroll' ? 'selected' : '' }}>Folha</option>
                            <option value="system" {{ $paymentChannel === 'system' ? 'selected' : '' }}>Sistema</option>
                        </select>
                    </td>
                    <td class="unit-col-amount">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">R$</span>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control unit-config-{{ $unit->id }} unit-config-input"
                                   data-unit-id="{{ $unit->id }}"
                                   name="unit_configurations[{{ $unit->id }}][custom_amount]"
                                   value="{{ Arr::get($config, 'custom_amount') }}"
                                   placeholder="Usar valor padrão"
                                   {{ $isSelected ? '' : 'disabled' }}>
                        </div>
                    </td>
                    <td class="unit-col-dates">
                        <div class="fee-units-dates-stack d-flex flex-column gap-1">
                            <input type="date"
                                   class="form-control form-control-sm unit-config-{{ $unit->id }} unit-config-input"
                                   data-unit-id="{{ $unit->id }}"
                                   name="unit_configurations[{{ $unit->id }}][starts_at]"
                                   value="{{ Arr::get($config, 'starts_at') }}"
                                   title="Início"
                                   {{ $isSelected ? '' : 'disabled' }}>
                            <input type="date"
                                   class="form-control form-control-sm unit-config-{{ $unit->id }} unit-config-input"
                                   data-unit-id="{{ $unit->id }}"
                                   name="unit_configurations[{{ $unit->id }}][ends_at]"
                                   value="{{ Arr::get($config, 'ends_at') }}"
                                   title="Fim"
                                   {{ $isSelected ? '' : 'disabled' }}>
                        </div>
                    </td>
                    <td class="unit-col-notes">
                        <textarea class="form-control form-control-sm unit-config-{{ $unit->id }} unit-config-input"
                                  data-unit-id="{{ $unit->id }}"
                                  name="unit_configurations[{{ $unit->id }}][notes]"
                                  rows="2"
                                  placeholder="Opcional"
                                  {{ $isSelected ? '' : 'disabled' }}>{{ Arr::get($config, 'notes') }}</textarea>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>

@if($wizardMode)
</div>
<div class="fee-wizard-pane" data-pane="3">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="alert alert-success border-0 bg-success bg-opacity-10 mb-4">
                <h5 class="mb-2"><i class="bi bi-clipboard-check me-1"></i> Revise antes de confirmar</h5>
                <p class="mb-0 small">Confira o resumo ao lado. Ao confirmar, a taxa será criada e as cobranças serão geradas conforme as opções marcadas.</p>
            </div>
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <h6 class="mb-3">Detalhes da taxa</h6>
                    <div id="fee-review-summary" class="small">Preencha os passos anteriores para ver o resumo detalhado.</div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card shadow-sm fee-preview-card border-primary border-opacity-25 h-100">
                <div class="card-header bg-primary bg-opacity-10">
                    <h6 class="mb-0"><i class="bi bi-eye me-1"></i> Resumo em tempo real</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="fee-hint">Nome</div>
                        <div id="preview-name" class="fw-semibold">—</div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="fee-hint">Valor base</div>
                            <div id="preview-amount" class="preview-value text-primary">R$ 0,00</div>
                        </div>
                        <div class="col-6">
                            <div class="fee-hint">Recorrência</div>
                            <div id="preview-recurrence" class="fw-semibold">—</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="fee-hint">Unidades selecionadas</div>
                        <div id="preview-units" class="fw-semibold">0</div>
                    </div>
                    <div class="mb-3">
                        <div class="fee-hint">Estimativa total por período</div>
                        <div id="preview-total" class="preview-value text-success">R$ 0,00</div>
                    </div>
                    <hr>
                    <div class="fee-hint mb-1">Ao salvar</div>
                    <ul class="small mb-0 ps-3" id="preview-actions">
                        <li>Taxa cadastrada como ativa</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const wizardMode = {{ $wizardMode ? 'true' : 'false' }};
        const recurrenceLabels = @json($recurrenceLabels);
        const billingLabels = @json($billingTypeOptions);
        let currentStep = 1;
        const maxStep = 3;

        const wizardSteps = document.querySelectorAll('.fee-wizard-step');
        const wizardPanes = document.querySelectorAll('.fee-wizard-pane');
        const prevBtn = document.getElementById('fee-wizard-prev');
        const nextBtn = document.getElementById('fee-wizard-next');
        const submitBtn = document.getElementById('fee-wizard-submit');
        const selectedUnitsBadge = document.getElementById('selected-units-count');

        const formatMoney = (value) => {
            const num = Number(value) || 0;
            return num.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        };

        const autoEligibleUnits = Number(document.getElementById('fee-units-auto-total')?.dataset.total || 0);

        const countManuallyAddedUnits = () => {
            let count = 0;
            document.querySelectorAll('.unit-row[data-auto-eligible="0"]:not(.d-none) .unit-toggle:checked').forEach(() => {
                count += 1;
            });
            return count;
        };

        const getSelectedFeeUnitModels = () => {
            return Array.from(document.querySelectorAll('.fee-unit-model-checkbox:checked'))
                .map(checkbox => checkbox.value);
        };

        const unitMatchesFeeModels = (unitModel) => {
            const selectedModels = getSelectedFeeUnitModels();
            if (!selectedModels.length) {
                return true;
            }
            return selectedModels.includes(unitModel);
        };

        const countAutoEligibleUnits = () => {
            let count = 0;
            document.querySelectorAll('.unit-row[data-auto-eligible="1"]').forEach(row => {
                if (unitMatchesFeeModels(row.dataset.unitModel)) {
                    count += 1;
                }
            });
            return count;
        };

        const countSelectedUnits = () => {
            if (applyAllField?.value === '1') {
                return countAutoEligibleUnits() + countManuallyAddedUnits();
            }
            return document.querySelectorAll('.unit-row:not(.d-none) .unit-toggle:checked').length;
        };

        const isCustomizationPanelVisible = () => !customPanel?.classList.contains('d-none');

        const updatePreview = () => {
            const name = document.querySelector('[name="name"]')?.value?.trim() || '—';
            const amount = document.querySelector('[name="amount"]')?.value || '0';
            const recurrence = document.querySelector('[name="recurrence"]')?.value;
            const units = countSelectedUnits();
            const total = (Number(amount) || 0) * units;

            document.getElementById('preview-name')?.replaceChildren(document.createTextNode(name));
            document.getElementById('preview-amount')?.replaceChildren(document.createTextNode(formatMoney(amount)));
            document.getElementById('preview-recurrence')?.replaceChildren(document.createTextNode(recurrenceLabels[recurrence] ?? '—'));
            document.getElementById('preview-units')?.replaceChildren(document.createTextNode(String(units)));
            document.getElementById('preview-total')?.replaceChildren(document.createTextNode(formatMoney(total)));
            selectedUnitsBadge && (selectedUnitsBadge.textContent = `${units} selecionadas`);

            const actions = [];
            if (document.getElementById('generate_charges_now')?.checked) {
                actions.push(`Gerar ${units} cobrança(s) agora`);
            }
            if (document.getElementById('auto_generate_charges')?.checked) {
                actions.push('Manter geração automática nos próximos períodos');
            }
            if (document.getElementById('active')?.checked) {
                actions.push('Taxa ficará ativa');
            }
            const actionsEl = document.getElementById('preview-actions');
            if (actionsEl) {
                actionsEl.innerHTML = actions.map(a => `<li>${a}</li>`).join('') || '<li>Taxa cadastrada sem cobranças imediatas</li>';
            }

            const review = document.getElementById('fee-review-summary');
            if (review) {
                review.innerHTML = `
                    <ul class="list-unstyled mb-0">
                        <li><strong>Taxa:</strong> ${name}</li>
                        <li><strong>Tipo:</strong> ${billingLabels[document.querySelector('[name="billing_type"]')?.value] ?? '—'}</li>
                        <li><strong>Valor base:</strong> ${formatMoney(amount)}</li>
                        <li><strong>Unidades:</strong> ${units}</li>
                        <li><strong>Total estimado:</strong> ${formatMoney(total)}</li>
                    </ul>`;
            }
        };

        const goToStep = (step) => {
            currentStep = Math.max(1, Math.min(maxStep, step));
            wizardSteps.forEach(el => {
                const n = Number(el.dataset.step);
                el.classList.toggle('active', n === currentStep);
                el.classList.toggle('done', n < currentStep);
            });
            wizardPanes.forEach(el => el.classList.toggle('active', Number(el.dataset.pane) === currentStep));
            if (prevBtn) prevBtn.disabled = currentStep === 1;
            if (nextBtn) nextBtn.classList.toggle('d-none', currentStep === maxStep);
            if (submitBtn) submitBtn.classList.toggle('d-none', currentStep !== maxStep);
            syncWizardLayout();
            if (currentStep === 2) {
                applyUnitModelTableFilter();
            }
            if (currentStep === 2 && isCustomizationPanelVisible()) {
                setTimeout(() => {
                    initUnitsDataTable();
                    adjustUnitsTable();
                }, 120);
            }
            updatePreview();
        };

        const validateStep = (step) => {
            if (step === 1) {
                const name = document.querySelector('[name="name"]');
                const amount = document.querySelector('[name="amount"]');
                if (!name?.value.trim()) { name?.focus(); return false; }
                if (!amount?.value || Number(amount.value) < 0) { amount?.focus(); return false; }
            }
            if (step === 2) {
                if (applyAllMode) {
                    if (countAutoEligibleUnits() === 0 && countManuallyAddedUnits() === 0) {
                        alert('Não há unidades elegíveis para automação. Marque ao menos uma unidade na tabela.');
                        return false;
                    }
                    return true;
                }
                const units = countSelectedUnits();
                if (units === 0) {
                    alert('Selecione ao menos uma unidade ou use o modo padrão (unidades habitadas com morador).');
                    return false;
                }
            }
            return true;
        };

        prevBtn?.addEventListener('click', () => goToStep(currentStep - 1));
        nextBtn?.addEventListener('click', () => {
            if (!validateStep(currentStep)) return;
            goToStep(currentStep + 1);
        });

        document.querySelectorAll('#fee-form input, #fee-form select, #fee-form textarea').forEach(el => {
            el.addEventListener('input', updatePreview);
            el.addEventListener('change', updatePreview);
        });

        const feeForm = document.getElementById('fee-form');
        const applyAllField = document.getElementById('apply_all_units');
        const simplePanel = document.getElementById('units-simple-panel');
        const customPanel = document.getElementById('units-custom-panel');
        const btnShowUnits = document.getElementById('btn-show-units');
        const btnUseAllUnits = document.getElementById('btn-use-all-units');
        const onlySelectedToggle = document.getElementById('only_selected_units');
        const selectAllButton = document.getElementById('select-all-units');
        const clearAllButton = document.getElementById('clear-all-units');
        const filterInput = document.getElementById('unit-filter');
        const unitModelFilter = document.getElementById('unit-model-filter');
        const unitsTable = $('#units-table');

        let applyAllMode = applyAllField?.value === '1';
        let unitsDataTable = null;
        const checkboxState = {};
        const modifiedUnits = new Set();

        const syncWizardLayout = () => {
            if (!wizardMode || !feeForm) {
                return;
            }
            feeForm.classList.toggle('fee-wizard-step-units', currentStep === 2 && isCustomizationPanelVisible());
        };

        const adjustUnitsTable = () => {
            if (unitsDataTable) {
                unitsDataTable.columns.adjust();
            }
        };

        const initUnitsDataTable = () => {
            if (unitsDataTable || !unitsTable.length || customPanel?.classList.contains('d-none')) {
                return;
            }

            unitsDataTable = initSafeDataTable('#units-table', {
                paging: true,
                pageLength: 50,
                lengthMenu: [[25, 50, 100, -1], [25, 50, 100, 'Todas']],
                ordering: false,
                searching: true,
                scrollX: true,
                autoWidth: false,
                dom: 'lrtip',
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json',
                    emptyTable: 'Nenhuma unidade cadastrada no condomínio.',
                },
                drawCallback: function () {
                    applyStateToAllCheckboxes();
                }
            });
        };

        const setApplyAllMode = (enabled) => {
            applyAllMode = enabled;
            if (applyAllField) {
                applyAllField.value = enabled ? '1' : '0';
            }
            if (onlySelectedToggle) {
                onlySelectedToggle.checked = !enabled;
            }

            if (enabled) {
                document.querySelectorAll('.unit-config-input').forEach(input => {
                    input.disabled = true;
                });
            } else if (isCustomizationPanelVisible()) {
                applyStateToAllCheckboxes();
            }

            updatePreview();
        };

        const showCustomizationPanel = () => {
            simplePanel?.classList.add('d-none');
            customPanel?.classList.remove('d-none');
            setApplyAllMode(onlySelectedToggle?.checked ? false : true);
            syncWizardLayout();
            setTimeout(() => {
                initUnitsDataTable();
                adjustUnitsTable();
                applyUnitModelTableFilter();
            }, 120);
            updatePreview();
        };

        const hideCustomizationPanel = () => {
            customPanel?.classList.add('d-none');
            simplePanel?.classList.remove('d-none');
            if (onlySelectedToggle) {
                onlySelectedToggle.checked = false;
            }
            setApplyAllMode(true);
            syncWizardLayout();
            updatePreview();
        };

        btnShowUnits?.addEventListener('click', () => showCustomizationPanel());
        btnUseAllUnits?.addEventListener('click', () => {
            document.querySelectorAll('.unit-toggle').forEach(checkbox => {
                checkboxState[checkbox.dataset.unitId] = false;
                checkbox.checked = false;
                checkbox.disabled = false;
            });
            modifiedUnits.clear();
            hideCustomizationPanel();
        });

        onlySelectedToggle?.addEventListener('change', () => {
            setApplyAllMode(!onlySelectedToggle.checked);
            if (isCustomizationPanelVisible()) {
                applyStateToAllCheckboxes();
            }
            updatePreview();
        });

        const toggleInputs = (targetClass, enabled) => {
            document.querySelectorAll('.' + targetClass).forEach(input => {
                if (applyAllMode && isCustomizationPanelVisible()) {
                    input.disabled = !enabled;
                    return;
                }

                if (applyAllMode) {
                    input.disabled = true;
                    return;
                }

                input.disabled = !enabled;

                if (!enabled) {
                    if (input.type === 'checkbox') {
                        input.checked = false;
                    } else if (input.tagName === 'SELECT') {
                        input.selectedIndex = 0;
                    } else if (input.type !== 'hidden') {
                        input.value = '';
                    }
                }
            });
        };

        const applyStateToCheckbox = (checkbox) => {
            if (!checkbox) return;

            const unitId = checkbox.dataset.unitId;
            const row = checkbox.closest('.unit-row');
            const isAutoEligible = row?.dataset.autoEligible === '1';
            const matchesFeeModels = unitMatchesFeeModels(row?.dataset.unitModel);

            if (!matchesFeeModels) {
                checkbox.checked = false;
                checkbox.disabled = true;
                checkboxState[unitId] = false;
                toggleInputs(checkbox.dataset.target, false);
                return;
            }

            checkbox.disabled = false;

            if (applyAllMode && !isCustomizationPanelVisible()) {
                checkbox.checked = true;
                return;
            }

            if (applyAllMode) {
                const isChecked = checkboxState.hasOwnProperty(unitId)
                    ? checkboxState[unitId]
                    : checkbox.checked;

                checkbox.checked = isChecked;

                if (isAutoEligible) {
                    toggleInputs(checkbox.dataset.target, isChecked || modifiedUnits.has(unitId));
                } else {
                    toggleInputs(checkbox.dataset.target, isChecked);
                }
                return;
            }

            const isChecked = checkboxState.hasOwnProperty(unitId)
                ? checkboxState[unitId]
                : checkbox.checked;

            checkbox.checked = isChecked;
            toggleInputs(checkbox.dataset.target, isChecked);
        };

        const applyStateToAllCheckboxes = () => {
            document.querySelectorAll('.unit-toggle').forEach(applyStateToCheckbox);
        };

        const markUnitAsModified = (unitId) => {
            if (!unitId) return;
            modifiedUnits.add(unitId);
        };

        document.addEventListener('change', function (event) {
            if (event.target.classList?.contains('unit-toggle')) {
                const unitId = event.target.dataset.unitId;
                checkboxState[unitId] = event.target.checked;
                applyStateToCheckbox(event.target);
                updatePreview();
            }

            if (event.target.classList?.contains('unit-config-input') && event.target.type !== 'hidden') {
                markUnitAsModified(event.target.dataset.unitId);
                if (applyAllMode && isCustomizationPanelVisible()) {
                    const row = event.target.closest('.unit-row');
                    const checkbox = row?.querySelector('.unit-toggle');
                    if (checkbox && row?.dataset.autoEligible === '1') {
                        toggleInputs(checkbox.dataset.target, true);
                    }
                }
            }
        });

        document.addEventListener('input', function (event) {
            if (event.target.classList?.contains('unit-config-input')) {
                markUnitAsModified(event.target.dataset.unitId);
                if (applyAllMode && isCustomizationPanelVisible()) {
                    const row = event.target.closest('.unit-row');
                    const checkbox = row?.querySelector('.unit-toggle');
                    if (checkbox && row?.dataset.autoEligible === '1') {
                        toggleInputs(checkbox.dataset.target, true);
                    }
                }
            }
        });

        selectAllButton?.addEventListener('click', () => {
            if (applyAllMode && !onlySelectedToggle?.checked) {
                return;
            }
            getVisibleUnitCheckboxes().forEach(checkbox => {
                const row = checkbox.closest('.unit-row');
                if (!row || !unitMatchesFeeModels(row.dataset.unitModel)) {
                    return;
                }
                checkboxState[checkbox.dataset.unitId] = true;
            });
            applyStateToAllCheckboxes();
            updatePreview();
        });

        clearAllButton?.addEventListener('click', () => {
            if (applyAllMode && !onlySelectedToggle?.checked) {
                return;
            }
            getVisibleUnitCheckboxes().forEach(checkbox => {
                checkboxState[checkbox.dataset.unitId] = false;
            });
            applyStateToAllCheckboxes();
            updatePreview();
        });

        const recurrenceSelect = document.getElementById('recurrence');
        const recurrenceDependentFields = document.querySelectorAll('.recurrence-dependent');
        const customScheduleSection = document.getElementById('custom-schedule-section');
        const customScheduleTextarea = document.getElementById('custom_schedule_text');

        const toggleRecurrenceFields = () => {
            const value = recurrenceSelect.value;
            const shouldShow = ['monthly', 'quarterly', 'yearly'].includes(value);

            recurrenceDependentFields.forEach(element => {
                element.style.display = shouldShow ? 'block' : 'none';
                element.querySelectorAll('input').forEach(input => input.disabled = !shouldShow);
            });

            if (value === 'custom') {
                customScheduleSection.style.display = 'block';
                customScheduleTextarea.disabled = false;
            } else {
                customScheduleSection.style.display = 'none';
                customScheduleTextarea.disabled = true;
            }
        };

        if (recurrenceSelect) {
            if (customScheduleTextarea) {
                customScheduleTextarea.dataset.original = customScheduleTextarea.value;
            }

            toggleRecurrenceFields();
            recurrenceSelect.addEventListener('change', toggleRecurrenceFields);
        }

        if (unitsTable.length && !wizardMode) {
            initUnitsDataTable();
        }

        if (filterInput) {
            filterInput.addEventListener('input', () => {
                if (unitsDataTable) {
                    unitsDataTable.search(filterInput.value).draw();
                }
            });
        }

        if (unitModelFilter) {
            unitModelFilter.addEventListener('change', () => {
                applyUnitModelTableFilter();
            });
        }

        document.querySelectorAll('.fee-unit-model-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const hint = document.getElementById('fee-models-filter-hint');
                if (hint) {
                    hint.textContent = getSelectedFeeUnitModels().length ? ' nos modelos selecionados' : '';
                }
                applyUnitModelTableFilter();
                updatePreview();
            });
        });

        const applyUnitModelTableFilter = () => {
            const tableModel = unitModelFilter?.value || '';

            document.querySelectorAll('.unit-row').forEach(row => {
                const rowModel = row.dataset.unitModel;
                const matchesFeeModels = unitMatchesFeeModels(rowModel);
                const matchesTableFilter = !tableModel || tableModel === rowModel;
                const isVisible = matchesFeeModels && matchesTableFilter;

                row.classList.toggle('d-none', !isVisible);

                if (!matchesFeeModels) {
                    const unitId = row.dataset.unitId;
                    const checkbox = row.querySelector('.unit-toggle');
                    checkboxState[unitId] = false;
                    if (checkbox) {
                        checkbox.checked = false;
                        checkbox.disabled = true;
                    }
                    if (checkbox) {
                        toggleInputs(checkbox.dataset.target, false);
                    }
                } else {
                    const checkbox = row.querySelector('.unit-toggle');
                    if (checkbox) {
                        checkbox.disabled = false;
                    }
                }
            });

            const eligibleCount = countAutoEligibleUnits();
            const totalLabel = document.getElementById('units-total-label');
            if (totalLabel) {
                totalLabel.textContent = String(eligibleCount);
            }

            if (unitsDataTable) {
                unitsDataTable.draw(false);
            }
        };

        const getVisibleUnitCheckboxes = () => {
            return Array.from(document.querySelectorAll('.unit-row:not(.d-none) .unit-toggle'));
        };

        document.querySelectorAll('.unit-toggle').forEach(checkbox => {
            checkboxState[checkbox.dataset.unitId] = checkbox.checked;
        });

        if (onlySelectedToggle && applyAllField?.value === '0') {
            onlySelectedToggle.checked = true;
        }

        setApplyAllMode(applyAllField?.value === '1');

        if (filterInput) {
            filterInput.dispatchEvent(new Event('input'));
        }

        applyUnitModelTableFilter();
        updatePreview();

        if (wizardMode) {
            goToStep(1);
        }

        if (feeForm) {
            feeForm.addEventListener('submit', () => {
                document.querySelectorAll('.unit-row').forEach(row => {
                    const unitId = row.dataset.unitId;
                    const checkbox = row.querySelector('.unit-toggle');
                    const matchesFeeModels = unitMatchesFeeModels(row.dataset.unitModel);
                    const isChecked = checkboxState.hasOwnProperty(unitId)
                        ? checkboxState[unitId]
                        : checkbox?.checked;
                    const inputs = row.querySelectorAll('.unit-config-input');

                    let shouldSubmit = false;

                    if (!matchesFeeModels) {
                        shouldSubmit = false;
                    } else if (applyAllMode) {
                        shouldSubmit = isChecked || modifiedUnits.has(unitId);
                    } else {
                        shouldSubmit = Boolean(isChecked);
                    }

                    inputs.forEach(input => {
                        input.disabled = !shouldSubmit;
                    });
                });
            });
        }
    });
</script>
@endpush

