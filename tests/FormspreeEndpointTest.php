<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;

final class FormspreeEndpointTest extends TestCase
{
    public function testAcceptsAFormspreeFormUrl(): void
    {
        self::assertSame('https://formspree.io/f/xwlppzoj', formspree_endpoint('https://formspree.io/f/xwlppzoj'));
    }

    public function testRejectsAnythingElseSoMessagesAreNeverPostedToAnArbitraryUrl(): void
    {
        self::assertNull(formspree_endpoint(null));
        self::assertNull(formspree_endpoint(''));
        self::assertNull(formspree_endpoint('http://formspree.io/f/xwlppzoj'));
        self::assertNull(formspree_endpoint('https://evil.example/f/xwlppzoj'));
        self::assertNull(formspree_endpoint('https://formspree.io.evil.example/f/xwlppzoj'));
        self::assertNull(formspree_endpoint('https://formspree.io/f/xwlppzoj/../../x'));
        self::assertNull(formspree_endpoint('https://formspree.io/f/xwlppzoj?next=https://evil.example'));
        self::assertNull(formspree_endpoint("https://formspree.io/f/xwlppzoj\n"));
    }
}
