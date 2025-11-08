# 📏 Regras do Projeto

## Alterações em Banco de Dados

- Antes de executar qualquer alteração no banco de dados, descreva os impactos esperados e os cenários que motivam a mudança.
- Solicite aprovação explícita do responsável pelo projeto antes de aplicar alterações que possam modificar estrutura de tabelas, remover dados ou afetar integridade referencial.
- Planeje sempre mecanismos de mitigação (backup, rollback, migrações reversíveis) para evitar perda de dados.
- Priorize a avaliação de impactos em todos os ambientes (`dev`, `test`, `prod`) e somente siga com a execução após autorização formal.

> Objetivo: garantir transparência, segurança e rastreabilidade em qualquer operação que envolva o banco de dados do CondoCenter.

