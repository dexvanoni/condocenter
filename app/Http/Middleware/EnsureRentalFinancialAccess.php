<?php

namespace App\Http\Middleware;

use App\Services\UnitOccupancyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRentalFinancialAccess
{
    public function __construct(
        private readonly UnitOccupancyService $unitOccupancyService,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $this->unitOccupancyService->canAccessFinancialModule($user)) {
            return $next($request);
        }

        $message = 'O módulo financeiro é de responsabilidade do proprietário em imóveis de aluguel. '
            . 'Multas e taxas de reserva podem ser quitadas em Minhas pendências.';

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['error' => $message], 403);
        }

        return redirect()
            ->route('dashboard')
            ->with('error', $message);
    }
}
