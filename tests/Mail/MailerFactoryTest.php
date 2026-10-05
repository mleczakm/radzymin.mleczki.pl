<?php

declare(strict_types=1);

namespace App\Tests\Mail;

use App\Mail\MailerFactory;
use PHPUnit\Framework\TestCase;

final class MailerFactoryTest extends TestCase
{
    public function testLeavesAWellFormedDsnUntouched(): void
    {
        self::assertSame(
            'smtp://user%40gmail.com:abcdefghijklmnop@smtp.gmail.com:587',
            MailerFactory::normalizeDsn('smtp://user%40gmail.com:abcdefghijklmnop@smtp.gmail.com:587'),
        );
    }

    public function testStripsQuotesThatDockerEnvFileKeepsInValues(): void
    {
        self::assertSame('smtp://localhost:1025', MailerFactory::normalizeDsn('"smtp://localhost:1025"'));
        self::assertSame('smtp://localhost:1025', MailerFactory::normalizeDsn("'smtp://localhost:1025'"));
    }

    public function testRemovesTheSpacesGoogleShowsInAppPasswords(): void
    {
        self::assertSame(
            'smtp://user%40gmail.com:abcdefghijklmnop@smtp.gmail.com:587',
            MailerFactory::normalizeDsn('"smtp://user%40gmail.com:abcd efgh ijkl mnop@smtp.gmail.com:587"'),
        );
    }

    public function testSurroundingWhitespaceAndTrailingNewlinesAreIgnored(): void
    {
        self::assertSame('smtp://localhost:1025', MailerFactory::normalizeDsn("  smtp://localhost:1025\r\n"));
    }
}
