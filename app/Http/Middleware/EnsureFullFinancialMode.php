<?php

namespace App\Http\Middleware;

use App\Helpers\SidebarHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFullFinancialMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->getActiveCondominiumId()) {
            return $next($request);
        }

        if (SidebarHelper::isFinancialSimplified($user)) {
            abort(403, 'Este recurso não está disponível no ambiente financeiro simplificado.');
        }

        return $next($request);
    }
}
