# 🚀 Guia de Deploy - CondoManager

## Deploy na Hostinger (Produção)

### Pré-requisitos

- Plano de hospedagem compartilhada ou VPS
- PHP 8.3+
- MySQL 8.0+
- Acesso SSH (recomendado) ou FTP
- Domínio configurado

---

## 📋 Checklist Pré-Deploy

- [ ] Código testado localmente
- [ ] Migrations testadas
- [ ] .env configurado para produção
- [ ] Assets compilados (`npm run build`)
- [ ] Chave Asaas de produção obtida
- [ ] Backup do banco de dados atual (se houver)
- [ ] Email de produção configurado

---

## 🔧 Passos de Deploy

### 1. Preparar o Código Local

```bash
# Limpar caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Compilar assets para produção
npm run build

# Otimizar autoload
composer install --optimize-autoloader --no-dev

# Testar
php artisan test
```

### 2. Upload de Arquivos

#### Via SSH (Recomendado)

```bash
# Comprimir projeto
tar -czf condomanager.tar.gz --exclude='node_modules' --exclude='.git' --exclude='vendor' .

# Upload via SCP
scp condomanager.tar.gz usuario@seudominio.com:/home/usuario/

# No servidor
cd /home/usuario/public_html
tar -xzf ../condomanager.tar.gz
```

#### Via FTP

1. Faça upload de todos os arquivos EXCETO:
   - `node_modules/`
   - `.git/`
   - `vendor/` (será instalado no servidor)
   - `.env` (criar manualmente no servidor)

### 3. Configurar no Servidor

#### A) Conectar via SSH

```bash
ssh usuario@seudominio.com
cd public_html
```

#### B) Instalar Dependências

```bash
# Instalar Composer dependencies
composer install --optimize-autoloader --no-dev

# Instalar Node dependencies (se necessário)
npm install --production
npm run build
```

#### C) Configurar .env

```bash
nano .env
```

Conteúdo do `.env` de produção:

```env
APP_NAME="CondoManager"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://seudominio.com
APP_TIMEZONE=America/Sao_Paulo

APP_LOCALE=pt_BR

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u123456_condocenter
DB_USERNAME=u123456_user
DB_PASSWORD=SENHA_SEGURA_AQUI

# Cache e Sessão
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# Email (Gmail ou SendGrid)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu_email@gmail.com
MAIL_PASSWORD=senha_app_gmail
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@seudominio.com"
MAIL_FROM_NAME="CondoManager"

# Asaas PRODUÇÃO
ASAAS_API_KEY=sua_chave_producao_aqui
ASAAS_SANDBOX=false
ASAAS_WEBHOOK_EMAIL=admin@seudominio.com

# Storage
FILESYSTEM_DISK=local
# Para usar S3:
# FILESYSTEM_DISK=s3
# AWS_ACCESS_KEY_ID=
# AWS_SECRET_ACCESS_KEY=
# AWS_DEFAULT_REGION=us-east-1
# AWS_BUCKET=
```

#### D) Gerar Application Key

```bash
php artisan key:generate
```

#### E) Executar Migrations

```bash
php artisan migrate --force
```

#### F) Popular Banco (apenas primeira vez)

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

**⚠️ NÃO** execute o `DemoDataSeeder` em produção!

#### G) Otimizar para Produção

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

#### H) Configurar Permissões

```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 4. Configurar .htaccess

Certifique-se de que o `.htaccess` está no diretório `public`:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Handle Front Controller
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### 5. Configurar Document Root

No painel da Hostinger, configure o **Document Root** para apontar para a pasta `public`:

```
/home/usuario/public_html/public
```

### 6. Configurar Webhook Asaas

No painel do Asaas, configure o webhook para:

```
https://seudominio.com/webhooks/asaas
```

### 7. Configurar Cron Jobs

No painel da Hostinger, adicione um cron job:

**Comando:**
```bash
cd /home/usuario/public_html && php artisan schedule:run >> /dev/null 2>&1
```

**Frequência:** A cada minuto (`* * * * *`)

### 8. Configurar Queue Worker

#### Opção A: Supervisor (VPS)

Criar arquivo `/etc/supervisor/conf.d/condomanager-worker.conf`:

```ini
[program:condomanager-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/usuario/public_html/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/home/usuario/public_html/storage/logs/worker.log
stopwaitsecs=3600
```

Depois:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start condomanager-worker:*
```

#### Opção B: Cron Job (Hospedagem Compartilhada)

Adicionar cron para processar fila a cada minuto:

```bash
* * * * * cd /home/usuario/public_html && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

---

## 🔒 Segurança em Produção

### 1. Configurar HTTPS

- Use SSL/TLS (Let's Encrypt gratuito)
- Force HTTPS no `.env`:
  ```env
  SESSION_SECURE_COOKIE=true
  ```

### 2. Proteger Arquivos Sensíveis

```bash
# Remover arquivos desnecessários
rm -rf tests/
rm -rf .git/
rm README.md
```

### 3. Desabilitar Debug

```env
APP_DEBUG=false
APP_ENV=production
```

### 4. Configurar Rate Limiting

Já configurado nas rotas API.

### 5. Backup Automático

Configure backup diário do banco:

```bash
#!/bin/bash
# Script: backup-db.sh
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u usuario -p senha condocenter > /backup/condocenter_$DATE.sql
find /backup -name "condocenter_*.sql" -mtime +7 -delete
```

Adicionar ao cron:
```
0 2 * * * /path/to/backup-db.sh
```

---

## 🔄 Atualizações Futuras

### Processo de Atualização

```bash
# 1. Backup
mysqldump -u root -p condocenter > backup_pre_update.sql

# 2. Modo manutenção
php artisan down

# 3. Atualizar código
git pull origin main
# ou fazer upload dos novos arquivos

# 4. Instalar dependências
composer install --optimize-autoloader --no-dev
npm install --production
npm run build

# 5. Executar migrations
php artisan migrate --force

# 6. Limpar e otimizar
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Reiniciar workers (se usar supervisor)
sudo supervisorctl restart condomanager-worker:*

# 8. Sair do modo manutenção
php artisan up
```

---

## 📊 Monitoramento

### Logs

Monitorar logs de erro:
```bash
tail -f storage/logs/laravel.log
```

### Performance

Usar ferramentas:
- Laravel Telescope (dev)
- New Relic (prod)
- Sentry (erro tracking)

### Health Check

Endpoint disponível em:
```
GET https://seudominio.com/api/health
```

---

## 🆘 Troubleshooting

### Erro 500

1. Verificar logs: `storage/logs/laravel.log`
2. Verificar permissões de `storage/` e `bootstrap/cache/`
3. Verificar se `.env` está configurado corretamente

### Erro de Conexão com Banco

1. Verificar credenciais no `.env`
2. Verificar se IP do servidor está liberado no MySQL
3. Testar conexão: `php artisan tinker` → `DB::connection()->getPdo();`

### Assets não carregam

1. Verificar se `npm run build` foi executado
2. Limpar cache: `php artisan view:clear`
3. Verificar caminho do `APP_URL` no `.env`

### Filas não processam

1. Verificar se cron está rodando
2. Verificar logs em `storage/logs/worker.log`
3. Reiniciar supervisor (se VPS)

---

## 📞 Suporte Pós-Deploy

- **Email:** suporte@condomanager.com
- **Logs:** `storage/logs/laravel.log`
- **Health Check:** `/api/health`

---

**Última atualização:** {{ date('d/m/Y') }}

