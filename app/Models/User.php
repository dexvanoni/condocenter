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
        'morador_vinculado_id',
        'name',
        'email',
        'password',
        'phone',
        'telefone_residencial',
        'telefone_celular',
        'telefone_comercial',
        'cpf',
        'cnh',
        'data_nascimento',
        'data_entrada',
        'data_saida',
        'necessita_cuidados_especiais',
        'descricao_cuidados_especiais',
        'local_trabalho',
        'contato_comercial',
        'photo',
        'qr_code',
        'senha_temporaria',
        'is_active',
        'possui_dividas',
        'fcm_token',
        'fcm_enabled',
        'fcm_topics',
        'fcm_token_updated_at',
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
            'data_nascimento' => 'date',
            'data_entrada' => 'date',
            'data_saida' => 'date',
            'necessita_cuidados_especiais' => 'boolean',
            'senha_temporaria' => 'boolean',
            'possui_dividas' => 'boolean',
            'fcm_enabled' => 'boolean',
            'fcm_topics' => 'array',
            'fcm_token_updated_at' => 'datetime',
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

    public function assemblyVotes()
    {
        return $this->hasMany(AssemblyVote::class, 'voter_id');
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

    public function activityLogs()
    {
        return $this->hasMany(UserActivityLog::class);
    }

    public function profileSelections()
    {
        return $this->hasMany(ProfileSelection::class);
    }

    // Relacionamento agregado-morador
    public function moradorVinculado()
    {
        return $this->belongsTo(User::class, 'morador_vinculado_id');
    }

    public function agregados()
    {
        return $this->hasMany(User::class, 'morador_vinculado_id');
    }

    public function agregadoPermissions()
    {
        return $this->hasMany(AgregadoPermission::class);
    }

    public function grantedAgregadoPermissions()
    {
        return $this->hasMany(AgregadoPermission::class, 'granted_by');
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

    public function isAgregado(): bool
    {
        return $this->hasRole('Agregado');
    }

    public function hasAgregadoPermission(string $permissionKey, string $permissionLevel = null): bool
    {
        if (!$this->isAgregado()) {
            return false;
        }

        return AgregadoPermission::hasPermission($this->id, $permissionKey, $permissionLevel);
    }

    public function getAgregadoPermissions(): array
    {
        if (!$this->isAgregado()) {
            return [];
        }

        return $this->agregadoPermissions()
            ->granted()
            ->pluck('permission_key')
            ->toArray();
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

    public function scopeWithDebts($query)
    {
        return $query->where('possui_dividas', true);
    }

    public function scopeAgregados($query)
    {
        return $query->whereHas('roles', function($q) {
            $q->where('name', 'Agregado');
        });
    }

    public function scopeMoradores($query)
    {
        return $query->whereHas('roles', function($q) {
            $q->where('name', 'Morador');
        });
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('cpf', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    // Métodos auxiliares
    public function getIdadeAttribute()
    {
        if (!$this->data_nascimento) {
            return null;
        }
        return $this->data_nascimento->age;
    }

    public function hasMultipleRoles(): bool
    {
        return $this->roles()->count() > 1;
    }

    public function needsPasswordChange(): bool
    {
        return $this->senha_temporaria === true;
    }

    public function logActivity(string $action, string $module, string $description, array $metadata = []): void
    {
        $this->activityLogs()->create([
            'condominium_id' => $this->condominium_id,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
