<?php

namespace App\Http\Middleware;

use App\Services\LeaseContractService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveRentalLease
{
    public function __construct(
        private readonly LeaseContractService $leaseContractService,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if ($user->isSindico() || $user->isAdmin() || $user->isPorteiro()) {
            return $next($request);
        }

        $unit = $this->leaseContractService->resolveRentalUnitForTenant($user);

        if (!$unit || $this->leaseContractService->leaseIsActive($unit)) {
            return $next($request);
        }

        if ((int) $unit->owner_user_id === (int) $user->id) {
            return $next($request);
        }

        if (!$user->isMorador() && !$user->isAgregado()) {
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->withErrors([
                'email' => 'O contrato de locação desta unidade encerrou. Seu acesso foi suspenso. Procure o proprietário do imóvel.',
            ]);
    }
}
