<?php

namespace App\Providers;

use App\Services\Ocr\OcrEngineResolver;
use Illuminate\Support\ServiceProvider;

class OcrServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OcrEngineResolver::class);
    }
}
