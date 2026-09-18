<?php

declare(strict_types=1);

namespace App\Tests\Storage;

use App\Domain\DuplicateSignatureException;
use App\Domain\SignatureSource;
use App\Domain\SignatureStatus;
use App\Storage\Database;
use App\Storage\SignatureRepository;
use PHPUnit\Framework\TestCase;

final class SignatureRepositoryTest extends TestCase
{
    private SignatureRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new SignatureRepository(Database::connect(':memory:'));
    }

    public function testCreateOnlineStartsAsPending(): void
    {
        $signature = $this->repository->createOnline('przyklad', 'Jan', 'Kowalski', 'Radzymin', 'jan@example.com', 'iphash');

        self::assertSame(SignatureStatus::Pending, $signature->status);
        self::assertSame(SignatureSource::Online, $signature->source);
        self::assertNotNull($signature->token);
        self::assertSame(0, $this->repository->countConfirmed('przyklad'));
        self::assertSame(1, $this->repository->countPending('przyklad'));
    }

    public function testDuplicateEmailForSamePetitionIsRejected(): void
    {
        $this->repository->createOnline('przyklad', 'Jan', 'Kowalski', 'Radzymin', 'jan@example.com', 'iphash');

        $this->expectException(DuplicateSignatureException::class);
        $this->repository->createOnline('przyklad', 'Jan', 'Kowalski', 'Radzymin', 'JAN@example.com', 'iphash2');
    }

    public function testSameEmailCanSignDifferentPetitions(): void
    {
        $this->repository->createOnline('petycja-a', 'Jan', 'Kowalski', 'Radzymin', 'jan@example.com', 'iphash');
        $signature = $this->repository->createOnline('petycja-b', 'Jan', 'Kowalski', 'Radzymin', 'jan@example.com', 'iphash');

        self::assertSame('petycja-b', $signature->petitionSlug);
    }

    public function testConfirmMovesSignatureToConfirmedAndCountsIt(): void
    {
        $signature = $this->repository->createOnline('przyklad', 'Jan', 'Kowalski', 'Radzymin', 'jan@example.com', 'iphash');

        $this->repository->confirm($signature);

        self::assertSame(1, $this->repository->countConfirmed('przyklad'));
        self::assertSame(0, $this->repository->countPending('przyklad'));

        self::assertNotNull($signature->token);
        $reloaded = $this->repository->findByToken($signature->token);
        self::assertNotNull($reloaded);
        self::assertSame(SignatureStatus::Confirmed, $reloaded->status);
    }

    public function testCreatePaperIsConfirmedImmediately(): void
    {
        $signature = $this->repository->createPaper('przyklad', 'Anna', 'Nowak', 'Ciemne');

        self::assertSame(SignatureStatus::Confirmed, $signature->status);
        self::assertSame(SignatureSource::Paper, $signature->source);
        self::assertNull($signature->email);
        self::assertSame(1, $this->repository->countConfirmed('przyklad'));
    }

    public function testMultiplePaperSignaturesWithoutEmailDoNotCollide(): void
    {
        $this->repository->createPaper('przyklad', 'Anna', 'Nowak', 'Ciemne');
        $this->repository->createPaper('przyklad', 'Adam', 'Nowak', 'Ciemne');

        self::assertSame(2, $this->repository->countConfirmed('przyklad'));
    }

    public function testFindByUnknownTokenReturnsNull(): void
    {
        self::assertNull($this->repository->findByToken('does-not-exist'));
    }

    public function testRecentConfirmedReturnsNewestFirst(): void
    {
        $this->repository->createPaper('przyklad', 'Anna', 'Nowak', 'Ciemne');
        $this->repository->createPaper('przyklad', 'Jan', 'Kowalski', 'Radzymin');

        $recent = $this->repository->recentConfirmed('przyklad');

        self::assertSame(['Jan', 'Anna'], array_map(static fn ($s) => $s->firstName, $recent));
    }

    public function testRecentConfirmedExcludesPendingAndOtherPetitions(): void
    {
        $this->repository->createOnline('przyklad', 'Pending', 'Osoba', 'Radzymin', 'pending@example.com', 'iphash');
        $this->repository->createPaper('inna-petycja', 'Inna', 'Osoba', 'Radzymin');
        $this->repository->createPaper('przyklad', 'Widoczna', 'Osoba', 'Radzymin');

        $recent = $this->repository->recentConfirmed('przyklad');

        self::assertCount(1, $recent);
        self::assertSame('Widoczna', $recent[0]->firstName);
    }

    public function testRecentConfirmedRespectsLimit(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->repository->createPaper('przyklad', "Osoba$i", 'Testowa', 'Radzymin');
        }

        self::assertCount(3, $this->repository->recentConfirmed('przyklad', limit: 3));
    }
}
