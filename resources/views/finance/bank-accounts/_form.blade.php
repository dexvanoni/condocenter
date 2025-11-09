@php
    $types = [
        'checking' => 'Conta Corrente',
        'savings' => 'Poupança',
        'payment' => 'Conta Pagamento',
        'other' => 'Outro',
    ];

    $account ??= null;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nome interno *</label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $account->name ?? '') }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Instituição</label>
        <input type="text" name="institution" class="form-control @error('institution') is-invalid @enderror"
               value="{{ old('institution', $account->institution ?? '') }}">
        @error('institution')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Titular</label>
        <input type="text" name="holder_name" class="form-control @error('holder_name') is-invalid @enderror"
               value="{{ old('holder_name', $account->holder_name ?? '') }}">
        @error('holder_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">CPF/CNPJ</label>
        <input type="text" name="document_number" class="form-control @error('document_number') is-invalid @enderror"
               value="{{ old('document_number', $account->document_number ?? '') }}">
        @error('document_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Banco</label>
        <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror"
               value="{{ old('bank_name', $account->bank_name ?? '') }}">
        @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Agência</label>
        <input type="text" name="agency" class="form-control @error('agency') is-invalid @enderror"
               value="{{ old('agency', $account->agency ?? '') }}">
        @error('agency')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Conta</label>
        <input type="text" name="account" class="form-control @error('account') is-invalid @enderror"
               value="{{ old('account', $account->account ?? '') }}">
        @error('account')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Tipo *</label>
        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
            @foreach($types as $value => $label)
                <option value="{{ $value }}" {{ old('type', $account->type ?? 'checking') === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Chave PIX</label>
        <input type="text" name="pix_key" class="form-control @error('pix_key') is-invalid @enderror"
               value="{{ old('pix_key', $account->pix_key ?? '') }}">
        @error('pix_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Saldo atual</label>
        <div class="input-group">
            <span class="input-group-text">R$</span>
            <input type="number" step="0.01" min="0" name="current_balance"
                   class="form-control @error('current_balance') is-invalid @enderror"
                   value="{{ old('current_balance', number_format($account->current_balance ?? 0, 2, '.', '')) }}">
        </div>
        @error('current_balance')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Data referência do saldo</label>
        <input type="date" name="balance_updated_at"
               class="form-control @error('balance_updated_at') is-invalid @enderror"
               value="{{ old('balance_updated_at', optional(optional($account)->balance_updated_at)->format('Y-m-d')) }}">
        @error('balance_updated_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Status</label>
        <div class="form-check form-switch">
            <input type="checkbox" class="form-check-input" id="active" name="active"
                   value="1" {{ old('active', $account->active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="active">Conta ativa</label>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">Anotações</label>
        <textarea class="form-control @error('notes') is-invalid @enderror" rows="3"
                  name="notes">{{ old('notes', $account->notes ?? '') }}</textarea>
        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    @if(isset($account))
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">Histórico de saldo</h6>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Novo saldo (R$)</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="history_balance" value="{{ old('history_balance') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Data</label>
                            <input type="date" class="form-control" name="history_recorded_at" value="{{ old('history_recorded_at') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Referência</label>
                            <input type="text" class="form-control" name="history_reference" placeholder="Ex: Extrato banco" value="{{ old('history_reference') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Notas</label>
                            <input type="text" class="form-control" name="history_notes" placeholder="Informações adicionais" value="{{ old('history_notes') }}">
                        </div>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" value="1" id="register_balance_history" name="register_balance_history"
                               {{ old('register_balance_history') ? 'checked' : '' }}>
                        <label class="form-check-label" for="register_balance_history">
                            Registrar valores acima no histórico ao salvar
                        </label>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

