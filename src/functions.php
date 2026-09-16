<?php

declare(strict_types=1);

if (!function_exists('e')) {
    /** Escapes a string for safe HTML output. */
    function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('env')) {
    function env(string $key, ?string $default = null): ?string
    {
        $value = $_SERVER[$key] ?? getenv($key);

        return $value === false || $value === null ? $default : $value;
    }
}
