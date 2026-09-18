<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;

final class ShareLinksTest extends TestCase
{
    public function testLinksCarryTheUrlAndTextEncoded(): void
    {
        $links = share_links('https://radzymin.mleczki.pl/petycja/x?a=1&b=2', 'Podpisz: Ścieżka & rower');

        self::assertSame(['WhatsApp', 'Facebook', 'E-mail'], array_keys($links));

        // A raw "&" in the message or URL would silently split the query string.
        foreach ($links as $href) {
            self::assertStringNotContainsString('Ścieżka & rower', $href);
            self::assertStringNotContainsString('a=1&b=2', $href);
        }

        self::assertStringContainsString(rawurlencode('https://radzymin.mleczki.pl/petycja/x?a=1&b=2'), $links['Facebook']);
        self::assertStringStartsWith('https://wa.me/?text=', $links['WhatsApp']);
        self::assertStringStartsWith('mailto:?subject=', $links['E-mail']);
    }
}
