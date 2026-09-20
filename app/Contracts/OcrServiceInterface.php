<?php

namespace App\Contracts;

use App\DTO\OcrResult;

interface OcrServiceInterface
{
    /**
     * Extrai texto de uma imagem de etiqueta.
     *
     * @param  string  $imagePath  Caminho absoluto do arquivo de imagem
     * @param  array{user_words?: list<string>}  $hints
     */
    public function extract(string $imagePath, array $hints = []): OcrResult;

    public function isAvailable(): bool;
}
