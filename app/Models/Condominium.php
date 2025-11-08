<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Condominium extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $table = 'condominiums';

    protected $fillable = [
        'name',
        'cnpj',
        'address',
        'city',
        'state',
        'zip_code',
        'phone',
        'email',
        'description',
        'is_active',
        'marketplace_allow_agregados',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'marketplace_allow_agregados' => 'boolean',
    ];

    // Relacionamentos
    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function charges()
    {
        return $this->hasMany(Charge::class);
    }

    public function spaces()
    {
        return $this->hasMany(Space::class);
    }

    public function reservations()
    {
        return $this->hasManyThrough(Reservation::class, Space::class);
    }

    public function marketplaceItems()
    {
        return $this->hasMany(MarketplaceItem::class);
    }

    public function entries()
    {
        return $this->hasMany(Entry::class);
    }

    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    public function assemblies()
    {
        return $this->hasMany(Assembly::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function bankStatements()
    {
        return $this->hasMany(BankStatement::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
