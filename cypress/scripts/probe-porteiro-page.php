<?php

declare(strict_types=1);

$base = getenv('PROBE_BASE_URL') ?: 'http://127.0.0.1:8000';
$email = getenv('PROBE_EMAIL') ?: 'cypress-porteiro@test.local';
$password = getenv('PROBE_PASSWORD') ?: 'password';
$cookieFile = sys_get_temp_dir() . '/cypress-probe-porteiro.txt';

@unlink($cookieFile);

function probeRequest(string $url, string $cookieFile, array $opts = []): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HTTPHEADER => $opts['headers'] ?? [],
    ]);

    if (isset($opts['post'])) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $opts['post']);
    }

    $raw = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['status' => $status, 'body' => substr($raw, strpos($raw, "\r\n\r\n") + 4)];
}

probeRequest($base . '/sanctum/csrf-cookie', $cookieFile);
$loginPage = probeRequest($base . '/login', $cookieFile);
preg_match('/name="_token"\s+value="([^"]+)"/', $loginPage['body'], $m);
$token = $m[1] ?? '';
preg_match('/XSRF-TOKEN=([^;]+)/', file_get_contents($cookieFile), $xm);
$xsrf = urldecode($xm[1]);

probeRequest($base . '/login', $cookieFile, [
    'post' => http_build_query(['email' => $email, 'password' => $password, '_token' => $token]),
    'headers' => [
        'Content-Type: application/x-www-form-urlencoded',
        'X-XSRF-TOKEN: ' . $xsrf,
        'Referer: ' . $base,
    ],
]);

$page = probeRequest($base . '/access-control/porteiro', $cookieFile, [
    'headers' => ['Referer: ' . $base],
]);

$body = $page['body'];
$hasCheckinApp = str_contains($body, 'id="access-checkin-app"');
$hasViteScript = (bool) preg_match('/access-porteiro-checkin[^"\']*\.js/', $body);
$hasPinBtn = str_contains($body, 'access-checkin__btn--pin');
$hasCardsGrid = str_contains($body, 'id="cardsGrid"');

echo json_encode([
    'status' => $page['status'],
    'has_checkin_app' => $hasCheckinApp,
    'has_vite_script' => $hasViteScript,
    'has_pin_btn_in_html' => $hasPinBtn,
    'has_cards_grid' => $hasCardsGrid,
    'script_match' => preg_match('/access-porteiro-checkin[^"\']*\.js/', $body, $sm) ? $sm[0] : null,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
