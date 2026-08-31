<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessListItem extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ENTERED = 'entered';
    public const STATUS_DENIED = 'denied';

    protected $fillable = [
        'access_list_group_id',
        'visitor_name',
        'status',
        'processed_by',
        'processed_at',
        'porteiro_notes',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(AccessListGroup::class, 'access_list_group_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
