<?php

namespace App\Http\Middleware;

use App\Helpers\SidebarHelper;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictDefaulterNavigation
{
    /**
     * @var array<int, string>
     */
    private const ALLOWED_ROUTE_PATTERNS = [
        'dashboard',
        'my-charges.*',
        'charges.data',
        'charges.show',
        'charges.receipt',
        'charges.checkout',
        'charges.pay-card',
        'charges.payment-status',
        'tenant-payables.*',
        'syndic-conversations.*',
        'profile.*',
        'password.change',
        'password.change.update',
        'condominium.switch',
        'condominium.current',
        'logout',
        'panic.*',
        'notifications.show',
        'api.charges.index',
        'api.charges.show',
        'api.conversations.syndic.*',
        'api.messages.index',
        'api.messages.show',
        'api.messages.store',
        'api.messages.read',
        'api.notifications.*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user instanceof User || !SidebarHelper::isDefaulterMenuLocked($user)) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($routeName && $this->isAllowedRoute($routeName)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Seu acesso está restrito por inadimplência. Regularize suas cobranças ou contate o síndico.',
            ], 403);
        }

        return redirect()
            ->route('dashboard')
            ->with('error', 'Seu acesso está restrito por inadimplência. Utilize Minhas Cobranças ou Fale com o Síndico.');
    }

    private function isAllowedRoute(string $routeName): bool
    {
        foreach (self::ALLOWED_ROUTE_PATTERNS as $pattern) {
            if ($routeName === $pattern || str($routeName)->is($pattern)) {
                return true;
            }
        }

        return false;
    }
}
