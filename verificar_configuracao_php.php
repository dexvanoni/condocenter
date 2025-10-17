<?php
/**
 * 🔧 VERIFICADOR DE CONFIGURAÇÃO PHP PARA CURSOR
 * ================================================
 * 
 * Este script verifica se o Cursor está usando o PHP correto do Laragon
 * e se todas as configurações estão funcionando.
 */

echo "🔧 VERIFICADOR DE CONFIGURAÇÃO PHP PARA CURSOR\n";
echo "================================================\n\n";

// 1. Verificar versão do PHP
echo "📋 INFORMAÇÕES DO PHP:\n";
echo "Versão: " . PHP_VERSION . "\n";
echo "Arquitetura: " . (PHP_INT_SIZE * 8) . " bits\n";
echo "SAPI: " . php_sapi_name() . "\n";
echo "Caminho do executável: " . PHP_BINARY . "\n\n";

// 2. Verificar se é o PHP do Laragon
$expectedPath = "C:\\laragon\\bin\\php\\php-8.3.16-Win32-vs16-x64\\php.exe";
$actualPath = PHP_BINARY;

echo "🎯 VERIFICAÇÃO DE CAMINHO:\n";
echo "Caminho esperado: $expectedPath\n";
echo "Caminho atual: $actualPath\n";

if (strpos($actualPath, 'laragon') !== false && strpos($actualPath, 'php-8.3.16') !== false) {
    echo "✅ SUCESSO: Usando PHP do Laragon 8.3.16!\n\n";
} else {
    echo "❌ ATENÇÃO: Não está usando PHP do Laragon!\n\n";
}

// 3. Verificar extensões importantes
echo "🔌 EXTENSÕES CARREGADAS:\n";
$importantExtensions = [
    'mbstring' => 'Necessária para Laravel',
    'openssl' => 'Necessária para HTTPS',
    'curl' => 'Necessária para APIs',
    'json' => 'Necessária para JSON',
    'pdo' => 'Necessária para banco de dados',
    'pdo_sqlite' => 'Necessária para SQLite',
    'fileinfo' => 'Necessária para uploads',
    'zip' => 'Necessária para Composer',
    'xml' => 'Necessária para XML',
    'dom' => 'Necessária para DOM'
];

foreach ($importantExtensions as $ext => $description) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? '✅' : '❌';
    echo "$status $ext - $description\n";
}

echo "\n";

// 4. Verificar configurações importantes
echo "⚙️ CONFIGURAÇÕES IMPORTANTES:\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "date.timezone: " . ini_get('date.timezone') . "\n\n";

// 5. Verificar se Laravel pode funcionar
echo "🚀 VERIFICAÇÃO PARA LARAVEL:\n";

// Verificar se está em um projeto Laravel
$isLaravel = file_exists('artisan');
echo "Projeto Laravel: " . ($isLaravel ? '✅ Sim' : '❌ Não') . "\n";

if ($isLaravel) {
    // Verificar se pode executar artisan
    $artisanOutput = shell_exec('php artisan --version 2>&1');
    if ($artisanOutput && strpos($artisanOutput, 'Laravel Framework') !== false) {
        echo "Artisan funcionando: ✅ Sim\n";
        echo "Versão Laravel: " . trim($artisanOutput) . "\n";
    } else {
        echo "Artisan funcionando: ❌ Não\n";
        echo "Erro: " . ($artisanOutput ?: 'Comando não executou') . "\n";
    }
}

echo "\n";

// 6. Verificar FCM específico
echo "🔔 VERIFICAÇÃO FCM:\n";
$fcmFiles = [
    'config/firebase.php' => 'Configuração Firebase',
    'public/firebase-messaging-sw.js' => 'Service Worker',
    'app/Services/FirebaseNotificationService.php' => 'Service FCM',
    '.env' => 'Arquivo de ambiente'
];

foreach ($fcmFiles as $file => $description) {
    $exists = file_exists($file);
    $status = $exists ? '✅' : '❌';
    echo "$status $description: $file\n";
}

// Verificar se FCM está habilitado no .env
if (file_exists('.env')) {
    $envContent = file_get_contents('.env');
    $fcmEnabled = strpos($envContent, 'FCM_ENABLED=true') !== false;
    echo ($fcmEnabled ? '✅' : '❌') . " FCM habilitado no .env\n";
}

echo "\n";

// 7. Resumo final
echo "📊 RESUMO FINAL:\n";
echo "================\n";

$allGood = true;

// Verificar PHP do Laragon
if (strpos($actualPath, 'laragon') === false || strpos($actualPath, 'php-8.3.16') === false) {
    $allGood = false;
    echo "❌ Não está usando PHP do Laragon 8.3.16\n";
} else {
    echo "✅ Usando PHP do Laragon 8.3.16\n";
}

// Verificar extensões críticas
$criticalExtensions = ['mbstring', 'openssl', 'json', 'pdo'];
foreach ($criticalExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $allGood = false;
        echo "❌ Extensão $ext não carregada\n";
    }
}

// Verificar Laravel
if ($isLaravel) {
    $artisanOutput = shell_exec('php artisan --version 2>&1');
    if (!$artisanOutput || strpos($artisanOutput, 'Laravel Framework') === false) {
        $allGood = false;
        echo "❌ Laravel Artisan não funciona\n";
    } else {
        echo "✅ Laravel Artisan funcionando\n";
    }
}

echo "\n";

if ($allGood) {
    echo "🎉 PARABÉNS! Configuração está perfeita!\n";
    echo "O Cursor está usando o PHP correto do Laragon.\n";
} else {
    echo "⚠️ ATENÇÃO! Alguns problemas foram encontrados.\n";
    echo "Verifique as configurações do Cursor.\n";
}

echo "\n";
echo "📝 Para aplicar as configurações no Cursor:\n";
echo "1. Abra Cursor\n";
echo "2. Pressione Ctrl+Shift+P\n";
echo "3. Digite: PHP: Restart Language Server\n";
echo "4. Pressione Enter\n";
echo "5. Reinicie o Cursor se necessário\n\n";

echo "✅ Verificação concluída!\n";
?>
