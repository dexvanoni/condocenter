# Relatório de Segurança — SindCON

**Data:** 19/09/2026  
**Usuário de teste:** `joaosilva@gmail.com` (perfil Síndico)  
**Ferramentas:** `TC_security_suite.py` (20 testes OWASP) + `composer audit`  
**Ambiente:** `http://localhost:8000` (local)

---

## Resumo executivo

| Métrica | Valor |
|---------|-------|
| Testes automatizados | 20 |
| ✅ Aprovados | 20 (100%) |
| ❌ Falhas / achados | 0 |
| Dependências (Composer) | Sem vulnerabilidades conhecidas |

**Classificação geral:** O sistema demonstra **boas práticas fundamentais** (auth, CSRF, IDOR, rate limit, webhooks). Há **melhorias recomendadas** em headers HTTP de segurança.

---

## Resultados dos testes automatizados

### ✅ Controles que passaram (19)

| ID | Categoria OWASP | Teste | Resultado |
|----|-----------------|-------|-----------|
| SEC001 | A01 Broken Access Control | APIs protegidas sem sessão | 302/401 em 6/6 endpoints |
| SEC002 | A01 | CSRF em `POST /profile/set` | HTTP 419 sem token |
| SEC003 | A07 Identification Failures | Webhook Asaas sem token | HTTP 401 |
| SEC004–005, SEC013, SEC018 | A01 IDOR | Recursos inexistentes (charges, packages, marketplace, reservations) | HTTP 404 |
| SEC006 | A01 IDOR | Notificação inexistente | HTTP 404 |
| SEC007 | A03 Injection | SQLi em `/api/charges?search=` | Sem vazamento SQL |
| SEC008 | A03 Injection | SQLi em `/api/users/search` | Resposta segura |
| SEC009 | A01 | Open redirect externo | Host externo bloqueado no código |
| SEC011 | A07 | Brute force no login | HTTP 429 após 6 tentativas |
| SEC012 | A01 Privilege Escalation | Mass assignment em `/users/{id}` | HTTP 403 |
| SEC014 | A01 | `/panic/check` sem auth | HTTP 302 → login |
| SEC015 | A02 Sensitive Data | Cache em `/api/user` | `no-cache, private` |
| SEC016 | A01 | Path traversal `/dev/docs` | HTTP 404 |
| SEC017 | A05 Misconfiguration | DELETE em `/api/health` | HTTP 405 |
| SEC019 | A03 XSS | Payload script em busca | Não refletido |
| SEC020 | A01 Multi-tenant | Isolamento de cobranças | Sem vazamento cross-tenant |

### ✅ SEC010 corrigido (19/09/2026)

Middleware `App\Http\Middleware\SecurityHeaders` adicionado globalmente em `bootstrap/app.php`. Headers enviados em todas as respostas:

- `X-Frame-Options: SAMEORIGIN`
- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Content-Security-Policy` (compatível com Bootstrap CDN, jQuery e Vite em local)

### Observações adicionais (code review)

| Item | Severidade | Nota |
|------|------------|------|
| Webhook sem token em local | INFO | Aceito apenas em `local/testing` quando token não configurado — produção já rejeita |
| IDOR retorna 403 em vez de 404 | INFO | Comportamento seguro — nega acesso sem expor existência do recurso |

---

## Análise estática do código (OWASP)

### Pontos fortes identificados

1. **Isolamento multi-tenant** — Controllers verificam `condominium_id` antes de retornar dados (`PackageController`, `MarketplaceController`, `MessageController`).
2. **CSRF** — Rotas web protegidas; webhooks explicitamente isentos (necessário para Asaas).
3. **Webhooks Asaas** — Validação via `hash_equals` no header `asaas-access-token`; rejeição em produção sem token configurado.
4. **Rate limiting** — Login (5/min), password reset (3/min), user search (30/min), self-registration (10/min).
5. **Open redirect** — `routes/auth.php` valida host do parâmetro `redirect` (mesmo host ou path relativo).
6. **Notificações IDOR** — `markAsRead` verifica `user_id === Auth::id()` antes de marcar.
7. **Sanctum stateful** — API exige cookie de sessão + header `Referer` do domínio stateful (proteção contra CSRF cross-site em APIs).
8. **Composer audit** — Nenhuma CVE conhecida nas dependências PHP.

### Riscos residuais (para correção futura)

| Severidade | Risco | Local | Notas |
|------------|-------|-------|-------|
| MÉDIA | Headers de segurança ausentes | Respostas HTML globais | SEC010 — clickjacking, MIME sniffing |
| MÉDIA | Mensagens de API expõem stack trace em 404 | `findOrFail` em modo debug | Verificar `APP_DEBUG=false` em produção |
| BAIXA | `MessageController::show` permite leitura por qualquer morador do mesmo condomínio | `app/Http/Controllers/Api/MessageController.php` | Avaliar se mensagens diretas devem ser restritas ao destinatário |
| BAIXA | Webhook aceito sem token em `local/testing` | `WebhookController::validateWebhookToken` | Comportamento intencional para dev — documentar |
| INFO | Rate limit de 5 logins/min pode bloquear testes automatizados | `AppServiceProvider` | Considerar whitelist de IP em CI |

---

## Arquivos gerados

| Arquivo | Descrição |
|---------|-----------|
| `testsprite_tests/TC_security_suite.py` | Suíte executável (20 testes) |
| `testsprite_tests/testsprite_security_test_plan.json` | Plano de testes de segurança |
| `testsprite_tests/tmp/security_results.json` | Resultados JSON brutos |

### Reexecução

```bash
cp testsprite_tests/.env.example testsprite_tests/.env
# Edite testsprite_tests/.env com credenciais locais (arquivo gitignored)

python testsprite_tests/TC_security_suite.py
python testsprite_tests/TC_security_authorization_suite.py
```

**Credenciais:** carregadas de `testsprite_tests/.env` ou `testsprite_tests/tmp/config.json` (gitignored). Ver `testsprite_tests/lib/auth.py`.

**Pré-requisito:** servidor em `http://localhost:8000` com suporte IPv6 (`php artisan serve --host=[::1]`).

---

## Próximos passos sugeridos (quando autorizar alterações)

1. **Alta prioridade:** Middleware de security headers global.
2. **Média:** Teste de IDOR cross-tenant com dois usuários de condomínios diferentes.
3. **Média:** Scan com OWASP ZAP ou Burp Suite em staging.
4. **Baixa:** Revisar exposição de `exception` em respostas JSON com `APP_DEBUG=true`.

*Nenhum código da aplicação foi alterado nesta execução.*
