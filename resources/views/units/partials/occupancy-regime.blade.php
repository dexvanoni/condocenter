@php
    $unit = $unit ?? new \App\Models\Unit(['occupancy_regime' => \App\Support\UnitOccupancyRegimes::PARTICULAR]);
    use App\Support\UnitOccupancyRegimes;
    use App\Support\UnitRentalPeriods;
    use App\Support\PublicPropertyKinds;

    $regime = old('occupancy_regime', $unit->occupancy_regime ?? UnitOccupancyRegimes::PARTICULAR);
@endphp

<div class="col-12">
    <label class="form-label fw-bold d-block mb-3">
        Regime do imóvel <span class="text-danger">*</span>
    </label>
    <div class="row g-2">
        @foreach(UnitOccupancyRegimes::labels() as $value => $label)
            <div class="col-md-4">
                <label class="situacao-option d-block {{ $regime === $value ? 'selected' : '' }}" style="cursor:pointer;">
                    <input type="radio" name="occupancy_regime" value="{{ $value }}"
                           class="occupancy-regime-radio"
                           {{ $regime === $value ? 'checked' : '' }} required>
                    <strong>{{ $label }}</strong>
                </label>
            </div>
        @endforeach
    </div>
    @error('occupancy_regime')<div class="text-danger mt-2"><small>{{ $message }}</small></div>@enderror
</div>

<div class="col-12 occupancy-field occupancy-rental occupancy-lease {{ $regime === UnitOccupancyRegimes::ALUGUEL ? '' : 'd-none' }}">
    <div class="alert alert-warning border-0 mb-0">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <strong>Contrato de locação:</strong> ao vincular um inquilino, informe a data de término do contrato.
        Depois dessa data, o acesso do inquilino e dos agregados é suspenso automaticamente.
        O proprietário recebe avisos antes do vencimento.
    </div>
</div>

<div class="col-md-6 occupancy-field occupancy-rental occupancy-lease {{ $regime === UnitOccupancyRegimes::ALUGUEL ? '' : 'd-none' }}">
    <label class="form-label fw-bold">Validade do contrato (inquilino)</label>
    <input type="date" name="lease_contract_ends_at"
           class="form-control form-control-lg @error('lease_contract_ends_at') is-invalid @enderror"
           value="{{ old('lease_contract_ends_at', optional($unit->lease_contract_ends_at)->format('Y-m-d')) }}">
    @error('lease_contract_ends_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <small class="text-muted">Obrigatório quando houver morador/inquilino em imóvel de aluguel.</small>
</div>

<div class="col-md-6 occupancy-field occupancy-rental {{ $regime === UnitOccupancyRegimes::ALUGUEL ? '' : 'd-none' }}">
    <label class="form-label fw-bold">Tipo de aluguel <span class="text-danger">*</span></label>
    <select name="rental_period" class="form-select form-select-lg @error('rental_period') is-invalid @enderror">
        <option value="">Selecione...</option>
        @foreach(UnitRentalPeriods::labels() as $value => $label)
            <option value="{{ $value }}" @selected(old('rental_period', $unit->rental_period ?? '') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    @error('rental_period')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="col-md-6 occupancy-field occupancy-public {{ $regime === UnitOccupancyRegimes::IMOVEL_PUBLICO ? '' : 'd-none' }}">
    <label class="form-label fw-bold">Classificação <span class="text-danger">*</span></label>
    <select name="public_property_kind" class="form-select form-select-lg @error('public_property_kind') is-invalid @enderror">
        <option value="">Selecione...</option>
        @foreach(PublicPropertyKinds::labels() as $value => $label)
            <option value="{{ $value }}" @selected(old('public_property_kind', $unit->public_property_kind ?? '') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    @error('public_property_kind')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

@push('scripts')
<script>
document.querySelectorAll('.occupancy-regime-radio').forEach((radio) => {
    radio.addEventListener('change', function () {
        const value = this.value;
        document.querySelectorAll('.occupancy-field').forEach((el) => el.classList.add('d-none'));
        if (value === 'aluguel') {
            document.querySelectorAll('.occupancy-rental, .occupancy-owner, .occupancy-lease').forEach((el) => el.classList.remove('d-none'));
        }
        if (value === 'imovel_publico') {
            document.querySelectorAll('.occupancy-public').forEach((el) => el.classList.remove('d-none'));
        }
    });
});
</script>
@endpush
