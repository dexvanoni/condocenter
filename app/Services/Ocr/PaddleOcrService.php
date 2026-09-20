<?php

namespace App\Services\Ocr;

use App\Contracts\OcrServiceInterface;
use App\DTO\OcrResult;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

/**
 * OCR via PaddleOCR (Python). Requer script e ambiente configurados no servidor.
 */
class PaddleOcrService implements OcrServiceInterface
{
    private ?string $resolvedPython = null;

    public function __construct(
        private readonly LabelOcrResultBuilder $resultBuilder
    ) {
    }

    public function isAvailable(): bool
    {
        return $this->availabilityStatus()['available'];
    }

    /**
     * @return array{available: bool, python: ?string, error: ?string}
     */
    public function availabilityStatus(bool $fresh = false): array
    {
        if (!config('ocr.enabled', true)) {
            return [
                'available' => false,
                'python' => null,
                'error' => 'OCR desabilitado no .env (OCR_ENABLED=false).',
            ];
        }

        if (!is_file($this->scriptPath())) {
            return [
                'available' => false,
                'python' => null,
                'error' => 'Script paddle_label.py não encontrado.',
            ];
        }

        $cacheKey = $this->availabilityCacheKey();

        if ($fresh) {
            Cache::forget($cacheKey);
        } else {
            $cached = Cache::get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $status = $this->probeAvailability();

        $ttl = $status['available']
            ? now()->addMinutes(30)
            : now()->addSeconds(45);

        Cache::put($cacheKey, $status, $ttl);

        return $status;
    }

    public function forgetAvailabilityCache(): void
    {
        Cache::forget($this->availabilityCacheKey());
        $this->resolvedPython = null;

        $marker = $this->availabilityMarkerPath();
        if (is_file($marker)) {
            @unlink($marker);
        }
    }

    private function availabilityCacheKey(): string
    {
        return 'ocr.paddle.status.' . md5((string) config('ocr.paddle_python') . '|' . PHP_OS_FAMILY);
    }

    public function extract(string $imagePath, array $hints = []): OcrResult
    {
        $preview = !empty($hints['preview']);
        if (!is_file($imagePath)) {
            return new OcrResult(extra: ['error' => 'Imagem não encontrada', 'engine' => 'paddle']);
        }

        if (!config('ocr.enabled', true)) {
            return new OcrResult(rawText: '', confidence: null, extra: [
                'error' => 'OCR desabilitado. Use o registro manual.',
                'engine' => 'paddle',
            ]);
        }

        $python = $this->resolvePythonBinary();
        $script = $this->scriptPath();

        if ($python === null || !is_file($script)) {
            $status = $this->availabilityStatus();

            return new OcrResult(rawText: '', confidence: null, extra: [
                'error' => $status['error'] ?? 'PaddleOCR não configurado no servidor.',
                'engine' => 'paddle',
            ]);
        }

        try {
            @set_time_limit((int) config('ocr.paddle_timeout', 120) + 90);

            $run = $this->runPaddleScript($python, $script, $imagePath, $preview);

            if (!$run['ok']) {
                $stderr = trim($run['stderr']);
                $detail = $this->parseProcessErrorMessage($stderr, $run['stdout']);

                Log::warning('PaddleOCR process failed', [
                    'exit' => $run['exit'],
                    'stderr' => mb_substr($stderr, 0, 10000),
                    'stdout' => mb_substr(trim($run['stdout']), 0, 2000),
                    'python' => $python,
                    'image' => basename($imagePath),
                    'runner' => $run['runner'] ?? 'process',
                ]);

                return new OcrResult(rawText: '', confidence: null, extra: [
                    'error' => $detail ?? 'Falha ao executar PaddleOCR. Verifique a instalação no servidor.',
                    'engine' => 'paddle',
                ]);
            }

            $payload = $this->decodeProcessPayload($run['stdout']);
            if (!is_array($payload)) {
                return new OcrResult(rawText: '', confidence: null, extra: [
                    'error' => 'Resposta inválida do PaddleOCR.',
                    'engine' => 'paddle',
                ]);
            }

            $rawText = (string) ($payload['text'] ?? '');
            $confidence = isset($payload['confidence']) ? (float) $payload['confidence'] : null;

            if (trim($rawText) === '') {
                return new OcrResult(rawText: '', confidence: null, extra: [
                    'error' => 'O PaddleOCR não encontrou texto legível na etiqueta.',
                    'engine' => 'paddle',
                ]);
            }

            return $this->resultBuilder->fromRawText($rawText, $confidence, [
                'engine' => 'paddle',
            ]);
        } catch (\Illuminate\Process\Exceptions\ProcessTimedOutException $e) {
            Log::warning('PaddleOCR extraction timed out', [
                'image' => basename($imagePath),
                'preview' => $preview,
            ]);

            return new OcrResult(rawText: '', confidence: null, extra: [
                'error' => 'A leitura com PaddleOCR excedeu o tempo limite. Tente novamente ou use o registro manual.',
                'engine' => 'paddle',
                'timed_out' => true,
            ]);
        } catch (\Throwable $e) {
            Log::error('PaddleOCR extraction failed', [
                'error' => $e->getMessage(),
                'image' => basename($imagePath),
            ]);

            return new OcrResult(rawText: '', confidence: null, extra: [
                'error' => 'Falha ao processar a etiqueta com PaddleOCR.',
                'engine' => 'paddle',
            ]);
        }
    }

    /**
     * @return array{available: bool, python: ?string, error: ?string, source?: string}
     */
    private function probeAvailability(): array
    {
        @set_time_limit((int) config('ocr.paddle_probe_timeout', 45) + 15);

        $python = $this->resolvePythonBinary();
        if ($python !== null) {
            $this->writeAvailabilityMarker($python, $this->discoverPythonSitePackages($python));

            return [
                'available' => true,
                'python' => $python,
                'error' => null,
                'source' => 'runtime',
            ];
        }

        $marker = $this->readAvailabilityMarker();
        if ($marker !== null) {
            return [
                'available' => true,
                'python' => $marker['python'],
                'error' => null,
                'source' => 'diagnose',
            ];
        }

        $configured = $this->normalizePythonPath((string) config('ocr.paddle_python', ''));
        if ($configured !== '' && is_file($configured)) {
            return [
                'available' => true,
                'python' => $configured,
                'error' => null,
                'source' => 'configured',
            ];
        }

        return [
            'available' => false,
            'python' => null,
            'error' => $this->availabilityHint(),
        ];
    }

    private function availabilityHint(): string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return 'Defina PADDLE_OCR_PYTHON com o caminho completo do Python onde você instalou o paddleocr (ex.: C:\\Python313\\python.exe). Depois rode php artisan config:clear e reinicie o servidor.';
        }

        return 'Instale paddleocr no Python do servidor e configure PADDLE_OCR_PYTHON no .env.';
    }

    private function resolvePythonBinary(): ?string
    {
        if ($this->resolvedPython !== null) {
            return $this->resolvedPython;
        }

        foreach ($this->pythonCandidates() as $candidate) {
            if ($this->pythonCanImportPaddle($candidate)) {
                $this->resolvedPython = $this->normalizePythonPath($candidate);

                return $this->resolvedPython;
            }
        }

        $configured = $this->normalizePythonPath((string) config('ocr.paddle_python', ''));
        if ($configured !== '' && is_file($configured)) {
            $this->resolvedPython = $configured;

            return $this->resolvedPython;
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function pythonCandidates(): array
    {
        $candidates = [];

        $configured = trim((string) config('ocr.paddle_python', ''));
        if ($configured !== '') {
            $candidates[] = $configured;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $candidates = array_merge($candidates, [
                'C:\\Python313\\python.exe',
                'C:\\Python312\\python.exe',
                'C:\\Python311\\python.exe',
            ]);

            $localAppData = getenv('LOCALAPPDATA') ?: '';
            if ($localAppData !== '') {
                foreach (glob($localAppData . '\\Programs\\Python\\Python*\\python.exe') ?: [] as $path) {
                    $candidates[] = $path;
                }
            }

            $candidates[] = 'py|-3';
            $candidates[] = 'python';
        } else {
            $candidates[] = 'python3';
            $candidates[] = 'python';
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    private function pythonCanImportPaddle(string $candidate): bool
    {
        if (!is_file($this->scriptPath())) {
            return false;
        }

        $candidate = $this->normalizePythonPath($candidate);

        if ($candidate !== '' && !str_contains($candidate, '|') && !is_file($candidate)) {
            return false;
        }

        try {
            if (function_exists('proc_open')) {
                $timeout = (int) config('ocr.paddle_probe_timeout', 45);
                $result = Process::timeout($timeout)
                    ->env($this->pythonProcessEnvironment($this->normalizePythonPath($candidate)))
                    ->run($this->pythonImportCommand($candidate));

                if ($result->successful()) {
                    return true;
                }

                Log::debug('PaddleOCR python probe failed', [
                    'python' => $candidate,
                    'exit' => $result->exitCode(),
                    'stderr' => mb_substr(trim($result->errorOutput()), 0, 500),
                ]);
            }
        } catch (\Throwable $e) {
            Log::debug('PaddleOCR python probe exception', [
                'python' => $candidate,
                'error' => $e->getMessage(),
            ]);
        }

        return $this->pythonCanImportPaddleViaShell($candidate);
    }

    private function pythonCanImportPaddleViaShell(string $candidate): bool
    {
        if (!function_exists('shell_exec') || str_contains($candidate, '|')) {
            return false;
        }

        $binary = $this->normalizePythonPath($candidate);
        if ($binary === '' || !is_file($binary)) {
            return false;
        }

        $command = escapeshellarg($binary) . ' -c ' . escapeshellarg('import paddleocr; print("ok")') . ' 2>&1';
        $output = shell_exec($command);

        if ($output === null) {
            return false;
        }

        return trim($output) === 'ok';
    }

    /**
     * @return list<string>
     */
    private function pythonImportCommand(string $candidate): array
    {
        if (str_contains($candidate, '|')) {
            return array_merge(explode('|', $candidate), ['-c', 'import paddleocr']);
        }

        return [$candidate, '-c', 'import paddleocr'];
    }

    public function scriptPath(): string
    {
        $configured = trim((string) config('ocr.paddle_script', ''));
        if ($configured !== '') {
            return $configured;
        }

        return base_path('scripts/ocr/paddle_label.py');
    }

    private function normalizePythonPath(string $path): string
    {
        $path = trim(str_replace('/', DIRECTORY_SEPARATOR, $path));

        return $path;
    }

    private function availabilityMarkerPath(): string
    {
        return storage_path('app/ocr/paddle.ok.json');
    }

    /**
     * @return array{python: string, checked_at: string}|null
     */
    private function readAvailabilityMarker(): ?array
    {
        $path = $this->availabilityMarkerPath();
        if (!is_file($path)) {
            return null;
        }

        $payload = json_decode((string) file_get_contents($path), true);
        if (!is_array($payload)) {
            return null;
        }

        $python = $this->normalizePythonPath((string) ($payload['python'] ?? ''));
        $configured = $this->normalizePythonPath((string) config('ocr.paddle_python', ''));
        if ($python === '' || ($configured !== '' && $python !== $configured)) {
            return null;
        }

        $checkedAt = strtotime((string) ($payload['checked_at'] ?? ''));
        if ($checkedAt === false || $checkedAt < time() - 86400) {
            return null;
        }

        $sitePackages = trim((string) ($payload['site_packages'] ?? ''));
        if ($sitePackages !== '') {
            $sitePackages = str_replace('/', DIRECTORY_SEPARATOR, $sitePackages);
        }

        return [
            'python' => $python,
            'checked_at' => (string) $payload['checked_at'],
            'site_packages' => $sitePackages !== '' ? $sitePackages : null,
        ];
    }

    /**
     * @return array<string, string>
     */
    /**
     * @return array{ok: bool, stdout: string, stderr: string, exit: int, runner?: string}
     */
    public function warmRuntime(): bool
    {
        $python = $this->resolvePythonBinary();
        if ($python === null) {
            return false;
        }

        $directory = $this->paddleRuntimeHome();
        $probe = $directory . DIRECTORY_SEPARATOR . '_warm.png';
        if (!is_file($probe)) {
            if (!function_exists('imagecreatetruecolor')) {
                return false;
            }

            $image = imagecreatetruecolor(64, 32);
            if ($image === false) {
                return false;
            }

            $white = imagecolorallocate($image, 255, 255, 255);
            $black = imagecolorallocate($image, 0, 0, 0);
            imagefilledrectangle($image, 0, 0, 63, 31, $white);
            imagestring($image, 3, 4, 8, 'WARM', $black);
            imagepng($image, $probe);
            imagedestroy($image);
        }

        $run = $this->runPaddleScript($python, $this->scriptPath(), $probe, true);

        return $run['ok'];
    }

    private function runPaddleScript(string $python, string $script, string $imagePath, bool $preview = false): array
    {
        $timeout = (int) config('ocr.paddle_timeout', 120);
        if ($preview) {
            $timeout = min($timeout, (int) config('ocr.paddle_preview_timeout', 75));
        }
        $command = [$python, $script, $imagePath];
        $environment = $this->pythonProcessEnvironment($python);

        $result = Process::timeout($timeout)
            ->env($environment)
            ->run($command);

        if ($result->successful()) {
            return [
                'ok' => true,
                'stdout' => $result->output(),
                'stderr' => $result->errorOutput(),
                'exit' => 0,
                'runner' => 'process',
            ];
        }

        if (PHP_OS_FAMILY === 'Windows' && $this->shouldRetryPaddleWithShell($result)) {
            $shell = $this->runPaddleViaWindowsShell($python, $script, $imagePath, $environment);
            if ($shell !== null) {
                return $shell;
            }
        }

        return [
            'ok' => false,
            'stdout' => $result->output(),
            'stderr' => $result->errorOutput(),
            'exit' => $result->exitCode() ?? 1,
            'runner' => 'process',
        ];
    }

    private function shouldRetryPaddleWithShell(\Illuminate\Contracts\Process\ProcessResult $result): bool
    {
        if (!function_exists('shell_exec')) {
            return false;
        }

        $stderr = $result->errorOutput();
        $stdout = $result->output();

        return $result->exitCode() === 2
            || str_contains($stderr, 'WinError')
            || str_contains($stderr, '_overlapped')
            || str_contains($stderr, 'asyncio')
            || str_contains($stdout, 'paddleocr_not_installed');
    }

    /**
     * @param  array<string, string>  $environment
     * @return array{ok: bool, stdout: string, stderr: string, exit: int, runner: string}|null
     */
    private function runPaddleViaWindowsShell(string $python, string $script, string $imagePath, array $environment): ?array
    {
        $setters = [];
        foreach ($environment as $key => $value) {
            $setters[] = 'set "' . $key . '=' . str_replace('"', '""', $value) . '"';
        }

        $command = implode(' && ', array_merge(
            $setters,
            [
                escapeshellarg($python) . ' ' . escapeshellarg($script) . ' ' . escapeshellarg($imagePath),
            ]
        ));

        $output = shell_exec('cmd /C "' . $command . '" 2>&1');
        if ($output === null) {
            return null;
        }

        $stdout = trim($output);
        $payload = $this->decodeProcessPayload($stdout);

        if (is_array($payload) && trim((string) ($payload['text'] ?? '')) !== '') {
            return [
                'ok' => true,
                'stdout' => $stdout,
                'stderr' => '',
                'exit' => 0,
                'runner' => 'shell',
            ];
        }

        if (is_array($payload) && ($payload['error'] ?? '') === 'paddleocr_not_installed') {
            return [
                'ok' => false,
                'stdout' => $stdout,
                'stderr' => '',
                'exit' => 2,
                'runner' => 'shell',
            ];
        }

        if ($stdout !== '' && str_contains($stdout, '"text"')) {
            return [
                'ok' => true,
                'stdout' => $stdout,
                'stderr' => '',
                'exit' => 0,
                'runner' => 'shell',
            ];
        }

        return [
            'ok' => false,
            'stdout' => '',
            'stderr' => $stdout,
            'exit' => 1,
            'runner' => 'shell',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function pythonProcessEnvironment(string $python): array
    {
        $env = PHP_OS_FAMILY === 'Windows'
            ? $this->windowsInheritedEnvironment()
            : [];

        $env['PADDLE_PDX_DISABLE_MODEL_SOURCE_CHECK'] = 'True';
        $env['PADDLE_PDX_ENABLE_MKLDNN_BYDEFAULT'] = '0';
        $env['FLAGS_enable_pir_api'] = '0';
        $env['FLAGS_use_mkldnn'] = '0';
        $env['PYTHONIOENCODING'] = 'utf-8';
        $env['PADDLE_OCR_HOME'] = $this->paddleRuntimeHome();

        $sitePackages = $this->resolvePythonSitePackages($python);
        if ($sitePackages !== null && is_dir($sitePackages)) {
            $inherited = $env['PYTHONPATH'] ?? getenv('PYTHONPATH') ?: '';
            $env['PYTHONPATH'] = $inherited === ''
                ? $sitePackages
                : $sitePackages . PATH_SEPARATOR . $inherited;
        }

        return $env;
    }

    /**
     * Garante variáveis mínimas do Windows para asyncio/socket no subprocesso do Python.
     *
     * @return array<string, string>
     */
    private function windowsInheritedEnvironment(): array
    {
        $keys = [
            'PATH', 'PATHEXT', 'SYSTEMROOT', 'WINDIR', 'COMSPEC',
            'USERPROFILE', 'APPDATA', 'LOCALAPPDATA', 'HOMEDRIVE', 'HOMEPATH',
            'TEMP', 'TMP', 'NUMBER_OF_PROCESSORS', 'PROCESSOR_ARCHITECTURE',
        ];

        $env = [];
        foreach ($keys as $key) {
            $value = getenv($key);
            if (is_string($value) && $value !== '') {
                $env[$key] = $value;
            }
        }

        $systemRoot = $env['SYSTEMROOT'] ?? 'C:\\Windows';
        $system32 = $systemRoot . '\\System32';
        $path = $env['PATH'] ?? '';
        if (!str_contains(strtolower($path), strtolower($system32))) {
            $env['PATH'] = $system32 . ';' . $systemRoot . ';' . $path;
        }

        if (!isset($env['SYSTEMROOT'])) {
            $env['SYSTEMROOT'] = $systemRoot;
        }
        if (!isset($env['WINDIR'])) {
            $env['WINDIR'] = $systemRoot;
        }

        return $env;
    }

    private function paddleRuntimeHome(): string
    {
        $path = storage_path('app/ocr/paddle-runtime');

        foreach ([$path, $path . DIRECTORY_SEPARATOR . 'cache'] as $directory) {
            if (!is_dir($directory)) {
                @mkdir($directory, 0775, true);
            }
        }

        return $path;
    }

    private function resolvePythonSitePackages(string $python): ?string
    {
        $configured = trim((string) config('ocr.paddle_pythonpath', ''));
        if ($configured !== '') {
            $path = str_replace('/', DIRECTORY_SEPARATOR, $configured);

            return is_dir($path) ? $path : null;
        }

        $marker = $this->readAvailabilityMarker();
        if ($marker !== null && !empty($marker['site_packages']) && is_dir($marker['site_packages'])) {
            return $marker['site_packages'];
        }

        return $this->discoverPythonSitePackages($python);
    }

    public function persistInstallationPaths(?string $python = null): void
    {
        $python = $this->normalizePythonPath($python ?? (string) config('ocr.paddle_python', ''));
        if ($python === '' || !is_file($python)) {
            return;
        }

        $this->writeAvailabilityMarker($python, $this->discoverPythonSitePackages($python));
    }

    public function discoverPythonSitePackages(string $python): ?string
    {
        $python = $this->normalizePythonPath($python);
        if ($python === '' || !is_file($python)) {
            return null;
        }

        try {
            $result = Process::timeout((int) config('ocr.paddle_probe_timeout', 45))->run([
                $python,
                '-c',
                'import os, paddleocr; print(os.path.dirname(os.path.dirname(paddleocr.__file__)))',
            ]);

            if (!$result->successful()) {
                return null;
            }

            $path = trim($result->output());
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);

            return $path !== '' && is_dir($path) ? $path : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function paddleNotInstalledHint(): string
    {
        $python = $this->normalizePythonPath((string) config('ocr.paddle_python', ''));
        $site = $this->resolvePythonSitePackages($python) ?? $this->discoverPythonSitePackages($python);

        $hint = 'O PHP do servidor não enxerga o pacote paddleocr no Python configurado. ';

        if ($site !== null) {
            $hint .= 'Defina no .env: PADDLE_OCR_PYTHONPATH=' . str_replace('\\', '/', $site) . ' e rode php artisan config:clear. ';
        } else {
            $hint .= 'No terminal (mesmo usuário do servidor), rode: '
                . ($python !== '' ? $python : 'python')
                . ' -m pip install paddlepaddle paddleocr. ';
        }

        $hint .= 'Ou instale para todos os usuários: '
            . ($python !== '' ? $python : 'python')
            . ' -m pip install paddlepaddle paddleocr (como administrador). Depois: php artisan ocr:diagnose.';

        return $hint;
    }

    private function parseProcessErrorMessage(string $stderr, string $stdout): ?string
    {
        foreach ([$stderr, $stdout] as $chunk) {
            if ($chunk === '') {
                continue;
            }

            $payload = $this->decodeProcessPayload($chunk);
            if (is_array($payload)) {
                $message = trim((string) ($payload['message'] ?? $payload['error'] ?? ''));
                if ($message !== '') {
                    if (($payload['error'] ?? '') === 'paddleocr_not_installed') {
                        return $this->paddleNotInstalledHint();
                    }

                    return 'PaddleOCR: ' . $message;
                }
            }
        }

        if ($stderr !== '') {
            if (preg_match('/OSError:\s*\[WinError \d+\]\s*(.+)$/m', $stderr, $matches)) {
                return 'PaddleOCR: falha ao iniciar o Python no Windows (' . trim($matches[1]) . '). Reinicie o servidor PHP e tente novamente.';
            }

            if (mb_strlen($stderr) < 600) {
                return 'PaddleOCR: ' . $stderr;
            }

            $tail = trim((string) preg_replace('/\s+/', ' ', mb_substr($stderr, -500)));

            return 'PaddleOCR: ' . $tail;
        }

        return null;
    }

    /**
     * O Paddle pode escrever avisos antes/depois do JSON no stderr.
     *
     * @return array<string, mixed>|null
     */
    private function decodeProcessPayload(string $output): ?array
    {
        foreach (array_reverse(preg_split('/\R/', trim($output)) ?: []) as $line) {
            $payload = json_decode(trim($line), true);
            if (is_array($payload)) {
                return $payload;
            }
        }

        return null;
    }

    private function writeAvailabilityMarker(string $python, ?string $sitePackages = null): void
    {
        $directory = storage_path('app/ocr');
        if (!is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }

        if ($sitePackages === null) {
            $sitePackages = $this->discoverPythonSitePackages($python);
        }

        $payload = [
            'python' => $this->normalizePythonPath($python),
            'checked_at' => now()->toIso8601String(),
        ];

        if ($sitePackages !== null && is_dir($sitePackages)) {
            $payload['site_packages'] = str_replace('/', DIRECTORY_SEPARATOR, $sitePackages);
        }

        file_put_contents($this->availabilityMarkerPath(), json_encode($payload, JSON_THROW_ON_ERROR));
    }
}
