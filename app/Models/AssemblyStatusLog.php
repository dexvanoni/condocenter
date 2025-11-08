<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssemblyStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'assembly_id',
        'changed_by',
        'from_status',
        'to_status',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function assembly()
    {
        return $this->belongsTo(Assembly::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}

