<?php

namespace App\Support;

class CondominiumModules
{
    /**
     * Módulos que o síndico pode ligar/desligar. Gestão, dashboard e pânico ficam sempre ativos.
     *
     * @return array<string, array{label: string, description: string, icon: string}>
     */
    public static function catalog(): array
    {
        return [
            'financial' => [
                'label' => 'Financeiro',
                'description' => 'Taxas, cobranças, caixa, multas e prestação de contas.',
                'icon' => 'bi-cash-coin',
            ],
            'spaces' => [
                'label' => 'Espaços e reservas',
                'description' => 'Áreas comuns, reservas e reservas recorrentes.',
                'icon' => 'bi-calendar-event',
            ],
            'marketplace' => [
                'label' => 'Marketplace',
                'description' => 'Classificados entre moradores.',
                'icon' => 'bi-shop',
            ],
            'rides' => [
                'label' => 'Caronas',
                'description' => 'Oferecer e pedir caronas no condomínio.',
                'icon' => 'bi-car-front',
            ],
            'pets' => [
                'label' => 'Pets',
                'description' => 'Cadastro de animais e QR Code.',
                'icon' => 'bi-heart',
            ],
            'service_orders' => [
                'label' => 'Ordens de serviço',
                'description' => 'Solicitações de manutenção e OS.',
                'icon' => 'bi-clipboard-check',
            ],
            'assemblies' => [
                'label' => 'Assembleias',
                'description' => 'Convocação, votação e atas.',
                'icon' => 'bi-people',
            ],
            'documents' => [
                'label' => 'Documentos',
                'description' => 'Regimento interno e documentos oficiais.',
                'icon' => 'bi-file-earmark-text',
            ],
            'packages' => [
                'label' => 'Encomendas',
                'description' => 'Registro e retirada de encomendas.',
                'icon' => 'bi-box-seam',
            ],
            'access_control' => [
                'label' => 'Controle de acesso',
                'description' => 'Liberações, visitantes e painel da portaria.',
                'icon' => 'bi-shield-lock',
            ],
            'communication' => [
                'label' => 'Comunicação',
                'description' => 'Mensagens, ocorrências, fale com o síndico e avisos.',
                'icon' => 'bi-chat-dots',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::catalog());
    }

    public static function isValid(string $key): bool
    {
        return array_key_exists($key, self::catalog());
    }
}
