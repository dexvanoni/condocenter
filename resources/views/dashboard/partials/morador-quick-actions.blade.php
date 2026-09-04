@php
    use App\Helpers\SidebarHelper;
    $user = Auth::user();
    $chargesCount = ($chargesPendentes->count() ?? 0) + ($chargesAtrasadas->count() ?? 0);
    $isRestricted = $defaulterRestriction['active'] ?? false;
@endphp

<div class="md-quick-grid fade-in">
    @if($chargesCount > 0)
    <a href="{{ route('my-charges.index', ['status' => ($chargesAtrasadas->count() ?? 0) > 0 ? 'overdue' : 'pending']) }}" class="md-quick-tile">
        @if($chargesCount > 0)<span class="md-quick-tile__badge">{{ $chargesCount }}</span>@endif
        <span class="md-quick-tile__icon md-quick-tile__icon--pay"><i class="bi bi-credit-card"></i></span>
        <span>Pagar</span>
    </a>
    @endif

    @if(Route::has('reservations.index') && SidebarHelper::canMakeReservations($user) && !$isRestricted)
    <a href="{{ route('reservations.index') }}" class="md-quick-tile">
        <span class="md-quick-tile__icon md-quick-tile__icon--reserve"><i class="bi bi-calendar-plus"></i></span>
        <span>Reservar</span>
    </a>
    @endif

    @if(Route::has('packages.index'))
    <a href="{{ route('packages.index') }}" class="md-quick-tile">
        @if(($encomendas->count() ?? 0) > 0)<span class="md-quick-tile__badge">{{ $encomendas->count() }}</span>@endif
        <span class="md-quick-tile__icon md-quick-tile__icon--package"><i class="bi bi-box-seam"></i></span>
        <span>Encomendas</span>
    </a>
    @endif

    @if(Route::has('assemblies.index') && ($assembliesPendentes->count() ?? 0) > 0 && !$isRestricted)
    <a href="{{ route('assemblies.index') }}" class="md-quick-tile">
        <span class="md-quick-tile__badge">{{ $assembliesPendentes->count() }}</span>
        <span class="md-quick-tile__icon md-quick-tile__icon--assembly"><i class="bi bi-check2-square"></i></span>
        <span>Votar</span>
    </a>
    @endif

    @if(Route::has('syndic-conversations.start'))
    <a href="{{ route('syndic-conversations.start') }}" class="md-quick-tile">
        <span class="md-quick-tile__icon md-quick-tile__icon--message"><i class="bi bi-chat-dots"></i></span>
        <span>Síndico</span>
    </a>
    @endif

    @if(Route::has('marketplace.index') && SidebarHelper::canAccessModule($user, 'marketplace') && !$isRestricted)
    <a href="{{ route('marketplace.index') }}" class="md-quick-tile">
        <span class="md-quick-tile__icon md-quick-tile__icon--market"><i class="bi bi-shop"></i></span>
        <span>Marketplace</span>
    </a>
    @endif

    <a href="{{ route('my-charges.index') }}" class="md-quick-tile">
        <span class="md-quick-tile__icon md-quick-tile__icon--charges"><i class="bi bi-receipt"></i></span>
        <span>Cobranças</span>
    </a>
</div>
