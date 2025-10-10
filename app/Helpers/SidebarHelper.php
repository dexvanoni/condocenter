<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\AgregadoPermission;

class SidebarHelper
{
    /**
     * Verifica se o usuário pode acessar um módulo específico
     */
    public static function canAccessModule(User $user, string $module): bool
    {
        // Agregados precisam de permissão específica
        if ($user->isAgregado()) {
            return $user->hasAgregadoPermission($module);
        }

        // Para outros perfis, usar permissões padrão do Spatie
        return match($module) {
            'spaces' => $user->can('view_spaces'),
            'marketplace' => $user->can('view_marketplace'),
            'pets' => $user->can('view_pets'),
            'notifications' => $user->can('view_notifications'),
            'packages' => $user->can('view_packages'),
            'messages' => $user->can('send_messages'),
            'financial' => $user->can('view_own_financial') || $user->can('view_transactions'),
            default => false,
        };
    }

    /**
     * Verifica se o agregado pode realizar ações CRUD em um módulo
     */
    public static function canCrudModule(User $user, string $module): bool
    {
        // Se não for agregado, verificar permissões administrativas normais
        if (!$user->isAgregado()) {
            return match($module) {
                'spaces' => $user->can('manage_spaces'),
                'marketplace' => $user->can('manage_marketplace'),
                'pets' => $user->can('manage_pets'),
                'packages' => $user->can('register_packages'),
                'messages' => $user->can('send_messages'),
                default => false,
            };
        }

        // Para agregados, verificar nível de permissão
        return AgregadoPermission::hasPermission($user->id, $module, 'crud');
    }

    /**
     * Verifica se pode fazer reservas (CRUD próprio)
     */
    public static function canMakeReservations(User $user): bool
    {
        if ($user->isAgregado()) {
            // Agregado precisa de permissão 'spaces' com nível 'crud' para fazer reservas
            return AgregadoPermission::hasPermission($user->id, 'spaces', 'crud');
        }

        return $user->can('make_reservations');
    }

    /**
     * Verifica se pode apenas visualizar reservas
     */
    public static function canViewReservations(User $user): bool
    {
        if ($user->isAgregado()) {
            // Agregado precisa de qualquer permissão para 'spaces' (view ou crud)
            return AgregadoPermission::hasPermission($user->id, 'spaces');
        }

        return $user->can('view_reservations') || $user->can('make_reservations');
    }

    /**
     * Verifica se pode gerenciar reservas de outros (função administrativa)
     * IMPORTANTE: Agregados NUNCA podem gerenciar reservas de outros
     */
    public static function canManageOthersReservations(User $user): bool
    {
        if ($user->isAgregado()) {
            // Agregados nunca podem gerenciar reservas de outros usuários
            return false;
        }

        return $user->can('manage_reservations') || $user->can('approve_reservations');
    }

    /**
     * Verifica se pode gerenciar espaços (admin/síndico)
     */
    public static function canManageSpaces(User $user): bool
    {
        // Agregados NUNCA podem gerenciar espaços (admin)
        if ($user->isAgregado()) {
            return false;
        }

        return $user->can('manage_spaces');
    }

    /**
     * Verifica se pode aprovar reservas (admin/síndico)
     */
    public static function canApproveReservations(User $user): bool
    {
        // Agregados NUNCA podem aprovar reservas
        if ($user->isAgregado()) {
            return false;
        }

        return $user->can('approve_reservations');
    }

    /**
     * Verifica se pode criar anúncios no marketplace
     */
    public static function canCreateMarketplace(User $user): bool
    {
        if ($user->isAgregado()) {
            return self::canCrudModule($user, 'marketplace');
        }

        return $user->can('view_marketplace');
    }

    /**
     * Verifica se pode gerenciar pets
     */
    public static function canManagePets(User $user): bool
    {
        if ($user->isAgregado()) {
            return self::canCrudModule($user, 'pets');
        }

        return $user->can('view_pets');
    }

    /**
     * Verifica se pode enviar mensagens
     */
    public static function canSendMessages(User $user): bool
    {
        if ($user->isAgregado()) {
            return self::canCrudModule($user, 'messages');
        }

        return $user->can('send_messages');
    }

    /**
     * Verifica se pode visualizar encomendas
     */
    public static function canViewPackages(User $user): bool
    {
        if ($user->isAgregado()) {
            return self::canAccessModule($user, 'packages');
        }

        return $user->can('view_packages') || $user->can('register_packages');
    }

    /**
     * Verifica se pode registrar encomendas (porteiro)
     */
    public static function canRegisterPackages(User $user): bool
    {
        if ($user->isAgregado()) {
            // Agregados podem registrar encomendas se tiverem permissão CRUD
            return self::canCrudModule($user, 'packages');
        }

        return $user->can('register_packages');
    }

    /**
     * Verifica se é administrador ou síndico
     */
    public static function isAdminOrSindico(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Síndico']);
    }

    /**
     * Obtém o texto do botão de ação baseado nas permissões
     */
    public static function getActionButtonText(User $user, string $module): string
    {
        if (self::canCrudModule($user, $module)) {
            return match($module) {
                'spaces' => 'Fazer Reserva',
                'marketplace' => 'Criar Anúncio',
                'pets' => 'Cadastrar Pet',
                'messages' => 'Nova Mensagem',
                default => 'Novo',
            };
        }

        return 'Visualizar';
    }
}
