# Relatório Backend — Perfil Síndico

**Data:** 19/09/2026  
**Usuário:** `joaosilva@gmail.com`  
**Perfil ativo:** Síndico  
**Ferramenta:** TestSprite MCP  
**Resultado:** **20/20 testes aprovados (100%)**

**Dashboard:** [TestSprite — Backend Síndico](https://www.testsprite.com/dashboard/mcp/tests/70788038-7173-557a-972e-b52e4f4b12b5)

---

## Fluxo de autenticação validado

1. `GET /sanctum/csrf-cookie` — obter cookie CSRF
2. `POST /login` — `joaosilva@gmail.com` / `@!T1q2w3e4r`
3. `POST /profile/set` — `role=Síndico`
4. Chamadas `/api/*` com cookies de sessão reutilizados

---

## Resultados por módulo

| ID | Teste | Endpoint | Status |
|----|-------|----------|--------|
| TC001 | Health check público | `GET /api/health` | ✅ |
| TC002 | Login + perfil Síndico | `POST /login` + `POST /profile/set` | ✅ |
| TC003 | Listar cobranças | `GET /api/charges` | ✅ |
| TC004 | Listar transações | `GET /api/transactions` | ✅ |
| TC005 | Relatório financeiro | `GET /api/reports/financial` | ✅ |
| TC006 | Relatório inadimplentes | `GET /api/reports/defaulters` | ✅ |
| TC007 | Relatório saldo | `GET /api/reports/balance` | ✅ |
| TC008 | Listar reservas | `GET /api/reservations` | ✅ |
| TC009 | Listar espaços | `GET /api/spaces` | ✅ |
| TC010 | Listar encomendas | `GET /api/packages` | ✅ |
| TC011 | Resumo encomendas/unidades | `GET /api/packages/summary/units` | ✅ |
| TC012 | Listar assembleias | `GET /api/assemblies` | ✅ |
| TC013 | Listar conversas | `GET /api/conversations` | ✅ |
| TC014 | Contagem notificações | `GET /api/notifications/unread-count` | ✅ |
| TC015 | Listar notificações | `GET /api/notifications` | ✅ |
| TC016 | Autorizações visitantes | `GET /api/access-control/authorizations` | ✅ |
| TC017 | Movimentações portaria | `GET /api/access-control/movements` | ✅ |
| TC018 | Busca de usuários | `GET /api/users/search` | ✅ |
| TC019 | Marketplace | `GET /api/marketplace` | ✅ |
| TC020 | Bloqueio sem autenticação | `GET /api/charges` → 401 | ✅ |

---

## Arquivos gerados

| Arquivo | Descrição |
|---------|-----------|
| `testsprite_tests/testsprite_backend_test_plan.json` | Plano com 20 casos (Síndico) |
| `testsprite_tests/TC002..TC020_syndic_*.py` | Scripts Python gerados pelo TestSprite |
| `testsprite_tests/TC_syndic_backend_suite.py` | Suíte local alternativa (mesmos endpoints) |
| `testsprite_tests/tmp/test_results.json` | Resultados brutos |
| `testsprite_tests/tmp/raw_report.md` | Relatório bruto TestSprite |

---

## Observações

- Nenhum código da aplicação foi alterado.
- Servidor local: `http://localhost:8000` (IPv6 `[::1]`).
- Todos os módulos habilitados para o condomínio do usuário responderam corretamente.
- O teste TC020 confirma que rotas protegidas retornam 401 sem sessão.
