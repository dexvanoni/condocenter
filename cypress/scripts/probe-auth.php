<?php

declare(strict_types=1);

$base = getenv('PROBE_BASE_URL') ?: 'http://127.0.0.1:8000';
$email = getenv('PROBE_EMAIL') ?: 'cypress-morador@test.local';
$password = getenv('PROBE_PASSWORD') ?: 'password';
$cookieFile = sys_get_temp_dir() . '/cypress-probe-cookies.txt';

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

    return ['status' => $status, 'raw' => $raw];
}

probeRequest($base . '/sanctum/csrf-cookie', $cookieFile);
$loginPage = probeRequest($base . '/login', $cookieFile);
preg_match('/name="_token"\s+value="([^"]+)"/', $loginPage['raw'], $m);
$token = $m[1] ?? '';

$xsrf = '';
if (preg_match('/XSRF-TOKEN=([^;]+)/', file_get_contents($cookieFile), $xm)) {
    $xsrf = urldecode($xm[1]);
}

$login = probeRequest($base . '/login', $cookieFile, [
    'post' => http_build_query([
        'email' => $email,
        'password' => $password,
        '_token' => $token,
    ]),
    'headers' => [
        'Content-Type: application/x-www-form-urlencoded',
        'X-XSRF-TOKEN: ' . $xsrf,
        'X-Requested-With: XMLHttpRequest',
        'Referer: ' . $base,
    ],
]);

$apiHeaders = [
    'Accept: application/json',
    'X-XSRF-TOKEN: ' . $xsrf,
    'X-Requested-With: XMLHttpRequest',
];

if (!getenv('PROBE_NO_REFERER')) {
    $apiHeaders[] = 'Referer: ' . $base;
}

$api = probeRequest($base . '/api/access-control/authorizations', $cookieFile, [
    'headers' => $apiHeaders,
]);

$user = probeRequest($base . '/api/user', $cookieFile, [
    'headers' => [
        'Accept: application/json',
        'X-XSRF-TOKEN: ' . $xsrf,
        'X-Requested-With: XMLHttpRequest',
        'Referer: ' . $base,
    ],
]);

echo json_encode([
    'login_status' => $login['status'],
    'api_status' => $api['status'],
    'api_body' => substr($api['raw'], strpos($api['raw'], "\r\n\r\n") + 4, 300),
    'user_status' => $user['status'],
    'user_body' => substr($user['raw'], strpos($user['raw'], "\r\n\r\n") + 4, 200),
    'has_token' => $token !== '',
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
