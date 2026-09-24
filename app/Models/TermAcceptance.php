<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TermAcceptance extends Model
{
    protected $fillable = [
        'user_id',
        'term_id',
        'term_version_id',
        'accepted_at',
        'ip_address',
        'user_agent',
        'content_hash',
    ];

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(TermVersion::class, 'term_version_id');
    }
}
