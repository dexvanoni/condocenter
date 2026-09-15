<?php

namespace App\Services\Packages;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class LabelImagePreprocessor
{
    /**
     * Persiste a imagem original e retorna caminho relativo no disco public.
     */
    public function storeOriginal(UploadedFile $file, int $condominiumId): string
    {
        $filename = 'label_' . time() . '_' . Str::random(10) . '.' . ($file->getClientOriginalExtension() ?: 'jpg');
        $directory = "packages/labels/{$condominiumId}";
        $path = $directory . '/' . $filename;

        Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));

        return $path;
    }

    /**
     * Mantido por compatibilidade. O primeiro item é a versão principal.
     */
    public function prepareForOcr(string $absoluteOriginalPath): string
    {
        return $this->prepareVariantsForOcr($absoluteOriginalPath)[0];
    }

    /**
     * Gera variantes complementares para OCR sem alterar a imagem original.
     *
     * @return list<string>
     */
    public function prepareVariantsForOcr(string $absoluteOriginalPath): array
    {
        if (!config('ocr.preprocess_enabled', true)) {
            return [$absoluteOriginalPath];
        }

        $variants = [];
        $variants[] = $this->writeVariant($absoluteOriginalPath, sharpen: false);
        $variants[] = $this->writeVariant($absoluteOriginalPath, sharpen: true);

        return array_values(array_unique($variants));
    }

    public function absolutePath(string $relativePublicPath): string
    {
        return Storage::disk('public')->path($relativePublicPath);
    }

    public function cleanupTemp(?string $tempPath): void
    {
        if ($tempPath && is_file($tempPath) && str_contains($tempPath, sys_get_temp_dir())) {
            @unlink($tempPath);
        }
    }

    /**
     * @param  iterable<string>  $paths
     */
    public function cleanupTemps(iterable $paths): void
    {
        foreach ($paths as $path) {
            $this->cleanupTemp($path);
        }
    }

    private function writeVariant(string $source, bool $sharpen): string
    {
        $maxDimension = max(1600, (int) config('ocr.max_dimension', 2400));
        $image = Image::read($source);

        // Não reduzir fotos já menores; ampliar texto pequeno até um patamar útil.
        $largestSide = max($image->width(), $image->height());
        if ($largestSide > $maxDimension) {
            $image->scaleDown(width: $maxDimension, height: $maxDimension);
        } elseif ($largestSide < 1400) {
            $factor = min(2.0, 1600 / max(1, $largestSide));
            $image->scale(
                width: (int) round($image->width() * $factor),
                height: (int) round($image->height() * $factor),
            );
        }

        try {
            $image->greyscale();
        } catch (\Throwable) {
            // O driver pode não implementar grayscale.
        }

        if ($sharpen) {
            try {
                $image->sharpen(8);
            } catch (\Throwable) {
                // Nitidez é apenas uma variante adicional.
            }
        }

        // PNG evita novos artefatos JPEG nas letras pequenas.
        $suffix = $sharpen ? 'sharp' : 'gray';
        $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR
            . 'ocr_' . $suffix . '_' . Str::random(12) . '.png';
        file_put_contents($tempPath, (string) $image->encodeByExtension('png'));

        return $tempPath;
    }
}
