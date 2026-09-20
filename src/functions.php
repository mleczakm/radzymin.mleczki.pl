<?php

declare(strict_types=1);

if (!function_exists('e')) {
    /** Escapes a string for safe HTML output. */
    function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('plural_form')) {
    /**
     * Noun form for a count in Polish: plural_form(2, 'sprawa', 'sprawy', 'spraw') is "sprawy".
     * Forms may include the agreeing verb or adjective too ("pozostał", "pozostały", "pozostało").
     */
    function plural_form(int $count, string $one, string $few, string $many): string
    {
        return App\Text\PolishPlural::form($count, $one, $few, $many);
    }
}

if (!function_exists('plural')) {
    /** Like plural_form() but with the number itself in place of each "%d": plural(2, '%d sprawa', '%d sprawy', '%d spraw'). */
    function plural(int $count, string $one, string $few, string $many): string
    {
        return str_replace('%d', (string) $count, App\Text\PolishPlural::form($count, $one, $few, $many));
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

if (!function_exists('asset_url')) {
    /**
     * URL of a file in public/ with a version taken from its modification time. The server sends
     * no Cache-Control header, so browsers guess freshness from Last-Modified; without a changing
     * URL, returning visitors could get new HTML with the previous CSS/JS right after a deploy.
     */
    function asset_url(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        $file = dirname(__DIR__) . '/public' . $path;
        $modified = is_file($file) ? filemtime($file) : false;

        return $modified === false ? $path : $path . '?v=' . $modified;
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

if (!function_exists('formspree_endpoint')) {
    /**
     * Accepts only https://formspree.io/f/<id>, so visitors' messages are never posted to an
     * arbitrary URL because of a typo or a bad value in the environment.
     */
    function formspree_endpoint(?string $url): ?string
    {
        return $url !== null && preg_match('#^https://formspree\.io/f/[A-Za-z0-9]+\z#', $url) === 1 ? $url : null;
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
