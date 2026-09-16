<?php

declare(strict_types=1);

namespace App\Security;

final class Honeypot
{
    /** Name deliberately looks like a normal field; hidden from humans via CSS, tempting for bots that autofill everything. */
    public const FIELD_NAME = 'strona_www';

    /** @param array<string, mixed> $post */
    public static function looksLikeBot(array $post): bool
    {
        return trim((string) ($post[self::FIELD_NAME] ?? '')) !== '';
    }
}
