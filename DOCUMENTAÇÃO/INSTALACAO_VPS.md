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
- **Última revisão:** 12/09/2026 (leads Supabase no admin)

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
```

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
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@SEU_DOMINIO
MAIL_FROM_NAME="${APP_NAME}"

ASAAS_API_KEY=
ASAAS_SANDBOX=false
ASAAS_WEBHOOK_TOKEN=

WHATSAPP_ENABLED=false
SAAS_ENFORCE_SUBSCRIPTION=true

DEV_DOCS_TOKEN=
DEV_DOCS_URL="${APP_URL}/dev/docs/"
```

Regras:

- Não copie o `.env` do computador local.
- `DEV_DOCS_TOKEN` vazio em produção (a página interna fica 404).
- `QUEUE_CONNECTION=database` (a fila entra no Passo 9).

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

```bash
tail -f /var/www/condocenter/storage/logs/laravel.log
tail -f /var/www/condocenter/storage/logs/worker.log
```

---

# PARTE 4 — Changelog (o que cada versão exige na VPS)

Ao implementar feature nova: coloque o passo na **Parte 1** se for instalação, ou na **Parte 2** se for só atualização. Depois registre aqui. Não solte comando fora da ordem.

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
