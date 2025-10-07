<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'condominium_id', 'user_id', 'type', 'title', 'message',
        'data', 'is_read', 'read_at', 'channel', 'sent', 'sent_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'sent' => 'boolean',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}
