<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Security\RateLimiter;
use PHPUnit\Framework\TestCase;

final class RateLimiterTest extends TestCase
{
    protected function setUp(): void
    {
        if (!extension_loaded('swoole')) {
            self::markTestSkipped('ext-swoole is required for Swoole\\Table.');
        }
    }

    public function testAllowsUpToTheLimitThenBlocks(): void
    {
        $limiter = new RateLimiter(RateLimiter::createTable());

        for ($i = 0; $i < 5; $i++) {
            self::assertFalse($limiter->tooManyAttempts('ip-a'), "attempt $i should be allowed");
        }

        self::assertTrue($limiter->tooManyAttempts('ip-a'));
    }

    public function testDifferentIpsAreTrackedIndependently(): void
    {
        $limiter = new RateLimiter(RateLimiter::createTable());

        for ($i = 0; $i < 5; $i++) {
            $limiter->tooManyAttempts('ip-a');
        }

        self::assertTrue($limiter->tooManyAttempts('ip-a'));
        self::assertFalse($limiter->tooManyAttempts('ip-b'));
    }
}
