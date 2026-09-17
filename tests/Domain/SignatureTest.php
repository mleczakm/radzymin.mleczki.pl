<?php

declare(strict_types=1);

namespace App\Tests\Domain;

use App\Domain\Signature;
use App\Domain\SignatureSource;
use App\Domain\SignatureStatus;
use PHPUnit\Framework\TestCase;

final class SignatureTest extends TestCase
{
    private function makeSignature(string $firstName, string $lastName): Signature
    {
        return new Signature(
            id: 1,
            petitionSlug: 'test',
            firstName: $firstName,
            lastName: $lastName,
            city: 'Radzymin',
            email: null,
            token: null,
            status: SignatureStatus::Confirmed,
            source: SignatureSource::Online,
            createdAt: '2026-01-01T00:00:00+00:00',
            confirmedAt: '2026-01-01T00:00:00+00:00',
        );
    }

    public function testPublicDisplayNameNeverExposesFullSurname(): void
    {
        $signature = $this->makeSignature('Jan', 'Kowalski');

        self::assertSame('Jan K.', $signature->publicDisplayName());
        self::assertStringNotContainsString('Kowalski', $signature->publicDisplayName());
    }

    public function testPublicDisplayNameHandlesUnicodeInitial(): void
    {
        self::assertSame('Łukasz Ż.', $this->makeSignature('Łukasz', 'Żak')->publicDisplayName());
    }

    public function testFullNameStillReturnsBothNames(): void
    {
        self::assertSame('Jan Kowalski', $this->makeSignature('Jan', 'Kowalski')->fullName());
    }
}
