# SindCON na VPS Hostinger compartilhada — tutorial em ordem

Siga **de cima para baixo**. Cada passo assume que o anterior já foi feito. No fim da Parte 1 o site abre em HTTPS, o cron gera as rotinas do dia e a fila processa e-mail, WhatsApp e cobranças.

Esta VPS **já hospeda outros sites**. O SindCON entra como mais um aplicativo: pasta própria, banco próprio, vhost próprio, cron e worker próprios. Nada abaixo apaga o disco, reinstala o sistema ou remove o site de outra pessoa.

Fontes usadas nesta sequência:

- Painel Hostinger: VPS → Overview (SSH e Browser terminal), Security → Firewall, Snapshots/Backups. Trocar o sistema em OS & Panel apaga o servidor inteiro — **não faça isso**.
- [How to Deploy Laravel on a VPS](https://www.hostinger.com/tutorials/how-to-deploy-laravel/) — preparar o Ubuntu, clonar o Git, `.env`, permissão `www-data`, raiz do site em `public`. O tutorial da Hostinger mostra Apache; o SindCON usa **Nginx**, no mesmo espírito de vhost separado.
- [How to install PHP on Ubuntu](https://www.hostinger.com/tutorials/how-to-install-php-ubuntu) — PHP-FPM e socket do Nginx.
- [Getting started with VPS](https://www.hostinger.com/in/tutorials/getting-started-with-vps-hosting/) — SSH, firewall do painel e DNS.

Substitua em todos os comandos:

| Placeholder | Exemplo |
|-------------|---------|
| `IP_DA_VPS` | IP que aparece em VPS → Overview → SSH access |
| `PORTA_SSH` | porta SSH do painel (muitas vezes `22`) |
| `SEU_DOMINIO` | `app.sindcon.com.br` |
| `SENHA_MYSQL` | senha forte só deste banco |
| `ORG` | organização do Git (`git@github-condocenter:ORG/condocenter.git`) |

Constantes desta instalação:

- Servidor: Ubuntu 22.04 ou 24.04 já existente na VPS Hostinger
- Pasta do projeto: `/var/www/condocenter`
- Site público (Nginx): `/var/www/condocenter/public`
- PHP 8.3 (FPM próprio; outras versões dos outros sites ficam no lugar)
- MySQL ou MariaDB do próprio servidor; o banco novo se chama `condocenter` (o serviço só é instalado se ainda não existir)
- Node.js 20, só para compilar os assets
- Fuso do SindCON: `America/Fortaleza` **no `.env`**. Não mude o fuso do sistema operacional — isso alteraria os outros sites. O agendador do Laravel usa `APP_TIMEZONE`.
- **Última revisão:** 05/10/2026 (API Key global Evolution para QR por condomínio)

Leitura no navegador (somente quem tiver o link): `DEV_DOCS_URL` no `.env`.

---

# PARTE 1 — Primeira instalação

Faça esta parte **uma vez** nesta VPS.

Antes de começar, anote o que você **não** vai fazer:

- Não use o template “Laravel” nem “Change OS” no painel. A Hostinger avisa que isso apaga todos os dados do servidor.
- Não apague `/etc/nginx/sites-enabled/default` nem o vhost de outro domínio.
- Não reinstale o MySQL se ele já estiver no ar.
- Não rode `ufw --force enable` se o firewall do painel Hostinger já controla as portas. Dois firewalls escondem o bloqueio.
- Não altere `timedatectl set-timezone`.
- Não rode `DemoDataSeeder` nem `db:wipe`.

---

## Passo 1 — Painel Hostinger: acesso, cópia de segurança, firewall e DNS

1. Entre em [hPanel](https://hpanel.hostinger.com/) → **VPS** → **Manage** neste servidor.
2. Em **Backups** ou **Snapshots**, crie um snapshot. Se um comando afetar outro site, este é o retorno.
3. Em **Overview → SSH access**, anote IP, porta, usuário `root` e a senha (ou a chave). Não anote isso neste arquivo.
4. Em **Security → Firewall**, libere o que o SindCON precisa **sem fechar** o que os outros sites já usam:
   - SSH (porta anotada, em geral 22)
   - HTTP `80`
   - HTTPS `443`
5. No DNS do domínio (hPanel → Domínios → DNS, ou no registrador), crie ou ajuste:

| Tipo | Nome | Valor |
|------|------|--------|
| A | `@` ou o subdomínio (`app`) | `IP_DA_VPS` |
| A | `www` (se for usar) | `IP_DA_VPS` |

O certificado do Passo 10 só funciona depois que esse registro responder o IP da VPS. A propagação pode levar alguns minutos.

Quando terminar: vá para o **Passo 2**.

---

## Passo 2 — Entrar no servidor e ver o que já existe

Pelo **Browser terminal** (Overview → Browser terminal) ou pelo seu computador:

```bash
ssh -p PORTA_SSH root@IP_DA_VPS
```

Confira o sistema e o que já está instalado. Não instale nada ainda.

```bash
lsb_release -ds
whoami
systemctl is-active nginx || true
systemctl is-active mysql || systemctl is-active mariadb || true
php -v || true
node -v || true
composer -V || true
git --version || true
ufw status || true
ls /etc/nginx/sites-enabled
```

Anote três coisas:

- Se o Nginx já está `active`.
- Se o MySQL ou o MariaDB já está `active`.
- Se o UFW está `inactive` (o normal quando o firewall é o do painel) ou `active`.

Quando terminar: vá para o **Passo 3**.

---

## Passo 3 — Completar só os pacotes que faltam

Atualize a lista de pacotes. O `upgrade` do sistema inteiro mexe em bibliotecas dos outros sites: só rode se o snapshot do Passo 1 existir e você aceitar esse efeito.

```bash
apt update
```

Ferramentas básicas (seguem o manual da Hostinger: Git, unzip, curl):

```bash
apt install -y curl git unzip ca-certificates software-properties-common
```

Nginx, só se o Passo 2 mostrou que ele não está ativo:

```bash
apt install -y nginx
systemctl enable --now nginx
```

MySQL, só se nem `mysql` nem `mariadb` estavam ativos. Se o banco já existe, **pule** este bloco.

```bash
apt install -y mysql-server
systemctl enable --now mysql
```

PHP 8.3 ao lado das outras versões. No Ubuntu 22.04 o PHP 8.3 vem do PPA; no 24.04 ele já está nos repositórios e o PPA também serve.

```bash
add-apt-repository -y ppa:ondrej/php
apt update
apt install -y \
  php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl \
  php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl php8.3-tokenizer
systemctl enable --now php8.3-fpm
php8.3 -v
```

Não desinstale `php8.1-fpm` nem outra versão. Os vhosts antigos continuam no socket antigo. O SindCON usa só `/run/php/php8.3-fpm.sock`.

OCR de etiquetas. O Tesseract atende o padrão do sistema. O Python só é necessário se algum condomínio escolher PaddleOCR (Python) em Meu Condomínio. A portaria (`/packages/intake`) usa PaddleOCR.js no navegador e **não** depende do Python da VPS.

```bash
apt install -y tesseract-ocr tesseract-ocr-por python3 python3-venv python3-pip
tesseract --version
```

Composer 2. Se `composer -V` já mostrar Composer 2, pule. O pacote `apt install composer` da Hostinger às vezes fica antigo demais para o Laravel 12; o instalador oficial evita isso.

```bash
curl -sS https://getcomposer.org/installer | php8.3
mv composer.phar /usr/local/bin/composer
composer -V
```

Node.js 20, necessário para `npm run build`. Se `node -v` já for v20 ou maior, pule. Se outro site depender de um Node mais antigo no comando `node` global, pare aqui e instale o Node 20 só para este build (nvm ou binário separado) antes de seguir — o script da NodeSource troca o `node` padrão do servidor.

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs
node -v && npm -v
```

Supervisor, para o worker da fila:

```bash
apt install -y supervisor
systemctl enable --now supervisor
```

Firewall local, só se o Passo 2 mostrou UFW **active**. Não ative um UFW que estava desligado.

```bash
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw status
```

Quando terminar: vá para o **Passo 4**.

---

## Passo 4 — Criar o banco só do SindCON

Entre no MySQL que já está no servidor. No Ubuntu o root do banco entra sem senha pelo socket:

```bash
mysql
```

Se pedir senha:

```bash
mysql -u root -p
```

Dentro do MySQL (não apague outro banco):

```sql
CREATE DATABASE condocenter CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'condocenter'@'localhost' IDENTIFIED BY 'SENHA_MYSQL';
GRANT ALL PRIVILEGES ON condocenter.* TO 'condocenter'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

O usuário `condocenter` só alcança o banco `condocenter`, e só a partir do próprio servidor (`localhost`). Guarde `SENHA_MYSQL` para o Passo 7.

Quando terminar: vá para o **Passo 5**.

---

## Passo 5 — Clonar o projeto (Git)

A Hostinger recomenda publicar pelo Git, não pelo upload solto do painel. A pasta fica fora de `/var/www/html`, para não misturar com o site padrão.

Repositório privado: crie uma deploy key **deste** servidor e cole a chave pública no Git (read-only).

```bash
ssh-keygen -t ed25519 -C "vps-condocenter" -f /root/.ssh/id_ed25519_condocenter -N ""
cat /root/.ssh/id_ed25519_condocenter.pub
```

Não use `Host github.com` no SSH config: isso trocaria a chave dos outros repositórios desta VPS. Crie um apelido só do SindCON:

```bash
mkdir -p /root/.ssh
chmod 700 /root/.ssh
cat >> /root/.ssh/config << 'EOF'
Host github-condocenter
  HostName github.com
  IdentityFile /root/.ssh/id_ed25519_condocenter
  IdentitiesOnly yes
EOF
chmod 600 /root/.ssh/config
```

Clone com esse apelido (troque `ORG`):

```bash
mkdir -p /var/www
git clone git@github-condocenter:ORG/condocenter.git /var/www/condocenter
```

HTTPS, se não for usar chave:

```bash
git clone https://github.com/SEU_ORG/condocenter.git /var/www/condocenter
```

Confira o branch e marque o diretório como seguro para o Git (a pasta vai ficar com dono `www-data` e o `git pull` roda como `root`):

```bash
cd /var/www/condocenter
git checkout main
git status
git config --global --add safe.directory /var/www/condocenter
```

Dono só desta pasta. Não rode `chown` em `/var/www` inteiro.

```bash
chown -R www-data:www-data /var/www/condocenter
```

Quando terminar: vá para o **Passo 6**.

---

## Passo 6 — Instalar dependências do projeto

Ainda em `/var/www/condocenter`. `HOME` e `COMPOSER_HOME` ficam dentro do projeto para o usuário `www-data` não escrever em `/root`.

```bash
cd /var/www/condocenter
sudo -u www-data env HOME=/var/www/condocenter COMPOSER_HOME=/var/www/condocenter/.composer \
  composer install --no-dev --optimize-autoloader
sudo -u www-data env HOME=/var/www/condocenter \
  npm ci
sudo -u www-data env HOME=/var/www/condocenter \
  npm run build
chown -R www-data:www-data /var/www/condocenter
```

O `npm run build` gera o CSS, o JS e o worker da leitura de etiqueta no celular.

Quando terminar: vá para o **Passo 7**.

---

## Passo 7 — Configurar o `.env`

Não copie o `.env` do computador local.

```bash
cd /var/www/condocenter
sudo -u www-data cp .env.example .env
sudo -u www-data php8.3 artisan key:generate
nano .env
```

Produção (ajuste domínio, senha e e-mail):

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

ASAAS_API_KEY=
ASAAS_SANDBOX=false
ASAAS_WEBHOOK_TOKEN=

WHATSAPP_ENABLED=false
EVOLUTION_API_URL=http://127.0.0.1:8080
EVOLUTION_API_KEY=
EVOLUTION_INSTANCE=
EVOLUTION_GLOBAL_API_KEY=
EVOLUTION_INSTANCE_PREFIX=sindcon
WHATSAPP_DEFAULT_COUNTRY_CODE=55

SAAS_ENFORCE_SUBSCRIPTION=true
SAAS_GRACE_DAYS=0
SAAS_WEBHOOK_BASE_URL="${APP_URL}"
SAAS_DEVELOPER_CONTACT=seu-email@exemplo.com

OCR_ENABLED=true
OCR_LANG=por
OCR_TIMEOUT=30
OCR_PREPROCESS_ENABLED=true
OCR_MAX_DIMENSION=2400
TESSERACT_PATH=
OCR_PREVIEW_TESSERACT_FAST_PATH=true
PADDLE_OCR_PREVIEW_TIMEOUT=75

DEV_DOCS_TOKEN=
DEV_DOCS_URL="${APP_URL}/dev/docs/"
```

Opcionais, só se for usar na hora (também estão no `.env.example`):

```env
# Consultor financeiro
OPENAI_API_KEY=
OPENAI_MODEL=gpt-5.6-luna
OPENAI_TIMEOUT=30
GEMINI_API_KEY=
GEMINI_MODEL=gemini-3.8-flash
GEMINI_TIMEOUT=30
FINANCE_AI_RATE_LIMIT=10
FINANCE_AI_CACHE_TTL=3600

# Leads da landing (menu do administrador)
SUPABASE_URL=
SUPABASE_KEY=
SUPABASE_LEADS_ADMIN_TOKEN=

# PaddleOCR em Python — descomente no Passo 8 se instalar o venv
# PADDLE_OCR_PYTHON=/var/www/condocenter/storage/app/ocr/venv/bin/python
# PADDLE_OCR_SCRIPT=/var/www/condocenter/scripts/ocr/paddle_label.py
# PADDLE_OCR_TIMEOUT=120

# API mobile: minutos até o token expirar (padrão 43200 = 30 dias)
# SANCTUM_TOKEN_EXPIRATION=43200
```

Regras:

- `DEV_DOCS_TOKEN` fica vazio em produção (a página interna responde 404).
- `QUEUE_CONNECTION=database`. A fila sobe no Passo 12. O Redis do Laravel não é obrigatório.
- O remetente (`MAIL_FROM_ADDRESS`) precisa estar verificado no painel Brevo.
- Câmera do porteiro exige `APP_URL=https://...`.
- Credenciais Asaas e WhatsApp de cada condomínio ficam no painel, não só neste arquivo.
- Sem Tesseract: `OCR_ENABLED=false`. O registro manual de encomendas continua.

Quando terminar: vá para o **Passo 8**.

---

## Passo 8 — Primeira carga do Laravel

Use `php8.3` de propósito, para não cair no PHP padrão de outro site.

```bash
cd /var/www/condocenter
mkdir -p storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

sudo -u www-data php8.3 artisan migrate --force
sudo -u www-data php8.3 artisan db:seed --class=RolesAndPermissionsSeeder --force
sudo -u www-data php8.3 artisan storage:link
sudo -u www-data php8.3 artisan config:cache
sudo -u www-data php8.3 artisan route:cache
sudo -u www-data php8.3 artisan view:cache
```

**Não rode** `DemoDataSeeder` nem `db:wipe`.

### Encomenda Inteligente — OCR neste servidor

| Camada | Onde roda | O que a VPS precisa |
|--------|-----------|---------------------|
| PaddleOCR.js | Navegador do porteiro (`/packages/intake`) | `npm run build` (Passo 6) e HTTPS (Passo 10) |
| Tesseract | PHP (`www-data`) | Pacote do Passo 3 |
| PaddleOCR (Python) | PHP chama `scripts/ocr/paddle_label.py` | Bloco abaixo, só se o condomínio for usar esse motor |

Conferir o Tesseract:

```bash
cd /var/www/condocenter
tesseract --version
sudo -u www-data php8.3 artisan ocr:diagnose
```

Saída esperada: `Tesseract: disponível`.

PaddleOCR em Python é opcional. Instale **como `www-data`**, senão o PHP-FPM não vê os pacotes. CPU, sem GPU. O primeiro `pip` pode levar vários minutos.

```bash
cd /var/www/condocenter
mkdir -p storage/app/ocr
chown -R www-data:www-data storage/app/ocr
sudo -u www-data python3 -m venv storage/app/ocr/venv
sudo -u www-data storage/app/ocr/venv/bin/pip install --upgrade pip wheel
sudo -u www-data storage/app/ocr/venv/bin/pip install paddlepaddle -i https://www.paddlepaddle.org.cn/packages/stable/cpu/
sudo -u www-data storage/app/ocr/venv/bin/pip install paddleocr
sudo -u www-data storage/app/ocr/venv/bin/python -c "import paddleocr; print('paddleocr OK')"
```

No `.env`, aponte o interpretador e rode de novo:

```env
PADDLE_OCR_PYTHON=/var/www/condocenter/storage/app/ocr/venv/bin/python
PADDLE_OCR_SCRIPT=/var/www/condocenter/scripts/ocr/paddle_label.py
PADDLE_OCR_TIMEOUT=120
```

```bash
sudo -u www-data php8.3 artisan config:clear
sudo -u www-data php8.3 artisan config:cache
sudo -u www-data php8.3 artisan ocr:diagnose
```

Modelos ficam em `storage/app/ocr/paddle-runtime/` (a pasta precisa de escrita). A primeira leitura baixa modelos. Se o diagnóstico disser `paddleocr_not_installed`, o `pip` foi feito em outro Python: o caminho de `PADDLE_OCR_PYTHON` tem de ser o do venv acima.

| Variável | Uso |
|----------|-----|
| `OCR_ENABLED` | `true`. Desligue só se não houver Tesseract e não quiser OCR no servidor |
| `OCR_LANG` | `por` |
| `OCR_TIMEOUT` | Segundos do Tesseract (30) |
| `OCR_PREPROCESS_ENABLED` | Trata a imagem antes do OCR |
| `OCR_MAX_DIMENSION` | Reduz imagem grande (2400 px) |
| `TESSERACT_PATH` | Vazio no Ubuntu |
| `PADDLE_OCR_TIMEOUT` | Subprocesso Paddle (120 s) |
| `PADDLE_OCR_PREVIEW_TIMEOUT` | Limite no preview (75 s) |
| `OCR_PREVIEW_TESSERACT_FAST_PATH` | No preview, tenta Tesseract antes do Paddle |

Quando terminar: vá para o **Passo 9**.

---

## Passo 9 — Nginx: um site novo, os outros intactos

Crie só o arquivo do SindCON:

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
        fastcgi_read_timeout 180;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Ative **este** site. Não apague os outros arquivos de `sites-enabled`.

```bash
ln -sf /etc/nginx/sites-available/condocenter /etc/nginx/sites-enabled/condocenter
nginx -t
systemctl reload nginx
```

`nginx -t` precisa terminar com `syntax is ok`. Se falhar, não recarregue: o erro está no arquivo novo ou num conflito de `server_name` com outro vhost. Ajuste o domínio e teste de novo.

Limite de upload de foto, só no PHP 8.3 (não edite o `php.ini` de outra versão):

```bash
nano /etc/php/8.3/fpm/php.ini
```

```ini
upload_max_filesize = 32M
post_max_size = 32M
```

```bash
systemctl reload php8.3-fpm
```

Quando terminar: vá para o **Passo 10**.

---

## Passo 10 — HTTPS deste domínio

O DNS do Passo 1 já precisa apontar para esta VPS. O Certbot altera só o vhost do domínio informado.

```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d SEU_DOMINIO -d www.SEU_DOMINIO
```

Se não existir `www`, use apenas `-d SEU_DOMINIO`.

Abra `https://SEU_DOMINIO`. Deve aparecer o login do SindCON. Os outros domínios da VPS continuam nos vhosts deles.

Quando terminar: vá para o **Passo 11**.

---

## Passo 11 — Ligar o agendador (cron)

Sem esta linha o sistema não gera a taxa do mês seguinte, não liquida folha e não marca atraso.

Edite o crontab **do www-data**. Se já houver linhas de outro projeto, acrescente a linha no final. Não apague as que já existem.

```bash
crontab -u www-data -e
```

```
* * * * * cd /var/www/condocenter && php8.3 artisan schedule:run >> /dev/null 2>&1
```

Confira:

```bash
sudo -u www-data php8.3 artisan schedule:list
```

O Laravel dispara no fuso `America/Fortaleza` por causa do `APP_TIMEZONE`. O relógio do sistema pode continuar em UTC.

| Quando | O que faz |
|--------|-----------|
| Todo dia 05:00 | Gera a próxima cobrança das taxas automáticas (`fees:generate-upcoming`) |
| Todo dia 06:00 | Suspende inquilino com contrato vencido (`leases:process-contracts`) |
| Todo dia 06:15 | Renova contratos SaaS vencidos com autorrenovação (`subscriptions:auto-renew`) |
| Todo dia 06:30 | Liquida desconto em folha no vencimento (`charges:settle-payroll`) |
| Todo dia 07:00 | Marca cobrança vencida como atraso, exceto folha (`charges:mark-overdue`) |
| Todo dia 08:00 | Lembretes de vencimento (`charges:send-reminders`) |
| Todo dia 09:00 | Aviso de atraso (`charges:check-overdue`) |
| Dia 1 às 08:00 | Relatórios mensais (`reports:generate-monthly`) |
| A cada hora | Cancela pré-reservas não pagas |
| A cada 15 min | Encerra avisos do condomínio após `expires_at` (`announcements:close-expired`) |
| Semanal | Apaga notificações lidas com mais de 30 dias |

Você não precisa rodar esses comandos na instalação. O cron cuida.

Quando terminar: vá para o **Passo 12**.

---

## Passo 12 — Ligar a fila (Supervisor)

O `.env` usa `QUEUE_CONNECTION=database`. O programa abaixo é só do SindCON. Não dê `restart all`.

```bash
nano /etc/supervisor/conf.d/condocenter-worker.conf
```

```ini
[program:condocenter-worker]
process_name=%(program_name)s_%(process_num)02d
command=php8.3 /var/www/condocenter/artisan queue:work database --sleep=3 --tries=3 --timeout=120 --max-time=3600
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
supervisorctl status condocenter-worker:*
```

O status esperado é `RUNNING`.

Quando terminar: vá para o **Passo 13**.

---

## Passo 13 — Backup diário só deste banco

O dump não inclui os outros bancos da VPS.

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
chmod 700 /usr/local/bin/backup-condocenter.sh
crontab -e
```

No crontab do **root**, acrescente uma linha. Não apague as outras.

```
0 2 * * * /usr/local/bin/backup-condocenter.sh
```

Quando terminar: vá para o **Passo 14**.

---

## Passo 14 — Integrações (o site já abre)

1. No Asaas, webhook da plataforma: `https://SEU_DOMINIO/webhooks/asaas`. Cobrança de condomínio usa a URL que o painel mostra (`/webhooks/asaas/condominium/{id}` e `/webhooks/asaas/platform`).
2. WhatsApp fica desligado até `WHATSAPP_ENABLED=true` e as variáveis `EVOLUTION_*`. A instância **SaaS** usa `EVOLUTION_API_KEY` + `EVOLUTION_INSTANCE`. Para cada condomínio criar a própria instância pelo QR no painel, informe também `EVOLUTION_GLOBAL_API_KEY` (mesma chave `AUTHENTICATION_API_KEY` do servidor Evolution) em **Plataforma → WhatsApp** ou no `.env`. Cada condomínio conecta em Configurações → WhatsApp (sem criar instância manual no manager). Se a Evolution roda em Docker nesta mesma VPS, deixe a API em `127.0.0.1` (não publique a porta 8080 na internet) e mantenha o container Redis no ar quando `CACHE_REDIS_ENABLED=true`. Instância `open` com timeout no envio costuma ser Redis parado.
3. Consultor financeiro: sem `OPENAI_API_KEY` ou `GEMINI_API_KEY` a tela abre e a análise devolve aviso amigável. O limite mensal é definido no painel da organização.
4. Leads da landing: `SUPABASE_URL`, `SUPABASE_KEY` e `SUPABASE_LEADS_ADMIN_TOKEN` só se o administrador for usar Configurações globais → Leads.
5. OCR: `sudo -u www-data php8.3 artisan ocr:diagnose`. Intake com câmera depende do build do Passo 6 e do HTTPS do Passo 10.
6. Depois de mudar o `.env`: `sudo -u www-data php8.3 artisan config:cache`.

Quando terminar: vá para o **Passo 15**.

---

## Passo 15 — Conferir a instalação

```bash
cd /var/www/condocenter
sudo -u www-data php8.3 artisan about
sudo -u www-data php8.3 artisan schedule:list
supervisorctl status condocenter-worker:*
nginx -t
tail -n 50 storage/logs/laravel.log
```

Checklist:

- [ ] Snapshot do Passo 1 existe, se você precisou atualizar pacotes
- [ ] `https://SEU_DOMINIO` abre o login
- [ ] Outro domínio que já estava nesta VPS continua abrindo
- [ ] Login com um usuário criado no sistema funciona
- [ ] `schedule:list` mostra os jobs da tabela do Passo 11
- [ ] `condocenter-worker` está `RUNNING`
- [ ] `php8.3 artisan ocr:diagnose` — Tesseract disponível
- [ ] `/packages/intake` abre a câmera em HTTPS (teste no celular)
- [ ] Crontab do `www-data` tem a linha do SindCON e as linhas antigas de outros projetos continuam lá

**Primeira instalação concluída.** No dia a dia use só a **Parte 2**.

---

# PARTE 2 — Atualizar o sistema (já instalado)

Use esta parte **sempre que houver código novo** (git pull). Não refaça a Parte 1.

Faça nesta ordem, sem pular:

```bash
cd /var/www/condocenter

# 1) Backup (só o banco condocenter)
mysqldump -u condocenter -p condocenter | gzip > /var/backups/condocenter/pre_deploy_$(date +%Y%m%d_%H%M%S).sql.gz

# 2) Manutenção
sudo -u www-data php8.3 artisan down

# 3) Código
git pull origin main

# 4) Dependências e assets
sudo -u www-data env HOME=/var/www/condocenter COMPOSER_HOME=/var/www/condocenter/.composer \
  composer install --no-dev --optimize-autoloader
sudo -u www-data env HOME=/var/www/condocenter npm ci
sudo -u www-data env HOME=/var/www/condocenter npm run build
chown -R www-data:www-data /var/www/condocenter

# 5) Banco e arquivos públicos
sudo -u www-data php8.3 artisan migrate --force
sudo -u www-data php8.3 artisan storage:link

# 6) Cache
sudo -u www-data php8.3 artisan optimize:clear
sudo -u www-data php8.3 artisan config:cache
sudo -u www-data php8.3 artisan route:cache
sudo -u www-data php8.3 artisan view:cache

# 7) Fila (só o worker do SindCON)
supervisorctl restart condocenter-worker:*

# 8) Site no ar
sudo -u www-data php8.3 artisan up
```

Se o `.env` ganhou variável nova (veja o changelog abaixo), edite o `.env` **antes** do `config:cache`.

Se o deploy alterou OCR, Python ou `scripts/ocr/`, rode após o `config:cache`:

```bash
sudo -u www-data php8.3 artisan ocr:diagnose
```

Não reexecute migrations antigas à mão. Só `php artisan migrate --force`.

### Depois do deploy (só se o changelog pedir)

O cron do Passo 11 já roda os jobs financeiros. Só execute na mão se o changelog disser “rodar uma vez agora”:

```bash
cd /var/www/condocenter
sudo -u www-data php8.3 artisan fees:generate-upcoming
sudo -u www-data php8.3 artisan charges:settle-payroll
sudo -u www-data php8.3 artisan charges:mark-overdue
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
| Outro site da VPS parou | Não troque o SO no painel. Confira se o vhost antigo continua em `sites-enabled`, se o UFW não foi ligado por cima do firewall da Hostinger e se o PHP desse site não foi desinstalado |
| Certbot não emite | DNS do Passo 1 ainda não aponta para `IP_DA_VPS`; `dig SEU_DOMINIO` |
| `php` roda a versão errada | Use `php8.3` nos comandos. O `php` solto pode ser o de outro site |

```bash
tail -f /var/www/condocenter/storage/logs/laravel.log
tail -f /var/www/condocenter/storage/logs/worker.log
```

---

# PARTE 4 — Changelog (o que cada versão exige na VPS)

Ao implementar feature nova: coloque o passo na **Parte 1** se for instalação, ou na **Parte 2** se for só atualização. Depois registre aqui. Não solte comando fora da ordem.

A Parte 1 foi renumerada em 30/09/2026. Nas entradas antigas, “Passo 8” do cron é o **Passo 11** atual; Nginx/HTTPS são os **Passos 9 e 10**; `.env` é o **Passo 7**; primeira carga e OCR são o **Passo 8**.

### 2026-10-05 — WhatsApp: QR Code por condomínio (provisionamento)

- Sem `php artisan migrate`.
- Opcional no `.env`: `EVOLUTION_GLOBAL_API_KEY` (chave global da Evolution) e `EVOLUTION_INSTANCE_PREFIX` (padrão `sindcon`). Também pode ser salva em **Plataforma → WhatsApp → API Key global (provisionamento)**.
- Após deploy: confirme `EVOLUTION_API_URL` acessível pelo PHP da aplicação; síndicos conectam o celular em **Gestão → WhatsApp → Gerar QR Code**.
- **Obrigatório na atualização:** subir `routes/web.php` e `routes/condominium_whatsapp_settings.php` e rodar `php artisan route:clear` antes de `route:cache` (rotas `condominiums.settings.whatsapp.instance.*`). Sem isso a tela do síndico pode retornar erro 500 ou o QR não responde.

### 2026-09-30 — VPS Hostinger compartilhada

- Instalação nova: seguir a Parte 1 inteira (painel, snapshot, pacotes que faltam, clone, Nginx sem apagar os outros vhosts, cron do `www-data`, worker `condocenter-worker`).
- Não há migrate nem variável nova por causa desta revisão.
- Servidor que já rodava o tutorial anterior: não reinstale. Confira apenas se o crontab e o Supervisor continuam os da Parte 2 e se o `php` do cron é o 8.3 (`php8.3 artisan schedule:run`).

### 2026-09-29 — WhatsApp / Evolution (Redis)

- Sem migrate, sem variável nova no `.env` do SindCON e sem worker novo.
- Se a Evolution API (Docker) usa Redis (`CACHE_REDIS_ENABLED=true`), o container `redis` precisa estar no ar. Instância `open` com timeout no envio costuma ser Redis parado — `docker start redis` (ou o compose da Evolution) e repetir o teste.
- Passo 11 atualizado com esse requisito operacional.

### 2026-09-26 — Provider LLM por condomínio (OpenAI + Gemini)

- Atualização: Parte 2 (`git pull` + `php artisan migrate --force`). Migration `2026_09_26_200000_add_ai_provider_fields_to_condominiums_and_consultations` adiciona `condominiums.ai_provider` / `ai_model` e `ai_financial_consultations.provider`. Condomínios existentes ficam em `openai` (compatível com o comportamento anterior).
- Antes do `config:cache`, acrescente no `.env` (opcional para Gemini): `GEMINI_API_KEY`, `GEMINI_MODEL` (padrão `gemini-3.8-flash`), `GEMINI_TIMEOUT`. OpenAI permanece com `OPENAI_*`.
- Admin escolhe provider/modelo em **Condomínios → [condo] → Consultor Financeiro — Inteligência Artificial**. Sem cron/worker novo. Sem dependência Composer nova.

### 2026-09-26 — Limite mensal LLM por organização

- Atualização: Parte 2 (`git pull` + `php artisan migrate --force`). Migration `2026_09_26_190000_add_llm_monthly_limit_to_organizations_table` adiciona `organizations.llm_monthly_limit` (nullable).
- Sem variável de `.env` nova. O admin da plataforma define o limite em **Organizações → [org] → Contrato → Consultor Financeiro (LLM)**.
- Administradora: a cota mensalmente é compartilhada entre todos os condomínios da org. Cliente direto: cota do condomínio da organização.
- Conta apenas status `success` (cache hit não consome).

### 2026-09-26 — Consultor Financeiro SindCON (IA)

- Atualização: Parte 2 (`git pull` + `php artisan migrate --force`). Migration `2026_09_26_180000_create_ai_financial_consultations_table` cria a tabela `ai_financial_consultations` (log de uso/tokens; sem snapshot financeiro completo).
- Antes do `config:cache`, acrescente no `.env` (veja `.env.example`): `OPENAI_API_KEY`, `OPENAI_MODEL` (padrão `gpt-5.6-luna`), `OPENAI_TIMEOUT`, `FINANCE_AI_RATE_LIMIT`, `FINANCE_AI_CACHE_TTL`. Sem a API key, a tela funciona mas a análise retorna mensagem amigável.
- Sem cron/worker novo. Sem dependência Composer nova (usa `Http` facade).
- Recurso só no **modo financeiro completo**: `/financial/consultor`.

### 2026-09-26 — Meus condomínios (síndico multi-origem)

- Atualização: Parte 2 (`git pull` + `php artisan migrate --force`). Migration `2026_09_26_170000_backfill_condominium_user_for_syndics` preenche a pivot `condominium_user` para síndicos que tinham só `users.condominium_id` (condomínio particular), sem apagar dados existentes (`insertOrIgnore`).
- Sem variável de `.env` nova. Sem cron/worker novo.

### 2026-09-22 — Categorias de despesa e insights no dashboard

- Atualização: Parte 2 (`git pull` + `php artisan migrate --force`). Migration `2026_09_22_210000_add_category_to_condominium_accounts` adiciona `category` / `subcategory` nullable em `condominium_accounts` (sem backfill; lançamentos antigos ficam “Não informada” até novas despesas).
- Sem variável de `.env` nova. Sem worker/cron novo.
- Síndico passa a informar categoria ao registrar pagamento; dashboard mostra alertas e previsão por categoria.

### 2026-09-22 — Biblioteca de documentos (módulo Documentos)

- Atualização: Parte 2 (`git pull` + `composer install --no-dev --optimize-autoloader` + `php artisan migrate --force`). Dependência PHP nova: `smalot/pdfparser` (extração de texto de PDF para busca).
- Arquivos enviados pelo síndico ficam em `storage/app/condominium_library/{condominium_id}/` (disco `local`, não público). Sem variável de `.env` nova. O regimento interno existente aparece na biblioteca após a primeira visita à tela (sincronização automática).

### 2026-09-21 — Expiração automática de avisos do síndico

- Atualização: Parte 2 (`git pull`). Sem migration. O cron do Passo 8 passa a executar `announcements:close-expired` a cada 15 minutos (encerra avisos com `expires_at` vencido).
- Ao abrir dashboard ou listar avisos, o sistema também encerra na hora os vencidos do condomínio. Opcional após deploy: `php artisan announcements:close-expired`.

### 2026-09-21 — Central de comunicação (manutenção)

- Comando opcional **somente homologação / reset de testes:** `php artisan communications:purge --force` — apaga todas as conversas (diretas, avisos, sigilosas), mensagens, anexos e reuniões vinculadas. **Não** usar em produção com dados reais sem backup.

### 2026-09-21 — Gestão SaaS: cobranças e contratos (administrador)

- Atualização: Parte 2 (`git pull` + `npm run build` se houver assets). Sem migration.
- Administrador: **Plataforma → Cobranças SaaS** (`/platform/billing`) — visão consolidada; por condomínio em **Gerenciar contrato** — cancelar/estornar cobrança Asaas, cobrança avulsa, cancelar/reativar assinatura, **Novo ciclo (rascunho)** para novo contrato.

### 2026-09-24 — Autorrenovação de contratos SaaS

- Atualização: Parte 2 (`git pull` + `php artisan migrate --force`). Migration `2026_09_24_181000_add_auto_renew_to_subscriptions` adiciona `auto_renew` em `organization_subscriptions` e `condominium_subscriptions`.
- Cron diário 06:15: `php artisan subscriptions:auto-renew` (já no scheduler do Passo 8). Não recria a assinatura no Asaas; só prorroga a vigência e envia e-mail.

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

### 2026-09-23 — Organizações multi-tenant, administradoras e LGPD

- Instalação nova: o `migrate` do Passo 5 (Parte 1) cria `organizations`, `organization_user`, `organization_subscriptions`, colunas de plano (`audience`, limites, módulos), FK `condominiums.organization_id` (com backfill), tabelas LGPD e `security_logs`.
- Atualização: Parte 2 (`git pull` + `php artisan migrate --force` + `npm run build` se houver front). Sem variável de `.env` nova.
- Depois do migrate, condomínios existentes passam a ter organização direta (`type=condominium`). Operador usa `/platform/organizations` e onboarding de clientes; administradoras usam `/organizacao`.

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
