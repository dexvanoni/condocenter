<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Concerns\HasActiveProfileRole;

class User extends Authenticatable implements Auditable, CanResetPasswordContract
{
    use CanResetPassword, HasFactory, Notifiable, HasApiTokens, SoftDeletes, \OwenIt\Auditing\Auditable;

    use HasRoles, HasActiveProfileRole {
        HasRoles::hasRole as protected spatieHasRole;
        HasRoles::hasAnyRole as protected spatieHasAnyRole;
        HasRoles::hasAllRoles as protected spatieHasAllRoles;
        HasRoles::hasPermissionTo as protected spatieHasPermissionTo;
        HasActiveProfileRole::hasRole insteadof HasRoles;
        HasActiveProfileRole::hasAnyRole insteadof HasRoles;
        HasActiveProfileRole::hasAllRoles insteadof HasRoles;
        HasActiveProfileRole::hasPermissionTo insteadof HasRoles;
    }

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
        'access_suspended_reason',
        'registration_status',
        'possui_dividas',
        'agregado_can_authorize_access',
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
            'agregado_can_authorize_access' => 'boolean',
        ];
    }

    // Relacionamentos
    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function managedCondominiums()
    {
        return $this->belongsToMany(Condominium::class, 'condominium_user')
            ->withTimestamps();
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function organizationMemberships()
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function belongsToOrganization(int $organizationId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->organizations()
            ->where('organizations.id', $organizationId)
            ->exists();
    }

    public function organizationRoleFor(int $organizationId): ?string
    {
        $membership = $this->organizations()
            ->where('organizations.id', $organizationId)
            ->first();

        return $membership?->pivot?->role;
    }

    public function isOrganizationMember(): bool
    {
        if ($this->isAdmin()) {
            return false;
        }

        return $this->organizations()->exists();
    }

    public function isManagementCompanyMember(): bool
    {
        return $this->organizations()
            ->where('organizations.type', Organization::TYPE_MANAGEMENT_COMPANY)
            ->exists();
    }

    public function managedOrganizations()
    {
        return $this->organizations()
            ->where('organizations.type', Organization::TYPE_MANAGEMENT_COMPANY)
            ->whereIn('organization_user.role', [
                Organization::ROLE_OWNER,
                Organization::ROLE_ADMIN,
                Organization::ROLE_MANAGER,
                Organization::ROLE_OPERATOR,
            ]);
    }

    public function getActiveCondominiumId(): ?int
    {
        return app(\App\Services\ActiveCondominiumService::class)->getActiveCondominiumId($this);
    }

    public function tenantCondominiumId(): ?int
    {
        return $this->getActiveCondominiumId();
    }

    public function belongsToTenant(int $condominiumId): bool
    {
        $tenantId = $this->tenantCondominiumId();

        return $tenantId !== null && $tenantId === $condominiumId;
    }

    public function activeCondominium(): ?Condominium
    {
        return app(\App\Services\ActiveCondominiumService::class)->getActiveCondominium($this);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function ownedUnits()
    {
        return $this->hasMany(Unit::class, 'owner_user_id');
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

    public function defaulterAccessOverrides()
    {
        return $this->hasMany(DefaulterAccessOverride::class);
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

    /** Morador cuja unidade está em regime de aluguel (inquilino). */
    public function isMoradorInquilino(): bool
    {
        if (!$this->isMorador() || !$this->unit_id) {
            return false;
        }

        $unit = $this->relationLoaded('unit') ? $this->unit : $this->unit()->first();

        return $unit?->isRental() ?? false;
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

    public function isProprietario(): bool
    {
        return $this->hasRole('Proprietário');
    }

    /**
     * Alertas de entrada/negação na portaria: moradores, agregados e staff
     * que também residem na unidade (liberações próprias). Síndico puro usa o relatório.
     */
    public function receivesAccessMovementAlerts(): bool
    {
        if (!$this->can('create_access_authorizations') && !$this->can('manage_access_lists')) {
            return false;
        }

        if ($this->isSindico() || $this->isAdmin()) {
            return $this->isMorador() || $this->isAgregado();
        }

        return true;
    }

    public function isPendingApproval(): bool
    {
        return $this->registration_status === 'pending';
    }

    public function isRegistrationRejected(): bool
    {
        return $this->registration_status === 'rejected';
    }

    public function isRegistrationApproved(): bool
    {
        return $this->registration_status === 'approved';
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

    public function whatsappPhone(): ?string
    {
        if (!$this->canReceiveWhatsApp()) {
            return null;
        }

        foreach (['phone', 'telefone_celular', 'telefone_residencial', 'telefone_comercial'] as $field) {
            $value = $this->{$field};

            if (filled($value)) {
                return $value;
            }
        }

        return null;
    }

    public function whatsappChatUrl(?string $prefilledMessage = null): ?string
    {
        $raw = $this->whatsappPhone();

        if ($raw === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $raw);

        if ($digits === '') {
            return null;
        }

        if (!str_starts_with($digits, '55')) {
            $digits = '55'.$digits;
        }

        $url = 'https://wa.me/'.$digits;

        if ($prefilledMessage !== null && $prefilledMessage !== '') {
            $url .= '?text='.rawurlencode($prefilledMessage);
        }

        return $url;
    }

    public function canReceiveWhatsApp(): bool
    {
        return $this->is_active && !$this->trashed();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEligibleForWhatsApp($query)
    {
        return $query->active();
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

    public const PROFILE_ADMINISTRADORA = 'Administradora';

    public function hasMultipleRoles(): bool
    {
        return $this->roles()->count() > 1;
    }

    public function canUseManagementCompanyProfile(): bool
    {
        if ($this->hasAssignedRole('Administrador') || !$this->isManagementCompanyMember()) {
            return false;
        }

        return $this->organizations()
            ->where('organizations.type', Organization::TYPE_MANAGEMENT_COMPANY)
            ->whereIn('organization_user.role', [
                Organization::ROLE_OWNER,
                Organization::ROLE_ADMIN,
                Organization::ROLE_MANAGER,
            ])
            ->exists();
    }

    /**
     * @return list<string>
     */
    public function selectableProfileNames(): array
    {
        $names = $this->roles->pluck('name')->all();

        if ($this->canUseManagementCompanyProfile() && !in_array(self::PROFILE_ADMINISTRADORA, $names, true)) {
            array_unshift($names, self::PROFILE_ADMINISTRADORA);
        }

        return array_values($names);
    }

    public function hasProfileSwitcher(): bool
    {
        return count($this->selectableProfileNames()) > 1;
    }

    public function acceptsProfile(string $roleName): bool
    {
        if ($roleName === self::PROFILE_ADMINISTRADORA) {
            return $this->canUseManagementCompanyProfile();
        }

        return $this->hasAssignedRole($roleName);
    }

    public function needsPasswordChange(): bool
    {
        return $this->senha_temporaria === true;
    }

    public function logActivity(string $action, string $module, string $description, array $metadata = []): void
    {
        $condominiumId = $this->tenantCondominiumId() ?? $this->condominium_id;

        if (!$condominiumId) {
            return;
        }

        $this->activityLogs()->create([
            'condominium_id' => $condominiumId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
