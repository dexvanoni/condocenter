<?php

function landing_app_url(): string
{
    static $url = null;

    if ($url !== null) {
        return $url;
    }

    $root = dirname(__DIR__);

    foreach ([
        landing_url_from_config_cache($root.'/bootstrap/cache/config.php'),
        landing_url_from_env($root.'/.env'),
        landing_url_from_getenv(),
    ] as $candidate) {
        if (is_string($candidate) && $candidate !== '') {
            return $url = rtrim($candidate, '/');
        }
    }

    return $url = '';
}

function landing_asset(string $path): string
{
    return landing_app_url().'/'.ltrim($path, '/');
}

function landing_url_from_config_cache(string $file): ?string
{
    try {
        if (!is_file($file)) {
            return null;
        }

        $handle = fopen($file, 'rb');

        if ($handle === false) {
            return null;
        }

        $buffer = '';
        $appAt = null;

        while (!feof($handle)) {
            $chunk = fread($handle, 8192);

            if (!is_string($chunk) || $chunk === '') {
                break;
            }

            $buffer .= $chunk;

            if ($appAt === null) {
                $pos = strpos($buffer, "'app' =>");

                if ($pos === false) {
                    $buffer = substr($buffer, -16);
                    continue;
                }

                $appAt = $pos;
                $buffer = substr($buffer, $pos);
            }

            if (preg_match("/'url'\\s*=>\\s*'((?:\\\\'|[^'])*)'/", $buffer, $matches) === 1) {
                fclose($handle);
                $value = stripcslashes($matches[1]);

                return $value !== '' ? $value : null;
            }

            if (strlen($buffer) > 65536) {
                break;
            }
        }

        fclose($handle);
    } catch (Throwable) {
        return null;
    }

    return null;
}

function landing_url_from_env(string $file): ?string
{
    try {
        if (!is_file($file)) {
            return null;
        }

        $handle = fopen($file, 'rb');

        if ($handle === false) {
            return null;
        }

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || !str_starts_with($line, 'APP_URL=')) {
                continue;
            }

            fclose($handle);
            $value = trim(substr($line, strlen('APP_URL=')), " \t\"'");

            return $value !== '' ? $value : null;
        }

        fclose($handle);
    } catch (Throwable) {
        return null;
    }

    return null;
}

function landing_url_from_getenv(): ?string
{
    if (!function_exists('getenv')) {
        return null;
    }

    $value = getenv('APP_URL');

    if (!is_string($value) || $value === '') {
        return null;
    }

    return $value;
}
