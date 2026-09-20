<?php

declare(strict_types=1);

namespace App\Tests\Domain;

use App\Domain\ResponseAssessment;
use App\Domain\ResponseTiming;
use App\Domain\StepKind;
use App\Domain\Topic;
use App\Domain\TopicStatus;
use App\Domain\TopicStep;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ResponseAssessmentTest extends TestCase
{
    private static function submission(string $date = '2026-09-01', bool $done = true): TopicStep
    {
        return new TopicStep('Złożono', $date, $done, StepKind::Submission);
    }

    private static function response(?string $date, bool $done = true, int $days = 14): TopicStep
    {
        return new TopicStep('Odpowiedź', $date, $done, StepKind::Response, $days);
    }

    private static function today(string $date): \DateTimeImmutable
    {
        return new \DateTimeImmutable($date);
    }

    /** @return iterable<string, array{string, ResponseTiming, string, int}> */
    public static function answeredProvider(): iterable
    {
        yield 'same day' => ['2026-09-01', ResponseTiming::OnTime, '2026-09-15', 0];
        yield 'day 9' => ['2026-09-10', ResponseTiming::OnTime, '2026-09-15', 9];
        yield 'last day is still on time' => ['2026-09-15', ResponseTiming::OnTime, '2026-09-15', 14];
        yield 'one day late' => ['2026-09-16', ResponseTiming::Late, '2026-09-15', 1];
        yield 'a month late' => ['2026-10-15', ResponseTiming::Late, '2026-09-15', 30];
    }

    #[DataProvider('answeredProvider')]
    public function testAnsweredResponseIsComparedWithTheDeadline(string $answeredOn, ResponseTiming $timing, string $deadline, int $days): void
    {
        $assessments = ResponseAssessment::forSteps([self::submission(), self::response($answeredOn)], self::today('2026-12-01'));

        self::assertArrayHasKey(1, $assessments);
        self::assertSame($timing, $assessments[1]->timing);
        self::assertSame($deadline, $assessments[1]->deadline);
        self::assertSame($days, $assessments[1]->days);
    }

    public function testDeadlineCountsAcrossDaylightSavingChange(): void
    {
        // Warsaw switches to summer time on 2026-03-29; 14 days from 03-20 is 04-03 either way.
        $assessments = ResponseAssessment::forSteps(
            [self::submission('2026-03-20'), self::response('2026-04-03')],
            self::today('2026-06-01'),
        );

        self::assertSame(ResponseTiming::OnTime, $assessments[1]->timing);
        self::assertSame(14, $assessments[1]->days);
    }

    public function testCustomDeadlineDaysAreUsed(): void
    {
        $assessments = ResponseAssessment::forSteps(
            [self::submission(), self::response('2026-09-25', true, 30)],
            self::today('2026-12-01'),
        );

        self::assertSame(ResponseTiming::OnTime, $assessments[1]->timing);
        self::assertSame('2026-10-01', $assessments[1]->deadline);
    }

    public function testPendingResponseIsAwaitingUntilTheDeadlineDayInclusive(): void
    {
        $steps = [self::submission(), self::response(null, false)];

        $before = ResponseAssessment::forSteps($steps, self::today('2026-09-12'))[1];
        self::assertSame(ResponseTiming::Awaiting, $before->timing);
        self::assertSame(3, $before->days);

        $lastDay = ResponseAssessment::forSteps($steps, self::today('2026-09-15'))[1];
        self::assertSame(ResponseTiming::Awaiting, $lastDay->timing);
        self::assertSame(0, $lastDay->days);
    }

    public function testPendingResponseBecomesOverdueAfterTheDeadline(): void
    {
        $assessment = ResponseAssessment::forSteps(
            [self::submission(), self::response(null, false)],
            self::today('2026-09-20'),
        )[1];

        self::assertSame(ResponseTiming::Overdue, $assessment->timing);
        self::assertSame(5, $assessment->days);
        self::assertTrue($assessment->timing->isPastDeadline());
    }

    public function testPendingResponseIsSkippedWhenTheMatterIsClosed(): void
    {
        self::assertSame([], ResponseAssessment::forAnsweredSteps([self::submission(), self::response(null, false)]));
        self::assertArrayHasKey(1, ResponseAssessment::forAnsweredSteps([self::submission(), self::response('2026-09-05')]));
    }

    public function testNothingIsAssessedWithoutAFiledDatedSubmission(): void
    {
        $today = self::today('2026-12-01');

        self::assertSame([], ResponseAssessment::forSteps([self::response('2026-09-05')], $today));
        self::assertSame([], ResponseAssessment::forSteps([self::submission('2026-09-01', false), self::response(null, false)], $today));
        self::assertSame([], ResponseAssessment::forSteps([new TopicStep('Złożono', null, true, StepKind::Submission), self::response(null, false)], $today));
    }

    public function testAnsweredResponseWithoutDateIsNotAssessed(): void
    {
        self::assertSame([], ResponseAssessment::forSteps([self::submission(), self::response(null)], self::today('2026-12-01')));
    }

    public function testEachResponseIsMeasuredFromItsOwnSubmission(): void
    {
        $assessments = ResponseAssessment::forSteps([
            self::submission('2026-01-01'),
            self::response('2026-01-10'),
            self::submission('2026-02-01'),
            self::response('2026-02-20'),
        ], self::today('2026-12-01'));

        self::assertSame(ResponseTiming::OnTime, $assessments[1]->timing);
        self::assertSame(ResponseTiming::Late, $assessments[3]->timing);
        self::assertSame('2026-02-01', $assessments[3]->submittedOn);
    }

    public function testStepsOfOtherKindsAreIgnored(): void
    {
        self::assertSame([], ResponseAssessment::forSteps([new TopicStep('A', '2026-01-01', true)], self::today('2026-12-01')));
    }

    public function testSummariesUsePolishSingularAndPlural(): void
    {
        $submission = self::submission();
        $today = self::today('2026-12-01');

        self::assertSame(
            '1 dzień po upływie terminu (15.09.2026)',
            ResponseAssessment::forSteps([$submission, self::response('2026-09-16')], $today)[1]->summary(),
        );
        self::assertSame(
            '9 dni od złożenia (termin: 15.09.2026)',
            ResponseAssessment::forSteps([$submission, self::response('2026-09-10')], $today)[1]->summary(),
        );
        self::assertSame(
            'termin na odpowiedź upływa dziś (15.09.2026)',
            ResponseAssessment::forSteps([$submission, self::response(null, false)], self::today('2026-09-15'))[1]->summary(),
        );
        self::assertSame(
            'termin minął 5 dni temu (15.09.2026)',
            ResponseAssessment::forSteps([$submission, self::response(null, false)], self::today('2026-09-20'))[1]->summary(),
        );
    }

    public function testEveryTimingHasALabel(): void
    {
        foreach (ResponseTiming::cases() as $timing) {
            $label = (new ResponseAssessment($timing, '2026-09-01', '2026-09-15', 1))->label();
            self::assertNotSame('', $label);
        }
    }

    public function testTopicFlagsLateAndOverdueAnswers(): void
    {
        $make = static fn (TopicStatus $status, TopicStep $response): Topic => new Topic(
            slug: 't',
            title: 'T',
            summary: 'S',
            bodyHtml: '',
            status: $status,
            steps: [self::submission(), $response],
        );
        $today = self::today('2026-09-20');

        self::assertTrue($make(TopicStatus::Completed, self::response('2026-09-20'))->hasLateResponse($today));
        self::assertFalse($make(TopicStatus::Completed, self::response('2026-09-10'))->hasLateResponse($today));
        self::assertTrue($make(TopicStatus::Waiting, self::response(null, false))->hasLateResponse($today));
        // A closed matter with a leftover pending answer is not reported as overdue.
        self::assertFalse($make(TopicStatus::Rejected, self::response(null, false))->hasLateResponse($today));
    }
}
