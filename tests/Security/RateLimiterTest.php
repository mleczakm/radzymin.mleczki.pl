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

    public function testWindowResetsAfterAnHour(): void
    {
        $now = 1_000_000;
        $limiter = new RateLimiter(RateLimiter::createTable(), static function () use (&$now): int {
            return $now;
        });

        for ($i = 0; $i < 6; $i++) {
            $limiter->tooManyAttempts('ip-a');
        }
        self::assertTrue($limiter->tooManyAttempts('ip-a'));

        $now += 3601;
        self::assertFalse($limiter->tooManyAttempts('ip-a'));
    }

    public function testExpiredRowsAreReclaimedBeforeTheTableFills(): void
    {
        $now = 1_000_000;
        $table = RateLimiter::createTable(64);
        $limiter = new RateLimiter($table, static function () use (&$now): int {
            return $now;
        });

        // Far more clients than the table can hold, each in a window that has expired by the next wave.
        for ($wave = 0; $wave < 5; $wave++) {
            for ($i = 0; $i < 30; $i++) {
                $limiter->tooManyAttempts("wave$wave-ip$i");
            }
            $now += 3601;
        }

        self::assertLessThan(64, $table->count());

        // A fresh client is still tracked and limited after all that churn.
        for ($i = 0; $i < 5; $i++) {
            self::assertFalse($limiter->tooManyAttempts('late-ip'));
        }
        self::assertTrue($limiter->tooManyAttempts('late-ip'));
    }

    public function testFullTableOfActiveClientsDoesNotWarnOrBlockNewOnes(): void
    {
        $table = RateLimiter::createTable(64);
        $limiter = new RateLimiter($table, static fn (): int => 1_000_000);

        // Every slot is taken by a client that is still inside its window: nothing can be purged.
        for ($i = 0; $i < 200; $i++) {
            self::assertFalse($limiter->tooManyAttempts("ip$i"));
        }

        self::assertLessThan(64, $table->count());
        // A tracked client keeps being limited.
        for ($i = 0; $i < 5; $i++) {
            $limiter->tooManyAttempts('ip0');
        }
        self::assertTrue($limiter->tooManyAttempts('ip0'));
    }
}
