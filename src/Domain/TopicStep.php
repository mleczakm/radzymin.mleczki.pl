<?php

declare(strict_types=1);

namespace App\Domain;

use App\Content\MarkdownLoader;

final class TopicStep
{
    public function __construct(
        public readonly string $title,
        /** ISO date (Y-m-d); optional — for done steps the date it happened, for pending ones a planned date. */
        public readonly ?string $date = null,
        public readonly bool $done = false,
        /** Filing or answer; lets the site check whether the institution answered within the deadline. */
        public readonly StepKind $kind = StepKind::Other,
        /** Days the institution has to answer, counted from the preceding submission (response steps only). */
        public readonly int $deadlineDays = ResponseAssessment::DEFAULT_DEADLINE_DAYS,
    ) {
    }

    /** @param int $position 1-based position in the steps list, for error messages */
    public static function fromFrontMatter(mixed $raw, string $file, int $position): self
    {
        $title = is_array($raw) ? ($raw['title'] ?? null) : null;
        if (!is_scalar($title) || (string) $title === '') {
            throw new \RuntimeException(sprintf('Topic file "%s": step #%d is missing a "title".', $file, $position));
        }

        return new self(
            title: (string) $title,
            date: MarkdownLoader::normalizeDate($raw['date'] ?? null),
            done: ($raw['done'] ?? false) === true,
            kind: self::parseKind($raw['kind'] ?? null, $file, $position),
            deadlineDays: self::parseDeadlineDays($raw['deadlineDays'] ?? null, $file, $position),
        );
    }

    private static function parseKind(mixed $value, string $file, int $position): StepKind
    {
        if ($value === null) {
            return StepKind::Other;
        }

        $kind = is_string($value) ? StepKind::tryFrom($value) : null;

        return $kind ?? throw new \RuntimeException(sprintf(
            'Topic file "%s": step #%d has invalid kind "%s" (allowed: %s).',
            $file,
            $position,
            is_scalar($value) ? (string) $value : get_debug_type($value),
            implode(', ', array_map(static fn (StepKind $kind): string => $kind->value, StepKind::cases())),
        ));
    }

    private static function parseDeadlineDays(mixed $value, string $file, int $position): int
    {
        if ($value === null) {
            return ResponseAssessment::DEFAULT_DEADLINE_DAYS;
        }

        if (!is_int($value) || $value < 1 || $value > 366) {
            throw new \RuntimeException(sprintf(
                'Topic file "%s": step #%d has invalid deadlineDays (expected a whole number of days from 1 to 366).',
                $file,
                $position,
            ));
        }

        return $value;
    }

    /**
     * Date of the most recent step that is already done, or null when none has a date.
     *
     * @param list<self> $steps
     */
    public static function latestDoneDate(array $steps): ?string
    {
        $dates = array_filter(
            array_map(static fn (self $step): ?string => $step->done ? $step->date : null, $steps),
        );

        return $dates === [] ? null : max($dates);
    }
}
