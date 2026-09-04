<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class Condominium extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $table = 'condominiums';

    protected $fillable = [
        'name',
        'cnpj',
        'address',
        'neighborhood',
        'city',
        'state',
        'zip_code',
        'phone',
        'email',
        'description',
        'is_active',
        'financial_mode',
        'payment_receiving_mode',
        'asaas_api_key',
        'asaas_sandbox',
        'asaas_webhook_email',
        'asaas_webhook_token',
        'asaas_setup_completed_at',
        'marketplace_allow_agregados',
        'restrict_defaulters',
        'occurrence_book_public_enabled',
        'registration_code',
        'whatsapp_enabled',
        'evolution_api_url',
        'evolution_api_key',
        'evolution_instance',
        'whatsapp_notify_groups',
        'whatsapp_announcements_group',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'marketplace_allow_agregados' => 'boolean',
        'restrict_defaulters' => 'boolean',
        'occurrence_book_public_enabled' => 'boolean',
        'whatsapp_enabled' => 'boolean',
        'asaas_sandbox' => 'boolean',
        'asaas_api_key' => 'encrypted',
        'asaas_webhook_token' => 'encrypted',
        'asaas_setup_completed_at' => 'datetime',
        'whatsapp_notify_groups' => 'array',
        'evolution_api_key' => 'encrypted',
    ];

    public function isFinancialSimplified(): bool
    {
        return $this->financial_mode === 'simplified';
    }

    public function isFinancialFull(): bool
    {
        return !$this->isFinancialSimplified();
    }

    public function acceptsOnlinePayments(): bool
    {
        return app(\App\Services\CondominiumAsaasSettingsService::class)->acceptsOnlinePayments($this);
    }

    public function isPlatformReceiving(): bool
    {
        return $this->payment_receiving_mode === 'platform';
    }

    public function accountabilityReportUploads()
    {
        return $this->hasMany(AccountabilityReportUpload::class);
    }

    // Relacionamentos
    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function syndics()
    {
        return $this->belongsToMany(User::class, 'condominium_user')
            ->withTimestamps();
    }

    public function subscription()
    {
        return $this->hasOne(CondominiumSubscription::class);
    }

    public function landingPage()
    {
        return $this->hasOne(CondominiumLandingPage::class);
    }

    public function hasActiveSaasSubscription(): bool
    {
        return (bool) $this->subscription?->isAccessAllowed();
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

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }

    public function fees()
    {
        return $this->hasMany(Fee::class);
    }

    public function condominiumAccounts()
    {
        return $this->hasMany(CondominiumAccount::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function generateUniqueRegistrationCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (static::withTrashed()->where('registration_code', $code)->exists());

        return $code;
    }

    public function regenerateRegistrationCode(): string
    {
        $code = static::generateUniqueRegistrationCode();
        $this->update(['registration_code' => $code]);

        return $code;
    }

    public function getFinancialModeLabelAttribute(): string
    {
        return $this->financial_mode === 'simplified'
            ? 'Simplificado'
            : 'Completo';
    }
}
