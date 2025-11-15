-- ============================================================================
-- SCRIPT PARA LIMPAR TODOS OS DADOS DO MÓDULO FINANCEIRO
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

-- Desabilitar verificações de foreign key temporariamente
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- ETAPA 1: Apagar itens de conciliação bancária
-- ============================================================================
DELETE FROM bank_account_reconciliation_items;
-- Reseta o auto_increment
ALTER TABLE bank_account_reconciliation_items AUTO_INCREMENT = 1;

-- ============================================================================
-- ETAPA 2: Apagar conciliações bancárias
-- ============================================================================
DELETE FROM bank_account_reconciliations;
-- Reseta o auto_increment
ALTER TABLE bank_account_reconciliations AUTO_INCREMENT = 1;

-- ============================================================================
-- ETAPA 3: Apagar saldos históricos de contas bancárias
-- ============================================================================
DELETE FROM bank_account_balances;
-- Reseta o auto_increment
ALTER TABLE bank_account_balances AUTO_INCREMENT = 1;

-- ============================================================================
-- ETAPA 4: Apagar cancelamentos de pagamentos
-- ============================================================================
DELETE FROM payment_cancellations;
-- Reseta o auto_increment
ALTER TABLE payment_cancellations AUTO_INCREMENT = 1;

-- ============================================================================
-- ETAPA 5: Apagar pagamentos
-- ============================================================================
DELETE FROM payments;
-- Reseta o auto_increment
ALTER TABLE payments AUTO_INCREMENT = 1;

-- ============================================================================
-- ETAPA 6: Apagar comprovantes (receipts)
-- ============================================================================
DELETE FROM receipts;
-- Reseta o auto_increment
ALTER TABLE receipts AUTO_INCREMENT = 1;

-- ============================================================================
-- ETAPA 7: Limpar reconciliation_id nas contas do condomínio antes de apagar
-- ============================================================================
UPDATE condominium_accounts SET reconciliation_id = NULL WHERE reconciliation_id IS NOT NULL;

-- Apagar contas do condomínio
DELETE FROM condominium_accounts;
-- Reseta o auto_increment
ALTER TABLE condominium_accounts AUTO_INCREMENT = 1;

-- ============================================================================
-- ETAPA 8: Limpar reconciliation_id nas transações antes de apagar
-- ============================================================================
UPDATE transactions SET reconciliation_id = NULL WHERE reconciliation_id IS NOT NULL;

-- Limpar parent_transaction_id (auto-relacionamento)
UPDATE transactions SET parent_transaction_id = NULL WHERE parent_transaction_id IS NOT NULL;

-- Apagar transações
DELETE FROM transactions;
-- Reseta o auto_increment
ALTER TABLE transactions AUTO_INCREMENT = 1;

-- ============================================================================
-- ETAPA 9: Apagar cobranças (charges)
-- ============================================================================
DELETE FROM charges;
-- Reseta o auto_increment
ALTER TABLE charges AUTO_INCREMENT = 1;

-- ============================================================================
-- ETAPA 10: Apagar configurações de taxas por unidade
-- ============================================================================
DELETE FROM fee_unit_configurations;
-- Reseta o auto_increment
ALTER TABLE fee_unit_configurations AUTO_INCREMENT = 1;

-- ============================================================================
-- ETAPA 11: Apagar taxas (fees)
-- ============================================================================
DELETE FROM fees;
-- Reseta o auto_increment
ALTER TABLE fees AUTO_INCREMENT = 1;

-- ============================================================================
-- ETAPA 12: Apagar contas bancárias
-- ============================================================================
DELETE FROM bank_accounts;
-- Reseta o auto_increment
ALTER TABLE bank_accounts AUTO_INCREMENT = 1;

-- Reabilitar verificações de foreign key
SET FOREIGN_KEY_CHECKS = 1;

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
