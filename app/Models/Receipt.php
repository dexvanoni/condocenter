<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Receipt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_id',
        'original_filename',
        'storage_path',
        'mime_type',
        'file_size',
        'description',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    // Relacionamentos
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    // Métodos auxiliares
    public function getUrlAttribute()
    {
        return Storage::url($this->storage_path);
    }

    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    public function delete()
    {
        // Deleta o arquivo do storage ao deletar o registro
        if (Storage::exists($this->storage_path)) {
            Storage::delete($this->storage_path);
        }
        return parent::delete();
    }
}
