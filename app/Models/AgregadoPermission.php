<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgregadoPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'granted_by',
        'permission_key',
        'permission_level',
        'is_granted',
        'notes',
    ];

    protected $casts = [
        'is_granted' => 'boolean',
    ];

    // Relacionamentos
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    // Scopes
    public function scopeGranted($query)
    {
        return $query->where('is_granted', true);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForPermission($query, $permissionKey)
    {
        return $query->where('permission_key', $permissionKey);
    }

    // Métodos auxiliares
    public static function hasPermission($userId, $permissionKey, $permissionLevel = null): bool
    {
        $query = self::where('user_id', $userId)
            ->where('permission_key', $permissionKey)
            ->where('is_granted', true);
            
        if ($permissionLevel) {
            $query->where('permission_level', $permissionLevel);
        }
        
        return $query->exists();
    }

    public static function grantPermission($userId, $permissionKey, $permissionLevel, $grantedById, $notes = null): self
    {
        return self::updateOrCreate(
            [
                'user_id' => $userId,
                'permission_key' => $permissionKey,
            ],
            [
                'granted_by' => $grantedById,
                'permission_level' => $permissionLevel,
                'is_granted' => true,
                'notes' => $notes,
            ]
        );
    }

    public static function revokePermission($userId, $permissionKey): bool
    {
        return self::where('user_id', $userId)
            ->where('permission_key', $permissionKey)
            ->update(['is_granted' => false]);
    }

    public static function getAvailablePermissions(): array
    {
        return [
            'spaces' => [
                'name' => 'Espaços',
                'description' => 'Gerenciar reservas de espaços comuns'
            ],
            'marketplace' => [
                'name' => 'Marketplace',
                'description' => 'Anúncios e comércio entre moradores'
            ],
            'pets' => [
                'name' => 'Pets',
                'description' => 'Cadastro e gestão de animais'
            ],
            'notifications' => [
                'name' => 'Notificações',
                'description' => 'Receber notificações do sistema'
            ],
            'packages' => [
                'name' => 'Encomendas',
                'description' => 'Controle de encomendas e entregas'
            ],
            'messages' => [
                'name' => 'Mensagens',
                'description' => 'Enviar mensagens para outros moradores'
            ],
            'financial' => [
                'name' => 'Financeiro',
                'description' => 'Visualizar informações financeiras limitadas'
            ],
        ];
    }

    public static function getPermissionLevels(): array
    {
        return [
            'view' => 'Apenas Visualização',
            'crud' => 'Acesso Completo',
        ];
    }
}