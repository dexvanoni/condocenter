<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivacyConsent extends Model
{
    public const STATUS_AUTHORIZED = 'authorized';
    public const STATUS_DENIED = 'denied';
    public const STATUS_REVOKED = 'revoked';

    protected $fillable = [
        'user_id',
        'term_version_id',
        'purpose',
        'status',
        'accepted_at',
        'revoked_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function termVersion(): BelongsTo
    {
        return $this->belongsTo(TermVersion::class);
    }
}
