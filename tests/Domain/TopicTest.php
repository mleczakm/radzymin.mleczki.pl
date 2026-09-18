<?php

declare(strict_types=1);

namespace App\Tests\Domain;

use App\Domain\Topic;
use App\Domain\TopicStatus;
use App\Domain\TopicStep;
use PHPUnit\Framework\TestCase;

final class TopicTest extends TestCase
{
    /** @param list<TopicStep> $steps */
    private function makeTopic(TopicStatus $status, array $steps = []): Topic
    {
        return new Topic(
            slug: 'test',
            title: 'Test',
            summary: 'Summary',
            bodyHtml: '',
            status: $status,
            steps: $steps,
        );
    }

    public function testNoStepsMeansNoProgress(): void
    {
        self::assertNull($this->makeTopic(TopicStatus::InProgress)->progressPercent());
    }

    public function testProgressIsShareOfDoneSteps(): void
    {
        $topic = $this->makeTopic(TopicStatus::Waiting, [
            new TopicStep('A', done: true),
            new TopicStep('B', done: false),
            new TopicStep('C', done: false),
            new TopicStep('D', done: false),
        ]);

        self::assertSame(25, $topic->progressPercent());
    }

    public function testProgressIsRoundedDown(): void
    {
        $topic = $this->makeTopic(TopicStatus::InProgress, [
            new TopicStep('A', done: true),
            new TopicStep('B', done: false),
            new TopicStep('C', done: false),
        ]);

        self::assertSame(33, $topic->progressPercent());
    }

    public function testCompletedTopicIsAlways100Percent(): void
    {
        self::assertSame(100, $this->makeTopic(TopicStatus::Completed)->progressPercent());
        self::assertSame(100, $this->makeTopic(TopicStatus::Completed, [new TopicStep('A')])->progressPercent());
    }

    public function testEveryStatusHasALabelAndAUniqueSortOrder(): void
    {
        $orders = [];
        foreach (TopicStatus::cases() as $status) {
            self::assertNotSame('', $status->label());
            $orders[] = $status->sortOrder();
        }

        self::assertSame($orders, array_unique($orders));
    }
}
