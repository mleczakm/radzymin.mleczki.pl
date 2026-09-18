<?php

declare(strict_types=1);

namespace App\Tests\Domain;

use App\Domain\TopicRepository;
use App\Domain\TopicStatus;
use PHPUnit\Framework\TestCase;

final class TopicRepositoryTest extends TestCase
{
    private const FIXTURES = __DIR__ . '/../fixtures';

    public function testParsesFrontMatterStepsAndMarkdownBody(): void
    {
        $topic = (new TopicRepository(self::FIXTURES . '/topics'))->find('wniosek-a');

        self::assertNotNull($topic);
        self::assertSame('Wniosek A', $topic->title);
        self::assertSame('Streszczenie A.', $topic->summary);
        self::assertSame(TopicStatus::InProgress, $topic->status);
        self::assertSame('Urząd A', $topic->institution);
        self::assertSame('2026-03-10', $topic->updatedAt);
        self::assertStringContainsString('<strong>A</strong>', $topic->bodyHtml);

        self::assertCount(2, $topic->steps);
        self::assertSame('Złożono wniosek', $topic->steps[0]->title);
        self::assertSame('2026-03-01', $topic->steps[0]->date);
        self::assertTrue($topic->steps[0]->done);
        self::assertNull($topic->steps[1]->date);
        self::assertFalse($topic->steps[1]->done);
    }

    public function testActiveTopicsComeFirstThenByNewestUpdate(): void
    {
        $slugs = array_keys((new TopicRepository(self::FIXTURES . '/topics'))->all());

        // in_progress (newest first: E, A), waiting (D), planned (C), completed (B)
        self::assertSame(['wniosek-e', 'wniosek-a', 'wniosek-d', 'wniosek-c', 'wniosek-b'], $slugs);
    }

    public function testUpdatedAtFallsBackToLatestDoneStepDate(): void
    {
        $topic = (new TopicRepository(self::FIXTURES . '/topics'))->find('wniosek-d');

        // The planned 2026-06-01 step is not done, so it must not count.
        self::assertSame('2026-04-05', $topic->updatedAt);
    }

    public function testTopicWithoutStepsOrDatesHasNoUpdatedAt(): void
    {
        $topic = (new TopicRepository(self::FIXTURES . '/topics'))->find('wniosek-c');

        self::assertNull($topic->updatedAt);
        self::assertNull($topic->institution);
        self::assertSame([], $topic->steps);
    }

    public function testUnknownSlugReturnsNull(): void
    {
        self::assertNull((new TopicRepository(self::FIXTURES . '/topics'))->find('nie-ma'));
    }

    public function testMissingDirectoryYieldsNoTopics(): void
    {
        self::assertSame([], (new TopicRepository(self::FIXTURES . '/does-not-exist'))->all());
    }

    public function testInvalidStatusThrowsWithAllowedValues(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/invalid status "finished".*in_progress/');

        new TopicRepository(self::FIXTURES . '/topics-invalid-status');
    }

    public function testMissingRequiredFieldThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/missing required front matter field "summary"/');

        new TopicRepository(self::FIXTURES . '/topics-invalid-missing');
    }

    public function testStepWithoutTitleThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/step #1 is missing a "title"/');

        new TopicRepository(self::FIXTURES . '/topics-invalid-step');
    }
}
