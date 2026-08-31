<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CondominiumSubscriptionDocument extends Model
{
    protected $fillable = [
        'condominium_subscription_id',
        'uploaded_by',
        'title',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(CondominiumSubscription::class, 'condominium_subscription_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
