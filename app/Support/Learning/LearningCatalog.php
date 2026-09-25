<?php

namespace App\Support\Learning;

/**
 * Catálogo estático da Central de Aprendizagem.
 * Conteúdo versionado no código — fácil de revisar e expandir sem migração.
 */
final class LearningCatalog
{
    /**
     * @return array<string, array{label: string, description: string, icon: string, order: int}>
     */
    public static function modules(): array
    {
        return [
            'getting-started' => [
                'label' => 'Primeiros passos',
                'description' => 'Login, perfil, condomínio e navegação no SindCON.',
                'icon' => 'bi-rocket-takeoff',
                'order' => 1,
            ],
            'financial' => [
                'label' => 'Financeiro',
                'description' => 'Taxas, cobranças, caixa, conciliação, fechamento e prestação de contas.',
                'icon' => 'bi-cash-coin',
                'order' => 2,
            ],
            'gestao' => [
                'label' => 'Gestão',
                'description' => 'Unidades, usuários, condomínio e módulos.',
                'icon' => 'bi-gear',
                'order' => 3,
            ],
            'spaces' => [
                'label' => 'Espaços e reservas',
                'description' => 'Áreas comuns, aprovações e reservas pagas.',
                'icon' => 'bi-calendar-event',
                'order' => 4,
            ],
            'packages' => [
                'label' => 'Encomendas',
                'description' => 'Registro, retirada e relatório de movimentações.',
                'icon' => 'bi-box-seam',
                'order' => 5,
            ],
            'access_control' => [
                'label' => 'Controle de acesso',
                'description' => 'Liberações, visitantes e painel da portaria.',
                'icon' => 'bi-shield-lock',
                'order' => 6,
            ],
            'assemblies' => [
                'label' => 'Assembleias',
                'description' => 'Convocação, votação e atas.',
                'icon' => 'bi-people',
                'order' => 7,
            ],
            'communication' => [
                'label' => 'Comunicação',
                'description' => 'Mensagens, ocorrências, landing e avisos.',
                'icon' => 'bi-chat-dots',
                'order' => 8,
            ],
            'service_orders' => [
                'label' => 'Ordens de serviço',
                'description' => 'Solicitações de manutenção e cobranças.',
                'icon' => 'bi-clipboard-check',
                'order' => 9,
            ],
            'documents' => [
                'label' => 'Documentos',
                'description' => 'Regimento interno e biblioteca.',
                'icon' => 'bi-file-earmark-text',
                'order' => 10,
            ],
            'marketplace' => [
                'label' => 'Marketplace e caronas',
                'description' => 'Classificados e caronas entre moradores.',
                'icon' => 'bi-shop',
                'order' => 11,
            ],
            'pets' => [
                'label' => 'Pets',
                'description' => 'Cadastro e QR Code de animais.',
                'icon' => 'bi-heart',
                'order' => 12,
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function tutorials(): array
    {
        return array_merge(
            self::gettingStartedTutorials(),
            self::financialTutorials(),
            self::gestaoTutorials(),
            self::operationalTutorials(),
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function gettingStartedTutorials(): array
    {
        return [
            [
                'slug' => 'bem-vindo-sindcon',
                'module' => 'getting-started',
                'title' => 'Bem-vindo ao SindCON',
                'summary' => 'O que o sistema faz e por onde o síndico deve começar.',
                'audience' => 'sindico',
                'level' => 'iniciante',
                'minutes' => 4,
                'critical' => false,
                'tags' => ['início', 'visão geral', 'menu'],
                'route_hint' => 'dashboard',
                'video' => null,
                'video_url' => null,
                'objectives' => [
                    'Entender o papel do SindCON na gestão do condomínio',
                    'Localizar Dashboard, Gestão e Financeiro no menu',
                    'Saber onde pedir ajuda (esta Central de Aprendizagem)',
                ],
                'steps' => [
                    [
                        'title' => 'O que é o SindCON',
                        'body' => 'O SindCON centraliza finanças, portaria, reservas, comunicação e segurança do condomínio. Cada condomínio é isolado (tenant): você só vê e gerencia o seu.',
                    ],
                    [
                        'title' => 'Menu lateral',
                        'body' => 'Use o menu à esquerda. **Dashboard** resume o dia. **Gestão** trata unidades e usuários. **Financeiro** concentra taxas, caixa e conciliação. Os demais itens seguem os módulos ligados no condomínio.',
                    ],
                    [
                        'title' => 'Perfil e troca de papel',
                        'body' => 'No canto do seu nome você abre **Meu Perfil**, **Alterar Senha** e, se tiver mais de um papel, troca o perfil ativo (ex.: Síndico ↔ Morador). As permissões seguem o perfil ativo.',
                    ],
                    [
                        'title' => 'Central de Aprendizagem',
                        'body' => 'Este menu (**Aprenda**) e o atalho no seu perfil reúnem tutoriais por módulo, com busca e vídeos nas funções críticas. Use sempre que tiver dúvida antes de confirmar uma ação financeira.',
                    ],
                ],
                'checklist' => [
                    'Encontrou o Dashboard',
                    'Abriu o menu Financeiro (se o módulo estiver ativo)',
                    'Localizou a Central de Aprendizagem',
                ],
            ],
            [
                'slug' => 'navegacao-e-permissoes',
                'module' => 'getting-started',
                'title' => 'Navegação, módulos e permissões',
                'summary' => 'Por que alguns menus aparecem ou somem e o que significa “módulo desligado”.',
                'audience' => 'sindico',
                'level' => 'iniciante',
                'minutes' => 5,
                'critical' => false,
                'tags' => ['módulos', 'permissões', 'menu'],
                'route_hint' => 'condominiums.show',
                'video' => null,
                'video_url' => null,
                'objectives' => [
                    'Saber ligar/desligar módulos em Meu Condomínio',
                    'Entender que módulo desligado gera 403 nas rotas',
                    'Diferenciar modo financeiro completo e simplificado',
                ],
                'steps' => [
                    [
                        'title' => 'Módulos do condomínio',
                        'body' => 'Em **Gestão → Meu Condomínio → Módulos**, você liga ou desliga funcionalidades (caronas, marketplace, etc.). O que estiver desligado some do menu e bloqueia o acesso.',
                    ],
                    [
                        'title' => 'Modo financeiro',
                        'body' => 'Há dois modos: **completo** (caixa, contas, conciliação, fechamento) e **simplificado** (upload de prestação). A conciliação CSV/OFX só existe no modo completo.',
                    ],
                    [
                        'title' => 'Assinatura SaaS',
                        'body' => 'Se a assinatura do condomínio estiver inativa, o sistema bloqueia módulos até a regularização em **Minha Assinatura**. Se a plataforma liberou **uso gratuito** para o condomínio, essa tela mostra o aviso verde e você não precisa pagar a assinatura SindCON.',
                    ],
                ],
                'checklist' => [
                    'Conferiu quais módulos estão ativos',
                    'Identificou se o financeiro está completo ou simplificado',
                ],
            ],
            [
                'slug' => 'painel-da-administradora',
                'module' => 'getting-started',
                'title' => 'Painel da administradora',
                'summary' => 'Como ler usuários, multas e saúde financeira de cada condomínio da carteira.',
                'audience' => 'sindico',
                'level' => 'iniciante',
                'minutes' => 3,
                'critical' => false,
                'tags' => ['administradora', 'carteira', 'multas', 'adimplência'],
                'route_hint' => 'organization.dashboard',
                'video' => null,
                'video_url' => null,
                'objectives' => [
                    'Abrir o painel da administradora',
                    'Ler usuários, multas aplicadas e saúde financeira de cada condomínio',
                    'Entrar no condomínio que precisa de atenção',
                ],
                'steps' => [
                    [
                        'title' => 'Onde fica',
                        'body' => 'Com o perfil **Administradora** ativo, abra **Painel da Administradora**. Cada condomínio da sua organização aparece em um card.',
                    ],
                    [
                        'title' => 'O que cada indicador significa',
                        'body' => '**Usuários** conta quem está vinculado ao condomínio. **Multas** mostra a quantidade e o valor das multas aplicadas (as canceladas não entram). **Saúde financeira** é a adimplência: unidades sem cobrança em atraso sobre o total. A partir de 90% o card fica **Saudável**; entre 70% e 90%, **Atenção**; abaixo de 70%, **Crítica**.',
                    ],
                    [
                        'title' => 'Quando agir',
                        'body' => 'Use **Entrar** no card em atenção ou crítico para tratar as cobranças em atraso e as multas dentro da operação daquele condomínio. Condomínio sem unidades aparece como **Sem unidades** até o cadastro existir.',
                    ],
                ],
                'checklist' => [
                    'Localizou o card de cada condomínio',
                    'Identificou se há multa aplicada e qual a faixa de saúde financeira',
                    'Entrou no condomínio que precisa de acompanhamento',
                ],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function financialTutorials(): array
    {
        return [
            [
                'slug' => 'financeiro-visao-geral',
                'module' => 'financial',
                'title' => 'Visão geral do Financeiro',
                'summary' => 'Mapa das telas financeiras e a ordem recomendada de trabalho do síndico.',
                'audience' => 'sindico',
                'level' => 'iniciante',
                'minutes' => 6,
                'critical' => true,
                'tags' => ['financeiro', 'visão geral', 'rotina'],
                'route_hint' => 'financial.settings.index',
                'video' => 'financeiro-visao-geral.mp4',
                'video_url' => null,
                'objectives' => [
                    'Conhecer a ordem: taxas → cobranças → caixa → conciliação → fechamento → prestação',
                    'Saber onde configurar contas e regras de destino',
                ],
                'steps' => [
                    [
                        'title' => 'Rotina mensal sugerida',
                        'body' => "1. Cadastre/confirme **contas bancárias** e **regras de destino**.\n2. Configure **taxas** e gere **cobranças**.\n3. Acompanhe **adimplência** e baixas.\n4. Registre despesas/receitas no **caixa**.\n5. Importe o **extrato** e faça a **conciliação**.\n6. Conclua o **fechamento mensal** e a **prestação de contas**.",
                    ],
                    [
                        'title' => 'Onde cada coisa fica',
                        'body' => 'No menu **Financeiro**: Taxas, Multas, Cobranças, Status financeiro, Caixa, Contas bancárias, Conciliação, Fechamento mensal, Prestação de contas e Configurações financeiras.',
                    ],
                    [
                        'title' => 'Regra de ouro',
                        'body' => 'Antes de confirmar conciliação ou baixar cobrança em lote, confira valores e datas. Ações financeiras costumam ser difíceis de desfazer (a conciliação só permite cancelar a **última**).',
                    ],
                ],
                'checklist' => [
                    'Abriu o menu Financeiro completo',
                    'Localizou Contas, Caixa e Conciliação',
                ],
            ],
            [
                'slug' => 'contas-bancarias-e-roteamento',
                'module' => 'financial',
                'title' => 'Contas bancárias e regras de destino',
                'summary' => 'Cadastre contas e diga ao sistema para onde vai cada tipo de lançamento.',
                'audience' => 'sindico',
                'level' => 'intermediario',
                'minutes' => 7,
                'critical' => true,
                'tags' => ['contas', 'roteamento', 'caixa'],
                'route_hint' => 'financial.bank-accounts.index',
                'video' => 'contas-bancarias.mp4',
                'video_url' => null,
                'objectives' => [
                    'Cadastrar conta principal e contas auxiliares',
                    'Configurar regras de destino (taxas, despesas, etc.)',
                ],
                'steps' => [
                    [
                        'title' => 'Cadastrar conta',
                        'body' => 'Vá em **Financeiro → Contas**. Cadastre nome, banco, agência/conta e marque uma como **Principal**. O saldo inicial pode ser informado e será atualizado nas conciliações.',
                    ],
                    [
                        'title' => 'Regras de destino',
                        'body' => 'Em **Configurações financeiras → Regras de destino**, associe origens (recebimento de taxas, despesas manuais, etc.) a uma conta. Isso faz a conciliação e o caixa agruparem corretamente.',
                    ],
                    [
                        'title' => 'Por que isso importa',
                        'body' => 'Sem roteamento correto, lançamentos aparecem na conta errada na conciliação. Ajuste as regras antes de importar extratos.',
                    ],
                ],
                'checklist' => [
                    'Há pelo menos uma conta ativa',
                    'Conta principal definida',
                    'Regras de destino revisadas',
                ],
            ],
            [
                'slug' => 'taxas-e-cobrancas',
                'module' => 'financial',
                'title' => 'Taxas e cobranças',
                'summary' => 'Criar taxa, gerar cobranças, acompanhar vencimentos e baixar pagamentos.',
                'audience' => 'sindico',
                'level' => 'intermediario',
                'minutes' => 10,
                'critical' => true,
                'tags' => ['taxas', 'cobranças', 'asaas', 'boleto'],
                'route_hint' => 'fees.index',
                'video' => 'taxas-cobrancas.mp4',
                'video_url' => null,
                'objectives' => [
                    'Criar uma taxa condominial',
                    'Gerar cobranças para as unidades',
                    'Entender Minhas Cobranças do morador e baixa manual',
                ],
                'steps' => [
                    [
                        'title' => 'Criar taxa',
                        'body' => 'Em **Financeiro → Taxas**, crie a taxa com valor, vencimento, recorrência e unidades (aplicar a todas ou configurar por unidade). Salve e, se desejar, gere cobranças imediatamente.',
                    ],
                    [
                        'title' => 'Cobranças e Asaas',
                        'body' => 'As cobranças aparecem na listagem. Com recebimento via Asaas, o sistema pode gerar boleto/PIX. Confirme o modo de recebimento (plataforma ou próprio) nas configurações do condomínio.',
                    ],
                    [
                        'title' => 'Baixa e atraso',
                        'body' => 'Cobranças vencidas aparecem como **Em atraso**. Você pode marcar como paga manualmente (com permissão) ou aguardar o webhook do gateway. Use o painel de status financeiro para inadimplência.',
                    ],
                    [
                        'title' => 'O que o síndico deve fazer todo mês',
                        'body' => '1) Conferir se todas as unidades elegíveis têm cobrança.\n2) Enviar lembretes se necessário.\n3) Tratar inadimplentes (restrição e liberação temporária, se o condomínio usar).\n4) Só então avançar no fechamento mensal.',
                    ],
                ],
                'checklist' => [
                    'Taxa do mês configurada',
                    'Cobranças geradas / cobertas',
                    'Painel de adimplência revisado',
                ],
            ],
            [
                'slug' => 'caixa-do-condominio',
                'module' => 'financial',
                'title' => 'Caixa do condomínio',
                'summary' => 'Registrar receitas e despesas manuais que alimentam a conciliação.',
                'audience' => 'sindico',
                'level' => 'intermediario',
                'minutes' => 6,
                'critical' => true,
                'tags' => ['caixa', 'despesas', 'receitas'],
                'route_hint' => 'financial.accounts.index',
                'video' => 'caixa-condominio.mp4',
                'video_url' => null,
                'objectives' => [
                    'Registrar pagamento (despesa) e recebimento avulso',
                    'Escolher a categoria correta da despesa (energia, pessoal, impostos…)',
                    'Anexar comprovante quando houver',
                    'Entender que lançamentos conciliados não devem ser alterados à toa',
                ],
                'steps' => [
                    [
                        'title' => 'Abrir o caixa',
                        'body' => 'Em **Financeiro → Caixa do condomínio**, filtre o período. Você verá entradas (taxas, avulsos) e saídas (pagamentos).',
                    ],
                    [
                        'title' => 'Registrar despesa',
                        'body' => 'Use **Registrar pagamento**: descrição, valor, data, **categoria** (obrigatória), forma e conta bancária. A categoria alimenta o gráfico **Despesas por categoria** e os alertas do dashboard. Anexe NF/comprovante se possível.',
                    ],
                    [
                        'title' => 'Registrar receita avulsa',
                        'body' => 'Use **Registrar recebimento** para valores que não vêm de taxa (ex.: reembolso). Escolha a conta correta.',
                    ],
                    [
                        'title' => 'Atenção',
                        'body' => 'Lançamentos já vinculados a uma conciliação fechada não devem ser “apagados” sem cancelar a conciliação. Prefira corrigir antes de fechar o período. Folha de funcionários e encargos entram automaticamente nas categorias **Pessoal** e **Encargos**.',
                    ],
                ],
                'checklist' => [
                    'Registrou um pagamento com categoria',
                    'Conferiu a conta bancária do lançamento',
                    'Viu o impacto no dashboard (categorias / alertas)',
                ],
            ],
            [
                'slug' => 'dashboard-custos-por-categoria',
                'module' => 'financial',
                'title' => 'Dashboard: custos, alertas e previsões por categoria',
                'summary' => 'Usar o painel do síndico para decidir cortes e acompanhar energia, pessoal, impostos e taxas.',
                'audience' => 'sindico',
                'level' => 'intermediario',
                'minutes' => 5,
                'critical' => true,
                'tags' => ['dashboard', 'categorias', 'previsão', 'energia', 'folha'],
                'route_hint' => 'dashboard',
                'video' => 'dashboard-categorias.mp4',
                'video_url' => null,
                'objectives' => [
                    'Ler o gráfico de despesas por categoria do ano',
                    'Interpretar alertas (alta, estável, queda) vs mês anterior e média 3 meses',
                    'Usar a previsão de custos até o fim do ano para decisões de corte',
                ],
                'steps' => [
                    [
                        'title' => 'Pré-requisito',
                        'body' => 'Só funciona bem se as despesas do caixa tiverem **categoria**. Sem categoria, o sistema mostra o aviso de % sem classificação.',
                    ],
                    [
                        'title' => 'Gráfico de categorias',
                        'body' => 'No dashboard, o card **Despesas por categoria** soma o caixa do ano (energia, pessoal, encargos, manutenção, etc.).',
                    ],
                    [
                        'title' => 'Alertas e tendências',
                        'body' => 'O painel compara o mês atual com o anterior e com a média dos últimos 3 meses. Alta ≥15% = atenção; ≥30% = urgente (ex.: energia ou horas extras). Queda ≥15% aparece como sinal positivo.',
                    ],
                    [
                        'title' => 'Previsão anual',
                        'body' => 'A projeção combina o gasto já realizado no ano com o ritmo mensal recente (run-rate) até dezembro. Use para antecipar pressão de caixa e negociar contratos.',
                    ],
                ],
                'checklist' => [
                    'Abriu o dashboard no modo financeiro completo',
                    'Identificou pelo menos uma categoria em atenção',
                    'Conferiu a previsão das maiores categorias',
                ],
            ],
            [
                'slug' => 'conciliacao-extrato-csv-ofx',
                'module' => 'financial',
                'title' => 'Conciliação bancária com extrato CSV/OFX',
                'summary' => 'Importar extrato, revisar vínculos, criar lançamentos faltantes e fechar o período.',
                'audience' => 'sindico',
                'level' => 'avancado',
                'minutes' => 12,
                'critical' => true,
                'tags' => ['conciliação', 'ofx', 'csv', 'extrato', 'saldo'],
                'route_hint' => 'bank-reconciliation.index',
                'video' => 'conciliacao-bancaria.mp4',
                'video_url' => null,
                'objectives' => [
                    'Importar OFX ou CSV',
                    'Entender automático, sugestão, só no banco e só no sistema',
                    'Criar lançamento a partir do extrato',
                    'Confirmar conciliação sem confundir com o upload',
                ],
                'steps' => [
                    [
                        'title' => 'Abrir Conciliação Bancária',
                        'body' => 'Menu **Financeiro → Conciliação Bancária**. Escolha a **conta**. O botão amarelo **Importar extrato** aparece após selecionar a conta.',
                    ],
                    [
                        'title' => 'Importar arquivo',
                        'body' => 'Baixe o extrato no internet banking (OFX preferível, ou CSV). Em **Importar extrato**, selecione a conta e o arquivo (até 5 MB) e clique **Importar e conciliar**. O processamento é imediato. O mesmo arquivo não pode ser reimportado na mesma conta.',
                    ],
                    [
                        'title' => 'CSV com cabeçalho estranho',
                        'body' => 'Se o sistema não reconhecer as colunas, aparece **Mapear colunas**: indique Data, Descrição e Valor (ou Débito e Crédito) e aplique.',
                    ],
                    [
                        'title' => 'Revisão — 4 blocos',
                        'body' => "**Automáticos:** valor e data iguais, um único candidato — já vinculados. Use **Desfazer** se estiver errado.\n\n**Sugestões:** data perto (±3 dias) ou valor quase igual (±R$ 0,50) — use **Aceitar**, **Vincular** outro ou **Ignorar**.\n\n**Só no extrato:** existe no banco e não no sistema — **Criar no caixa** ou ignorar.\n\n**Só no sistema:** existe no SindCON e não veio neste arquivo — fica pendente.",
                    ],
                    [
                        'title' => 'Importante: upload não muda saldo',
                        'body' => 'Importar e vincular **não** altera o saldo da conta. O saldo só muda quando você **Confirma a conciliação** do período.',
                    ],
                    [
                        'title' => 'Fechar o período',
                        'body' => 'Clique **Ir para fechamento**. Com extrato ativo, só entram lançamentos **já vinculados**. Se o OFX trouxer saldo final diferente do projetado, marque o checkbox de ciência. Depois clique **Confirmar conciliação**.',
                    ],
                    [
                        'title' => 'Cancelar última',
                        'body' => 'Se errou agora, use **Cancelar última conciliação** (reverte o saldo daquela operação). Não há cancelamento em cascata de várias antigas.',
                    ],
                ],
                'checklist' => [
                    'Importou um extrato de teste ou real',
                    'Revisou sugestões e “só no extrato”',
                    'Entendeu que confirmar é o que atualiza o saldo',
                ],
            ],
            [
                'slug' => 'fechamento-mensal',
                'module' => 'financial',
                'title' => 'Fechamento mensal (checklist)',
                'summary' => 'Percorrer os 9 passos e só encerrar quando não houver pendências críticas.',
                'audience' => 'sindico',
                'level' => 'avancado',
                'minutes' => 8,
                'critical' => true,
                'tags' => ['fechamento', 'checklist', 'mês'],
                'route_hint' => 'monthly-closing.index',
                'video' => 'fechamento-mensal.mp4',
                'video_url' => null,
                'objectives' => [
                    'Abrir o fechamento do mês',
                    'Interpretar status done/warning de cada passo',
                    'Usar o passo 8 ligado à conciliação de extrato',
                ],
                'steps' => [
                    [
                        'title' => 'Abrir o fechamento',
                        'body' => '**Financeiro → Fechamento mensal**. Escolha a competência. Cada passo mostra métricas e atalhos.',
                    ],
                    [
                        'title' => 'Os 9 passos',
                        'body' => "1. Geração de taxas\n2. Cobertura de unidades\n3. Cobranças\n4. Multas\n5. Reservas\n6. Funcionários\n7. Caixa manual\n8. Conciliação bancária (linhas de extrato sem vínculo contam aqui)\n9. Prestação de contas",
                    ],
                    [
                        'title' => 'O que fazer',
                        'body' => 'Resolva os passos em **warning** usando os botões de ação. Não “force” o encerramento com conciliação incompleta se o conselho exige saldo batido.',
                    ],
                ],
                'checklist' => [
                    'Abriu o mês corrente no fechamento',
                    'Verificou o passo 8 (conciliação)',
                ],
            ],
            [
                'slug' => 'prestacao-de-contas',
                'module' => 'financial',
                'title' => 'Prestação de contas',
                'summary' => 'Gerar ou enviar a prestação para moradores e conselho.',
                'audience' => 'sindico',
                'level' => 'intermediario',
                'minutes' => 5,
                'critical' => false,
                'tags' => ['prestação', 'pdf', 'transparência'],
                'route_hint' => 'accountability-reports.index',
                'video' => null,
                'video_url' => null,
                'objectives' => [
                    'Gerar relatório no modo completo',
                    'Saber o fluxo de upload no modo simplificado',
                ],
                'steps' => [
                    [
                        'title' => 'Modo completo',
                        'body' => 'Após o fechamento, abra **Prestação de contas**, escolha o período e exporte PDF/Excel. Confira receitas, despesas e saldo antes de divulgar.',
                    ],
                    [
                        'title' => 'Modo simplificado',
                        'body' => 'Faça upload do arquivo da administração. O conselho pode precisar aprovar, conforme configuração.',
                    ],
                ],
                'checklist' => [
                    'Localizou a tela de prestação de contas',
                ],
            ],
            [
                'slug' => 'multas-e-inadimplencia',
                'module' => 'financial',
                'title' => 'Multas e inadimplência',
                'summary' => 'Aplicar multa, restringir inadimplentes e conceder liberação temporária.',
                'audience' => 'sindico',
                'level' => 'intermediario',
                'minutes' => 7,
                'critical' => false,
                'tags' => ['multas', 'inadimplentes', 'restrição'],
                'route_hint' => 'fines.index',
                'video' => null,
                'video_url' => null,
                'objectives' => [
                    'Emitir multa com notificação',
                    'Ativar restrição de inadimplentes',
                    'Conceder liberação temporária (1–30 dias)',
                ],
                'steps' => [
                    [
                        'title' => 'Multas',
                        'body' => 'Em **Financeiro → Multas**, registre a infração. O sistema pode gerar PDF de notificação e cobrança vinculada.',
                    ],
                    [
                        'title' => 'Restrição',
                        'body' => 'Em **Meu Condomínio**, ative `Restringir inadimplentes` se a convenção permitir. O morador fica com menu reduzido até regularizar.',
                    ],
                    [
                        'title' => 'Liberação temporária',
                        'body' => 'Na ficha do usuário, conceda liberação de 1 a 30 dias quando fizer sentido (acordo, boleto em compensação, etc.).',
                    ],
                ],
                'checklist' => [
                    'Sabe onde emitir multa',
                    'Sabe onde ligar a restrição de inadimplentes',
                ],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function gestaoTutorials(): array
    {
        return [
            [
                'slug' => 'unidades-e-moradores',
                'module' => 'gestao',
                'title' => 'Unidades e moradores',
                'summary' => 'Cadastrar unidades, vincular moradores e regimes de ocupação.',
                'audience' => 'sindico',
                'level' => 'iniciante',
                'minutes' => 8,
                'critical' => false,
                'tags' => ['unidades', 'usuários', 'aluguel'],
                'route_hint' => 'units.index',
                'video' => 'unidades-moradores.mp4',
                'video_url' => null,
                'objectives' => [
                    'Cadastrar unidade',
                    'Vincular morador responsável',
                    'Entender particular vs aluguel',
                ],
                'steps' => [
                    [
                        'title' => 'Unidades',
                        'body' => '**Gestão → Unidades**: ao criar uma unidade, o topo da tela mostra o **limite do contrato**, quantas já foram cadastradas e quantas **ainda pode criar**. Cadastre bloco, número, tipo e situação. Mantenha unidades inativas fora da cobrança automática.',
                    ],
                    [
                        'title' => 'Usuários',
                        'body' => '**Gestão → Usuários**: crie morador, porteiro, conselho etc. Morador exige unidade. Agregado vincula-se ao morador responsável.',
                    ],
                    [
                        'title' => 'Aluguel',
                        'body' => 'No regime **aluguel**, informe proprietário e contrato. Taxas do condomínio vão ao proprietário; multas/reservas do inquilino vão às Minhas pendências dele.',
                    ],
                ],
                'checklist' => [
                    'Abriu a lista de unidades',
                    'Conferiu um morador vinculado',
                ],
            ],
            [
                'slug' => 'aprovar-cadastros',
                'module' => 'gestao',
                'title' => 'Aprovar auto-cadastros',
                'summary' => 'Moradores se cadastram com o código do condomínio; o síndico aprova.',
                'audience' => 'sindico',
                'level' => 'iniciante',
                'minutes' => 4,
                'critical' => false,
                'tags' => ['cadastro', 'aprovação'],
                'route_hint' => 'users.index',
                'video' => null,
                'video_url' => null,
                'objectives' => [
                    'Encontrar usuários pendentes',
                    'Aprovar ou rejeitar com segurança',
                ],
                'steps' => [
                    [
                        'title' => 'Pendentes',
                        'body' => 'Em Usuários, filtre por status **pendente**. Confira unidade e dados antes de ativar.',
                    ],
                    [
                        'title' => 'Reset de senha',
                        'body' => 'Nunca invente senha fixa na tela: use o fluxo de **reset por e-mail** com link temporário.',
                    ],
                ],
                'checklist' => [
                    'Sabe filtrar usuários pendentes',
                ],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function operationalTutorials(): array
    {
        return [
            [
                'slug' => 'reservas-e-espacos',
                'module' => 'spaces',
                'title' => 'Espaços e reservas',
                'summary' => 'Cadastrar espaço, aprovar reservas e entender pré-reserva paga.',
                'audience' => 'sindico',
                'level' => 'intermediario',
                'minutes' => 7,
                'critical' => false,
                'tags' => ['reservas', 'espaços'],
                'route_hint' => 'spaces.index',
                'video' => null,
                'video_url' => null,
                'objectives' => ['Cadastrar um espaço', 'Aprovar ou rejeitar reserva'],
                'steps' => [
                    ['title' => 'Cadastro', 'body' => 'Em **Espaços**, cadastre salão, churrasqueira etc. Defina regras de aprovação (automática/manual) e valores se houver cobrança.'],
                    ['title' => 'Gestão', 'body' => 'Em gerenciar reservas, aprove, rejeite ou cancele. Conflitos de horário são bloqueados pelo sistema.'],
                ],
                'checklist' => ['Localizou Espaços e Reservas no menu'],
            ],
            [
                'slug' => 'encomendas-sindico',
                'module' => 'packages',
                'title' => 'Encomendas (visão do síndico)',
                'summary' => 'Como a portaria registra e como você consulta movimentações.',
                'audience' => 'sindico',
                'level' => 'iniciante',
                'minutes' => 5,
                'critical' => false,
                'tags' => ['encomendas', 'ocr', 'portaria'],
                'route_hint' => 'packages.reports',
                'video' => null,
                'video_url' => null,
                'objectives' => ['Abrir relatório de movimentações', 'Entender senha de retirada'],
                'steps' => [
                    ['title' => 'Operação da portaria', 'body' => 'O porteiro registra chegada (manual ou OCR) e o morador recebe senha no WhatsApp para retirada.'],
                    ['title' => 'Seu papel', 'body' => 'Em **Encomendas → Movimentações**, filtre período e exporte se precisar para arquivo.'],
                ],
                'checklist' => ['Abriu o relatório de encomendas'],
            ],
            [
                'slug' => 'controle-de-acesso',
                'module' => 'access_control',
                'title' => 'Liberações e portaria',
                'summary' => 'Visitantes, listas e credencial digital “Outro”.',
                'audience' => 'sindico',
                'level' => 'intermediario',
                'minutes' => 6,
                'critical' => false,
                'tags' => ['acesso', 'visitante', 'qr'],
                'route_hint' => 'access-control.index',
                'video' => null,
                'video_url' => null,
                'objectives' => ['Entender liberação nomeada', 'Saber o que a portaria valida'],
                'steps' => [
                    ['title' => 'Morador libera', 'body' => 'Morador cria liberação (presets ou visitante Outro com QR/senha até a validade).'],
                    ['title' => 'Portaria', 'body' => 'Porteiro usa painel e liberação rápida (QR/senha). Cada entrada gera movimento sem “gastar” a liberação até expirar.'],
                ],
                'checklist' => ['Abriu o módulo de controle de acesso'],
            ],
            [
                'slug' => 'assembleias',
                'module' => 'assemblies',
                'title' => 'Assembleias',
                'summary' => 'Convocar, abrir votação e publicar ata.',
                'audience' => 'sindico',
                'level' => 'intermediario',
                'minutes' => 6,
                'critical' => false,
                'tags' => ['assembleia', 'votação'],
                'route_hint' => 'assemblies.index',
                'video' => null,
                'video_url' => null,
                'objectives' => ['Criar assembleia', 'Entender quem vota em unidade alugada'],
                'steps' => [
                    ['title' => 'Criar', 'body' => 'Cadastre pauta, data e tipo de voto. Publique para os aptos.'],
                    ['title' => 'Aluguel', 'body' => 'Em unidades alugadas, quem vota é o **proprietário**, não o inquilino.'],
                ],
                'checklist' => ['Localizou Assembleias no menu'],
            ],
            [
                'slug' => 'comunicacao-e-landing',
                'module' => 'communication',
                'title' => 'Comunicação e landing page',
                'summary' => 'Avisos, ocorrências, Fale com o síndico e página pública.',
                'audience' => 'sindico',
                'level' => 'iniciante',
                'minutes' => 5,
                'critical' => false,
                'tags' => ['mensagens', 'landing', 'ocorrências'],
                'route_hint' => 'condominium.landing.admin',
                'video' => null,
                'video_url' => null,
                'objectives' => ['Publicar um aviso', 'Saber onde editar a landing'],
                'steps' => [
                    ['title' => 'Canais', 'body' => 'Use mensagens/avisos para comunicados. O livro de ocorrências pode ser sigiloso.'],
                    ['title' => 'Landing', 'body' => 'Em Comunicação → Landing, escolha template, publique conteúdos e baixe o QR da página pública.'],
                ],
                'checklist' => ['Abriu a área de comunicação'],
            ],
            [
                'slug' => 'ordens-de-servico',
                'module' => 'service_orders',
                'title' => 'Ordens de serviço',
                'summary' => 'Receber solicitações, atualizar status e gerar cobrança se houver ressarcimento.',
                'audience' => 'sindico',
                'level' => 'intermediario',
                'minutes' => 5,
                'critical' => false,
                'tags' => ['os', 'manutenção'],
                'route_hint' => 'service-orders.index',
                'video' => null,
                'video_url' => null,
                'objectives' => ['Gerenciar uma OS', 'Entender visibilidade ao inquilino'],
                'steps' => [
                    ['title' => 'Fluxo', 'body' => 'Morador (ou proprietário no aluguel) abre OS. Você atualiza status, mensagens e itens.'],
                    ['title' => 'Cobrança', 'body' => 'Se houver ressarcimento, gere a cobrança vinculada conforme a política do condomínio.'],
                ],
                'checklist' => ['Localizou Ordens de Serviço'],
            ],
            [
                'slug' => 'documentos-e-regimento',
                'module' => 'documents',
                'title' => 'Documentos e regimento',
                'summary' => 'Publicar regimento e documentos oficiais para consulta.',
                'audience' => 'sindico',
                'level' => 'iniciante',
                'minutes' => 4,
                'critical' => false,
                'tags' => ['regimento', 'documentos'],
                'route_hint' => 'library-documents.index',
                'video' => null,
                'video_url' => null,
                'objectives' => ['Publicar um documento'],
                'steps' => [
                    ['title' => 'Biblioteca', 'body' => 'Envie PDFs e organize a biblioteca do condomínio.'],
                    ['title' => 'Regimento', 'body' => 'Mantenha o regimento atualizado; alterações ficam no histórico.'],
                ],
                'checklist' => ['Abriu Documentos'],
            ],
            [
                'slug' => 'marketplace-e-caronas',
                'module' => 'marketplace',
                'title' => 'Marketplace e caronas',
                'summary' => 'Moderar anúncios e entender o módulo de caronas.',
                'audience' => 'sindico',
                'level' => 'iniciante',
                'minutes' => 4,
                'critical' => false,
                'tags' => ['marketplace', 'caronas'],
                'route_hint' => 'marketplace.index',
                'video' => null,
                'video_url' => null,
                'objectives' => ['Saber onde moderar anúncios'],
                'steps' => [
                    ['title' => 'Marketplace', 'body' => 'Moradores anunciam; você pode moderar pelo painel administrativo do marketplace.'],
                    ['title' => 'Caronas', 'body' => 'Ofertas e reservas ficam no módulo Caronas, se estiver ligado.'],
                ],
                'checklist' => ['Conferiu se os módulos estão ativos'],
            ],
            [
                'slug' => 'pets',
                'module' => 'pets',
                'title' => 'Pets e QR Code',
                'summary' => 'Cadastro de animais e verificação na portaria.',
                'audience' => 'sindico',
                'level' => 'iniciante',
                'minutes' => 3,
                'critical' => false,
                'tags' => ['pets', 'qr'],
                'route_hint' => 'pets.index',
                'video' => null,
                'video_url' => null,
                'objectives' => ['Entender o QR do pet'],
                'steps' => [
                    ['title' => 'Cadastro', 'body' => 'Moradores cadastram pets com foto. O QR público ajuda identificação.'],
                    ['title' => 'Portaria', 'body' => 'Porteiro pode verificar o pet pelo fluxo de verificação.'],
                ],
                'checklist' => ['Abriu o módulo Pets (se ativo)'],
            ],
        ];
    }
}
