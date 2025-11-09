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
@endphp

@once
    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    @endpush
@endonce

@once
    @push('scripts')
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    @endpush
@endonce

<input type="hidden" name="apply_all_units" id="apply_all_units" value="{{ old('apply_all_units', 0) }}">

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
        <label class="form-label fw-semibold">Dias de antecedência</label>
        <input type="number"
               min="0"
               max="365"
               name="due_offset_days"
               class="form-control @error('due_offset_days') is-invalid @enderror"
               value="{{ old('due_offset_days', $fee->due_offset_days) }}">
        <small class="text-muted">Número de dias antes para gerar a cobrança</small>
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

    <div class="col-md-3 form-check form-switch mt-4">
        <input type="checkbox"
               name="auto_generate_charges"
               id="auto_generate_charges"
               class="form-check-input"
               value="1"
               {{ old('auto_generate_charges', $fee->auto_generate_charges) ? 'checked' : '' }}>
        <label class="form-check-label fw-semibold" for="auto_generate_charges">Gerar cobranças automaticamente</label>
    </div>

    <div class="col-md-3 form-check form-switch mt-4">
        <input type="checkbox"
               name="active"
               id="active"
               class="form-check-input"
               value="1"
               {{ old('active', $fee->active) ? 'checked' : '' }}>
        <label class="form-check-label fw-semibold" for="active">Taxa ativa</label>
    </div>
</div>

<hr class="my-4">

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h5 class="mb-0">Unidades e formas de pagamento</h5>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <div class="input-group input-group-sm" style="min-width: 220px;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text"
                   id="unit-filter"
                   class="form-control"
                   placeholder="Filtrar por bloco, número ou morador">
        </div>
        <button type="button" class="btn btn-sm btn-outline-success" id="apply-all-units">
            Aplicar a todos
        </button>
        <button type="button" class="btn btn-sm btn-outline-primary" id="select-all-units">
            Selecionar todas
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-all-units">
            Limpar seleção
        </button>
        <span class="badge bg-success d-none" id="apply-all-feedback">Aplicando taxa em todas as unidades</span>
    </div>
</div>

<div class="table-responsive border rounded">
    <table id="units-table" class="table table-hover mb-0 align-middle">
        <thead class="table-light">
            <tr>
                <th style="width: 50px;"></th>
                <th>Unidade</th>
                <th>Forma de pagamento</th>
                <th>Valor personalizado</th>
                <th>Vigência personalizada</th>
                <th>Anotações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($units as $unit)
                @php
                    $config = $unitConfigurations->get($unit->id, []);
                    $isSelected = !empty($config);
                    $paymentChannel = Arr::get($config, 'payment_channel', $unit->default_payment_channel ?? 'system');
                    $searchBlob = strtolower(trim(($unit->full_identifier ?? '') . ' ' . (optional($unit->morador)->name ?? '')));
                @endphp
                <tr class="unit-row" data-search="{{ $searchBlob }}" data-unit-id="{{ $unit->id }}">
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
                        <small class="text-muted d-block">
                            Morador atual: {{ optional($unit->morador)->name ?? 'Não cadastrado' }}
                        </small>
                        @if($unit->default_payment_channel)
                            <small class="text-muted">Preferência padrão: {{ strtoupper($unit->default_payment_channel) }}</small>
                        @endif
                    </td>
                    <td style="width: 180px;">
                        <select name="unit_configurations[{{ $unit->id }}][payment_channel]"
                                class="form-select form-select-sm unit-config-{{ $unit->id }} unit-config-input"
                                data-unit-id="{{ $unit->id }}"
                                {{ $isSelected ? '' : 'disabled' }}>
                            <option value="payroll" {{ $paymentChannel === 'payroll' ? 'selected' : '' }}>Desconto em folha</option>
                            <option value="system" {{ $paymentChannel === 'system' ? 'selected' : '' }}>Sistema</option>
                        </select>
                    </td>
                    <td style="width: 180px;">
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
                    <td style="width: 220px;">
                        <div class="d-flex gap-2">
                            <input type="date"
                                   class="form-control form-control-sm unit-config-{{ $unit->id }} unit-config-input"
                                   data-unit-id="{{ $unit->id }}"
                                   name="unit_configurations[{{ $unit->id }}][starts_at]"
                                   value="{{ Arr::get($config, 'starts_at') }}"
                                   {{ $isSelected ? '' : 'disabled' }}>
                            <input type="date"
                                   class="form-control form-control-sm unit-config-{{ $unit->id }} unit-config-input"
                                   data-unit-id="{{ $unit->id }}"
                                   name="unit_configurations[{{ $unit->id }}][ends_at]"
                                   value="{{ Arr::get($config, 'ends_at') }}"
                                   {{ $isSelected ? '' : 'disabled' }}>
                        </div>
                    </td>
                    <td>
                        <textarea class="form-control form-control-sm unit-config-{{ $unit->id }} unit-config-input"
                                  data-unit-id="{{ $unit->id }}"
                                  name="unit_configurations[{{ $unit->id }}][notes]"
                                  rows="2"
                                  placeholder="Detalhes específicos"
                                  {{ $isSelected ? '' : 'disabled' }}>{{ Arr::get($config, 'notes') }}</textarea>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const feeForm = document.getElementById('fee-form');
        const applyAllField = document.getElementById('apply_all_units');
        const applyAllButton = document.getElementById('apply-all-units');
        const applyAllFeedback = document.getElementById('apply-all-feedback');
        const selectAllButton = document.getElementById('select-all-units');
        const clearAllButton = document.getElementById('clear-all-units');
        const filterInput = document.getElementById('unit-filter');

        let applyAllMode = applyAllField?.value === '1';
        const checkboxState = {};
        const modifiedUnits = new Set();

        const toggleInputs = (targetClass, enabled) => {
            document.querySelectorAll('.' + targetClass).forEach(input => {
                if (applyAllMode) {
                    input.disabled = false;
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

            if (applyAllMode) {
                checkbox.checked = true;
                toggleInputs(checkbox.dataset.target, true);
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
            }

            if (applyAllMode && event.target.classList?.contains('unit-config-input') && event.target.type !== 'hidden') {
                markUnitAsModified(event.target.dataset.unitId);
            }
        });

        document.addEventListener('input', function (event) {
            if (applyAllMode && event.target.classList?.contains('unit-config-input')) {
                markUnitAsModified(event.target.dataset.unitId);
            }
        });

        selectAllButton?.addEventListener('click', () => {
            if (applyAllMode) return;
            document.querySelectorAll('.unit-toggle').forEach(checkbox => {
                checkboxState[checkbox.dataset.unitId] = true;
            });
            applyStateToAllCheckboxes();
        });

        clearAllButton?.addEventListener('click', () => {
            if (applyAllMode) return;
            document.querySelectorAll('.unit-toggle').forEach(checkbox => {
                checkboxState[checkbox.dataset.unitId] = false;
            });
            applyStateToAllCheckboxes();
        });

        applyAllButton?.addEventListener('click', () => {
            if (applyAllMode) return;

            applyAllMode = true;
            applyAllField.value = '1';
            applyAllButton.classList.add('active');
            applyAllFeedback?.classList.remove('d-none');
            selectAllButton?.setAttribute('disabled', 'disabled');
            clearAllButton?.setAttribute('disabled', 'disabled');

            document.querySelectorAll('.unit-toggle').forEach(checkbox => {
                checkboxState[checkbox.dataset.unitId] = true;
                checkbox.checked = true;
                checkbox.disabled = true;
            });

            applyStateToAllCheckboxes();
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

        const unitsTable = $('#units-table');
        let dataTable = null;

        if (unitsTable.length) {
            dataTable = unitsTable.DataTable({
                paging: true,
                pageLength: 25,
                ordering: false,
                searching: true,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json'
                },
                drawCallback: function () {
                    applyStateToAllCheckboxes();
                }
            });

            if (filterInput) {
                filterInput.addEventListener('input', () => {
                    dataTable.search(filterInput.value).draw();
                });
            }
        }

        document.querySelectorAll('.unit-toggle').forEach(checkbox => {
            checkboxState[checkbox.dataset.unitId] = checkbox.checked;
        });

        if (applyAllMode) {
            applyAllButton?.classList.add('active');
            applyAllFeedback?.classList.remove('d-none');
            selectAllButton?.setAttribute('disabled', 'disabled');
            clearAllButton?.setAttribute('disabled', 'disabled');

            document.querySelectorAll('.unit-toggle').forEach(checkbox => {
                checkbox.checked = true;
                checkbox.disabled = true;
            });
        }

        applyStateToAllCheckboxes();

        if (filterInput) {
            filterInput.dispatchEvent(new Event('input'));
        }

        if (feeForm) {
            feeForm.addEventListener('submit', () => {
                if (applyAllMode) {
                    document.querySelectorAll('.unit-row').forEach(row => {
                        const unitId = row.dataset.unitId;
                        const inputs = row.querySelectorAll('.unit-config-input');

                        if (modifiedUnits.has(unitId)) {
                            inputs.forEach(input => input.disabled = false);
                        } else {
                            inputs.forEach(input => input.disabled = true);
                        }
                    });
                }
            });
        }
    });
</script>
@endpush

