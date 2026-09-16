<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

if (extension_loaded('swoole')) {
    Swoole\Runtime::setHookFlags(SWOOLE_HOOK_ALL);
}

if (is_file(dirname(__DIR__) . '/.env') && !getenv('APP_ENV')) {
    foreach (file(dirname(__DIR__) . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if ($key !== '' && getenv($key) === false) {
            putenv("$key=$value");
            $_SERVER[$key] = $value;
            $_ENV[$key] = $value;
        }
    }
}
