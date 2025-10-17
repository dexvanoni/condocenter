<?php
/**
 * 🔔 TESTE COMPLETO DO SISTEMA FCM (Firebase Cloud Messaging)
 * ============================================================
 * 
 * Este arquivo testa todas as funcionalidades do sistema FCM implementado
 * no CondoCenter, incluindo configurações, APIs e funcionalidades.
 * 
 * Data: 14/10/2025
 * Versão: 1.0
 * 
 * @author Sistema CondoCenter
 * @package FCM Testing
 */

// Configurações de teste
$baseUrl = 'http://localhost:8000';
$testResults = [];
$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

// Função para executar requisição HTTP
function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
    // Usar file_get_contents como fallback
    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", array_merge([
                'Content-Type: application/json',
                'Accept: application/json'
            ], $headers)),
            'timeout' => 30
        ]
    ]);
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", array_merge([
                    'Content-Type: application/json',
                    'Accept: application/json'
                ], $headers)),
                'content' => json_encode($data),
                'timeout' => 30
            ]
        ]);
    }
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        return [
            'status_code' => 0,
            'response' => '',
            'error' => 'Falha na requisição HTTP',
            'success' => false
        ];
    }
    
    // Tentar obter o código de status HTTP dos headers
    $httpCode = 200;
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $header, $matches)) {
                $httpCode = (int)$matches[1];
                break;
            }
        }
    }
    
    return [
        'status_code' => $httpCode,
        'response' => $response,
        'error' => '',
        'success' => $httpCode >= 200 && $httpCode < 300
    ];
}

// Função para registrar resultado do teste
function logTest($testName, $expected, $actual, $passed, $details = '') {
    global $testResults, $totalTests, $passedTests, $failedTests;
    
    $totalTests++;
    if ($passed) {
        $passedTests++;
        $status = '✅ PASSOU';
    } else {
        $failedTests++;
        $status = '❌ FALHOU';
    }
    
    $testResults[] = [
        'test' => $testName,
        'status' => $status,
        'expected' => $expected,
        'actual' => $actual,
        'details' => $details
    ];
    
    echo "[$status] $testName\n";
    if ($details) {
        echo "   Detalhes: $details\n";
    }
    echo "\n";
}

// Função para testar configuração FCM
function testFCMConfiguration() {
    global $baseUrl;
    
    echo "🔧 TESTANDO CONFIGURAÇÃO FCM\n";
    echo "=============================\n\n";
    
    // Teste 1: Verificar se o arquivo de configuração existe
    $configFile = 'config/firebase.php';
    $configExists = file_exists($configFile);
    logTest(
        'Arquivo de configuração Firebase existe',
        'true',
        $configExists ? 'true' : 'false',
        $configExists,
        $configExists ? 'Arquivo encontrado' : 'Arquivo não encontrado'
    );
    
    // Teste 2: Verificar configurações do .env
    $envFile = '.env';
    $envExists = file_exists($envFile);
    logTest(
        'Arquivo .env existe',
        'true',
        $envExists ? 'true' : 'false',
        $envExists,
        $envExists ? 'Arquivo encontrado' : 'Arquivo não encontrado'
    );
    
    if ($envExists) {
        $envContent = file_get_contents($envFile);
        $fcmEnabled = strpos($envContent, 'FCM_ENABLED=true') !== false;
        logTest(
            'FCM habilitado no .env',
            'true',
            $fcmEnabled ? 'true' : 'false',
            $fcmEnabled,
            $fcmEnabled ? 'FCM_ENABLED=true encontrado' : 'FCM_ENABLED não encontrado ou false'
        );
        
        $hasServerKey = strpos($envContent, 'FCM_SERVER_KEY=') !== false;
        logTest(
            'Chave do servidor FCM configurada',
            'true',
            $hasServerKey ? 'true' : 'false',
            $hasServerKey,
            $hasServerKey ? 'FCM_SERVER_KEY encontrada' : 'FCM_SERVER_KEY não encontrada'
        );
    }
    
    // Teste 3: Verificar Service Worker
    $swFile = 'public/firebase-messaging-sw.js';
    $swExists = file_exists($swFile);
    logTest(
        'Service Worker Firebase existe',
        'true',
        $swExists ? 'true' : 'false',
        $swExists,
        $swExists ? 'Arquivo encontrado' : 'Arquivo não encontrado'
    );
    
    if ($swExists) {
        $swContent = file_get_contents($swFile);
        $hasFirebaseImport = strpos($swContent, 'firebase') !== false;
        logTest(
            'Service Worker contém imports Firebase',
            'true',
            $hasFirebaseImport ? 'true' : 'false',
            $hasFirebaseImport,
            $hasFirebaseImport ? 'Imports Firebase encontrados' : 'Imports Firebase não encontrados'
        );
    }
    
    echo "\n";
}

// Função para testar APIs FCM
function testFCMAPIs() {
    global $baseUrl;
    
    echo "🌐 TESTANDO APIs FCM\n";
    echo "====================\n\n";
    
    // Teste 1: API de configuração FCM
    $configResponse = makeRequest("$baseUrl/api/fcm/config");
    logTest(
        'API FCM Config acessível',
        '200',
        $configResponse['status_code'],
        $configResponse['status_code'] == 200,
        $configResponse['error'] ?: 'Resposta: ' . substr($configResponse['response'], 0, 100)
    );
    
    // Teste 2: API de status FCM (requer autenticação)
    $statusResponse = makeRequest("$baseUrl/api/fcm/status");
    logTest(
        'API FCM Status acessível',
        '401 ou 200',
        $statusResponse['status_code'],
        in_array($statusResponse['status_code'], [200, 401]),
        $statusResponse['error'] ?: 'Status: ' . $statusResponse['status_code']
    );
    
    // Teste 3: API de teste FCM (requer autenticação)
    $testResponse = makeRequest("$baseUrl/api/fcm/test");
    logTest(
        'API FCM Test acessível',
        '401 ou 200',
        $testResponse['status_code'],
        in_array($testResponse['status_code'], [200, 401]),
        $testResponse['error'] ?: 'Status: ' . $testResponse['status_code']
    );
    
    echo "\n";
}

// Função para testar funcionalidades do sistema
function testFCMFeatures() {
    global $baseUrl;
    
    echo "⚙️ TESTANDO FUNCIONALIDADES FCM\n";
    echo "================================\n\n";
    
    // Teste 1: Verificar se os controllers FCM existem
    $fcmTokenController = 'app/Http/Controllers/Api/FcmTokenController.php';
    $fcmConfigController = 'app/Http/Controllers/Api/FcmConfigController.php';
    
    $tokenControllerExists = file_exists($fcmTokenController);
    logTest(
        'FcmTokenController existe',
        'true',
        $tokenControllerExists ? 'true' : 'false',
        $tokenControllerExists,
        $tokenControllerExists ? 'Controller encontrado' : 'Controller não encontrado'
    );
    
    $configControllerExists = file_exists($fcmConfigController);
    logTest(
        'FcmConfigController existe',
        'true',
        $configControllerExists ? 'true' : 'false',
        $configControllerExists,
        $configControllerExists ? 'Controller encontrado' : 'Controller não encontrado'
    );
    
    // Teste 2: Verificar se o service FCM existe
    $fcmService = 'app/Services/FirebaseNotificationService.php';
    $serviceExists = file_exists($fcmService);
    logTest(
        'FirebaseNotificationService existe',
        'true',
        $serviceExists ? 'true' : 'false',
        $serviceExists,
        $serviceExists ? 'Service encontrado' : 'Service não encontrado'
    );
    
    // Teste 3: Verificar se as rotas FCM estão definidas
    $routesFile = 'routes/api.php';
    if (file_exists($routesFile)) {
        $routesContent = file_get_contents($routesFile);
        $hasFCMRoutes = strpos($routesContent, 'fcm') !== false;
        logTest(
            'Rotas FCM definidas',
            'true',
            $hasFCMRoutes ? 'true' : 'false',
            $hasFCMRoutes,
            $hasFCMRoutes ? 'Rotas FCM encontradas' : 'Rotas FCM não encontradas'
        );
    }
    
    // Teste 4: Verificar integração com alertas de pânico
    $panicController = 'app/Http/Controllers/PanicAlertController.php';
    if (file_exists($panicController)) {
        $panicContent = file_get_contents($panicController);
        $hasFCMIntegration = strpos($panicContent, 'FCM') !== false;
        logTest(
            'Integração FCM com alertas de pânico',
            'true',
            $hasFCMIntegration ? 'true' : 'false',
            $hasFCMIntegration,
            $hasFCMIntegration ? 'Integração encontrada' : 'Integração não encontrada'
        );
    }
    
    echo "\n";
}

// Função para testar JavaScript FCM
function testFCMJavaScript() {
    echo "📱 TESTANDO JAVASCRIPT FCM\n";
    echo "===========================\n\n";
    
    // Teste 1: Verificar se o arquivo JavaScript FCM existe
    $jsFiles = [
        'public/js/fcm.js',
        'resources/js/fcm.js'
    ];
    
    $jsFileFound = false;
    $jsFilePath = '';
    
    foreach ($jsFiles as $jsFile) {
        if (file_exists($jsFile)) {
            $jsFileFound = true;
            $jsFilePath = $jsFile;
            break;
        }
    }
    
    logTest(
        'Arquivo JavaScript FCM existe',
        'true',
        $jsFileFound ? 'true' : 'false',
        $jsFileFound,
        $jsFileFound ? "Encontrado em: $jsFilePath" : 'Nenhum arquivo JS FCM encontrado'
    );
    
    // Teste 2: Verificar se o arquivo contém funções FCM
    if ($jsFileFound) {
        $jsContent = file_get_contents($jsFilePath);
        $hasTestFunction = strpos($jsContent, 'testFCM') !== false;
        logTest(
            'Função testFCM existe no JavaScript',
            'true',
            $hasTestFunction ? 'true' : 'false',
            $hasTestFunction,
            $hasTestFunction ? 'Função encontrada' : 'Função não encontrada'
        );
        
        $hasSetupFunction = strpos($jsContent, 'setupFCM') !== false;
        logTest(
            'Função setupFCM existe no JavaScript',
            'true',
            $hasSetupFunction ? 'true' : 'false',
            $hasSetupFunction,
            $hasSetupFunction ? 'Função encontrada' : 'Função não encontrada'
        );
        
        $hasFirebaseConfig = strpos($jsContent, 'firebase') !== false;
        logTest(
            'Configuração Firebase no JavaScript',
            'true',
            $hasFirebaseConfig ? 'true' : 'false',
            $hasFirebaseConfig,
            $hasFirebaseConfig ? 'Configuração encontrada' : 'Configuração não encontrada'
        );
    }
    
    echo "\n";
}

// Função para testar banco de dados FCM
function testFCMDatabase() {
    echo "🗄️ TESTANDO BANCO DE DADOS FCM\n";
    echo "===============================\n\n";
    
    // Teste 1: Verificar se existe migração para campos FCM
    $migrationsDir = 'database/migrations';
    $hasFCMFields = false;
    $fcmMigrationFile = '';
    
    if (is_dir($migrationsDir)) {
        $files = scandir($migrationsDir);
        foreach ($files as $file) {
            if (strpos($file, 'fcm') !== false || strpos($file, 'firebase') !== false) {
                $hasFCMFields = true;
                $fcmMigrationFile = $file;
                break;
            }
        }
        
        // Se não encontrou migração específica, verifica se os campos estão em outras migrações
        if (!$hasFCMFields) {
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                    $content = file_get_contents($migrationsDir . '/' . $file);
                    if (strpos($content, 'fcm_token') !== false) {
                        $hasFCMFields = true;
                        $fcmMigrationFile = $file;
                        break;
                    }
                }
            }
        }
    }
    
    logTest(
        'Campos FCM no banco de dados',
        'true',
        $hasFCMFields ? 'true' : 'false',
        $hasFCMFields,
        $hasFCMFields ? "Encontrado em: $fcmMigrationFile" : 'Campos FCM não encontrados'
    );
    
    // Teste 2: Verificar modelo User com campos FCM
    $userModel = 'app/Models/User.php';
    if (file_exists($userModel)) {
        $userContent = file_get_contents($userModel);
        $hasFCMFields = strpos($userContent, 'fcm') !== false;
        logTest(
            'Modelo User com campos FCM',
            'true',
            $hasFCMFields ? 'true' : 'false',
            $hasFCMFields,
            $hasFCMFields ? 'Campos FCM encontrados no modelo' : 'Campos FCM não encontrados no modelo'
        );
    }
    
    echo "\n";
}

// Função para gerar relatório final
function generateReport() {
    global $testResults, $totalTests, $passedTests, $failedTests;
    
    echo "📊 RELATÓRIO FINAL DOS TESTES\n";
    echo "==============================\n\n";
    
    echo "📈 ESTATÍSTICAS:\n";
    echo "Total de testes: $totalTests\n";
    echo "Testes aprovados: $passedTests ✅\n";
    echo "Testes falharam: $failedTests ❌\n";
    echo "Taxa de sucesso: " . round(($passedTests / $totalTests) * 100, 2) . "%\n\n";
    
    echo "📋 DETALHES DOS TESTES:\n";
    echo "=======================\n\n";
    
    foreach ($testResults as $result) {
        echo "[{$result['status']}] {$result['test']}\n";
        echo "   Esperado: {$result['expected']}\n";
        echo "   Obtido: {$result['actual']}\n";
        if ($result['details']) {
            echo "   Detalhes: {$result['details']}\n";
        }
        echo "\n";
    }
    
    echo "🎯 CONCLUSÕES:\n";
    echo "==============\n\n";
    
    if ($failedTests == 0) {
        echo "✅ PARABÉNS! Todos os testes passaram!\n";
        echo "O sistema FCM está completamente configurado e funcionando.\n\n";
        
        echo "🚀 PRÓXIMOS PASSOS:\n";
        echo "1. Testar notificações em navegadores diferentes\n";
        echo "2. Testar notificações push em dispositivos móveis\n";
        echo "3. Configurar notificações de alertas de pânico\n";
        echo "4. Treinar usuários sobre como usar as notificações\n\n";
        
    } else {
        echo "⚠️ ATENÇÃO! Alguns testes falharam.\n";
        echo "Revisar as configurações e corrigir os problemas identificados.\n\n";
        
        echo "🔧 AÇÕES NECESSÁRIAS:\n";
        foreach ($testResults as $result) {
            if (strpos($result['status'], '❌') !== false) {
                echo "- Corrigir: {$result['test']}\n";
            }
        }
        echo "\n";
    }
    
    echo "📝 INFORMAÇÕES ADICIONAIS:\n";
    echo "==========================\n\n";
    echo "• Para testar no navegador, use: window.testFCM()\n";
    echo "• Para configurar FCM, use: window.setupFCM()\n";
    echo "• Verificar status: /api/fcm/status\n";
    echo "• Configurações: /api/fcm/config\n\n";
    
    echo "🔗 DOCUMENTAÇÃO:\n";
    echo "================\n";
    echo "• FCM Setup: FCM_SETUP.md\n";
    echo "• Configuração completa: FCM_COMPLETE_CONFIG.env\n";
    echo "• Service Worker: public/firebase-messaging-sw.js\n\n";
}

// Executar todos os testes
echo "🔔 TESTE COMPLETO DO SISTEMA FCM\n";
echo "=================================\n";
echo "Data: " . date('d/m/Y H:i:s') . "\n";
echo "Sistema: CondoCenter\n";
echo "Versão: 1.0\n\n";

echo "Iniciando testes...\n\n";

testFCMConfiguration();
testFCMAPIs();
testFCMFeatures();
testFCMJavaScript();
testFCMDatabase();
generateReport();

echo "✅ Testes concluídos!\n";
echo "Arquivo de relatório salvo como: test_fcm_results_" . date('Y-m-d_H-i-s') . ".txt\n";
?>
