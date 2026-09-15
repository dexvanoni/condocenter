<?php

namespace App\Providers;

use App\Contracts\OcrServiceInterface;
use App\Services\Ocr\FakeOcrService;
use App\Services\Ocr\TesseractOcrService;
use Illuminate\Support\ServiceProvider;

class OcrServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OcrServiceInterface::class, function ($app) {
            if ($app->environment('testing') && !$app->bound('ocr.force_tesseract')) {
                return $app->make(FakeOcrService::class);
            }

            return $app->make(TesseractOcrService::class);
        });
    }
}
