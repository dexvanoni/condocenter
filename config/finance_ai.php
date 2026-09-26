<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Consultor Financeiro SindCON
    |--------------------------------------------------------------------------
    |
    | Catálogo de perguntas pré-definidas e parâmetros operacionais.
    | A LLM nunca consulta o banco — apenas interpreta snapshots agregados.
    |
    */

    'rate_limit' => (int) env('FINANCE_AI_RATE_LIMIT', 10),

    'cache_ttl' => (int) env('FINANCE_AI_CACHE_TTL', 3600),

    'max_output_tokens' => (int) env('FINANCE_AI_MAX_OUTPUT_TOKENS', 2000),

    'default_provider' => env('FINANCE_AI_DEFAULT_PROVIDER', 'openai'),

    /*
    | Modelos oferecidos no painel admin (por provider).
    | A API key permanece global no .env — o admin só escolhe provider/modelo.
    */
    'providers' => [
        'openai' => [
            'models' => array_values(array_unique(array_filter([
                env('OPENAI_MODEL', 'gpt-5.6-luna'),
            ]))),
        ],
        'gemini' => [
            'models' => array_values(array_unique(array_filter([
                env('GEMINI_MODEL', 'gemini-3.8-flash'),
                'gemini-3.8-flash',
                'gemini-3.1-flash-lite',
                'gemini-flash-lite-latest',
                'gemini-flash-latest',
                'gemini-3.7-flash',
                'gemini-3.6-flash',
            ]))),
        ],
    ],

    'friendly_error' => 'Não foi possível gerar a análise financeira neste momento. Seus dados financeiros continuam disponíveis normalmente.',

    'system_prompt' => <<<'PROMPT'
Você é o Consultor Financeiro do SindCON, uma plataforma de gestão condominial.

Sua função é interpretar indicadores financeiros fornecidos pelo sistema e ajudar o síndico a identificar oportunidades de economia, controle de despesas, melhoria de receitas e saúde financeira.

REGRAS:
1. Nunca invente números.
2. Nunca invente despesas.
3. Nunca invente receitas.
4. Nunca invente contratos.
5. Nunca invente fornecedores.
6. Nunca afirme que determinada economia será garantida.
7. Não faça cálculos financeiros que não tenham sido fornecidos pelo sistema.
8. Quando precisar de um cálculo, utilize somente os indicadores fornecidos.
9. Diferencie fatos observados de sugestões.
10. Não recomende aumento automático da taxa condominial.
11. Priorize redução de desperdícios, renegociação, eficiência operacional, recuperação de inadimplência e novas receitas legítimas.
12. Não forneça orientação jurídica como se fosse parecer jurídico.
13. Quando uma recomendação depender de análise jurídica, contábil ou contratual, sinalize isso.
14. Não faça acusações contra fornecedores, funcionários, moradores ou administração.
15. Não invente problemas.
16. Se os dados forem insuficientes, diga claramente quais informações seriam necessárias.
17. Utilize linguagem clara e profissional.
18. A resposta deve ser útil para um síndico, não para um contador ou programador.
19. Prefira formulações como "Os indicadores sugerem avaliar...", "Uma possibilidade é...", "Vale analisar...".
20. Responda APENAS com um JSON válido (sem markdown) no formato:
{
  "titulo": "string",
  "resumo": "string",
  "pontos_atencao": ["string"],
  "recomendacoes": [
    {
      "titulo": "string",
      "acao": "string",
      "motivo": "string",
      "impacto": "string",
      "prioridade": "alta|media|baixa"
    }
  ],
  "observacoes": ["string"]
}
PROMPT,

    'questions' => [

        'where_spending' => [
            'title' => 'Onde meu condomínio está gastando mais?',
            'description' => 'Identifique as principais categorias de despesas.',
            'periods' => ['6m', 'current_month'],
            'sections' => ['meta', 'despesas', 'resultado', 'category_insights'],
            'focus_categories' => [],
        ],

        'reduce_energy' => [
            'title' => 'Como podemos reduzir a conta de energia?',
            'description' => 'Analise o comportamento das despesas de energia.',
            'periods' => ['6m', '12m', 'current_month'],
            'sections' => ['meta', 'despesas', 'resultado', 'category_insights'],
            'focus_categories' => ['energia'],
        ],

        'expense_attention' => [
            'title' => 'Quais despesas precisam de atenção?',
            'description' => 'Aponta categorias com variação ou concentração relevantes.',
            'periods' => ['3m', '6m', 'current_month'],
            'sections' => ['meta', 'despesas', 'resultado', 'category_insights'],
            'focus_categories' => [],
        ],

        'increase_revenue' => [
            'title' => 'Como aumentar as receitas sem aumentar a taxa condominial?',
            'description' => 'Avalie composição e evolução das receitas.',
            'periods' => ['6m', '12m', 'current_month'],
            'sections' => ['meta', 'receitas', 'resultado', 'inadimplencia'],
            'focus_categories' => [],
        ],

        'contracts_review' => [
            'title' => 'Existe algum contrato que merece ser renegociado?',
            'description' => 'Foque em categorias típicas de contratos recorrentes.',
            'periods' => ['6m', '12m'],
            'sections' => ['meta', 'despesas', 'category_insights'],
            'focus_categories' => ['administracao', 'seguros', 'manutencao', 'seguranca', 'limpeza', 'elevadores'],
        ],

        'default_analysis' => [
            'title' => 'Como está nossa inadimplência?',
            'description' => 'Resumo agregado de valores e unidades em atraso.',
            'periods' => ['3m', '6m', 'current_month'],
            'sections' => ['meta', 'receitas', 'inadimplencia', 'resultado'],
            'focus_categories' => [],
        ],

        'expense_evolution' => [
            'title' => 'Quais despesas aumentaram mais nos últimos 6 meses?',
            'description' => 'Comparativo de categorias entre períodos.',
            'periods' => ['6m'],
            'sections' => ['meta', 'despesas', 'category_insights', 'expense_evolution'],
            'focus_categories' => [],
        ],

        'financial_health' => [
            'title' => 'Como está nossa saúde financeira?',
            'description' => 'Visão geral de receitas, despesas, saldo e inadimplência.',
            'periods' => ['3m', '6m', '12m', 'current_month'],
            'sections' => ['meta', 'receitas', 'despesas', 'resultado', 'inadimplencia', 'category_insights'],
            'focus_categories' => [],
        ],

        'ninety_day_savings' => [
            'title' => 'Quais medidas podem gerar economia nos próximos 90 dias?',
            'description' => 'Oportunidades com base em despesas recorrentes e evolução.',
            'periods' => ['3m', '6m', 'current_month'],
            'sections' => ['meta', 'despesas', 'resultado', 'inadimplencia', 'category_insights'],
            'focus_categories' => [],
        ],

    ],

];
