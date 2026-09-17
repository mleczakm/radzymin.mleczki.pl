<?php

declare(strict_types=1);

namespace App\Tests\Domain;

use App\Domain\PetitionRepository;
use PHPUnit\Framework\TestCase;

final class PetitionRepositoryTest extends TestCase
{
    public function testParsesFrontMatterAndRendersMarkdownBody(): void
    {
        $repository = new PetitionRepository(__DIR__ . '/../fixtures/petitions');

        $petition = $repository->find('test-petition');

        self::assertNotNull($petition);
        self::assertSame('Testowa petycja', $petition->title);
        self::assertSame('Krótki opis testowej petycji.', $petition->lead);
        self::assertSame('2026-01-15', $petition->createdAt);
        self::assertSame(100, $petition->goal);
        self::assertSame('2026-01-25', $petition->deadline);
        self::assertStringContainsString('<li>Punkt A</li>', $petition->bodyHtml);
        self::assertStringContainsString('<p>Akapit treści.</p>', $petition->bodyHtml);
    }

    public function testUnknownSlugReturnsNull(): void
    {
        $repository = new PetitionRepository(__DIR__ . '/../fixtures/petitions');

        self::assertNull($repository->find('does-not-exist'));
    }

    public function testAllReturnsEveryParsedPetitionKeyedBySlug(): void
    {
        $repository = new PetitionRepository(__DIR__ . '/../fixtures/petitions');

        self::assertSame(['test-petition'], array_keys($repository->all()));
    }

    public function testMissingRequiredFrontMatterFieldThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/missing required front matter field "lead"/');

        new PetitionRepository(__DIR__ . '/../fixtures/petitions-invalid');
    }

    public function testEmptyDirectoryYieldsNoPetitions(): void
    {
        $repository = new PetitionRepository(__DIR__ . '/../fixtures/petitions-empty');

        self::assertSame([], $repository->all());
    }
}
