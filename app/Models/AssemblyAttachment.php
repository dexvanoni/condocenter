<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AssemblyAttachment extends Model
{
    use HasFactory;

    protected $appends = ['url'];

    protected $fillable = [
        'assembly_id',
        'uploaded_by',
        'collection',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function assembly()
    {
        return $this->belongsTo(Assembly::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): ?string
    {
        if (!$this->path) {
            return null;
        }

        return Storage::disk($this->disk ?? 'public')->url($this->path);
    }
}

