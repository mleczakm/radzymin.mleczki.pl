<?php

declare(strict_types=1);

namespace App\Tests\Domain;

use App\Domain\ResponseTiming;
use App\Domain\StepKind;
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
        self::assertNotNull($topic);

        // The planned 2026-06-01 step is not done, so it must not count.
        self::assertSame('2026-04-05', $topic->updatedAt);
    }

    public function testTopicWithoutStepsOrDatesHasNoUpdatedAt(): void
    {
        $topic = (new TopicRepository(self::FIXTURES . '/topics'))->find('wniosek-c');
        self::assertNotNull($topic);

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

    public function testParsesStepKindAndDeadlineDays(): void
    {
        $topic = (new TopicRepository(self::FIXTURES . '/topics-responses'))->find('wniosek-r');

        self::assertNotNull($topic);
        self::assertSame(StepKind::Submission, $topic->steps[0]->kind);
        self::assertSame(14, $topic->steps[0]->deadlineDays);
        self::assertSame(StepKind::Response, $topic->steps[1]->kind);
        self::assertSame(30, $topic->steps[1]->deadlineDays);

        // 30-day deadline: answered on day 19, so on time although it would be late at 14 days.
        self::assertSame(ResponseTiming::OnTime, $topic->responseAssessments(new \DateTimeImmutable('2026-12-01'))[1]->timing);
    }

    public function testStepsDefaultToOtherKindWithFourteenDayDeadline(): void
    {
        $topic = (new TopicRepository(self::FIXTURES . '/topics'))->find('wniosek-a');

        self::assertNotNull($topic);
        self::assertSame(StepKind::Other, $topic->steps[0]->kind);
        self::assertSame(14, $topic->steps[0]->deadlineDays);
        self::assertSame([], $topic->responseAssessments(new \DateTimeImmutable('2026-12-01')));
    }

    public function testInvalidStepKindThrowsWithAllowedValues(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/step #1 has invalid kind "answer".*submission, response/');

        new TopicRepository(self::FIXTURES . '/topics-invalid-kind');
    }

    public function testInvalidDeadlineDaysThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/step #2 has invalid deadlineDays/');

        new TopicRepository(self::FIXTURES . '/topics-invalid-deadline');
    }

    public function testResponseWithoutPrecedingSubmissionThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/response step #1 has no preceding step with kind "submission"/');

        new TopicRepository(self::FIXTURES . '/topics-invalid-orphan-response');
    }

    public function testResponseDatedBeforeItsSubmissionThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/response step #2 is dated 2026-03-05, before its submission \(2026-03-10\)/');

        new TopicRepository(self::FIXTURES . '/topics-invalid-response-order');
    }
}
