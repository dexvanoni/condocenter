<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use OwenIt\Auditing\Contracts\Auditable;

class User extends Authenticatable implements Auditable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles, SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'condominium_id',
        'unit_id',
        'name',
        'email',
        'password',
        'phone',
        'cpf',
        'photo',
        'qr_code',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Relacionamentos
    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function charges()
    {
        return $this->hasManyThrough(Charge::class, Unit::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function approvedReservations()
    {
        return $this->hasMany(Reservation::class, 'approved_by');
    }

    public function marketplaceItems()
    {
        return $this->hasMany(MarketplaceItem::class, 'seller_id');
    }

    public function pets()
    {
        return $this->hasMany(Pet::class, 'owner_id');
    }

    public function registeredEntries()
    {
        return $this->hasMany(Entry::class, 'registered_by');
    }

    public function registeredPackages()
    {
        return $this->hasMany(Package::class, 'registered_by');
    }

    public function collectedPackages()
    {
        return $this->hasMany(Package::class, 'collected_by');
    }

    public function createdAssemblies()
    {
        return $this->hasMany(Assembly::class, 'created_by');
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'from_user_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'to_user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function credits()
    {
        return $this->hasMany(UserCredit::class);
    }

    public function availableCredits()
    {
        return $this->hasMany(UserCredit::class)->available();
    }

    // Métodos auxiliares
    public function getTotalCredits()
    {
        return $this->credits()->available()->sum('amount');
    }
    public function isSindico(): bool
    {
        return $this->hasRole('Síndico');
    }

    public function isMorador(): bool
    {
        return $this->hasRole('Morador');
    }

    public function isPorteiro(): bool
    {
        return $this->hasRole('Porteiro');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('Administrador');
    }

    public function isConselhoFiscal(): bool
    {
        return $this->hasRole('Conselho Fiscal');
    }

    public function isSecretaria(): bool
    {
        return $this->hasRole('Secretaria');
    }

    public function generateQRCode(): string
    {
        if (!$this->qr_code) {
            $this->qr_code = uniqid('QR-', true);
            $this->save();
        }
        return $this->qr_code;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCondominium($query, $condominiumId)
    {
        return $query->where('condominium_id', $condominiumId);
    }
}
