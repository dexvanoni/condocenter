<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();

        $middleware->validateCsrfTokens(except: [
            'webhooks/asaas',
            'webhooks/asaas/platform',
            'webhooks/asaas/condominium/*',
        ]);

        if (env('AMBIENTE') === 'ngrok') {
            $middleware->trustProxies(at: '*');
        }
        
        // Registrar aliases de middlewares personalizados
        $middleware->alias([
            'check.password' => \App\Http\Middleware\CheckPasswordChange::class,
            'check.profile' => \App\Http\Middleware\CheckActiveProfile::class,
            'check.agregado.permission' => \App\Http\Middleware\CheckAgregadoPermission::class,
            'check.reservation.access' => \App\Http\Middleware\CheckReservationAccess::class,
            'check.module.access' => \App\Http\Middleware\CheckModuleAccess::class,
            'restrict.defaulters' => \App\Http\Middleware\RestrictDefaulters::class,
            'restrict.defaulter.navigation' => \App\Http\Middleware\RestrictDefaulterNavigation::class,
            'condominium.module' => \App\Http\Middleware\EnsureCondominiumModule::class,
            'rental.financial.access' => \App\Http\Middleware\EnsureRentalFinancialAccess::class,
            'rental.tenant.payables' => \App\Http\Middleware\EnsureRentalTenantPayablesAccess::class,
            'rental.lease.active' => \App\Http\Middleware\EnsureActiveRentalLease::class,
            'ensure.full.financial' => \App\Http\Middleware\EnsureFullFinancialMode::class,
            'ensure.saas.subscription' => \App\Http\Middleware\EnsureActiveSaasSubscription::class,
            'ensure.condominium.saas.access' => \App\Http\Middleware\EnsureCondominiumSaasAccess::class,
            'resolve.condominium' => \App\Http\Middleware\ResolveActiveCondominium::class,
            'require.condominium' => \App\Http\Middleware\RequireActiveCondominium::class,
        ]);

        $middleware->web(prepend: [
            \App\Http\Middleware\ResolveCondominiumLandingDomain::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\ResolveActiveCondominium::class,
            \App\Http\Middleware\EnsureActiveRentalLease::class,
        ]);

        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
