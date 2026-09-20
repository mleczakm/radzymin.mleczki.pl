<?php

declare(strict_types=1);

namespace App\Domain;

use App\Text\PolishPlural;

/**
 * Whether an institution's answer came within the deadline counted from the filing date.
 * Dates are calendar days: an answer on the last day of the deadline is still on time.
 */
final class ResponseAssessment
{
    public const DEFAULT_DEADLINE_DAYS = 14;

    public function __construct(
        public readonly ResponseTiming $timing,
        /** ISO date (Y-m-d) of the filing the deadline is counted from. */
        public readonly string $submittedOn,
        /** ISO date (Y-m-d) of the last day to answer. */
        public readonly string $deadline,
        /** Days late (Late/Overdue), days left (Awaiting) or days the answer took (OnTime). */
        public readonly int $days,
    ) {
    }

    /**
     * Assessments keyed by step index, for response steps that follow a filed (done, dated)
     * submission — including answers still pending, which are on time until the deadline passes.
     *
     * @param list<TopicStep> $steps
     * @return array<int, self>
     */
    public static function forSteps(array $steps, \DateTimeImmutable $today): array
    {
        return self::assess($steps, $today);
    }

    /**
     * Like forSteps() but only for answers that already arrived; for closed matters, where a
     * leftover pending answer must not be reported as overdue.
     *
     * @param list<TopicStep> $steps
     * @return array<int, self>
     */
    public static function forAnsweredSteps(array $steps): array
    {
        return self::assess($steps, null);
    }

    /**
     * @param list<TopicStep> $steps
     * @param \DateTimeImmutable|null $today when null, pending answers are not assessed
     * @return array<int, self>
     */
    private static function assess(array $steps, ?\DateTimeImmutable $today): array
    {
        $todayDay = $today === null ? null : self::day($today->format('Y-m-d'));
        $assessments = [];
        $submittedOn = null;

        foreach ($steps as $index => $step) {
            if ($step->kind === StepKind::Submission) {
                $submittedOn = $step->done ? $step->date : null;

                continue;
            }

            if ($step->kind !== StepKind::Response || $submittedOn === null) {
                continue;
            }

            $deadline = self::day($submittedOn)->modify(sprintf('+%d days', $step->deadlineDays));

            if ($step->done) {
                if ($step->date === null) {
                    continue;
                }

                $answered = self::day($step->date);
                $assessments[$index] = $answered <= $deadline
                    ? new self(ResponseTiming::OnTime, $submittedOn, $deadline->format('Y-m-d'), self::daysBetween(self::day($submittedOn), $answered))
                    : new self(ResponseTiming::Late, $submittedOn, $deadline->format('Y-m-d'), self::daysBetween($deadline, $answered));
            } elseif ($todayDay !== null) {
                $assessments[$index] = $todayDay <= $deadline
                    ? new self(ResponseTiming::Awaiting, $submittedOn, $deadline->format('Y-m-d'), self::daysBetween($todayDay, $deadline))
                    : new self(ResponseTiming::Overdue, $submittedOn, $deadline->format('Y-m-d'), self::daysBetween($deadline, $todayDay));
            }
        }

        return $assessments;
    }

    /**
     * Rejects sequences that cannot be assessed, so authoring mistakes fail at boot rather than
     * showing a wrong verdict: an answer needs a preceding filing, and cannot predate it.
     *
     * @param list<TopicStep> $steps
     */
    public static function assertSequence(array $steps, string $file): void
    {
        $submission = null;

        foreach ($steps as $index => $step) {
            if ($step->kind === StepKind::Submission) {
                $submission = $step;

                continue;
            }

            if ($step->kind !== StepKind::Response) {
                continue;
            }

            if ($submission === null) {
                throw new \RuntimeException(sprintf(
                    'Topic file "%s": response step #%d has no preceding step with kind "submission".',
                    $file,
                    $index + 1,
                ));
            }

            if ($step->done && $step->date !== null && $submission->done && $submission->date !== null && $step->date < $submission->date) {
                throw new \RuntimeException(sprintf(
                    'Topic file "%s": response step #%d is dated %s, before its submission (%s).',
                    $file,
                    $index + 1,
                    $step->date,
                    $submission->date,
                ));
            }
        }
    }

    /** Polish detail shown after the label, e.g. "5 dni po upływie terminu (15.09.2026)". */
    public function summary(): string
    {
        $deadline = (new \DateTimeImmutable($this->deadline))->format('d.m.Y');

        return match ($this->timing) {
            ResponseTiming::OnTime => sprintf('%s od złożenia (termin: %s)', self::days($this->days), $deadline),
            ResponseTiming::Late => sprintf('%s po upływie terminu (%s)', self::days($this->days), $deadline),
            ResponseTiming::Awaiting => $this->days === 0
                ? sprintf('termin na odpowiedź upływa dziś (%s)', $deadline)
                : sprintf('termin na odpowiedź: %s — %s', $deadline, PolishPlural::form($this->days, 'został', 'zostały', 'zostało') . ' ' . self::days($this->days)),
            ResponseTiming::Overdue => sprintf('termin minął %s temu (%s)', self::days($this->days), $deadline),
        };
    }

    /** Short label for the badge next to the step. */
    public function label(): string
    {
        return match ($this->timing) {
            ResponseTiming::OnTime => 'Odpowiedź w terminie',
            ResponseTiming::Late => 'Odpowiedź po terminie',
            ResponseTiming::Awaiting => 'Oczekiwanie na odpowiedź',
            ResponseTiming::Overdue => 'Brak odpowiedzi w terminie',
        };
    }

    private static function days(int $count): string
    {
        return $count . ' ' . PolishPlural::form($count, 'dzień', 'dni', 'dni');
    }

    /** Midnight UTC, so day arithmetic is not affected by daylight-saving changes. */
    private static function day(string $isoDate): \DateTimeImmutable
    {
        return new \DateTimeImmutable($isoDate, new \DateTimeZone('UTC'));
    }

    private static function daysBetween(\DateTimeImmutable $from, \DateTimeImmutable $to): int
    {
        return (int) $from->diff($to)->days;
    }
}
