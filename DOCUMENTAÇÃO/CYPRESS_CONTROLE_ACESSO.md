# Cypress — Controle de Acesso

Testes E2E (frontend) e de API (backend via `cy.request`) para o módulo `access_control`.

## Pré-requisitos

1. Ambiente `local` ou `testing` (os scripts de seed Cypress bloqueiam produção).
2. **APP_URL local:** os assets do Vite usam `APP_URL`. Se o `.env` apontar para túnel (Cloudflare/ngrok), o Cypress em `127.0.0.1:8000` não carrega JS/CSS. Use uma das opções:
   - `npm run cypress:access` — sobe o `artisan serve` com `APP_URL=http://127.0.0.1:8000` automaticamente (recomendado);
   - ou defina `APP_URL=http://127.0.0.1:8000` no `.env` e reinicie o servidor antes dos testes.
3. Servidor (se não usar o script acima):
   ```powershell
   $env:APP_URL="http://127.0.0.1:8000"
   C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan serve --host=127.0.0.1 --port=8000
   ```
4. Assets compilados (`npm run build`) — necessário para o painel do porteiro (Vue check-in).
5. Fila/worker **não** obrigatória para os testes (notificações WhatsApp podem falhar silenciosamente em local).

Na primeira execução, o hook `before()` chama `ensureTestUsers`, que cria/atualiza usuários dedicados (`cypress-morador@test.local` e `cypress-porteiro@test.local`) com módulo `access_control` habilitado e `senha_temporaria = false`.

## Instalação

```bash
npm install
copy cypress.env.json.example cypress.env.json
```

Opcional no Windows (script de seed com PIN fixo):

```powershell
$env:CYPRESS_PHP_PATH="C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe"
```

## Executar

| Comando | Descrição |
|---------|-----------|
| `npm run cypress:open` | Abre o Cypress Test Runner (extensão/IDE) |
| `npm run cypress:access` | Sobe servidor local (se necessário) e roda os specs de controle de acesso |
| `npm run cypress:access:run` | Roda os specs sem subir servidor (exige `APP_URL` local já ativo) |
| `npm run cypress:run` | Roda toda a suíte |

No Cypress Open, selecione **E2E Testing** e o spec em `cypress/e2e/access-control/`.

## Estrutura

| Arquivo | Cobertura |
|---------|-----------|
| `api-backend.cy.js` | API REST: liberações, proibições, listas, check-in PIN/QR |
| `resident-frontend.cy.js` | UI morador (`/access-control`): presets, visitante conhecido, histórico |
| `porteiro-frontend.cy.js` | UI porteiro (`/access-control/porteiro`): senha, grid, sucesso |

## Credenciais padrão (Cypress)

| Perfil | E-mail | Senha |
|--------|--------|-------|
| Morador | `cypress-morador@test.local` | `password` |
| Porteiro | `cypress-porteiro@test.local` | `password` |

Sobrescreva em `cypress.env.json` se quiser usar outros usuários do banco.

## Seed auxiliar (PIN conhecido)

O task `seedAccessAuthorization` executa `cypress/scripts/seed-access-authorization.php` para criar liberação "Outro" com senha `4821`, usada nos testes de check-in do porteiro.

**Somente ambientes `local` e `testing`.**

## Variáveis

| Variável | Padrão |
|----------|--------|
| `CYPRESS_BASE_URL` | definido automaticamente pelo `npm run cypress:access` |
| `CYPRESS_SERVER_PORT` | `8011` (primeira porta livre a partir daí) |
| `CYPRESS_PHP_PATH` | `php` |
| `CYPRESS_MORADOR_EMAIL` | `cypress-morador@test.local` |
| `CYPRESS_PORTEIRO_EMAIL` | `cypress-porteiro@test.local` |
| `PASSWORD` (cypress.env.json) | `password` |
