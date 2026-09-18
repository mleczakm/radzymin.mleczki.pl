<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Security\ProductionGuard;
use PHPUnit\Framework\TestCase;

final class ProductionGuardTest extends TestCase
{
    // A made-up value standing in for a real secret in tests.
    // @mago-ignore lint:no-literal-password
    private const REAL_SECRET = 'a3f1c0de5b7e4d29a3f1c0de5b7e4d29a3f1c0de5b7e4d29a3f1c0de5b7e4d29';

    private static function hash(#[\SensitiveParameter] string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 4]);
    }

    public function testDevEnvironmentAcceptsTheDevDefaults(): void
    {
        ProductionGuard::assertSafe('dev', 'dev-insecure-do-not-use-in-production', self::hash('admin'));

        $this->expectNotToPerformAssertions();
    }

    public function testProductionAcceptsRealCredentials(): void
    {
        ProductionGuard::assertSafe('prod', self::REAL_SECRET, self::hash('a-real-password'));

        $this->expectNotToPerformAssertions();
    }

    public function testProductionRejectsTheDevSecret(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/APP_SECRET/');

        ProductionGuard::assertSafe('prod', 'dev-insecure-do-not-use-in-production', self::hash('a-real-password'));
    }

    public function testProductionRejectsTheDevAdminPassword(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/ADMIN_PASSWORD_HASH/');

        ProductionGuard::assertSafe('prod', self::REAL_SECRET, self::hash('admin'));
    }

    public function testAnyNonDevEnvironmentIsTreatedAsProduction(): void
    {
        $this->expectException(\RuntimeException::class);

        ProductionGuard::assertSafe('staging', 'dev-insecure-x', self::hash('a-real-password'));
    }

    public function testMissingHashDoesNotTripTheGuard(): void
    {
        // Missing config is reported separately by WorkerServices::boot(); the guard only blocks known-bad values.
        ProductionGuard::assertSafe('prod', self::REAL_SECRET, '');

        $this->expectNotToPerformAssertions();
    }
}
