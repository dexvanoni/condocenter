<div id="accessAlertsContainer">
@if(($accessAlerts ?? collect())->isNotEmpty())
<div class="row mb-4">
    @foreach($accessAlerts as $alert)
    <div class="col-12">
        <div class="widget-notification {{ $alert->type === 'access_prohibition_critical' ? 'critical' : ($alert->type === 'access_denied' ? 'danger' : 'success') }} fade-in access-alert-item" data-notification-id="{{ $alert->id }}">
            <div class="d-flex align-items-center flex-wrap gap-3">
                <i class="bi {{ $alert->type === 'access_prohibition_critical' ? 'bi-exclamation-octagon-fill' : ($alert->type === 'access_denied' ? 'bi-x-octagon-fill' : 'bi-door-open-fill') }} fs-3"></i>
                <div class="flex-grow-1">
                    <h6 class="mb-1">{{ $alert->title }}</h6>
                    <p class="mb-0">{{ $alert->message }}</p>
                    <small class="text-muted">{{ $alert->created_at->diffForHumans() }}</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary btn-mark-access-alert" data-id="{{ $alert->id }}">
                    <i class="bi bi-check2"></i> Ok
                </button>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
</div>
