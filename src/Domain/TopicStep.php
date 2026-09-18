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
        );
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
