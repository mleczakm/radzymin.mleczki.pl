<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Security\Honeypot;
use PHPUnit\Framework\TestCase;

final class HoneypotTest extends TestCase
{
    public function testEmptyFieldLooksHuman(): void
    {
        self::assertFalse(Honeypot::looksLikeBot([Honeypot::FIELD_NAME => '']));
        self::assertFalse(Honeypot::looksLikeBot([]));
    }

    public function testFilledFieldLooksLikeBot(): void
    {
        self::assertTrue(Honeypot::looksLikeBot([Honeypot::FIELD_NAME => 'https://spam.example']));
    }
}
