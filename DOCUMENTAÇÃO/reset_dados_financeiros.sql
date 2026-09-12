-- =============================================================================
-- CondoCenter — RESET DE DADOS FINANCEIROS (DEV / TESTES)
-- =============================================================================
--
-- OBJETIVO
--   Apagar taxas, cobranças, multas, pagamentos, caixa, conciliação, folha,
--   fechamento mensal, prestação de contas (uploads), ordens de serviço
--   financeiras e auditorias relacionadas — para recomeçar testes do zero.
--
-- IMPACTO
--   ✗ IRREVERSÍVEL sem backup
--   ✗ Zera saldos, histórico de cobranças, multas, lançamentos e conciliações
--   ✗ Remove funcionários e lançamentos de folha
--   ✗ Remove fechamentos mensais conferidos
--   ✗ Limpa campos de pagamento em reservas (pré-reserva)
--   ✓ Mantém: usuários, condomínios, unidades, permissões, reservas (estrutura),
--              espaços, portaria, conversas, marketplace, caronas, etc.
--
-- NÃO INCLUI (opcional — ver seção 3)
--   Assinatura SaaS (condominium_subscriptions), marketplace, caronas
--
-- ANTES DE EXECUTAR
--   1. Confirme ambiente: dev/test — NUNCA rode em produção sem backup formal
--   2. Backup:
--        mysqldump -u root condocenter > backup_antes_reset_financeiro.sql
--   3. Escolha UMA das opções abaixo (comente a outra)
--
-- =============================================================================


-- =============================================================================
-- OPÇÃO 1 — RESET TOTAL (todos os condomínios)
-- Recomendado para ambiente local de testes
--
-- Usamos DELETE (não TRUNCATE): o MySQL/MariaDB bloqueia TRUNCATE em tabelas
-- referenciadas por FK (#1701), mesmo com FOREIGN_KEY_CHECKS = 0.
-- DELETE funciona dentro de transação — ROLLBACK desfaz se ainda não deu COMMIT.
-- =============================================================================

START TRANSACTION;

SET FOREIGN_KEY_CHECKS = 0;

-- Fechamento mensal (filha → pai)
DELETE FROM monthly_closing_step_confirmations;
DELETE FROM monthly_closings;

-- Conciliação bancária
DELETE FROM bank_account_reconciliation_items;
DELETE FROM bank_account_reconciliations;

-- Pagamentos e cobranças
DELETE FROM payment_cancellations;
DELETE FROM payments;
DELETE FROM receipts;

-- Multas
DELETE FROM fine_recipients;
DELETE FROM fines;

-- Folha / funcionários
DELETE FROM employee_financial_entries;
DELETE FROM employees;

-- Créditos de usuário (reembolsos de reserva etc.)
DELETE FROM user_credits;

-- Ordens de serviço (módulo com cobrança)
DELETE FROM service_order_items;
DELETE FROM service_order_messages;
DELETE FROM service_orders;

-- Taxas e cobranças
DELETE FROM charges;
DELETE FROM fee_unit_configurations;
DELETE FROM fees;

-- Caixa e transações legadas
DELETE FROM condominium_accounts;
DELETE FROM transactions;

-- Extratos e contas bancárias
DELETE FROM bank_statements;
DELETE FROM bank_account_balances;
DELETE FROM bank_account_routing_rules;
DELETE FROM bank_accounts;

-- Prestação de contas (uploads simplificado)
DELETE FROM accountability_report_uploads;

-- Auditoria de modelos financeiros
DELETE FROM audits
WHERE auditable_type IN (
    'App\\Models\\Charge',
    'App\\Models\\Fee',
    'App\\Models\\FeeUnitConfiguration',
    'App\\Models\\Payment',
    'App\\Models\\PaymentCancellation',
    'App\\Models\\Transaction',
    'App\\Models\\CondominiumAccount',
    'App\\Models\\BankAccount',
    'App\\Models\\Fine',
    'App\\Models\\Employee',
    'App\\Models\\EmployeeFinancialEntry'
);

-- Limpar campos de pagamento nas reservas (mantém a reserva)
UPDATE reservations
SET
    prereservation_status     = NULL,
    payment_deadline          = NULL,
    payment_completed_at      = NULL,
    payment_reference         = NULL,
    prereservation_amount     = NULL;

SET FOREIGN_KEY_CHECKS = 1;

-- Revise o resultado antes de confirmar:
-- SELECT COUNT(*) FROM charges;
-- SELECT COUNT(*) FROM fees;
-- SELECT COUNT(*) FROM fines;

COMMIT;
-- Para desfazer antes do COMMIT: ROLLBACK;


-- =============================================================================
-- OPÇÃO 2 — RESET POR CONDOMÍNIO (descomente e ajuste @condominium_id)
-- =============================================================================
/*
SET @condominium_id = 1;  -- ← altere para o ID do condomínio

START TRANSACTION;

SET FOREIGN_KEY_CHECKS = 0;

-- Fechamento mensal
DELETE mcsc FROM monthly_closing_step_confirmations mcsc
INNER JOIN monthly_closings mc ON mc.id = mcsc.monthly_closing_id
WHERE mc.condominium_id = @condominium_id;

DELETE FROM monthly_closings WHERE condominium_id = @condominium_id;

-- Conciliação
DELETE bari FROM bank_account_reconciliation_items bari
INNER JOIN bank_account_reconciliations bar ON bar.id = bari.reconciliation_id
WHERE bar.condominium_id = @condominium_id;

DELETE FROM bank_account_reconciliations WHERE condominium_id = @condominium_id;

-- Pagamentos (via cobranças do condomínio)
DELETE pc FROM payment_cancellations pc
INNER JOIN charges c ON c.id = pc.charge_id
WHERE c.condominium_id = @condominium_id;

DELETE p FROM payments p
INNER JOIN charges c ON c.id = p.charge_id
WHERE c.condominium_id = @condominium_id;

-- Recibos (via transações do condomínio)
DELETE r FROM receipts r
INNER JOIN transactions t ON t.id = r.transaction_id
WHERE t.condominium_id = @condominium_id;

-- Multas
DELETE fr FROM fine_recipients fr
INNER JOIN fines f ON f.id = fr.fine_id
WHERE f.condominium_id = @condominium_id;

DELETE FROM fines WHERE condominium_id = @condominium_id;

-- Folha
DELETE efe FROM employee_financial_entries efe
INNER JOIN employees e ON e.id = efe.employee_id
WHERE e.condominium_id = @condominium_id;

DELETE FROM employees WHERE condominium_id = @condominium_id;

-- Créditos
DELETE FROM user_credits WHERE condominium_id = @condominium_id;

-- Ordens de serviço
DELETE soi FROM service_order_items soi
INNER JOIN service_orders so ON so.id = soi.service_order_id
WHERE so.condominium_id = @condominium_id;

DELETE som FROM service_order_messages som
INNER JOIN service_orders so ON so.id = som.service_order_id
WHERE so.condominium_id = @condominium_id;

DELETE FROM service_orders WHERE condominium_id = @condominium_id;

-- Taxas e cobranças
DELETE FROM charges WHERE condominium_id = @condominium_id;
DELETE fuc FROM fee_unit_configurations fuc
INNER JOIN fees f ON f.id = fuc.fee_id
WHERE f.condominium_id = @condominium_id;
DELETE FROM fees WHERE condominium_id = @condominium_id;

-- Caixa e transações
DELETE FROM condominium_accounts WHERE condominium_id = @condominium_id;
DELETE FROM transactions WHERE condominium_id = @condominium_id;

-- Extratos e contas bancárias
DELETE FROM bank_statements WHERE condominium_id = @condominium_id;

DELETE bab FROM bank_account_balances bab
INNER JOIN bank_accounts ba ON ba.id = bab.bank_account_id
WHERE ba.condominium_id = @condominium_id;

DELETE barr FROM bank_account_routing_rules barr
INNER JOIN bank_accounts ba ON ba.id = barr.bank_account_id
WHERE ba.condominium_id = @condominium_id;

DELETE FROM bank_accounts WHERE condominium_id = @condominium_id;

-- Prestação de contas (uploads)
DELETE FROM accountability_report_uploads WHERE condominium_id = @condominium_id;

-- Auditoria financeira (registros ligados ao condomínio)
DELETE a FROM audits a
INNER JOIN charges c ON a.auditable_type = 'App\\Models\\Charge' AND a.auditable_id = c.id
WHERE c.condominium_id = @condominium_id;

DELETE a FROM audits a
INNER JOIN fees f ON a.auditable_type = 'App\\Models\\Fee' AND a.auditable_id = f.id
WHERE f.condominium_id = @condominium_id;

DELETE a FROM audits a
INNER JOIN transactions t ON a.auditable_type = 'App\\Models\\Transaction' AND a.auditable_id = t.id
WHERE t.condominium_id = @condominium_id;

DELETE a FROM audits a
INNER JOIN condominium_accounts ca ON a.auditable_type = 'App\\Models\\CondominiumAccount' AND a.auditable_id = ca.id
WHERE ca.condominium_id = @condominium_id;

DELETE a FROM audits a
INNER JOIN bank_accounts ba ON a.auditable_type = 'App\\Models\\BankAccount' AND a.auditable_id = ba.id
WHERE ba.condominium_id = @condominium_id;

-- Reservas: limpar pagamento (espaços deste condomínio)
UPDATE reservations r
INNER JOIN spaces s ON s.id = r.space_id
SET
    r.prereservation_status     = NULL,
    r.payment_deadline          = NULL,
    r.payment_completed_at      = NULL,
    r.payment_reference         = NULL,
    r.prereservation_amount     = NULL
WHERE s.condominium_id = @condominium_id;

SET FOREIGN_KEY_CHECKS = 1;

COMMIT;
*/


-- =============================================================================
-- OPÇÃO 3 — EXTRAS OPCIONAIS (descomente se quiser apagar também)
-- =============================================================================

-- Assinatura SaaS do condomínio ao CondoCenter (cobranças da plataforma)
/*
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM condominium_subscription_documents;
DELETE FROM condominium_subscription_logs;
DELETE FROM condominium_subscriptions;
-- NÃO apagar subscription_plans — catálogo global da plataforma
SET FOREIGN_KEY_CHECKS = 1;
*/

-- Reset de configuração Asaas/recebimento nos condomínios (não apaga o condomínio)
/*
UPDATE condominiums SET
    asaas_api_key              = NULL,
    asaas_sandbox              = 1,
    asaas_webhook_email        = NULL,
    asaas_webhook_token        = NULL,
    asaas_setup_completed_at   = NULL;
*/

-- Marketplace e caronas (têm campo price, mas não são módulo financeiro principal)
/*
DELETE FROM marketplace_items;
UPDATE rides SET price_per_seat = 0;
*/


-- =============================================================================
-- VERIFICAÇÃO PÓS-RESET
-- =============================================================================
/*
SELECT 'charges' AS tabela, COUNT(*) AS total FROM charges
UNION ALL SELECT 'fees', COUNT(*) FROM fees
UNION ALL SELECT 'fines', COUNT(*) FROM fines
UNION ALL SELECT 'payments', COUNT(*) FROM payments
UNION ALL SELECT 'condominium_accounts', COUNT(*) FROM condominium_accounts
UNION ALL SELECT 'transactions', COUNT(*) FROM transactions
UNION ALL SELECT 'employees', COUNT(*) FROM employees
UNION ALL SELECT 'monthly_closings', COUNT(*) FROM monthly_closings
UNION ALL SELECT 'accountability_report_uploads', COUNT(*) FROM accountability_report_uploads;
*/
