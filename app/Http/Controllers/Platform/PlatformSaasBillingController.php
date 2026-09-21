<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SubscriptionBillingService;
use Illuminate\Http\Request;

class PlatformSaasBillingController extends Controller
{
    public function __construct(private SubscriptionBillingService $billing) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        abort_unless($user instanceof User && $user->isAdmin(), 403);

        $filters = $this->billing->filtersFromRequest($request);
        $report = $this->billing->globalBillingReport($filters);

        return view('platform.billing.index', [
            'billingReport' => $report,
            'billingFilters' => $filters,
            'formAction' => route('platform.billing.index'),
        ]);
    }
}
