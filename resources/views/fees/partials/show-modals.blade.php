@can('manage_transactions')
    @php
        $paymentMethods = [
            'cash' => 'Dinheiro',
            'pix' => 'PIX',
            'bank_transfer' => 'Transferência bancária',
            'credit_card' => 'Cartão de crédito',
            'debit_card' => 'Cartão de débito',
            'boleto' => 'Boleto',
            'payroll' => 'Desconto em folha',
            'other' => 'Outro',
        ];
    @endphp

    <div class="modal fade" id="markAllPaidModal" tabindex="-1" aria-labelledby="markAllPaidModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('fees.charges.mark-all-paid', $fee) }}">
                @csrf
                <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="markAllPaidModalLabel">Efetivar pagamentos (todos)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">Marcará todas as cobranças pendentes desta taxa como pagas.</p>
                        <div class="mb-3">
                            <label class="form-label">Data do pagamento</label>
                            <input type="date" name="paid_at" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Forma de pagamento</label>
                            <select name="payment_method" class="form-select" required>
                                @foreach($paymentMethods as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observações (opcional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Informações adicionais"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Confirmar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @foreach($charges as $charge)
        <div class="modal fade" id="markPaidModal-{{ $charge->id }}" tabindex="-1" aria-labelledby="markPaidModalLabel-{{ $charge->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('charges.mark-paid', $charge) }}">
                    @csrf
                    <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="markPaidModalLabel-{{ $charge->id }}">Efetivar pagamento</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">
                                <strong>{{ $charge->title }}</strong><br>
                                {{ optional($charge->unit)->full_identifier ?? '—' }} ·
                                R$ {{ number_format($charge->amount, 2, ',', '.') }}
                            </p>
                            <div class="mb-3">
                                <label class="form-label">Data do pagamento</label>
                                <input type="date" name="paid_at" class="form-control" value="{{ optional($charge->due_date)->format('Y-m-d') ?? now()->format('Y-m-d') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Forma de pagamento</label>
                                @php
                                    $defaultPaymentMethod = $charge->metadata['payment_channel'] ?? 'system';
                                    if ($defaultPaymentMethod === 'system') {
                                        $defaultPaymentMethod = 'pix';
                                    }
                                @endphp
                                <select name="payment_method" class="form-select" required>
                                    @foreach($paymentMethods as $value => $label)
                                        <option value="{{ $value }}" {{ $defaultPaymentMethod === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Observações (opcional)</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Informações complementares sobre o pagamento"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success">Confirmar pagamento</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="revokePayrollModal-{{ $charge->id }}" tabindex="-1" aria-labelledby="revokePayrollModalLabel-{{ $charge->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('charges.revoke-payroll', $charge) }}">
                    @csrf
                    <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="revokePayrollModalLabel-{{ $charge->id }}">Revogar desconto em folha</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">Esta ação desfaz o lançamento automático via folha e reabre a cobrança como pendente.</p>
                            <div class="mb-3">
                                <label class="form-label">Motivo (opcional)</label>
                                <textarea name="reason" class="form-control" rows="3" placeholder="Explique o motivo da revogação, se necessário."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-warning">Revogar desconto</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endcan

@can('manage_charges')
@if($fee->hasPaidCharges() && !$fee->isInvalidated())
<div class="modal fade" id="invalidateFeeModal" tabindex="-1" aria-labelledby="invalidateFeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST" action="{{ route('fees.invalidate', $fee) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="invalidateFeeModalLabel">
                        <i class="bi bi-exclamation-triangle"></i> Invalidar taxa
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Atenção!</strong> Esta ação irá:
                        <ul class="mb-0 mt-2">
                            <li>Invalidar a taxa (não poderá mais ser editada)</li>
                            <li>Debitar do caixa o valor total pago: <strong>R$ {{ number_format($fee->paidCharges()->sum('amount'), 2, ',', '.') }}</strong></li>
                            <li>Criar despesas na prestação de contas para cada cobrança paga</li>
                            <li>Notificar todos os moradores que pagaram esta taxa</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Motivo da invalidação <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="4" placeholder="Descreva o motivo (mínimo 10 caracteres)" required minlength="10">{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nova taxa (opcional)</label>
                        <select name="new_fee_id" class="form-select">
                            <option value="">Selecione uma nova taxa para substituir esta (opcional)</option>
                            @foreach(\App\Models\Fee::where('condominium_id', $fee->condominium_id)
                                ->where('id', '!=', $fee->id)
                                ->where('active', true)
                                ->orderBy('name')
                                ->get() as $newFee)
                                <option value="{{ $newFee->id }}">{{ $newFee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja invalidar esta taxa? Esta ação não pode ser desfeita.');">
                        <i class="bi bi-x-circle"></i> Confirmar invalidação
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endcan
