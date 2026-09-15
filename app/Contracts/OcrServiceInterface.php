<?php

namespace App\Contracts;

use App\DTO\OcrResult;

interface OcrServiceInterface
{
    /**
     * Extrai texto de uma imagem de etiqueta.
     *
     * @param  string  $imagePath  Caminho absoluto do arquivo de imagem
     */
    public function extract(string $imagePath): OcrResult;

    public function isAvailable(): bool;
}
