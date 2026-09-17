<?php

declare(strict_types=1);

namespace App\Tests\Domain;

use App\Domain\Petition;
use PHPUnit\Framework\TestCase;

final class PetitionTest extends TestCase
{
    private function makePetition(?int $goal = null, ?string $deadline = null): Petition
    {
        return new Petition(
            slug: 'test',
            title: 'Test',
            lead: 'Lead',
            bodyHtml: '<p>Body</p>',
            createdAt: '2026-01-01',
            goal: $goal,
            deadline: $deadline,
        );
    }

    public function testProgressPercentIsNullWithoutGoal(): void
    {
        self::assertNull($this->makePetition()->progressPercent(50));
    }

    public function testProgressPercentIsComputedFromGoal(): void
    {
        $petition = $this->makePetition(goal: 200);

        self::assertSame(25, $petition->progressPercent(50));
    }

    public function testProgressPercentIsCappedAt100(): void
    {
        $petition = $this->makePetition(goal: 10);

        self::assertSame(100, $petition->progressPercent(999));
    }

    public function testProgressPercentIsZeroWithNoSignatures(): void
    {
        $petition = $this->makePetition(goal: 10);

        self::assertSame(0, $petition->progressPercent(0));
    }

    public function testDaysRemainingIsNullWithoutDeadline(): void
    {
        self::assertNull($this->makePetition()->daysRemaining());
    }

    public function testDaysRemainingCountsDaysUntilAFutureDeadline(): void
    {
        $inTenDays = (new \DateTimeImmutable('+10 days'))->format('Y-m-d');

        self::assertSame(10, $this->makePetition(deadline: $inTenDays)->daysRemaining());
    }

    public function testDaysRemainingIsZeroWhenDeadlineHasPassed(): void
    {
        $lastWeek = (new \DateTimeImmutable('-7 days'))->format('Y-m-d');

        self::assertSame(0, $this->makePetition(deadline: $lastWeek)->daysRemaining());
    }
}
