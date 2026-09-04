<?php

namespace App\Http\Controllers;

use App\Services\CondominiumLandingService;
use Illuminate\View\View;

class CondominiumLandingPublicController extends Controller
{
    public function __construct(
        private readonly CondominiumLandingService $landingService,
    ) {
    }

    public function show(string $slug): View
    {
        $page = $this->landingService->findPublishedBySlug($slug);

        abort_unless($page, 404);

        $payload = $this->landingService->buildPublicPayload($page);

        return view('landing.show', $payload);
    }
}
