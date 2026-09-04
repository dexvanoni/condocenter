<div class="landing-carousel" @if(!empty($carouselId)) id="{{ $carouselId }}" @endif data-landing-carousel>
    <div class="landing-carousel-viewport">
        <div class="landing-carousel-track">
            {{ $slot }}
        </div>
    </div>
    <div class="landing-carousel-controls">
        <button type="button" class="landing-carousel-btn" data-carousel-prev aria-label="Anterior">
            <i class="bi bi-chevron-left"></i>
        </button>
        <button type="button" class="landing-carousel-btn" data-carousel-next aria-label="Próximo">
            <i class="bi bi-chevron-right"></i>
        </button>
    </div>
</div>
