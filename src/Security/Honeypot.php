<?php

declare(strict_types=1);

namespace App\Security;

final class Honeypot
{
    /** Name deliberately looks like a normal field; hidden from humans via CSS, tempting for bots that autofill everything. */
    public const FIELD_NAME = 'strona_www';

    /** @param array<array-key, mixed> $post raw POST fields */
    public static function looksLikeBot(array $post): bool
    {
        $value = $post[self::FIELD_NAME] ?? '';

        // A nested value (strona_www[]=x) can't come from the rendered form, so it counts as filled.
        return is_array($value) || (is_scalar($value) && trim((string) $value) !== '');
    }
}
