<?php

namespace App\Providers;

use App\Models\Pet;
use App\Models\ServiceProvider as AccessServiceProvider;
use App\Models\Unit;
use App\Models\User as UserModel;
use App\Services\AccessAlertService;
use App\Services\RideAlertService;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.ambiente') === 'ngrok') {
            URL::forceScheme('https');

            $rootUrl = rtrim((string) config('app.url'), '/');
            if ($rootUrl !== '') {
                URL::forceRootUrl($rootUrl);
            }
        }

        $this->registerTenantRouteBindings();

        View::composer(['dashboard.*', 'access-control.*'], function ($view) {
            $user = Auth::user();
            $rideAlerts = collect();
            $accessAlerts = collect();

            if ($user) {
                $rideAlerts = app(RideAlertService::class)->unreadPublishedAlerts($user);
                $accessAlerts = app(AccessAlertService::class)->unreadAccessAlerts($user);
            }

            $view->with('rideAlerts', $rideAlerts);
            $view->with('accessAlerts', $accessAlerts);
        });
    }

    protected function registerTenantRouteBindings(): void
    {
        $scopeByTenant = function ($query) {
            if ($tenantId = TenantContext::id()) {
                $query->where('condominium_id', $tenantId);
            }

            return $query;
        };

        Route::bind('unit', function (string $value) use ($scopeByTenant) {
            return $scopeByTenant(Unit::query()->whereKey($value))->firstOrFail();
        });

        Route::bind('user', function (string $value) use ($scopeByTenant) {
            return $scopeByTenant(UserModel::query()->whereKey($value))->firstOrFail();
        });

        Route::bind('pet', function (string $value) use ($scopeByTenant) {
            return $scopeByTenant(Pet::query()->whereKey($value))->firstOrFail();
        });

        Route::bind('provider', function (string $value) use ($scopeByTenant) {
            return $scopeByTenant(AccessServiceProvider::query()->whereKey($value))->firstOrFail();
        });
    }
}
