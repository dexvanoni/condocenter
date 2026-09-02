# 🔧 CONFIGURAÇÃO DO CURSOR PARA USAR PHP DO LARAGON

## ✅ CONFIGURAÇÕES APLICADAS

### 📁 **Configurações Globais do Cursor**
**Arquivo:** `C:\Users\dexva\AppData\Roaming\Cursor\User\settings.json`

```json
{
    "window.commandCenter": true,
    "php.executablePath": "C:\\laragon\\bin\\php\\php-8.3.16-Win32-vs16-x64\\php.exe",
    "php.validate.executablePath": "C:\\laragon\\bin\\php\\php-8.3.16-Win32-vs16-x64\\php.exe",
    "php.suggest.basic": false,
    "php.validate.enable": true,
    "php.validate.run": "onSave",
    "intelephense.environment.phpVersion": "8.3.16",
    "intelephense.environment.includePaths": [
        "C:\\laragon\\bin\\php\\php-8.3.16-Win32-vs16-x64"
    ],
    "intelephense.executable.php": "C:\\laragon\\bin\\php\\php-8.3.16-Win32-vs16-x64\\php.exe",
    "git.enableSmartCommit": true,
    "git.confirmSync": false,
    "database-client.autoSync": true
}
```

### 📁 **Configurações do Projeto**
**Arquivo:** `.vscode/settings.json`

```json
{
    "php.executablePath": "C:\\laragon\\bin\\php\\php-8.3.16-Win32-vs16-x64\\php.exe",
    "php.validate.executablePath": "C:\\laragon\\bin\\php\\php-8.3.16-Win32-vs16-x64\\php.exe",
    "php.suggest.basic": false,
    "php.validate.enable": true,
    "php.validate.run": "onSave",
    "intelephense.environment.phpVersion": "8.3.16",
    "intelephense.environment.includePaths": [
        "C:\\laragon\\bin\\php\\php-8.3.16-Win32-vs16-x64"
    ],
    "intelephense.executable.php": "C:\\laragon\\bin\\php\\php-8.3.16-Win32-vs16-x64\\php.exe"
}
```

---

## 🚀 COMO VERIFICAR SE ESTÁ FUNCIONANDO

### **1. Verificar no Terminal Integrado**
```bash
# No terminal do Cursor, execute:
php -v

# Deve mostrar:
# PHP 8.3.16 (cli) (built: Oct 15 2024 10:48:28) ( ZTS Visual C++ 2019 x64 )
```

### **2. Verificar no Intelephense**
- Abra um arquivo PHP
- O Intelephense deve mostrar "PHP 8.3.16" na barra de status
- Autocompletar deve funcionar corretamente

### **3. Verificar Validação PHP**
- Salve um arquivo PHP com erro de sintaxe
- Deve aparecer erro de validação usando o PHP 8.3.16

---

## 🔧 CONFIGURAÇÕES ADICIONAIS RECOMENDADAS

### **Para Terminal Integrado**
Adicione ao `settings.json`:

```json
{
    "terminal.integrated.env.windows": {
        "PATH": "C:\\laragon\\bin\\php\\php-8.3.16-Win32-vs16-x64;${env:PATH}"
    },
    "terminal.integrated.defaultProfile.windows": "PowerShell",
    "terminal.integrated.profiles.windows": {
        "PowerShell": {
            "source": "PowerShell",
            "icon": "terminal-powershell",
            "path": "C:\\Windows\\System32\\WindowsPowerShell\\v1.0\\powershell.exe",
            "env": {
                "PATH": "C:\\laragon\\bin\\php\\php-8.3.16-Win32-vs16-x64;${env:PATH}"
            }
        }
    }
}
```

### **Para Composer**
```json
{
    "composer.executablePath": "C:\\laragon\\bin\\composer\\composer.phar"
}
```

### **Para Laravel Artisan**
```json
{
    "laravel-artisan.phpPath": "C:\\laragon\\bin\\php\\php-8.3.16-Win32-vs16-x64\\php.exe"
}
```

---

## 📝 COMANDOS ÚTEIS PARA TESTAR

### **No Terminal do Cursor:**
```bash
# Verificar versão do PHP
php -v

# Verificar extensões carregadas
php -m

# Verificar configuração do PHP
php --ini

# Testar Laravel Artisan
php artisan --version

# Testar Composer
composer --version
```

### **No Cursor (Ctrl+Shift+P):**
```
> PHP: Restart Language Server
> Developer: Reload Window
```

---

## 🛠️ TROUBLESHOOTING

### **Problema: Cursor ainda usa PHP do sistema**
1. **Reiniciar o Cursor completamente**
2. **Executar:** `Ctrl+Shift+P` → `PHP: Restart Language Server`
3. **Verificar se o caminho está correto no settings.json**

### **Problema: Intelephense não reconhece**
1. **Instalar extensão Intelephense** se não estiver instalada
2. **Executar:** `Ctrl+Shift+P` → `Intelephense: Restart`
3. **Verificar se `intelephense.executable.php` está configurado**

### **Problema: Terminal não usa PHP do Laragon**
1. **Adicionar PATH ao terminal integrado** (configuração acima)
2. **Reiniciar o terminal integrado**
3. **Verificar variável PATH:** `echo $env:PATH`

---

## ✅ BENEFÍCIOS DESTA CONFIGURAÇÃO

1. **Consistência:** Sempre usa a mesma versão do PHP
2. **Compatibilidade:** PHP 8.3.16 com Laravel 11
3. **Performance:** Intelephense otimizado para PHP 8.3
4. **Debugging:** Xdebug funciona corretamente
5. **Laravel:** Artisan e Composer funcionam perfeitamente

---

## 📋 CHECKLIST DE VERIFICAÇÃO

- [ ] ✅ Configurações globais aplicadas
- [ ] ✅ Configurações do projeto aplicadas
- [ ] ✅ PHP 8.3.16 no terminal integrado
- [ ] ✅ Intelephense funcionando
- [ ] ✅ Validação PHP funcionando
- [ ] ✅ Laravel Artisan funcionando
- [ ] ✅ Composer funcionando
- [ ] ✅ Teste FCM executado com sucesso

---

**🎯 Agora o Cursor sempre usará o PHP do Laragon em todos os projetos!**
