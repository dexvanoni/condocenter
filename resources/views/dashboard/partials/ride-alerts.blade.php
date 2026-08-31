@if($rideAlerts->isNotEmpty())
<div class="row mb-4">
    @foreach($rideAlerts as $alert)
    <div class="col-12">
        <div class="widget-notification success fade-in">
            <div class="d-flex align-items-center flex-wrap gap-3">
                <i class="bi bi-car-front-fill fs-3"></i>
                <div class="flex-grow-1">
                    <h6 class="mb-1">{{ $alert->title }}</h6>
                    <p class="mb-0">{{ $alert->message }}</p>
                </div>
                <a
                    href="{{ route('rides.index', ['notification' => $alert->id, 'highlight' => $alert->data['ride_id'] ?? null]) }}"
                    class="btn btn-success"
                >
                    <i class="bi bi-arrow-right-circle"></i> Ver Carona!
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
