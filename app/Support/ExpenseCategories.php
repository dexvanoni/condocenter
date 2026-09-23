<?php

namespace App\Support;

final class ExpenseCategories
{
    /**
     * Categorias oficiais de despesa do condomínio (chave estável → rótulo).
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
            'pessoal' => 'Pessoal / Folha',
            'encargos' => 'Encargos e impostos',
            'energia' => 'Energia elétrica',
            'agua' => 'Água e esgoto',
            'gas' => 'Gás',
            'manutencao' => 'Manutenção e reparos',
            'limpeza' => 'Limpeza e higiene',
            'seguranca' => 'Segurança e vigilância',
            'seguros' => 'Seguros',
            'administracao' => 'Administração / honorários',
            'juridico' => 'Jurídico e contábil',
            'comunicacao' => 'Telefonia / internet',
            'elevadores' => 'Elevadores',
            'jardim' => 'Jardinagem e áreas comuns',
            'materiais' => 'Materiais e suprimentos',
            'obras' => 'Obras e melhorias',
            'taxas_bancarias' => 'Tarifas bancárias',
            'outros' => 'Outros',
        ];
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function label(?string $key): string
    {
        if ($key === null || $key === '') {
            return 'Não informada';
        }

        return self::all()[$key] ?? $key;
    }

    public static function isValid(?string $key): bool
    {
        return $key !== null && array_key_exists($key, self::all());
    }

    /**
     * Ícones Bootstrap para insights no dashboard.
     *
     * @return array<string, string>
     */
    public static function icons(): array
    {
        return [
            'pessoal' => 'bi-people',
            'encargos' => 'bi-receipt',
            'energia' => 'bi-lightning-charge',
            'agua' => 'bi-droplet',
            'gas' => 'bi-fire',
            'manutencao' => 'bi-tools',
            'limpeza' => 'bi-bucket',
            'seguranca' => 'bi-shield-check',
            'seguros' => 'bi-umbrella',
            'administracao' => 'bi-briefcase',
            'juridico' => 'bi-balance-scale',
            'comunicacao' => 'bi-wifi',
            'elevadores' => 'bi-building',
            'jardim' => 'bi-tree',
            'materiais' => 'bi-box-seam',
            'obras' => 'bi-cone-striped',
            'taxas_bancarias' => 'bi-bank',
            'outros' => 'bi-three-dots',
        ];
    }
}
