<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OCR enabled
    |--------------------------------------------------------------------------
    */
    'enabled' => env('OCR_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Tesseract binary path
    |--------------------------------------------------------------------------
    | Leave empty to use PATH. On Windows/Laragon set e.g.:
    | C:\Program Files\Tesseract-OCR\tesseract.exe
    */
    'tesseract_path' => env('TESSERACT_PATH', ''),

    /*
    |--------------------------------------------------------------------------
    | Language
    |--------------------------------------------------------------------------
    */
    'lang' => env('OCR_LANG', 'por'),

    /*
    |--------------------------------------------------------------------------
    | Timeout (seconds)
    |--------------------------------------------------------------------------
    */
    'timeout' => (int) env('OCR_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Preprocess image before OCR
    |--------------------------------------------------------------------------
    */
    'preprocess_enabled' => env('OCR_PREPROCESS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Max image dimension (px) before OCR
    |--------------------------------------------------------------------------
    */
    'max_dimension' => (int) env('OCR_MAX_DIMENSION', 2400),

    /*
    |--------------------------------------------------------------------------
    | PaddleOCR (Python)
    |--------------------------------------------------------------------------
    | Opcional. Cada condomínio escolhe tesseract ou paddle em Meu Condomínio.
    */
    'paddle_python' => env('PADDLE_OCR_PYTHON') ?: (PHP_OS_FAMILY === 'Windows' ? '' : 'python3'),

    /*
    | Pasta site-packages onde o paddleocr está (Windows: pip --user em AppData\Roaming).
    | O PHP do servidor web pode usar outro perfil; use o caminho retornado por ocr:diagnose.
    */
    'paddle_pythonpath' => env('PADDLE_OCR_PYTHONPATH', ''),

    'paddle_script' => env('PADDLE_OCR_SCRIPT') ?: base_path('scripts/ocr/paddle_label.py'),

    'paddle_timeout' => (int) env('PADDLE_OCR_TIMEOUT', 120),

    'paddle_probe_timeout' => (int) env('PADDLE_OCR_PROBE_TIMEOUT', 45),

    /*
    | Preview na portaria: Tesseract rápido antes do Paddle quando o match já é bom.
    */
    'preview_tesseract_fast_path' => env('OCR_PREVIEW_TESSERACT_FAST_PATH', true),

    /*
    | Limite do Paddle no preview (evita HTTP 524 do Cloudflare ~100s).
    */
    'paddle_preview_timeout' => (int) env('PADDLE_OCR_PREVIEW_TIMEOUT', 75),
];
