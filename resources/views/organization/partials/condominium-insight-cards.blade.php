@php
    $insights = $insights ?? collect();
@endphp

@if($condominiums->isNotEmpty())
    <section class="mb-4" aria-labelledby="org-condo-insights-title">
        <div class="d-flex flex-wrap justify-content-between align-items-baseline gap-2 mb-3">
            <h2 id="org-condo-insights-title" class="org-condo-insights-title mb-0">Condomínios sob sua responsabilidade</h2>
            <span class="text-muted small">{{ $condominiums->count() }} {{ $condominiums->count() === 1 ? 'condomínio' : 'condomínios' }}</span>
        </div>

        <div class="row g-3">
            @foreach($condominiums as $condo)
                @php
                    $insight = $insights->get($condo->id, [
                        'users' => (int) $condo->users_count,
                        'fines_count' => 0,
                        'fines_amount' => 0,
                        'overdue_units' => 0,
                        'compliance_rate' => null,
                        'health' => 'empty',
                        'health_label' => 'Sem unidades',
                    ]);
                    $rate = $insight['compliance_rate'];
                    $barWidth = $rate === null ? 0 : min(100, max(0, $rate));
                @endphp
                <div class="col-md-6 col-xl-4">
                    <article class="org-condo-card h-100">
                        <header class="org-condo-card__head">
                            <div class="min-w-0">
                                <h3 class="org-condo-card__name text-truncate">{{ $condo->name }}</h3>
                                <p class="org-condo-card__meta mb-0">
                                    @if($condo->city || $condo->state)
                                        {{ $condo->city }}{{ $condo->city && $condo->state ? ' / ' : '' }}{{ $condo->state }}
                                    @else
                                        Local não informado
                                    @endif
                                </p>
                            </div>
                            <span class="org-condo-card__status {{ $condo->is_active ? 'is-active' : 'is-inactive' }}">
                                {{ $condo->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </header>

                        <dl class="org-condo-card__metrics">
                            <div>
                                <dt>Usuários</dt>
                                <dd>{{ number_format($insight['users'], 0, ',', '.') }}</dd>
                            </div>
                            <div>
                                <dt>Multas</dt>
                                <dd>
                                    @if((int) $insight['fines_count'] === 0)
                                        Nenhuma
                                    @else
                                        {{ number_format($insight['fines_count'], 0, ',', '.') }}
                                        <span>R$ {{ number_format($insight['fines_amount'], 2, ',', '.') }}</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt>Saúde financeira</dt>
                                <dd class="is-{{ $insight['health'] }}">
                                    {{ $insight['health_label'] }}
                                    @if($rate !== null)
                                        <span>{{ number_format($rate, 1, ',', '.') }}%</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>

                        <div class="org-condo-card__bar" role="img" aria-label="Adimplência {{ $rate === null ? 'indisponível' : number_format($rate, 1, ',', '.').'%' }}">
                            <span class="is-{{ $insight['health'] }}" style="width: {{ $barWidth }}%"></span>
                        </div>
                        <p class="org-condo-card__hint mb-0">
                            @if($rate === null)
                                Cadastre unidades para calcular a adimplência.
                            @elseif((int) $insight['overdue_units'] === 0)
                                Nenhuma unidade em atraso.
                            @else
                                {{ number_format($insight['overdue_units'], 0, ',', '.') }} {{ (int) $insight['overdue_units'] === 1 ? 'unidade em atraso' : 'unidades em atraso' }}.
                            @endif
                        </p>

                        @can('enterCondominium', $organization)
                            <form method="POST" action="{{ route('organization.condominiums.enter') }}" class="org-condo-card__action">
                                @csrf
                                <input type="hidden" name="condominium_id" value="{{ $condo->id }}">
                                <button type="submit" class="btn btn-sm btn-link p-0">
                                    Entrar <i class="bi bi-arrow-right"></i>
                                </button>
                            </form>
                        @endcan
                    </article>
                </div>
            @endforeach
        </div>
    </section>
@endif
