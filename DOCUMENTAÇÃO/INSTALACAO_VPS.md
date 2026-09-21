# SindCON na VPS — tutorial em ordem

Siga **de cima para baixo**. Cada passo assume que o anterior já foi feito.

Substitua em todos os comandos:

| Placeholder | Exemplo |
|-------------|---------|
| `SEU_DOMINIO` | `app.sindcon.com.br` |
| `SENHA_MYSQL` | senha forte só do banco |
| URL do Git | repositório real do projeto |

Constantes desta instalação:

- Servidor: Ubuntu 22.04 ou 24.04 LTS
- Pasta do projeto: `/var/www/condocenter`
- Site público (Nginx): `/var/www/condocenter/public`
- PHP 8.3, MySQL 8, Node 20
- Fuso: `America/Fortaleza`
- **Última revisão:** 21/09/2026 (expiração automática de avisos + `announcements:close-expired`)

Leitura no navegador (somente quem tiver o link): `DEV_DOCS_URL` no `.env`.

---

# PARTE 1 — Primeira instalação

Faça esta parte **uma vez**, em um servidor vazio.

---

## Passo 1 — Configurar a VPS

Entre no servidor:

```bash
ssh root@IP_DA_VPS
```

Atualize o sistema e instale o básico:

```bash
apt update && apt upgrade -y
apt install -y software-properties-common curl git unzip ufw nginx mysql-server supervisor
```

Instale o PHP 8.3 e as extensões do Laravel:

```bash
add-apt-repository -y ppa:ondrej/php
apt update
apt install -y \
  php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl \
  php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl php8.3-tokenizer

# OCR de etiquetas — Tesseract (obrigatório no servidor) + Python (opcional, só se usar Paddle no PHP)
apt install -y tesseract-ocr tesseract-ocr-por python3 python3-venv python3-pip
tesseract --version
python3 --version
```

O **Tesseract** atende o padrão do sistema e o fallback no servidor. O **Python 3** só é necessário se algum condomínio escolher o motor **PaddleOCR (Python)** em Meu Condomínio. A portaria (`/packages/intake`) usa **PaddleOCR.js no navegador** do celular (assets do `npm run build`); isso **não** depende do Python na VPS.

Instale o Composer:

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
composer -V
```

Instale o Node.js 20 (necessário para `npm run build`):

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs
node -v && npm -v
```

Libere SSH, HTTP e HTTPS no firewall:

```bash
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable
```

Aponte o DNS do domínio (registro A) para o IP desta VPS **antes** do passo do certificado HTTPS.

Quando terminar: vá para o **Passo 2**.

---

## Passo 2 — Criar o banco de dados

```bash
mysql
```

Dentro do MySQL:

```sql
CREATE DATABASE condocenter CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'condocenter'@'localhost' IDENTIFIED BY 'SENHA_MYSQL';
GRANT ALL PRIVILEGES ON condocenter.* TO 'condocenter'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Guarde `SENHA_MYSQL`. Você vai colar no `.env` no Passo 5.

Quando terminar: vá para o **Passo 3**.

---

## Passo 3 — Clonar o projeto (Git)

Crie a pasta e baixe o código:

```bash
mkdir -p /var/www
git clone git@SEU_GIT:SEU_ORG/condocenter.git /var/www/condocenter
```

Se o repositório for HTTPS:

```bash
git clone https://github.com/SEU_ORG/condocenter.git /var/www/condocenter
```

Entre na pasta e confira o branch:

```bash
cd /var/www/condocenter
git checkout main
git status
```

Dê a pasta ao usuário do Nginx/PHP:

```bash
chown -R www-data:www-data /var/www/condocenter
```

Se o `git clone` precisar da sua chave SSH, clone com o seu usuário e só depois rode o `chown`.

Quando terminar: vá para o **Passo 4**.

---

## Passo 4 — Instalar dependências do projeto

Ainda em `/var/www/condocenter`:

```bash
cd /var/www/condocenter
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data npm ci
sudo -u www-data npm run build
```

Quando terminar: vá para o **Passo 5**.

---

## Passo 5 — Configurar o `.env`

```bash
cd /var/www/condocenter
sudo -u www-data cp .env.example .env
sudo -u www-data php artisan key:generate
nano .env
```

Preencha **produção** assim (ajuste domínio, senha e e-mail):

```env
APP_NAME=SindCON
APP_ENV=production
APP_DEBUG=false
APP_URL=https://SEU_DOMINIO
APP_TIMEZONE=America/Fortaleza
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=condocenter
DB_USERNAME=condocenter
DB_PASSWORD=SENHA_MYSQL

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
SESSION_SECURE_COOKIE=true

MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=SEU_LOGIN@smtp-brevo.com
MAIL_PASSWORD=xsmtpsib_SUA_CHAVE_SMTP_BREVO
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@SEU_DOMINIO
MAIL_FROM_NAME="${APP_NAME}"
# O remetente (MAIL_FROM_ADDRESS) deve estar verificado no painel Brevo > Remetentes.

ASAAS_API_KEY=
ASAAS_SANDBOX=false
ASAAS_WEBHOOK_TOKEN=

WHATSAPP_ENABLED=false
SAAS_ENFORCE_SUBSCRIPTION=true
SAAS_DEVELOPER_CONTACT=seu-email@exemplo.com

# --- OCR de etiquetas (Encomenda Inteligente) — ver também Passo 6 (ambiente Python) ---
OCR_ENABLED=true
OCR_LANG=por
OCR_TIMEOUT=30
OCR_PREPROCESS_ENABLED=true
OCR_MAX_DIMENSION=2400
# Ubuntu: deixe vazio (usa PATH). Só preencha se o binário estiver fora do PATH.
TESSERACT_PATH=

# PaddleOCR via Python (opcional). Padrão no Linux: python3 do sistema ou venv do Passo 6.
# PADDLE_OCR_PYTHON=/var/www/condocenter/storage/app/ocr/venv/bin/python
# PADDLE_OCR_SCRIPT=/var/www/condocenter/scripts/ocr/paddle_label.py
# PADDLE_OCR_TIMEOUT=120
# PADDLE_OCR_PROBE_TIMEOUT=45
# Só no Windows/Laragon se pip instalou no perfil do usuário (veja php artisan ocr:diagnose):
# PADDLE_OCR_PYTHONPATH=
# Preview legado no servidor (captura manual / APIs antigas): Tesseract rápido e limite do Paddle
OCR_PREVIEW_TESSERACT_FAST_PATH=true
PADDLE_OCR_PREVIEW_TIMEOUT=75

DEV_DOCS_TOKEN=
DEV_DOCS_URL="${APP_URL}/dev/docs/"
```

Regras:

- Não copie o `.env` do computador local.
- `DEV_DOCS_TOKEN` vazio em produção (a página interna fica 404).
- `QUEUE_CONNECTION=database` (a fila entra no Passo 9).
- A câmera do porteiro no navegador exige **HTTPS** em produção (`APP_URL=https://...`).
- **Portaria com câmera:** exige HTTPS (`APP_URL=https://...`). O primeiro acesso à intake baixa modelos WASM no celular (pode demorar); não é o Python da VPS.
- **Motor por condomínio:** em **Meu Condomínio → Leitura de etiquetas (OCR)** o síndico escolhe Tesseract ou Paddle (Python). Se o motor escolhido falhar, o sistema tenta o outro quando possível; o registro manual sempre funciona.
- Sem Tesseract no servidor: use `OCR_ENABLED=false` ou instale o pacote do Passo 1 — o registro manual de encomendas continua.

Quando terminar: vá para o **Passo 6**.

---

## Passo 6 — Primeira carga do Laravel

```bash
cd /var/www/condocenter
mkdir -p storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan db:seed --class=RolesAndPermissionsSeeder --force
sudo -u www-data php artisan storage:link
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

**Não rode** `DemoDataSeeder` nem `db:wipe`.

### Encomenda Inteligente — ambiente OCR no servidor

| Camada | Onde roda | Quando precisa na VPS |
|--------|-----------|------------------------|
| **PaddleOCR.js (PP-OCRv6)** | Navegador do porteiro (`/packages/intake`) | Só `npm run build` (Passo 4) + HTTPS |
| **Tesseract** | PHP (`www-data`) | `apt install tesseract-ocr tesseract-ocr-por` (Passo 1) |
| **PaddleOCR (Python)** | Subprocesso PHP → `scripts/ocr/paddle_label.py` | Passo abaixo + variáveis `PADDLE_OCR_*` no `.env` |

**1) Conferir Tesseract (sempre):**

```bash
cd /var/www/condocenter
tesseract --version
sudo -u www-data php artisan ocr:diagnose
```

Saída esperada: `Tesseract: disponível`. O comando também limpa o cache de detecção do Paddle e, se o Python estiver OK, pode pré-carregar modelos (`warm-up`).

**2) Instalar PaddleOCR em Python (opcional)** — faça **como `www-data`**, para o PHP-FPM enxergar os mesmos pacotes:

```bash
cd /var/www/condocenter
mkdir -p storage/app/ocr
chown -R www-data:www-data storage/app/ocr

# Ambiente virtual dedicado (recomendado; não misture com pip do root)
sudo -u www-data python3 -m venv storage/app/ocr/venv
sudo -u www-data storage/app/ocr/venv/bin/pip install --upgrade pip wheel

# CPU (VPS sem GPU). Na primeira instalação o download pode levar vários minutos.
sudo -u www-data storage/app/ocr/venv/bin/pip install paddlepaddle -i https://www.paddlepaddle.org.cn/packages/stable/cpu/
sudo -u www-data storage/app/ocr/venv/bin/pip install paddleocr

# Teste rápido de import (deve imprimir caminho site-packages sem erro)
sudo -u www-data storage/app/ocr/venv/bin/python -c "import paddleocr; print('paddleocr OK')"
```

No `.env` (Passo 5), descomente e ajuste:

```env
PADDLE_OCR_PYTHON=/var/www/condocenter/storage/app/ocr/venv/bin/python
PADDLE_OCR_SCRIPT=/var/www/condocenter/scripts/ocr/paddle_label.py
PADDLE_OCR_TIMEOUT=120
```

Depois:

```bash
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan ocr:diagnose
```

- Modelos e cache do Paddle no servidor ficam em `storage/app/ocr/paddle-runtime/` (criado automaticamente; precisa de escrita em `storage/`).
- O script `paddle_label.py` desativa oneDNN/PIR por padrão (compatibilidade PaddlePaddle 3.3+ em CPU). A **primeira** leitura com Paddle Python pode baixar modelos — respeite `PADDLE_OCR_TIMEOUT` (padrão 120 s).
- Se `ocr:diagnose` mostrar `paddleocr_not_installed` mas o `pip` funcionou no terminal, confira que instalou no **mesmo** Python do `.env` e, se necessário, copie o `site-packages` sugerido pelo comando para `PADDLE_OCR_PYTHONPATH=` (caso típico no Windows; raro na VPS com venv).

**3) Variáveis OCR (referência):**

| Variável | Padrão / uso |
|----------|----------------|
| `OCR_ENABLED` | `true` — desligue só se não houver Tesseract e não quiser OCR no servidor |
| `OCR_LANG` | `por` |
| `OCR_TIMEOUT` | Segundos por chamada Tesseract (30) |
| `OCR_PREPROCESS_ENABLED` | Pré-processamento da imagem antes do OCR |
| `OCR_MAX_DIMENSION` | Redimensiona imagem grande antes do OCR (2400 px) |
| `TESSERACT_PATH` | Vazio no Ubuntu; caminho completo se necessário |
| `PADDLE_OCR_PYTHON` | Linux: `python3` ou venv acima; Windows: caminho do `python.exe` |
| `PADDLE_OCR_PYTHONPATH` | Pasta `site-packages` se o PHP não encontrar o pacote |
| `PADDLE_OCR_SCRIPT` | Padrão: `scripts/ocr/paddle_label.py` no projeto |
| `PADDLE_OCR_TIMEOUT` | Timeout do subprocesso Paddle (120 s) |
| `PADDLE_OCR_PROBE_TIMEOUT` | Timeout do teste de disponibilidade (45 s) |
| `OCR_PREVIEW_TESSERACT_FAST_PATH` | No preview no servidor, Tesseract rápido antes do Paddle |
| `PADDLE_OCR_PREVIEW_TIMEOUT` | Limite do Paddle no preview (75 s; evita HTTP 524 em proxy ~100 s) |

**4) Comando de operação:**

```bash
sudo -u www-data php artisan ocr:diagnose
```

Use após deploy, mudança de `.env`, instalação de pacotes Python ou quando **Meu Condomínio** mostrar Paddle indisponível.

Quando terminar: vá para o **Passo 7**.

---

## Passo 7 — Nginx e HTTPS

Crie o site:

```bash
nano /etc/nginx/sites-available/condocenter
```

Cole (troque `SEU_DOMINIO`):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name SEU_DOMINIO www.SEU_DOMINIO;
    root /var/www/condocenter/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;
    client_max_body_size 32M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Ative e recarregue:

```bash
ln -sf /etc/nginx/sites-available/condocenter /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx
```

Instale o certificado (o DNS já deve apontar para esta VPS):

```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d SEU_DOMINIO -d www.SEU_DOMINIO
```

Abra `https://SEU_DOMINIO` no navegador. Deve aparecer o login.

Quando terminar: vá para o **Passo 8**.

---

## Passo 8 — Ligar o agendador (cron)

Sem este passo o sistema **não** gera taxas no mês seguinte, **não** liquida folha e **não** marca atraso.

```bash
crontab -u www-data -e
```

Uma linha só:

```
* * * * * cd /var/www/condocenter && php artisan schedule:run >> /dev/null 2>&1
```

Confira:

```bash
sudo -u www-data php artisan schedule:list
```

O Laravel dispara sozinho, no fuso `America/Fortaleza`:

| Quando | O que faz |
|--------|-----------|
| Todo dia 05:00 | Gera a próxima cobrança das taxas automáticas (`fees:generate-upcoming`) |
| Todo dia 06:30 | Liquida desconto em folha no vencimento (`charges:settle-payroll`) |
| Todo dia 07:00 | Marca cobrança vencida como atraso, exceto folha (`charges:mark-overdue`) |
| Todo dia 08:00 | Lembretes de vencimento (`charges:send-reminders`) |
| Todo dia 09:00 | Aviso de atraso (`charges:check-overdue`) |
| Dia 1 às 08:00 | Relatórios mensais (`reports:generate-monthly`) |
| A cada hora | Cancela pré-reservas não pagas |
| A cada 15 min | Encerra avisos do condomínio após `expires_at` (`announcements:close-expired`) |
| Semanal | Apaga notificações lidas com mais de 30 dias |

Você **não** precisa rodar esses comandos na instalação. O cron cuida.

Quando terminar: vá para o **Passo 9**.

---

## Passo 9 — Ligar a fila (Supervisor)

Necessário porque o `.env` usa `QUEUE_CONNECTION=database`.

```bash
nano /etc/supervisor/conf.d/condocenter-worker.conf
```

```ini
[program:condocenter-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/condocenter/artisan queue:work database --sleep=3 --tries=3 --timeout=120 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/condocenter/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
supervisorctl reread
supervisorctl update
supervisorctl start condocenter-worker:*
supervisorctl status
```

Quando terminar: vá para o **Passo 10**.

---

## Passo 10 — Backup diário do banco

```bash
mkdir -p /var/backups/condocenter
nano /usr/local/bin/backup-condocenter.sh
```

```bash
#!/bin/bash
set -euo pipefail
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u condocenter -p'SENHA_MYSQL' condocenter | gzip > /var/backups/condocenter/condocenter_$DATE.sql.gz
find /var/backups/condocenter -name "condocenter_*.sql.gz" -mtime +7 -delete
```

```bash
chmod +x /usr/local/bin/backup-condocenter.sh
crontab -e
```

Adicione (além do cron do Laravel, este é do `root`):

```
0 2 * * * /usr/local/bin/backup-condocenter.sh
```

Quando terminar: vá para o **Passo 11**.

---

## Passo 11 — Integrações (depois que o site já abre)

1. No Asaas, webhook: `https://SEU_DOMINIO/webhooks/asaas`
2. WhatsApp: só se for usar — `WHATSAPP_ENABLED=true` e as variáveis `EVOLUTION_*`, depois `php artisan config:cache`
3. Se upload de foto falhar: em `/etc/php/8.3/fpm/php.ini` aumente `upload_max_filesize` e `post_max_size`, depois `systemctl reload php8.3-fpm`
4. Encomenda Inteligente (OCR): `sudo -u www-data php artisan ocr:diagnose`. Tesseract obrigatório para o padrão; Paddle Python só se o condomínio usar esse motor (Passo 6). Intake com câmera usa OCR no navegador — confira `npm run build` e HTTPS.
5. Câmera no celular: o site precisa estar em HTTPS (já previsto no Passo 7). Proxy (Cloudflare): a intake evita OCR pesado no PHP; previews antigos com Paddle no servidor devem respeitar `PADDLE_OCR_PREVIEW_TIMEOUT`.

Quando terminar: vá para o **Passo 12**.

---

## Passo 12 — Conferir a instalação

```bash
cd /var/www/condocenter
sudo -u www-data php artisan about
sudo -u www-data php artisan schedule:list
supervisorctl status
tail -n 50 storage/logs/laravel.log
```

Checklist:

- [ ] `https://SEU_DOMINIO` abre o login
- [ ] Login com usuário criado no sistema funciona
- [ ] `schedule:list` mostra os jobs da tabela do Passo 8
- [ ] Supervisor `condocenter-worker` está `RUNNING`
- [ ] `php artisan ocr:diagnose` — Tesseract disponível; Paddle conforme necessidade do condomínio
- [ ] `/packages/intake` abre a câmera em HTTPS (teste no celular)

**Primeira instalação concluída.** No dia a dia use só a **Parte 2**.

---

# PARTE 2 — Atualizar o sistema (já instalado)

Use esta parte **sempre que houver código novo** (git pull). Não refaça a Parte 1.

Faça nesta ordem, sem pular:

```bash
cd /var/www/condocenter

# 1) Backup
mysqldump -u condocenter -p condocenter | gzip > /var/backups/condocenter/pre_deploy_$(date +%Y%m%d_%H%M%S).sql.gz

# 2) Manutenção
sudo -u www-data php artisan down

# 3) Código
git pull origin main

# 4) Dependências e assets
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data npm ci
sudo -u www-data npm run build

# 5) Banco e arquivos públicos
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan storage:link

# 6) Cache
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# 7) Fila
supervisorctl restart condocenter-worker:*

# 8) Site no ar
sudo -u www-data php artisan up
```

Se o `.env` ganhou variável nova (veja o changelog abaixo), edite o `.env` **antes** do `config:cache`.

Se o deploy alterou OCR, Python ou `scripts/ocr/`, rode após o `config:cache`:

```bash
sudo -u www-data php artisan ocr:diagnose
```

Não reexecute migrations antigas à mão. Só `php artisan migrate --force`.

### Depois do deploy (só se o changelog pedir)

O cron do Passo 8 já roda os jobs financeiros. Só execute na mão se o changelog disser “rodar uma vez agora”:

```bash
cd /var/www/condocenter
sudo -u www-data php artisan fees:generate-upcoming
sudo -u www-data php artisan charges:settle-payroll
sudo -u www-data php artisan charges:mark-overdue
```

---

# PARTE 3 — Se algo der errado

| O que você vê | Onde olhar |
|---------------|------------|
| HTTP 500 | `storage/logs/laravel.log`, dono de `storage/` e `bootstrap/cache`, `APP_KEY` |
| CSS/JS quebrado | repetir `npm run build` e `php artisan view:cache`; `APP_URL` com https |
| 502 Bad Gateway | `systemctl status php8.3-fpm`; socket `/run/php/php8.3-fpm.sock` |
| Taxa não nasceu no mês seguinte | crontab do `www-data` com `schedule:run`; `php artisan schedule:list` |
| Folha não pagou no vencimento | job 06:30; timezone `America/Fortaleza` |
| E-mail / lembrete não sai | Supervisor `RUNNING`; tabela `jobs`; SMTP no `.env` |
| OCR / etiqueta não lê no servidor | `php artisan ocr:diagnose`; `tesseract --version`; `.env` `TESSERACT_PATH` / `OCR_ENABLED` |
| Paddle “indisponível” no condomínio | Mesmo usuário do PHP (`www-data`) instalou o venv? `PADDLE_OCR_PYTHON` aponta para o venv? `storage/app/ocr/paddle-runtime` gravável |
| `paddleocr_not_installed` | `sudo -u www-data storage/app/ocr/venv/bin/pip install paddleocr`; `config:clear`; `ocr:diagnose` |
| Leitura na portaria trava no “Preparando…” | Celular precisa HTTPS; primeira vez baixa WASM/modelos; rede lenta — aguardar ou usar fallback Capturar (se Paddle.js falhar) |
| HTTP 524 / timeout na leitura | Intake usa OCR no navegador; em APIs com imagem no servidor, reduza Paddle ou use Tesseract no condomínio; `PADDLE_OCR_PREVIEW_TIMEOUT` |

```bash
tail -f /var/www/condocenter/storage/logs/laravel.log
tail -f /var/www/condocenter/storage/logs/worker.log
```

---

# PARTE 4 — Changelog (o que cada versão exige na VPS)

Ao implementar feature nova: coloque o passo na **Parte 1** se for instalação, ou na **Parte 2** se for só atualização. Depois registre aqui. Não solte comando fora da ordem.

### 2026-09-21 — Expiração automática de avisos do síndico

- Atualização: Parte 2 (`git pull`). Sem migration. O cron do Passo 8 passa a executar `announcements:close-expired` a cada 15 minutos (encerra avisos com `expires_at` vencido).
- Ao abrir dashboard ou listar avisos, o sistema também encerra na hora os vencidos do condomínio. Opcional após deploy: `php artisan announcements:close-expired`.

### 2026-09-21 — Central de comunicação (manutenção)

- Comando opcional **somente homologação / reset de testes:** `php artisan communications:purge --force` — apaga todas as conversas (diretas, avisos, sigilosas), mensagens, anexos e reuniões vinculadas. **Não** usar em produção com dados reais sem backup.

### 2026-09-21 — Gestão SaaS: cobranças e contratos (administrador)

- Atualização: Parte 2 (`git pull` + `npm run build` se houver assets). Sem migration.
- Administrador: **Plataforma → Cobranças SaaS** (`/platform/billing`) — visão consolidada; por condomínio em **Gerenciar contrato** — cancelar/estornar cobrança Asaas, cobrança avulsa, cancelar/reativar assinatura, **Novo ciclo (rascunho)** para novo contrato.

### 2026-09-21 — Limite de unidades por condomínio (SaaS)

- Atualização: Parte 2 (`git pull` + `php artisan migrate --force`). Opcional no `.env`: `SAAS_DEVELOPER_CONTACT` (e-mail ou texto exibido ao síndico quando o limite de unidades for atingido).
- Migration adiciona `units_limit` em `condominiums`. O administrador define na criação/edição do condomínio; o síndico não altera esse valor.
- Condomínios antigos com `units_limit` nulo permanecem sem cota até o admin definir um limite na edição.

### 2026-09-20 — OCR completo (Tesseract, Paddle Python, PaddleOCR.js na portaria)

- **Instalação nova:** Passo 1 (`tesseract-ocr`, `tesseract-ocr-por`, `python3`, `python3-venv`, `python3-pip`); Passo 5 (bloco `OCR_*` / `PADDLE_OCR_*`); Passo 6 (subseção **Encomenda Inteligente — ambiente OCR** com venv opcional); Passo 4 (`npm run build` inclui worker WASM da intake).
- **Atualização:** Parte 2 (`git pull` + `migrate --force` se ainda não rodou + `npm ci` + `npm run build`). Acrescente no `.env` as variáveis novas do `.env.example` (`OCR_MAX_DIMENSION`, `OCR_PREVIEW_TESSERACT_FAST_PATH`, `PADDLE_OCR_PREVIEW_TIMEOUT`, etc.) antes do `config:cache`.
- Migration: `label_ocr_engine` em `condominiums` (padrão `tesseract`). Motor em **Meu Condomínio → Leitura de etiquetas (OCR)**.
- **Portaria:** OCR em tempo real no **navegador** (`/packages/intake`); APIs `match-text` e `preview-client`. Não exige Python na VPS para esse fluxo.
- **Servidor:** Tesseract (padrão) e Paddle Python (opcional, venv + `PADDLE_OCR_PYTHON`). Após mudanças: `php artisan ocr:diagnose`.
- Fallback entre motores no PHP quando um falha; registro manual sempre disponível.

### 2026-09-19 — Regime de ocupação (aluguel / particular / imóvel público)

- Atualização: Parte 2 (`git pull` + `php artisan migrate --force`).
- Em produção, após o migrate, rode uma vez: `php artisan db:seed --class=RolesAndPermissionsSeeder --force` (cria o papel **Proprietário** e permissões).
- Migrations: campos de regime em `units`, `lease_contract_ends_at`, `visible_to_tenant` em `service_orders`, `syndic_participant_profile` em `conversations` e `access_suspended_reason` em `users`. Sem variável de `.env` nova.
- Cron diário 06:00: `php artisan leases:process-contracts` (já no scheduler do Passo 8).

### 2026-09-18 — SMTP Brevo (e-mails transacionais)

- Atualização: configurar no `.env` as variáveis `MAIL_*` com credenciais Brevo (Passo 4 da Parte 1 / bloco de e-mail na Parte 2).
- Servidor: `smtp-relay.brevo.com`, porta `587`, TLS. Login: `SEU_ID@smtp-brevo.com`; senha: chave SMTP gerada no painel Brevo.
- `MAIL_FROM_ADDRESS` deve ser um remetente **verificado** na Brevo (Remetentes / domínio autenticado).
- Após alterar: `php artisan config:clear` e `php artisan config:cache`.

### 2026-09-18 — Dependências, policies de reserva e reset de senha por e-mail

- Atualização: Parte 2 (`git pull` + `composer install --no-dev --optimize-autoloader` + `php artisan migrate --force`).
- `composer.lock` passa a versionar dependências; `yajra/laravel-datatables-oracle` atualizado para `^12.0` (compatível com Laravel 12).
- Pacotes corrigidos via `composer update` (dompdf, guzzle, laravel/framework, phpspreadsheet, league/commonmark, symfony/yaml, etc.) — `composer audit` sem advisories.
- Reset de senha pelo síndico/admin envia **link por e-mail** (não define senha temporária na tela).
- Policies `ReservationPolicy` e `RecurringReservationPolicy` alinhadas às permissões reais do sistema.

### 2026-09-18 — Área SaaS do administrador, uso gratuito e segurança

- Atualização: Parte 2 (`git pull` + `php artisan migrate --force`). Sem variável de `.env` obrigatória.
- Migration adiciona `saas_complimentary` e `saas_complimentary_notes` em `condominiums` (condomínios com acesso gratuito à plataforma, sem contrato SaaS).
- Perfil **Administrador** redireciona para `/platform` (dashboard SaaS), não para o painel do síndico.
- Uso gratuito é configurado em **Plataforma → Assinatura do condomínio**.
- Opcional no `.env`: `SANCTUM_TOKEN_EXPIRATION` (minutos; padrão `43200` = 30 dias) para expiração de tokens da API mobile.
- Correções de segurança: reset de senha sem senha fixa, IDOR em API (espaços, mensagens, pets, marketplace), rate limit no auto-cadastro e bloqueio de navegação de inadimplentes também na API.

### 2026-09-18 — Inadimplência: status, menu restrito e liberação temporária

- Instalação nova: o `migrate` do Passo 5 (Parte 1) cria a tabela `defaulter_access_overrides`.
- Atualização: Parte 2 (`git pull` + `php artisan migrate --force`). Sem variável de `.env` nova.
- Cobranças com vencimento passado passam a aparecer como **Em atraso** em Minhas Cobranças (mesmo antes do job diário marcar `overdue` no banco).
- Com restrição de inadimplentes ativa, o morador bloqueado vê só **Dashboard**, **Minhas Cobranças** e **Fale com o Síndico**; o síndico pode conceder liberação temporária na ficha do usuário (até 30 dias).

### 2026-09-15 — Visitantes (Outro) com QR Code e senha na portaria

- Atualização: Parte 2 (`git pull` + `npm ci` + `npm run build` + `php artisan migrate --force`).
- Migration adiciona `visitor_preset_key`, `access_pin_hash` e `qr_token` em `access_authorizations`.
- Liberações do tipo **Outro** exigem validade; o morador recebe senha de 4 dígitos no WhatsApp e baixa o **PDF com QR Code** na plataforma.
- Senha e QR permanecem válidos até a data/hora de expiração; o visitante pode entrar e sair quantas vezes quiser nesse período.
- O porteiro libera cada entrada escaneando o QR ou digitando a senha no painel de acesso.
- Sem variável de `.env` nova. Tipo WhatsApp `access_visitor_credential` incluído no grupo **Controle de acesso**.

### 2026-09-15 — Taxas do gateway Asaas na prestação de contas

- Atualização: Parte 2 (`git pull` + `php artisan migrate --force`). Migration adiciona `gross_amount`, `net_amount`, `gateway_fee` em `payments`.
- Pagamentos online passam a registrar líquido (`netValue` da API Asaas) no caixa e despesa automática `asaas_gateway_fee` com a tarifa.
- Sem variável de `.env` nova. Requer integração Asaas ativa por condomínio.
- Cobranças já liquidadas antes da atualização mantêm valores anteriores (bruto); novas liquidações via webhook/checkout usam taxa da API.

### 2026-09-14 — Relatório de movimentações de encomendas (síndico)

- Atualização: Parte 2 (`git pull` + `npm ci` + `npm run build`). Sem migration nova.
- Nova rota web `/packages/reports` para síndico/secretaria (`view_packages`). Exportação PDF e Excel exige permissão `export_packages_reports` (incluída no seeder para Síndico e Secretaria).
- Após deploy, rode `php artisan db:seed --class=RolesAndPermissionsSeeder --force` **somente** se precisar sincronizar a permissão `export_packages_reports` em produção sem recriar roles manualmente; alternativa: conceder a permissão via painel de roles.
- O porteiro continua em `/packages` (painel operacional). Síndico acessa movimentações completas e exportação.

### 2026-09-14 — Encomenda Inteligente (OCR de etiqueta + senha de retirada)

- Instalação nova: Passo 1 (Tesseract + Python opcional), Passo 5 (`OCR_*`), Passo 6 (diagnóstico e venv Paddle se necessário); `migrate` cria campos OCR/senha em `packages`.
- Atualização: Parte 2 (`git pull` + `npm ci` + `npm run build` + `php artisan migrate --force`). Antes do `config:cache`, confira o bloco OCR no `.env.example` (inclui `OCR_MAX_DIMENSION` e timeouts Paddle).
- Se a VPS ainda não tem Tesseract: `apt install -y tesseract-ocr tesseract-ocr-por` (uma vez) e depois `OCR_ENABLED=true`.
- HTTPS obrigatório para a câmera do porteiro (`/packages/intake`).
- Sem comando Artisan one-off. Encomendas antigas sem senha continuam retiráveis sem código até esgotarem.
- A atualização também cria os campos de auditoria da retirada (`picked_up_by_name` e `pickup_verified_at`). O porteiro usa `/packages/pickup`, informa a senha e confirma o nome opcional de quem retirou.

### 2026-09-14 — Exibir reservante no calendário de espaços

- Instalação nova: o `migrate` do Passo 5 (Parte 1) cria `spaces.show_reserver_on_calendar` (padrão `false`).
- Atualização: Parte 2 (`git pull` + `php artisan migrate --force`). Sem variável de `.env` nova.
- O síndico ativa por espaço em **Espaços → criar/editar → Calendário Público**. Quando ligado, moradores veem nome e unidade em datas indisponíveis.

### 2026-09-12 — Leads da landing de vendas (Supabase)

- Atualização: Parte 2 (`git pull`). Opcional: no `.env` de produção, configure `SUPABASE_URL`, `SUPABASE_KEY` (publishable) e `SUPABASE_LEADS_ADMIN_TOKEN` para o menu **Configurações globais → Leads** no painel do administrador.
- Sem migration. Sem comando Artisan extra.

### 2026-09-12 — Segundo template de landing page (Connect)

- Instalação nova: o `migrate` do Passo 5 (Parte 1) cria `condominium_landing_pages.template` (padrão `classic`).
- Atualização: Parte 2 (`git pull` + `npm run build` + `php artisan migrate --force`). Sem variável de `.env` nova.
- O síndico escolhe o modelo em Landing Page → aba Geral → **Modelo visual** (Clássica ou Connect). Conteúdos e integrações permanecem os mesmos.

### 2026-09-10 — Módulos do condomínio (síndico liga/desliga)

- Instalação nova: o `migrate` do Passo 5 (Parte 1) cria `condominiums.enabled_modules` e preenche condomínios existentes com todos os módulos ligados.
- Atualização: Parte 2 (`git pull` + `php artisan migrate --force`). Sem variável de `.env` nova. Sem comando Artisan extra.
- Depois do migrate, o síndico configura em Gestão → Meu Condomínio. Módulo desmarcado some do menu e as rotas (web/API) passam a recusar acesso.

### 2026-09-10 — Tutorial interno no navegador

- Instalação nova: `DEV_DOCS_TOKEN` vazio (Passo 5).
- Atualização: nada a rodar. Só defina token no `.env` se o **dev** quiser abrir `/dev/docs/{token}`.
- Sem migration.

### 2026-09-10 — Taxas automáticas e folha no vencimento

- Instalação nova: Passos 8 e 9 bastam.
- Atualização: Parte 2 (git pull + migrate se houver). Cron e worker já precisam existir.
- Sem migration nova desses jobs.
- Opcional no dia do deploy, se o horário do cron já passou: comandos manuais da Parte 2.

---

Quem atualizar este arquivo deve **inserir o comando no passo certo da cronologia**, não criar uma lista solta no final.
