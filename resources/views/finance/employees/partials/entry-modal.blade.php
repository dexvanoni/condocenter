<div class="modal fade" id="modalLancamento" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('financial.employees.entries.store', $employee) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-cash"></i> Novo lançamento — {{ $employee->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipo *</label>
                            <select name="type" id="entryType" class="form-select" required>
                                @foreach($entryTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Data *</label>
                            <input type="date" name="reference_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Competência</label>
                            <input type="month" name="competence_month" class="form-control" value="{{ now()->format('Y-m') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Valor (R$) *</label>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
                        </div>
                        <div class="col-md-4 entry-overtime">
                            <label class="form-label">Horas extras</label>
                            <input type="number" step="0.01" min="0" name="hours" class="form-control">
                        </div>
                        <div class="col-md-4 entry-overtime">
                            <label class="form-label">Valor hora (R$)</label>
                            <input type="number" step="0.01" min="0" name="hourly_rate" class="form-control">
                        </div>
                        <div class="col-md-6 entry-vacation">
                            <label class="form-label">Início férias</label>
                            <input type="date" name="vacation_start" class="form-control">
                        </div>
                        <div class="col-md-6 entry-vacation">
                            <label class="form-label">Fim férias</label>
                            <input type="date" name="vacation_end" class="form-control">
                        </div>
                        <div class="col-12 entry-tax">
                            <label class="form-label">Encargos (detalhamento)</label>
                            <div class="row g-2">
                                @foreach(['INSS patronal', 'FGTS', 'IRRF', 'Outros encargos'] as $taxLabel)
                                    <div class="col-md-6">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">{{ $taxLabel }}</span>
                                            <input type="number" step="0.01" min="0" name="tax_breakdown[{{ $taxLabel }}]" class="form-control" placeholder="0,00">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted">Se preencher os encargos, o total acima pode ser a soma ou valor complementar.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observações</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar na folha</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('entryType');
    const toggleFields = () => {
        const type = typeSelect.value;
        document.querySelectorAll('.entry-overtime').forEach(el => el.style.display = type === 'overtime' ? '' : 'none');
        document.querySelectorAll('.entry-vacation').forEach(el => el.style.display = type === 'vacation' ? '' : 'none');
        document.querySelectorAll('.entry-tax').forEach(el => el.style.display = type === 'employer_tax' ? '' : 'none');
    };
    typeSelect?.addEventListener('change', toggleFields);
    toggleFields();
});
</script>
