<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class FileUploadService
{
    /**
     * Faz upload de foto de usuário
     *
     * @param UploadedFile $file
     * @param int|null $userId
     * @return string Path do arquivo
     */
    public function uploadUserPhoto(UploadedFile $file, ?int $userId = null): string
    {
        return $this->uploadPhoto($file, 'photos/users', $userId ? "user_{$userId}" : null);
    }

    /**
     * Faz upload de foto de unidade
     *
     * @param UploadedFile $file
     * @param int|null $unitId
     * @return string Path do arquivo
     */
    public function uploadUnitPhoto(UploadedFile $file, ?int $unitId = null): string
    {
        return $this->uploadPhoto($file, 'photos/units', $unitId ? "unit_{$unitId}" : null);
    }

    /**
     * Faz upload genérico de foto
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string|null $prefix
     * @return string Path do arquivo
     */
    protected function uploadPhoto(UploadedFile $file, string $directory, ?string $prefix = null): string
    {
        // Gera nome único para o arquivo
        $filename = ($prefix ? $prefix . '_' : '') . time() . '_' . Str::random(10) . '.jpg';
        $path = $directory . '/' . $filename;

        // Redimensiona e otimiza a imagem
        $image = Image::read($file->getRealPath());
        
        // Mantém proporção e redimensiona se maior que 800px
        $image->scale(width: 800, height: 800);

        // Salva no storage como JPG
        Storage::disk('public')->put($path, $image->encodeByExtension('jpg', quality: 85));

        return $path;
    }

    /**
     * Deleta foto do storage
     *
     * @param string|null $path
     * @return bool
     */
    public function deletePhoto(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }

    /**
     * Retorna URL pública da foto
     *
     * @param string|null $path
     * @param string $default
     * @return string
     */
    public function getPhotoUrl(?string $path, string $default = '/images/default-avatar.png'): string
    {
        if (!$path) {
            return asset($default);
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::url($path);
        }

        return asset($default);
    }
}

