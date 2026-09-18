<?php

declare(strict_types=1);

namespace App\Security;

/**
 * Signed timestamp embedded as a hidden field when a form is rendered.
 *
 * Rejects submissions that arrive faster than a human could plausibly fill
 * the form (typical bot behaviour) and, as a side effect, submissions that
 * were tampered with or replayed long after the token expired.
 */
final class FormTimingToken
{
    private const MIN_SECONDS = 3;
    private const MAX_SECONDS = 6 * 3600;

    public function __construct(#[\SensitiveParameter] private readonly string $secret)
    {
    }

    public function generate(): string
    {
        $timestamp = (string) time();

        return $timestamp . '.' . hash_hmac('sha256', $timestamp, $this->secret);
    }

    public function isValid(#[\SensitiveParameter] ?string $token): bool
    {
        if ($token === null || !str_contains($token, '.')) {
            return false;
        }

        [$timestamp, $signature] = explode('.', $token, 2);

        if (!ctype_digit($timestamp)) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp, $this->secret);
        if (!hash_equals($expected, $signature)) {
            return false;
        }

        $elapsed = time() - (int) $timestamp;

        return $elapsed >= self::MIN_SECONDS && $elapsed <= self::MAX_SECONDS;
    }
}
