<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Services\PlatformAdminInvitationService;
use App\Services\PlatformSubscriptionStatsService;

class PlatformDashboardController extends Controller
{
    public function __construct(
        private PlatformSubscriptionStatsService $stats,
        private PlatformAdminInvitationService $platformAdmins,
    ) {}

    public function index()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $metrics = $this->stats->dashboardMetrics();
        $platformAdmins = $this->platformAdmins->listAdmins();

        return view('platform.dashboard', compact('metrics', 'platformAdmins'));
    }
}
