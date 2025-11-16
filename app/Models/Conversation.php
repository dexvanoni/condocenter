<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'condominium_id',
        'created_by',
        'subject',
        'type',
        'priority',
        'is_active',
        'is_closed',
        'expires_at',
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_closed' => 'boolean',
        'expires_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(ConversationRecipient::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function meeting(): HasOne
    {
        return $this->hasOne(Meeting::class);
    }
}


