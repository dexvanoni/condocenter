<?php

namespace App\Http\Middleware;

use App\Services\ActiveCondominiumService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSaasSubscription
{
    public function __construct(private ActiveCondominiumService $activeCondominiumService) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (!config('saas.enforce_subscription', true)) {
            return $next($request);
        }

        $user = $request->user();

        if (!$user || $user->isAdmin()) {
            return $next($request);
        }

        if ($request->routeIs(
            'syndic-subscription.*',
            'logout',
            'profile.*',
            'password.*',
            'condominium.switch',
            'condominiums.index',
        )) {
            return $next($request);
        }

        $condominium = $this->activeCondominiumService->getActiveCondominium($user) ?? $user->condominium;

        if (!$condominium) {
            return $next($request);
        }

        $subscription = $condominium->subscription;

        if (!$subscription) {
            return $next($request);
        }

        if ($subscription->isAccessAllowed()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Assinatura do condomínio inativa. Entre em contato com a administração ou regularize o pagamento.',
                'subscription_status' => $subscription->status,
            ], 402);
        }

        return redirect()
            ->route('syndic-subscription.show')
            ->with('error', 'O acesso ao sistema está suspenso. Regularize a assinatura do condomínio para continuar.');
    }
}
