<?php

declare(strict_types=1);

if (!function_exists('e')) {
    /** Escapes a string for safe HTML output. */
    function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('format_date')) {
    /** Formats an ISO date (Y-m-d) as dd.mm.yyyy for display; empty string for null/empty input. */
    function format_date(?string $isoDate): string
    {
        if ($isoDate === null || $isoDate === '') {
            return '';
        }

        try {
            return (new DateTimeImmutable($isoDate))->format('d.m.Y');
        } catch (Exception) {
            return $isoDate;
        }
    }
}

if (!function_exists('share_links')) {
    /**
     * Plain-link share targets (no JavaScript, no third-party scripts or trackers).
     *
     * @return array<string, string> label => URL
     */
    function share_links(string $url, string $text): array
    {
        return [
            'WhatsApp' => 'https://wa.me/?text=' . rawurlencode($text . ' ' . $url),
            'Facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($url),
            'E-mail' => 'mailto:?subject=' . rawurlencode($text) . '&body=' . rawurlencode($text . "\n\n" . $url),
        ];
    }
}

if (!function_exists('env')) {
    /**
     * @template T of string|null
     * @param T $default
     * @return ($default is string ? string : string|null)
     */
    function env(string $key, ?string $default = null): ?string
    {
        // $_SERVER also holds non-string entries (e.g. argv), so accept strings only.
        $value = $_SERVER[$key] ?? getenv($key);

        return is_string($value) ? $value : $default;
    }
}
