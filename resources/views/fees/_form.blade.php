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
        <style>
            .fee-units-table-container {
                width: 100%;
                border-radius: .375rem;
                border: 1px solid #dee2e6;
                background: #fff;
            }
            .fee-units-table-container table {
                width: 100%;
                table-layout: fixed;
            }
            #units-table th, #units-table td {
                vertical-align: middle;
                word-wrap: break-word;
            }
            #units-table .unit-col-check { width: 72px; }
            #units-table .unit-col-amount { width: 140px; }
            .fee-payment-option {
                border: 2px solid #dee2e6;
                border-radius: .5rem;
                padding: .85rem 1rem;
                cursor: pointer;
                transition: border-color .15s, background .15s;
                height: 100%;
            }
            .fee-payment-option:has(input:checked) {
                border-color: #0d6efd;
                background: #e7f1ff;
            }
            .fee-payment-option input { margin-top: .2rem; }
            .fee-scope-option {
                border: 1px solid #dee2e6;
                border-radius: .5rem;
                padding: .75rem 1rem;
                cursor: pointer;
            }
            .fee-scope-option:has(input:checked) {
                border-color: #198754;
                background: #d1e7dd;
            }
            .fee-auto-units-list {
                max-height: 220px;
                overflow-y: auto;
            }
            .fee-preview-card .preview-value { font-size: 1.35rem; font-weight: 700; }
            .fee-hint { font-size: .85rem; color: #6c757d; }
        </style>
    @endpush
@endonce

@php
    $totalUnits = $units->count();
    $autoEligibleUnitsCount = $autoEligibleUnitsCount ?? $units->filter(fn ($u) => $u->isEligibleForAutomaticFee())->count();
    $defaultPaymentChannel = old('default_payment_channel', ($fee->metadata['default_payment_channel'] ?? null) ?? 'system');
    $defaultApplyAll = old('apply_all_units', ($wizardMode ?? false) ? '1' : (empty($existingConfigurations) ? '1' : '0'));
    $defaultUnitScope = filter_var($defaultApplyAll, FILTER_VALIDATE_BOOLEAN) ? 'automatic' : 'manual';
    $defaultUnitScope = old('unit_scope', $defaultUnitScope);
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
    <div class="mb-4">
        <h4 class="mb-1">Pagamento e unidades</h4>
        <p class="text-muted mb-0">Defina como a taxa será paga e quais unidades receberão a cobrança.</p>
    </div>
@endif

@error('unit_configurations')
    <div class="alert alert-danger">{{ $message }}</div>
@enderror
@error('default_payment_channel')
    <div class="alert alert-danger">{{ $message }}</div>
@enderror

<div class="mb-4">
    <label class="form-label fw-semibold">Forma de pagamento padrão desta taxa *</label>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="fee-payment-option d-flex gap-2 mb-0">
                <input type="radio" name="default_payment_channel" value="system" class="form-check-input flex-shrink-0"
                    {{ $defaultPaymentChannel === 'system' ? 'checked' : '' }}>
                <span>
                    <strong>Sistema (online)</strong>
                    <span class="d-block small text-muted">Morador paga em Minhas Cobranças via PIX, cartão ou boleto.</span>
                </span>
            </label>
        </div>
        <div class="col-md-6">
            <label class="fee-payment-option d-flex gap-2 mb-0">
                <input type="radio" name="default_payment_channel" value="payroll" class="form-check-input flex-shrink-0"
                    {{ $defaultPaymentChannel === 'payroll' ? 'checked' : '' }}>
                <span>
                    <strong>Desconto em folha</strong>
                    <span class="d-block small text-muted">Liquidação automática na folha (taxa condominial).</span>
                </span>
            </label>
        </div>
    </div>
</div>

<div class="mb-4">
    <label class="form-label fw-semibold">Quem recebe esta cobrança? *</label>
    <div class="d-flex flex-column gap-2">
        <label class="fee-scope-option d-flex gap-2 mb-0">
            <input type="radio" name="unit_scope" value="automatic" class="form-check-input flex-shrink-0 mt-1"
                {{ $defaultUnitScope === 'automatic' ? 'checked' : '' }}>
            <span>
                <strong>Todas as unidades habitadas com morador</strong>
                <span class="d-block small text-muted">
                    <span id="units-total-label">{{ $autoEligibleUnitsCount }}</span> unidade(s) incluídas automaticamente
                    <span id="fee-models-filter-hint">{{ empty($selectedUnitModels) ? '' : ' (modelos selecionados)' }}</span>.
                </span>
            </span>
        </label>
        <label class="fee-scope-option d-flex gap-2 mb-0">
            <input type="radio" name="unit_scope" value="manual" class="form-check-input flex-shrink-0 mt-1"
                {{ $defaultUnitScope === 'manual' ? 'checked' : '' }}>
            <span>
                <strong>Escolher unidades manualmente</strong>
                <span class="d-block small text-muted">Marque abaixo somente quem deve receber a cobrança.</span>
            </span>
        </label>
    </div>
</div>

<div id="units-auto-summary" class="card border-success border-opacity-50 bg-success bg-opacity-10 mb-4 {{ $defaultUnitScope === 'manual' ? 'd-none' : '' }}">
    <div class="card-body">
        <h6 class="mb-2"><i class="bi bi-check2-circle me-1"></i> Unidades que receberão a cobrança</h6>
        <p class="small text-muted mb-2">Pagamento: <strong id="auto-payment-label">{{ $defaultPaymentChannel === 'payroll' ? 'Desconto em folha' : 'Sistema (online)' }}</strong></p>
        <div class="fee-auto-units-list" id="auto-units-list"></div>
    </div>
</div>

<div id="units-manual-panel" class="mb-4 {{ $defaultUnitScope === 'automatic' ? 'd-none' : '' }}">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
        <p class="text-muted small mb-0">Marque <strong>Cobrar</strong> para incluir a unidade. Pagamento conforme padrão definido acima.</p>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <button type="button" class="btn btn-sm btn-outline-primary" id="select-all-units">Marcar todas</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-all-units">Desmarcar todas</button>
            <span class="badge bg-primary" id="selected-units-count">0 selecionadas</span>
        </div>
    </div>

    <div class="input-group input-group-sm mb-3" style="max-width: 360px;">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" id="unit-filter" class="form-control" placeholder="Buscar unidade ou morador">
    </div>

    <div class="fee-units-table-container">
        <table id="units-table" class="table table-sm table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th class="unit-col-check text-center">Cobrar</th>
                    <th>Unidade / Morador</th>
                    <th class="unit-col-amount">Valor diferente</th>
                </tr>
            </thead>
            <tbody>
                @foreach($units as $unit)
                    @php
                        $config = $unitConfigurations->get($unit->id, []);
                        $isSelected = !empty($config);
                        $autoEligible = $unit->isEligibleForAutomaticFee();
                        $searchBlob = strtolower(trim(($unit->full_identifier ?? '') . ' ' . (optional($unit->morador)->name ?? '') . ' ' . $unit->situacao_label));
                        $manualChecked = $defaultUnitScope === 'manual' ? $isSelected : false;
                    @endphp
                    <tr class="unit-row {{ $autoEligible ? '' : 'table-light' }}"
                        data-search="{{ $searchBlob }}"
                        data-unit-id="{{ $unit->id }}"
                        data-unit-model="{{ $unit->unit_model }}"
                        data-auto-eligible="{{ $autoEligible ? '1' : '0' }}">
                        <td class="text-center">
                            <input class="form-check-input unit-toggle"
                                   type="checkbox"
                                   value="1"
                                   data-target="unit-config-{{ $unit->id }}"
                                   data-unit-id="{{ $unit->id }}"
                                   {{ $manualChecked ? 'checked' : '' }}>
                            <input type="hidden"
                                   name="unit_configurations[{{ $unit->id }}][unit_id]"
                                   value="{{ $unit->id }}"
                                   class="unit-config-{{ $unit->id }} unit-config-input unit-id-input"
                                   data-unit-id="{{ $unit->id }}"
                                   disabled>
                            <input type="hidden"
                                   name="unit_configurations[{{ $unit->id }}][payment_channel]"
                                   value="{{ $defaultPaymentChannel }}"
                                   class="unit-config-{{ $unit->id }} unit-config-input unit-payment-input"
                                   data-unit-id="{{ $unit->id }}"
                                   disabled>
                            @if(isset($config['id']))
                                <input type="hidden"
                                       name="unit_configurations[{{ $unit->id }}][id]"
                                       value="{{ $config['id'] }}"
                                       class="unit-config-{{ $unit->id }} unit-config-input"
                                       data-unit-id="{{ $unit->id }}"
                                       disabled>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $unit->full_identifier }}</div>
                            <small class="text-muted">{{ optional($unit->morador)->name ?? 'Sem morador' }}</small>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                <span class="badge bg-secondary">{{ $unit->unit_model_label }}</span>
                                <span class="badge {{ $unit->situacao === 'habitado' ? 'bg-success' : 'bg-secondary' }}">{{ $unit->situacao_label }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">R$</span>
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       class="form-control unit-config-{{ $unit->id }} unit-config-input"
                                       data-unit-id="{{ $unit->id }}"
                                       name="unit_configurations[{{ $unit->id }}][custom_amount]"
                                       value="{{ Arr::get($config, 'custom_amount') }}"
                                       placeholder="Padrão"
                                       disabled>
                            </div>
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
            if (getUnitScope() === 'automatic') {
                return countAutoEligibleUnits();
            }
            return document.querySelectorAll('.unit-row:not(.d-none) .unit-toggle:checked').length;
        };

        const getUnitScope = () => {
            return document.querySelector('input[name="unit_scope"]:checked')?.value || 'automatic';
        };

        const getDefaultPaymentChannel = () => {
            return document.querySelector('input[name="default_payment_channel"]:checked')?.value || 'system';
        };

        const paymentChannelLabel = (channel) => channel === 'payroll' ? 'Desconto em folha' : 'Sistema (online)';

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
            selectedUnitsBadge && (selectedUnitsBadge.textContent = `${units} selecionada(s)`);

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
                        <li><strong>Pagamento:</strong> ${paymentChannelLabel(getDefaultPaymentChannel())}</li>
                        <li><strong>Escopo:</strong> ${getUnitScope() === 'automatic' ? 'Todas habitadas com morador' : 'Unidades selecionadas'}</li>
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
                syncScopeUI();
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
                if (getUnitScope() === 'automatic') {
                    if (countAutoEligibleUnits() === 0) {
                        alert('Não há unidades habitadas com morador para este filtro. Escolha unidades manualmente.');
                        return false;
                    }
                    return true;
                }
                if (countSelectedUnits() === 0) {
                    alert('Marque ao menos uma unidade para receber a cobrança.');
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
        const autoSummary = document.getElementById('units-auto-summary');
        const manualPanel = document.getElementById('units-manual-panel');
        const scopeRadios = document.querySelectorAll('input[name="unit_scope"]');
        const paymentRadios = document.querySelectorAll('input[name="default_payment_channel"]');
        const selectAllButton = document.getElementById('select-all-units');
        const clearAllButton = document.getElementById('clear-all-units');
        const filterInput = document.getElementById('unit-filter');
        const checkboxState = {};

        const syncWizardLayout = () => {
            if (!wizardMode || !feeForm) {
                return;
            }
            feeForm.classList.toggle('fee-wizard-step-units', currentStep === 2 && getUnitScope() === 'manual');
        };

        const toggleInputs = (targetClass, enabled) => {
            document.querySelectorAll('.' + targetClass).forEach(input => {
                input.disabled = !enabled;
            });
        };

        const applyStateToCheckbox = (checkbox) => {
            if (!checkbox) return;

            const unitId = checkbox.dataset.unitId;
            const row = checkbox.closest('.unit-row');
            const matchesFeeModels = unitMatchesFeeModels(row?.dataset.unitModel);

            if (!matchesFeeModels) {
                checkbox.checked = false;
                checkbox.disabled = true;
                checkboxState[unitId] = false;
                toggleInputs(checkbox.dataset.target, false);
                return;
            }

            if (getUnitScope() === 'automatic') {
                checkbox.disabled = true;
                checkbox.checked = false;
                toggleInputs(checkbox.dataset.target, false);
                return;
            }

            checkbox.disabled = false;
            const isChecked = Object.prototype.hasOwnProperty.call(checkboxState, unitId)
                ? checkboxState[unitId]
                : checkbox.checked;
            checkbox.checked = isChecked;
            toggleInputs(checkbox.dataset.target, isChecked);
        };

        const applyStateToAllCheckboxes = () => {
            document.querySelectorAll('.unit-toggle').forEach(applyStateToCheckbox);
        };

        const syncPaymentInputs = () => {
            const channel = getDefaultPaymentChannel();
            document.querySelectorAll('.unit-payment-input').forEach(input => {
                input.value = channel;
            });
            const label = document.getElementById('auto-payment-label');
            if (label) {
                label.textContent = paymentChannelLabel(channel);
            }
        };

        const buildAutoUnitsList = () => {
            const list = document.getElementById('auto-units-list');
            if (!list) return;

            const items = [];
            document.querySelectorAll('.unit-row[data-auto-eligible="1"]').forEach(row => {
                if (!unitMatchesFeeModels(row.dataset.unitModel)) {
                    return;
                }
                const unit = row.querySelector('.fw-semibold')?.textContent?.trim() || '—';
                const morador = row.querySelector('small.text-muted')?.textContent?.trim() || 'Sem morador';
                items.push(`<div class="small py-1 border-bottom">${unit} <span class="text-muted">— ${morador}</span></div>`);
            });

            list.innerHTML = items.length
                ? items.join('')
                : '<div class="small text-muted">Nenhuma unidade elegível com os filtros atuais.</div>';
        };

        const syncScopeUI = () => {
            const automatic = getUnitScope() === 'automatic';
            if (applyAllField) {
                applyAllField.value = automatic ? '1' : '0';
            }
            autoSummary?.classList.toggle('d-none', !automatic);
            manualPanel?.classList.toggle('d-none', automatic);
            syncPaymentInputs();
            if (automatic) {
                buildAutoUnitsList();
            }
            applyStateToAllCheckboxes();
            syncWizardLayout();
            updatePreview();
        };

        scopeRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                if (getUnitScope() === 'manual') {
                    document.querySelectorAll('.unit-row[data-auto-eligible="1"]:not(.d-none) .unit-toggle').forEach(checkbox => {
                        if (!Object.prototype.hasOwnProperty.call(checkboxState, checkbox.dataset.unitId)) {
                            checkboxState[checkbox.dataset.unitId] = true;
                        }
                    });
                }
                syncScopeUI();
            });
        });

        paymentRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                syncPaymentInputs();
                updatePreview();
            });
        });

        document.addEventListener('change', function (event) {
            if (event.target.classList?.contains('unit-toggle')) {
                checkboxState[event.target.dataset.unitId] = event.target.checked;
                applyStateToCheckbox(event.target);
                updatePreview();
            }
        });

        selectAllButton?.addEventListener('click', () => {
            if (getUnitScope() !== 'manual') return;
            getVisibleUnitCheckboxes().forEach(checkbox => {
                checkboxState[checkbox.dataset.unitId] = true;
            });
            applyStateToAllCheckboxes();
            updatePreview();
        });

        clearAllButton?.addEventListener('click', () => {
            if (getUnitScope() !== 'manual') return;
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

        if (filterInput) {
            filterInput.addEventListener('input', () => {
                const query = filterInput.value.trim().toLowerCase();
                document.querySelectorAll('.unit-row').forEach(row => {
                    const matchesSearch = !query || (row.dataset.search || '').includes(query);
                    const matchesModels = unitMatchesFeeModels(row.dataset.unitModel);
                    row.classList.toggle('d-none', !(matchesSearch && matchesModels));
                });
                applyStateToAllCheckboxes();
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
            document.querySelectorAll('.unit-row').forEach(row => {
                const rowModel = row.dataset.unitModel;
                const matchesFeeModels = unitMatchesFeeModels(rowModel);

                row.classList.toggle('d-none', !matchesFeeModels);

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

            if (getUnitScope() === 'automatic') {
                buildAutoUnitsList();
            }
            applyStateToAllCheckboxes();
        };

        const getVisibleUnitCheckboxes = () => {
            return Array.from(document.querySelectorAll('.unit-row:not(.d-none) .unit-toggle'));
        };

        document.querySelectorAll('.unit-toggle').forEach(checkbox => {
            checkboxState[checkbox.dataset.unitId] = checkbox.checked;
        });

        applyUnitModelTableFilter();
        syncScopeUI();
        updatePreview();

        if (wizardMode) {
            goToStep(1);
        }

        if (feeForm) {
            feeForm.addEventListener('submit', () => {
                const automatic = getUnitScope() === 'automatic';
                document.querySelectorAll('.unit-row').forEach(row => {
                    const unitId = row.dataset.unitId;
                    const checkbox = row.querySelector('.unit-toggle');
                    const matchesFeeModels = unitMatchesFeeModels(row.dataset.unitModel);
                    const isChecked = Object.prototype.hasOwnProperty.call(checkboxState, unitId)
                        ? checkboxState[unitId]
                        : checkbox?.checked;
                    const inputs = row.querySelectorAll('.unit-config-input');
                    const shouldSubmit = matchesFeeModels && !automatic && Boolean(isChecked);

                    inputs.forEach(input => {
                        input.disabled = !shouldSubmit;
                    });
                });
            });
        }
    });
</script>
@endpush

