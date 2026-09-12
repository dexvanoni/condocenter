<?php

namespace App\Http\Middleware;

use App\Helpers\SidebarHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCondominiumModule
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();

        if (! $user || ! SidebarHelper::moduleEnabled($user, $module)) {
            $message = 'Este módulo não está habilitado neste condomínio.';

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => $message], 403);
            }

            return redirect()
                ->route('dashboard')
                ->with('error', $message);
        }

        return $next($request);
    }
}
