-- ============================================================================
-- SCRIPT PARA LIMPAR TODOS OS DADOS DO MÓDULO FINANCEIRO (PostgreSQL)
-- ============================================================================
-- 
-- ATENÇÃO: Este script apaga TODOS os registros financeiros e seus relacionamentos.
-- Use apenas para testes. Faça backup antes de executar em produção!
--
-- Tabelas que serão limpas:
-- - bank_account_reconciliation_items (itens de conciliação)
-- - bank_account_reconciliations (conciliações bancárias)
-- - bank_account_balances (saldos de contas bancárias)
-- - payment_cancellations (cancelamentos de pagamentos)
-- - payments (pagamentos)
-- - receipts (comprovantes)
-- - condominium_accounts (contas do condomínio)
-- - transactions (transações)
-- - charges (cobranças)
-- - fee_unit_configurations (configurações de taxas por unidade)
-- - fees (taxas)
-- - bank_accounts (contas bancárias)
--
-- ============================================================================

-- Desabilitar triggers temporariamente
SET session_replication_role = 'replica';

-- ============================================================================
-- ETAPA 1: Apagar itens de conciliação bancária
-- ============================================================================
TRUNCATE TABLE bank_account_reconciliation_items RESTART IDENTITY CASCADE;

-- ============================================================================
-- ETAPA 2: Apagar conciliações bancárias
-- ============================================================================
TRUNCATE TABLE bank_account_reconciliations RESTART IDENTITY CASCADE;

-- ============================================================================
-- ETAPA 3: Apagar saldos históricos de contas bancárias
-- ============================================================================
TRUNCATE TABLE bank_account_balances RESTART IDENTITY CASCADE;

-- ============================================================================
-- ETAPA 4: Apagar cancelamentos de pagamentos
-- ============================================================================
TRUNCATE TABLE payment_cancellations RESTART IDENTITY CASCADE;

-- ============================================================================
-- ETAPA 5: Apagar pagamentos
-- ============================================================================
TRUNCATE TABLE payments RESTART IDENTITY CASCADE;

-- ============================================================================
-- ETAPA 6: Apagar comprovantes (receipts)
-- ============================================================================
TRUNCATE TABLE receipts RESTART IDENTITY CASCADE;

-- ============================================================================
-- ETAPA 7: Limpar reconciliation_id nas contas do condomínio antes de apagar
-- ============================================================================
UPDATE condominium_accounts SET reconciliation_id = NULL WHERE reconciliation_id IS NOT NULL;

-- Apagar contas do condomínio
TRUNCATE TABLE condominium_accounts RESTART IDENTITY CASCADE;

-- ============================================================================
-- ETAPA 8: Limpar reconciliation_id nas transações antes de apagar
-- ============================================================================
UPDATE transactions SET reconciliation_id = NULL WHERE reconciliation_id IS NOT NULL;

-- Limpar parent_transaction_id (auto-relacionamento)
UPDATE transactions SET parent_transaction_id = NULL WHERE parent_transaction_id IS NOT NULL;

-- Apagar transações
TRUNCATE TABLE transactions RESTART IDENTITY CASCADE;

-- ============================================================================
-- ETAPA 9: Apagar cobranças (charges)
-- ============================================================================
TRUNCATE TABLE charges RESTART IDENTITY CASCADE;

-- ============================================================================
-- ETAPA 10: Apagar configurações de taxas por unidade
-- ============================================================================
TRUNCATE TABLE fee_unit_configurations RESTART IDENTITY CASCADE;

-- ============================================================================
-- ETAPA 11: Apagar taxas (fees)
-- ============================================================================
TRUNCATE TABLE fees RESTART IDENTITY CASCADE;

-- ============================================================================
-- ETAPA 12: Apagar contas bancárias
-- ============================================================================
TRUNCATE TABLE bank_accounts RESTART IDENTITY CASCADE;

-- Reabilitar triggers
SET session_replication_role = 'origin';

-- ============================================================================
-- VERIFICAÇÃO: Contar registros restantes (todos devem ser 0)
-- ============================================================================
SELECT 
    'bank_account_reconciliation_items' AS tabela, 
    COUNT(*) AS registros_restantes 
FROM bank_account_reconciliation_items
UNION ALL
SELECT 'bank_account_reconciliations', COUNT(*) FROM bank_account_reconciliations
UNION ALL
SELECT 'bank_account_balances', COUNT(*) FROM bank_account_balances
UNION ALL
SELECT 'payment_cancellations', COUNT(*) FROM payment_cancellations
UNION ALL
SELECT 'payments', COUNT(*) FROM payments
UNION ALL
SELECT 'receipts', COUNT(*) FROM receipts
UNION ALL
SELECT 'condominium_accounts', COUNT(*) FROM condominium_accounts
UNION ALL
SELECT 'transactions', COUNT(*) FROM transactions
UNION ALL
SELECT 'charges', COUNT(*) FROM charges
UNION ALL
SELECT 'fee_unit_configurations', COUNT(*) FROM fee_unit_configurations
UNION ALL
SELECT 'fees', COUNT(*) FROM fees
UNION ALL
SELECT 'bank_accounts', COUNT(*) FROM bank_accounts;

-- ============================================================================
-- FIM DO SCRIPT
-- ============================================================================
