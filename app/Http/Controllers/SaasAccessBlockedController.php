<?php

namespace App\Http\Controllers;

use App\Services\SaasCondominiumAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SaasAccessBlockedController extends Controller
{
    public function __construct(private SaasCondominiumAccessService $access) {}

    public function show(): View|RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $context = $this->access->blockedContext($user);

        if (!$context) {
            return redirect()->route('dashboard');
        }

        return view('auth.saas-access-blocked', [
            'condominium' => $context['condominium'],
            'subscriptionStatus' => $context['status_label'],
            'supportContact' => $this->access->supportContact(),
        ]);
    }
}
