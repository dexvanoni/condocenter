<?php

namespace App\Console\Commands;

use App\Services\Ocr\OcrEngineResolver;
use App\Services\Ocr\PaddleOcrService;
use App\Services\Ocr\TesseractOcrService;
use Illuminate\Console\Command;

class DiagnoseOcrCommand extends Command
{
    protected $signature = 'ocr:diagnose';

    protected $description = 'Verifica Tesseract e PaddleOCR no servidor e limpa cache de detecção do Paddle';

    public function handle(
        TesseractOcrService $tesseract,
        PaddleOcrService $paddle,
        OcrEngineResolver $resolver
    ): int {
        $paddle->forgetAvailabilityCache();

        $this->info('Tesseract: ' . ($tesseract->isAvailable() ? 'disponível' : 'indisponível'));
        $this->line('  binário: ' . (config('ocr.tesseract_path') ?: 'PATH'));

        $status = $resolver->paddleStatus(fresh: true);
        $this->info('PaddleOCR: ' . ($status['available'] ? 'disponível' : 'indisponível'));
        $this->line('  python: ' . ($status['python'] ?? config('ocr.paddle_python') ?: '(não resolvido)'));
        if (!empty($status['source'])) {
            $this->line('  origem: ' . $status['source']);
        }
        if (!empty($status['error'])) {
            $this->warn('  ' . $status['error']);
        }
        if ($status['available']) {
            $python = (string) ($status['python'] ?? config('ocr.paddle_python'));
            $paddle->persistInstallationPaths($python);
            if ($paddle->warmRuntime()) {
                $this->comment('  Modelos Paddle pré-carregados (warm-up).');
            }
            $site = $paddle->discoverPythonSitePackages($python);
            if ($site !== null) {
                $this->line('  site-packages: ' . $site);
                $this->comment('  Se a leitura falhar no navegador, adicione ao .env:');
                $this->comment('  PADDLE_OCR_PYTHONPATH=' . str_replace('\\', '/', $site));
            }
            $this->comment('  Atualize a página Meu Condomínio no navegador (F5).');
        }

        return self::SUCCESS;
    }
}
