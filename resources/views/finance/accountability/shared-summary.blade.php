<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="summary-card h-100">
            <strong>Saldo Inicial</strong>
            <div>R$ {{ number_format($data['totals']['opening_balance'], 2, ',', '.') }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="summary-card h-100">
            <strong>Entradas (Taxas)</strong>
            <div class="text-success">R$ {{ number_format($data['totals']['charges_income'], 2, ',', '.') }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="summary-card h-100">
            <strong>Entradas (Avulsas)</strong>
            <div class="text-success">R$ {{ number_format($data['totals']['manual_income'], 2, ',', '.') }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="summary-card h-100">
            <strong>Saídas</strong>
            <div class="text-danger">R$ {{ number_format($data['totals']['manual_expense'], 2, ',', '.') }}</div>
        </div>
    </div>
</div>

<div class="mb-4">
    <strong>Resultado do Período:</strong>
    <span class="{{ $data['totals']['balance_period'] >= 0 ? 'text-success' : 'text-danger' }}">
        R$ {{ number_format($data['totals']['balance_period'], 2, ',', '.') }}
    </span>
    <br>
    <strong>Saldo Final:</strong> R$ {{ number_format($data['totals']['closing_balance'], 2, ',', '.') }}
</div>

