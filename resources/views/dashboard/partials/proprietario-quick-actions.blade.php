@php
    use App\Helpers\SidebarHelper;
    use App\Services\UnitOccupancyService;

    $user = Auth::user();
    $ownedUnitIds = app(UnitOccupancyService::class)->ownedUnitIds($user, (int) $user->tenantCondominiumId());
    $pendingChargesCount = 0;

    $occupancy = app(UnitOccupancyService::class);

    if ($ownedUnitIds !== [] && SidebarHelper::canAccessModule($user, 'financial')) {
        $ownerPending = \App\Models\Charge::query()
            ->whereIn('unit_id', $ownedUnitIds)
            ->get()
            ->filter(fn ($charge) => !$occupancy->isMoradorResponsibleCharge($charge));

        $pendingChargesCount = $ownerPending->filter(fn ($c) => in_array($c->effectiveStatus(), ['pending', 'overdue'], true))->count();
    }
@endphp

<div class="md-quick-grid fade-in">
    @if($pendingChargesCount > 0 && Route::has('my-charges.index') && SidebarHelper::canAccessMyChargesIndex($user))
    <a href="{{ route('my-charges.index') }}" class="md-quick-tile">
        <span class="md-quick-tile__badge">{{ $pendingChargesCount }}</span>
        <span class="md-quick-tile__icon md-quick-tile__icon--pay"><i class="bi bi-credit-card"></i></span>
        <span>Financeiro</span>
    </a>
    @elseif(Route::has('my-charges.index') && SidebarHelper::canAccessMyChargesIndex($user))
    <a href="{{ route('my-charges.index') }}" class="md-quick-tile">
        <span class="md-quick-tile__icon md-quick-tile__icon--pay"><i class="bi bi-credit-card"></i></span>
        <span>Financeiro</span>
    </a>
    @endif

    @if(Route::has('syndic-conversations.start') && SidebarHelper::moduleEnabled($user, 'communication') && $user->can('contact_sindico'))
    <a href="{{ route('syndic-conversations.start') }}" class="md-quick-tile">
        <span class="md-quick-tile__icon md-quick-tile__icon--message"><i class="bi bi-chat-dots"></i></span>
        <span>Síndico</span>
    </a>
    @endif

    @if(Route::has('service-orders.index') && SidebarHelper::canAccessModule($user, 'service_orders'))
    <a href="{{ route('service-orders.index') }}" class="md-quick-tile">
        <span class="md-quick-tile__icon md-quick-tile__icon--charges"><i class="bi bi-tools"></i></span>
        <span>Ordem de Serviço</span>
    </a>
    @endif

    @if(Route::has('units.index') && $user->can('view_units'))
    <a href="{{ route('units.index') }}" class="md-quick-tile">
        <span class="md-quick-tile__icon md-quick-tile__icon--package"><i class="bi bi-building"></i></span>
        <span>Minhas unidades</span>
    </a>
    @endif
</div>
