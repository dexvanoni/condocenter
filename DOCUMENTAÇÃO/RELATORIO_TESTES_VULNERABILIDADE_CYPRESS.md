# Relatório — testes de vulnerabilidade (Cypress + SAST)

**Data:** 22/09/2026  
**Ambiente:** `http://127.0.0.1:8000` (Laravel local)  
**Escopo:** E2E Cypress (controle de acesso + suíte `security/vulnerability.cy.js`) + Semgrep OSS (`--config=auto`)

---

## 1. Resumo executivo

| Camada | Resultado | Interpretação |
|--------|-----------|---------------|
| Cypress — fluxos de controle de acesso | **20/20 OK** | Autenticação, papéis morador/porteiro, API e UI funcionam como esperado |
| Cypress — testes de segurança dedicados | **11/14 OK**, 3 falhas | 2 falhas são **artefato de sessão Cypress** (não brecha); 1 aponta **validação fraca de entrada** na API |
| Semgrep OSS | **11 achados** | Padrões de risco no código (maioria baixa confiança / dev-only); **2 ERROR** em OCR (`shell_exec`) |
| Verificação manual (curl sem cookie) | Biblioteca de documentos → **302** | Rotas protegidas; alinhado com login obrigatório |

**Conclusão:** Não foi identificada brecha crítica confirmada de acesso anônimo à biblioteca ou bypass óbvio de autenticação na API de controle de acesso. Há **melhorias recomendadas** (sanitização de `visitor_name`, endurecimento de `unlink`/paths, rate limit no PIN, revisão OCR).

---

## 2. Como os testes foram executados

```powershell
$env:CYPRESS_BASE_URL="http://127.0.0.1:8000"
$env:CYPRESS_PHP_PATH="C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe"
php cypress/scripts/ensure-test-users.php
npx cypress run --spec "cypress/e2e/access-control/**/*.cy.js,cypress/e2e/security/**/*.cy.js"
```

- Usuários de teste: `cypress-morador@test.local`, `cypress-porteiro@test.local` (senha `password`).
- **Correção aplicada:** `ensure-test-users.php` passou a ser idempotente (`updateOrCreate`) para não quebrar o `cy.task('ensureTestUsers')`.
- **Nova suíte:** `cypress/e2e/security/vulnerability.cy.js` (autorização, CSRF, documentos, IDOR PDF, PIN, XSS na API).

Semgrep (resumo salvo em `semgrep-report.json` na raiz do projeto):

```powershell
semgrep --config=auto --exclude=vendor --exclude=node_modules --exclude=storage --exclude=bootstrap/cache --exclude=.git .
```

---

## 3. Cypress — controle de acesso (regressão + superfície atacada)

Todos os cenários abaixo passaram em **~1m53s**.

### API (`api-backend.cy.js`)

- Listagem e criação de liberações (preset e visitante “Outro” + PDF).
- Validação `valid_until` obrigatório para “Outro”.
- Proibições e listas de evento.
- Porteiro: painel, check-in por PIN válido, rejeição de PIN `0000`.

### Frontend morador / porteiro

- Formulários, histórico, modal de credencial PDF.
- Portaria: PIN inválido com feedback de erro; sucesso com PIN semeado.

**Implicação de segurança:** Separação morador × porteiro na API está **consistente** com os testes (morador não usa painel do porteiro — ver seção 4).

---

## 4. Cypress — testes de segurança (`vulnerability.cy.js`)

### 4.1 Controles que **passaram** (comportamento desejado)

| Teste | Evidência |
|-------|-----------|
| API `/api/access-control/*` sem sessão | HTTP **401/403** |
| `/api/user` sem token | **401** |
| Morador → `/api/access-control/porteiro/panel` | **403** |
| Morador → rota web `/access-control/porteiro` | **403** ou redirect fora da portaria |
| Porteiro → `POST /api/access-control/authorizations` | **403/422** (não cria como morador) |
| POST API sem `X-XSRF-TOKEN` (após csrf-cookie) | **401/419/403** |
| Headers em `/login` | `X-Frame-Options`, `X-Content-Type-Options: nosniff` |
| PDF autorização inexistente `.../authorizations/999999/pdf` | **403/404** |
| 5× PIN inválido no check-in | Nenhum **200** |

### 4.2 Falhas do Cypress e triagem

#### A) Biblioteca de documentos “exige login” / arquivo “não público” — **falso positivo E2E**

- **Sintoma:** Cypress reportou HTTP **200** em `/library-documents` e `/library-documents/1/file`.
- **Causa:** A suíte roda **depois** dos testes que fazem `cy.loginAsMorador()` / `cy.loginAsPorteiro()`; o `cy.session` do Cypress **reutiliza** autenticação mesmo após `clearAllCookies()` em alguns cenários.
- **Confirmação independente (sem cookie):**

  ```text
  curl http://127.0.0.1:8000/library-documents        → 302
  curl http://127.0.0.1:8000/library-documents/1/file → 302
  ```

- **Classificação:** Não é vulnerabilidade confirmada; ajustar a ordem dos `describe` ou usar `cy.request({ jar: false })` / spec isolada só anônima.

#### B) Nome de visitante com `<script>` na API — **achado real (severidade média-baixa)**

- **Sintoma:** `POST /api/access-control/authorizations` com `visitor_name: "<script>alert(1)</script>VisitorXSS"` retorna **201** e ecoa o nome **sem remover tags**.
- **Regra violada:** Entrada não normalizada (OWASP A03 — Injection / stored XSS se renderizada sem escape).
- **Mitigação parcial no front:** Painel do porteiro usa função `esc()` em HTML dinâmico; modal do morador usa `textContent` para o nome — **reduz exploit no browser atual**.
- **Risco residual:** PDF de credencial, e-mails, relatórios, integrações ou futuras telas que usem `{!! !!}` ou `innerHTML` sem escape.
- **Recomendação:** Validar/sanitizar no backend (`strip_tags` ou rejeitar `<>`), ou policy de “nome legível” com regex; manter escape em todas as saídas.

---

## 5. Semgrep OSS — achados estáticos (11)

| Severidade | Arquivo | Regra | Triagem |
|------------|---------|-------|---------|
| WARNING | `MarketplaceController.php` | `unlink` com path derivado de dados | Endurecer path (sem `..`, prefixo `marketplace/{id}/`) |
| WARNING | `AccountabilityReportController.php` | `unlink($zipPath)` | Path gerado no servidor — **baixo risco** |
| WARNING | `PaddleOcrService.php` | `unlink` marker | Path de config/cache — **baixo risco** |
| **ERROR** | `PaddleOcrService.php` | `shell_exec` (2×) | Command injection se `paddle_python` ou imagem forem controlados por atacante; hoje há `escapeshellarg` — **revisar origem dos paths** |
| WARNING | `TesseractOcrService.php`, `LabelImagePreprocessor.php` | `unlink` temp | Preprocessor já restringe a `sys_get_temp_dir()` |
| WARNING | `cypress/scripts/probe-*.php` | `unlink` cookie fixo | Apenas dev |
| ERROR | `cypress/scripts/run-access-tests.mjs` | `spawn` com `shell: true` | Apenas CI/local |
| WARNING | `cypress/e2e/security/vulnerability.cy.js` | string com `<script>` no spec | Falso positivo (payload de teste) |

Nenhum achado Semgrep equivale, sozinho, a CVE confirmada — são **alertas para revisão**.

---

## 6. Outros pontos observados (não cobertos por Cypress)

| Tópico | Observação |
|--------|------------|
| **Rate limit PIN (4 dígitos)** | 5 tentativas seguidas falharam sem 200; **não há evidência de bloqueio/throttle** — risco de enumeração offline em janela longa |
| **CSP** | `unsafe-inline` / `unsafe-eval` em scripts (facilita XSS se houver ponto de injeção) |
| **PIN de visitante** | Fluxo funcional correto; segurança depende de entropia (4 dígitos) + controle físico na portaria |

---

## 7. Inventário de artefatos

| Artefato | Caminho |
|----------|---------|
| Spec de segurança | `cypress/e2e/security/vulnerability.cy.js` |
| Log da última execução | `cypress-last-run.log` |
| Screenshots de falhas | `cypress/screenshots/security/` ou `cypress/screenshots/vulnerability.cy.js/` |
| JSON Semgrep | `semgrep-report.json` (raiz; pode excluir do commit) |

---

## 8. Plano de ação priorizado

1. **Alta (rápida):** Sanitizar ou rejeitar `visitor_name` / campos similares na API de controle de acesso.
2. **Média:** `MarketplaceController::deletePublicStorageFile` — normalização de path contra traversal.
3. **Média:** Rate limit / lockout em `check-in/pin` (por IP + condomínio).
4. **Baixa:** Revisar `shell_exec` no OCR; trocar `shell: true` no runner Cypress quando possível.
5. **QA:** Reordenar spec de segurança (bloco anônimo primeiro) ou `jar: false` para testes sem sessão.

---

## 9. Limitações deste relatório

- Não substitui pentest manual nem DAST completo (Burp, OWASP ZAP em todas as rotas).
- Cypress cobre principalmente **controle de acesso** e amostra de rotas; módulos financeiro, mensagens, marketplace, webhooks Asaas etc. não foram varridos nesta execução.
- Ambiente **local** (`APP_DEBUG`, credenciais de teste) pode divergir de produção.

**Última execução Cypress registrada:** 34 testes, 31 passando, 3 falhas triadas conforme seção 4.2.
