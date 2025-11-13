# Testes do Módulo Financeiro – 13/11/2025

## Contexto
- Ambiente: `localhost:8000`
- Perfil autenticado: Administrador (`dex.vanoni@gmail.com`)
- Objetivo: Exercitar operações de transações, taxas, cobranças, adimplência, contas bancárias, prestação de contas e fluxo de recebimentos/pagamentos avulsos via interface web.

## Ações Executadas
- Cadastro de transação (`Receita Teste Automatizada`, R$ 1.500,00, 13/11/2025, método PIX).
- Criação de taxa (`Taxa Teste Automação`, valor base R$ 150,00, unidade Bloco 1 - Ap 101).
- Navegação pelo painel de cobranças, geração de filtros e tentativa de criação manual.
- Consulta ao Painel de Adimplência.
- Cadastro de nova conta bancária (`Conta Teste Automação`, saldo inicial R$ 5.000,00).
- Geração de relatório em Prestação de Contas (verificação pós-cadastros).
- Registro de recebimento avulso (R$ 250,00, PIX) e novo pagamento (R$ 180,00, PIX) nas Contas do Condomínio.

## Problemas Encontrados
1. **Transação não aparece na listagem principal**  
   - **Passos**: `Financeiro > Gerenciar Transações` → `Nova Transação` → preencher dados válidos → salvar.  
   - **Resultado observado**: alerta de sucesso, porém a tabela permanece vazia (nenhuma linha exibida).  
   - **Impacto**: impossibilidade de auditar ou editar o lançamento recém-criado; risco de duplicidade por ausência de feedback visual.

2. **Cobrança automática fica marcada como “Paid” imediatamente após criar a taxa**  
   - **Passos**: `Financeiro > Configurar Taxas` → `Nova Taxa` → vincular unidade e salvar.  
   - **Resultado observado**: em `Detalhes da Taxa` a cobrança gerada (`Taxa Teste Automação - dezembro 2025`) aparece com status `Paid`.  
   - **Impacto**: confunde a conciliação, pois lançamentos deveriam iniciar como pendentes até confirmação de pagamento.

3. **Cobranças recém-geradas não aparecem em `Gerenciar Cobranças`**  
   - **Passos**: após criar a taxa acima, acessar `Financeiro > Gerenciar Cobranças`, limpar filtros ou buscar por “Automação”.  
   - **Resultado observado**: listagem retorna somente cobranças antigas (“TX-Condomínio - outubro 2025”); busca por “Automação” não encontra registros.  
   - **Impacto**: gestor não consegue acompanhar ou cobrar lançamentos vinculados às taxas recém-cadastradas.

4. **Indicadores de `Gerenciar Cobranças` exibem “--”**  
   - **Passos**: abrir `Financeiro > Gerenciar Cobranças` com dados existentes (inclusive a cobrança paga de outubro).  
   - **Resultado observado**: cards “Pendentes”, “Em Atraso”, “Pagas”, “A Receber” mostram `--`.  
   - **Impacto**: perda de visibilidade sobre volumes e valores consolidados.

5. **Painel de Adimplência não reflete valores pagos**  
   - **Passos**: `Financeiro > Painel de Adimplência`, manter período padrão.  
   - **Resultado observado**: todas as 264 unidades listadas como adimplentes com `R$ 0,00` pago, apesar de existir cobrança quitada (R$ 370,58) e recebimento avulso recente.  
   - **Impacto**: indicadores ficam incorretos, dificultando tomada de decisão e comunicação com inadimplentes.

## Observações Gerais
- Botão “Nova Cobrança” apenas orienta o usuário a usar “Recebimento Avulso” nas Contas do Condomínio; registrar manualmente por ali funciona, porém não há forma direta de lançar cobrança vinculada a unidade específica nesta tela.
- Erros JavaScript de OneSignal surgem em console (ambiente HTTP local); não impactaram a execução, mas podem afetar notificações push em produção caso não tratados.

## Recomendações
- Validar o fluxo de persistência e exibição das transações recém-criadas.
- Ajustar status padrão das cobranças geradas a partir de taxas para `Pendente` (ou equivalente) e garantir que elas apareçam na tela de gerenciamento.
- Revisar agregações utilizadas nos indicadores de cobranças e Painel de Adimplência para refletir os dados reais.
- Considerar UX mais clara para criação de cobranças manuais diretamente na tela de cobranças, reduzindo o desvio para “Contas do Condomínio”.

