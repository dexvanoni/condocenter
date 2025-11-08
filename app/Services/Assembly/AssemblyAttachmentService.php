<?php

namespace App\Services\Assembly;

use App\Models\Assembly;
use App\Models\AssemblyAttachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AssemblyAttachmentService
{
    private const ALLOWED_EXTENSIONS = ['png', 'jpg', 'jpeg', 'webp', 'pdf'];
    private const DEFAULT_COLLECTION = 'documents';

    public function storeMany(Assembly $assembly, array $files, User $uploadedBy, string $collection = self::DEFAULT_COLLECTION): array
    {
        $attachments = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $attachments[] = $this->store($assembly, $file, $uploadedBy, $collection);
            }
        }

        return $attachments;
    }

    public function store(Assembly $assembly, UploadedFile $file, User $uploadedBy, string $collection = self::DEFAULT_COLLECTION): AssemblyAttachment
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw ValidationException::withMessages([
                'attachments' => "Extensão {$extension} não suportada. Tipos permitidos: " . implode(', ', self::ALLOWED_EXTENSIONS),
            ]);
        }

        $directory = sprintf('assemblies/%d/%s', $assembly->id, $collection);
        $filename = Str::uuid() . '.' . $extension;
        $path = $file->storeAs($directory, $filename, 'public');

        return $assembly->attachments()->create([
            'uploaded_by' => $uploadedBy->id,
            'collection' => $collection,
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    public function delete(AssemblyAttachment $attachment): void
    {
        if (Storage::disk($attachment->disk ?? 'public')->exists($attachment->path)) {
            Storage::disk($attachment->disk ?? 'public')->delete($attachment->path);
        }

        $attachment->delete();
    }
}

