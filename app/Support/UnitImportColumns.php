<?php

namespace App\Support;

class UnitImportColumns
{
    /**
     * Cabeçalhos da planilha modelo (ordem fixa).
     *
     * @return list<string>
     */
    public static function headings(): array
    {
        return [
            'numero',
            'bloco',
            'uso',
            'modelo',
            'situacao',
            'regime_ocupacao',
            'tipo_imovel_publico',
            'andar',
            'fracao_ideal',
            'ativo',
            'possui_dividas',
        ];
    }

    /**
     * @return list<string>
     */
    public static function requiredKeys(): array
    {
        return [
            'numero',
            'uso',
            'modelo',
            'situacao',
            'regime_ocupacao',
        ];
    }

    /**
     * Linhas de exemplo para a planilha modelo.
     *
     * @return list<list<string>>
     */
    public static function sampleRows(): array
    {
        return [
            ['101', 'A', 'residencial', 'apartamento', 'fechado', 'particular', '', '1', '0,0125', 'sim', 'nao'],
            ['102', 'A', 'residencial', 'apartamento', 'habitado', 'particular', '', '1', '0,0125', 'sim', 'nao'],
            ['Loja 01', '', 'comercial', 'apartamento', 'habitado', 'particular', '', '', '', 'sim', 'nao'],
        ];
    }

    /**
     * @return list<array{coluna: string, obrigatorio: string, descricao: string, valores: string}>
     */
    public static function instructions(): array
    {
        return [
            [
                'coluna' => 'numero',
                'obrigatorio' => 'Sim',
                'descricao' => 'Identificação da unidade (apartamento, casa, loja etc.)',
                'valores' => 'Texto livre, até 50 caracteres',
            ],
            [
                'coluna' => 'bloco',
                'obrigatorio' => 'Não',
                'descricao' => 'Bloco ou torre, quando houver',
                'valores' => 'Texto livre ou vazio',
            ],
            [
                'coluna' => 'uso',
                'obrigatorio' => 'Sim',
                'descricao' => 'Finalidade da unidade',
                'valores' => 'residencial ou comercial',
            ],
            [
                'coluna' => 'modelo',
                'obrigatorio' => 'Sim',
                'descricao' => 'Tipologia',
                'valores' => implode(', ', UnitModels::values()),
            ],
            [
                'coluna' => 'situacao',
                'obrigatorio' => 'Sim',
                'descricao' => 'Situação operacional',
                'valores' => 'habitado, fechado, indisponivel, em_obra',
            ],
            [
                'coluna' => 'regime_ocupacao',
                'obrigatorio' => 'Sim',
                'descricao' => 'Regime de ocupação',
                'valores' => 'particular ou imovel_publico (aluguel: cadastre pela tela individual)',
            ],
            [
                'coluna' => 'tipo_imovel_publico',
                'obrigatorio' => 'Condicional',
                'descricao' => 'Obrigatório se regime_ocupacao = imovel_publico',
                'valores' => implode(', ', PublicPropertyKinds::values()),
            ],
            [
                'coluna' => 'andar',
                'obrigatorio' => 'Não',
                'descricao' => 'Andar numérico',
                'valores' => 'Número inteiro ou vazio',
            ],
            [
                'coluna' => 'fracao_ideal',
                'obrigatorio' => 'Não',
                'descricao' => 'Fração ideal (condomínio)',
                'valores' => 'Decimal com vírgula ou ponto',
            ],
            [
                'coluna' => 'ativo',
                'obrigatorio' => 'Não',
                'descricao' => 'Unidade ativa no sistema',
                'valores' => 'sim ou nao (padrão: sim)',
            ],
            [
                'coluna' => 'possui_dividas',
                'obrigatorio' => 'Não',
                'descricao' => 'Marcador de dívidas',
                'valores' => 'sim ou nao (padrão: nao)',
            ],
        ];
    }
}
