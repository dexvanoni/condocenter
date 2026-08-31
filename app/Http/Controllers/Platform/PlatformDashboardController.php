<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Services\PlatformSubscriptionStatsService;

class PlatformDashboardController extends Controller
{
    public function __construct(private PlatformSubscriptionStatsService $stats) {}

    public function index()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $metrics = $this->stats->dashboardMetrics();

        return view('platform.dashboard', compact('metrics'));
    }
}
