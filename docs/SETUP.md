# Guia de Configuração - CondoManager

## Variáveis de Ambiente (.env)

Crie um arquivo `.env` na raiz do projeto com as seguintes configurações:

```env
# Aplicação
APP_NAME="CondoManager"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=America/Sao_Paulo
APP_URL=http://localhost

# Locale
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR
APP_FAKER_LOCALE=pt_BR

# Banco de Dados
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=condocenter
DB_USERNAME=root
DB_PASSWORD=

# Cache e Sessão
SESSION_DRIVER=database
SESSION_LIFETIME=120

CACHE_STORE=database
QUEUE_CONNECTION=database

# Redis (Opcional - para produção)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu_email@gmail.com
MAIL_PASSWORD=sua_senha_app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@condomanager.com"
MAIL_FROM_NAME="${APP_NAME}"

# Asaas Payment Gateway
ASAAS_API_KEY=seu_token_aqui
ASAAS_SANDBOX=true
ASAAS_WEBHOOK_EMAIL=admin@condomanager.com

# Storage (local para dev, s3 para prod)
FILESYSTEM_DISK=local

# AWS S3 (Produção)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
```

## Comandos de Instalação

### Windows (Laragon)

```bash
# 1. Navegar até o diretório
cd C:\laragon\www\condocenter

# 2. Instalar dependências PHP
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe C:\laragon\bin\composer\composer.phar install

# 3. Instalar dependências Node
npm install

# 4. Copiar arquivo de ambiente
copy .env.example .env
# Edite o .env com suas configurações

# 5. Gerar chave da aplicação
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan key:generate

# 6. Criar banco de dados MySQL
# No MySQL: CREATE DATABASE condocenter CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 7. Executar migrations
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan migrate

# 8. Popular banco com dados demo
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan db:seed

# 9. Criar link simbólico para storage
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan storage:link

# 10. Compilar assets
npm run build

# 11. Iniciar servidor
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan serve
```

### Linux

```bash
# 1. Instalar dependências
composer install
npm install

# 2. Configurar ambiente
cp .env.example .env
php artisan key:generate

# 3. Criar banco de dados
mysql -u root -p -e "CREATE DATABASE condocenter CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 4. Executar migrations e seeders
php artisan migrate
php artisan db:seed

# 5. Configurar storage
php artisan storage:link

# 6. Compilar assets
npm run build

# 7. Configurar permissões
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 8. Iniciar servidor
php artisan serve
```

## Configuração do Asaas

### 1. Criar Conta

Acesse: https://www.asaas.com/

### 2. Obter API Key

1. Faça login no painel Asaas
2. Acesse: **Integrações** > **API**
3. Copie sua chave de API
4. Cole no `.env`: `ASAAS_API_KEY=sua_chave_aqui`

### 3. Configurar Webhook

No painel Asaas, configure o webhook:

**Sandbox:**
```
https://seudominio.com/webhooks/asaas
```

**Produção:**
```
https://seudominio.com/webhooks/asaas
```

**Eventos a escutar:**
- PAYMENT_CREATED
- PAYMENT_UPDATED
- PAYMENT_CONFIRMED
- PAYMENT_RECEIVED
- PAYMENT_OVERDUE
- PAYMENT_DELETED

### 4. Teste em Sandbox

Para testar pagamentos, use o ambiente sandbox:
```env
ASAAS_SANDBOX=true
```

Dados de teste para cartão de crédito:
- Número: 5162306219378829
- CVV: 318
- Validade: qualquer data futura

## Processamento de Filas

### Desenvolvimento

```bash
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan queue:work
```

### Produção (Linux)

Configure o Supervisor:

```ini
[program:condomanager-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/condocenter/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/path/to/condocenter/storage/logs/worker.log
```

## Configuração de Email

### Gmail

1. Ative a verificação em 2 etapas
2. Gere uma "Senha de app"
3. Use a senha de app no `.env`:

```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu_email@gmail.com
MAIL_PASSWORD=sua_senha_app
MAIL_ENCRYPTION=tls
```

### SendGrid / Mailgun

Configure de acordo com a documentação do provedor.

## Deploy na Hostinger

### 1. Upload de Arquivos

- Faça upload via FTP/SFTP para `public_html`
- **Importante:** O conteúdo da pasta `public` deve estar na raiz do `public_html`

### 2. Configurar .env

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seudominio.com

DB_HOST=localhost
DB_DATABASE=u123456_condocenter
DB_USERNAME=u123456_user
DB_PASSWORD=senha_segura

ASAAS_SANDBOX=false
ASAAS_API_KEY=sua_chave_producao
```

### 3. Executar via SSH

```bash
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

### 4. Configurar Permissões

```bash
chmod -R 755 storage bootstrap/cache
```

### 5. Configurar .htaccess

Certifique-se de que o `.htaccess` está no diretório público:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

## Troubleshooting

### Erro: "Class not found"

```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Erro: "Permission denied" no storage

```bash
chmod -R 775 storage bootstrap/cache
```

### Migrations não executam

```bash
php artisan migrate:fresh --seed
```

### Assets não carregam

```bash
npm run build
php artisan view:clear
```

### Erro no Asaas

1. Verifique se a chave API está correta
2. Confirme se está usando o ambiente correto (sandbox/produção)
3. Verifique os logs: `storage/logs/laravel.log`

## Backup

### Backup do Banco de Dados

```bash
mysqldump -u root -p condocenter > backup_$(date +%Y%m%d).sql
```

### Backup de Arquivos

```bash
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/app
```

## Monitoramento

### Logs

```bash
tail -f storage/logs/laravel.log
```

### Health Check

Crie um endpoint de health check:

```
GET /api/health
```

Retorna status da aplicação, banco de dados e filas.

## Suporte

Para problemas ou dúvidas:
- Email: suporte@condomanager.com
- GitHub Issues: [URL do repositório]
- Documentação: https://docs.condomanager.com

