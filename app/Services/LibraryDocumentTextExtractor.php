<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PdfParser;

class LibraryDocumentTextExtractor
{
    public function fromPlainContent(?string $content): string
    {
        if ($content === null || $content === '') {
            return '';
        }

        $text = strip_tags($content);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return $this->normalizeWhitespace($text);
    }

    public function fromUploadedFile(UploadedFile $file): string
    {
        $mime = $file->getMimeType() ?? '';
        $extension = strtolower($file->getClientOriginalExtension());

        if ($mime === 'application/pdf' || $extension === 'pdf') {
            return $this->fromPdfPath($file->getRealPath() ?: $file->path());
        }

        if (in_array($extension, ['txt', 'md', 'csv'], true) || str_starts_with($mime, 'text/')) {
            $raw = file_get_contents($file->getRealPath() ?: $file->path());

            return $this->normalizeWhitespace($raw !== false ? $raw : '');
        }

        return '';
    }

    public function fromStoragePath(string $disk, string $path, ?string $mime = null): string
    {
        if (!Storage::disk($disk)->exists($path)) {
            return '';
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = $mime ?? '';

        if ($mime === 'application/pdf' || $extension === 'pdf') {
            $full = Storage::disk($disk)->path($path);

            return $this->fromPdfPath($full);
        }

        if (in_array($extension, ['txt', 'md', 'csv'], true) || str_starts_with($mime, 'text/')) {
            $raw = Storage::disk($disk)->get($path);

            return $this->normalizeWhitespace($raw);
        }

        return '';
    }

    public function fromPdfPath(string $absolutePath): string
    {
        if (!is_readable($absolutePath)) {
            return '';
        }

        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($absolutePath);
            $text = $pdf->getText();
        } catch (\Throwable) {
            return '';
        }

        return $this->normalizeWhitespace($text);
    }

    private function normalizeWhitespace(string $text): string
    {
        $text = preg_replace("/\r\n|\r/", "\n", $text) ?? $text;
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }
}
