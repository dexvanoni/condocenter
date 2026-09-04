@if(($defaulterRestriction['active'] ?? false))
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-danger shadow-sm">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-shield-exclamation"></i> Acesso restrito por inadimplência</h5>
            </div>
            <div class="card-body">
                <p class="mb-3">
                    O condomínio restringe moradores com cobranças vencidas. Enquanto houver débitos em atraso, as funções abaixo ficam bloqueadas:
                </p>
                <ul class="mb-4">
                    @foreach($defaulterRestriction['restrictions'] as $restriction)
                        <li class="text-danger-emphasis">{{ $restriction }}</li>
                    @endforeach
                </ul>

                <h6 class="mb-3"><i class="bi bi-receipt-cutoff"></i> Débitos vencidos</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Vencimento</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($defaulterRestriction['overdue_charges'] as $charge)
                                <tr>
                                    <td>{{ $charge->title }}</td>
                                    <td>{{ $charge->due_date?->format('d/m/Y') }}</td>
                                    <td class="text-end">R$ {{ number_format($charge->amount, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2">Total em atraso</th>
                                <th class="text-end text-danger">R$ {{ number_format($defaulterRestriction['total_overdue'], 2, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <a href="{{ $defaulterRestriction['regularize_url'] }}" class="btn btn-danger">
                    <i class="bi bi-wallet2"></i> Regularizar débitos
                </a>
            </div>
        </div>
    </div>
</div>
@endif
