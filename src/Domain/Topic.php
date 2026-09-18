<?php

declare(strict_types=1);

namespace App\Domain;

/** A matter the site owner is working on (e.g. a formal request to an institution) with its current state. */
final class Topic
{
    /** @param list<TopicStep> $steps */
    public function __construct(
        public readonly string $slug,
        public readonly string $title,
        public readonly string $summary,
        public readonly string $bodyHtml,
        public readonly TopicStatus $status,
        public readonly ?string $institution = null,
        /** ISO date (Y-m-d) of the last update. */
        public readonly ?string $updatedAt = null,
        public readonly array $steps = [],
    ) {
    }

    /** Share of completed steps; a completed matter is always 100%. Null when there is nothing to measure. */
    public function progressPercent(): ?int
    {
        if ($this->status === TopicStatus::Completed) {
            return 100;
        }

        if ($this->steps === []) {
            return null;
        }

        $done = count(array_filter($this->steps, static fn (TopicStep $step): bool => $step->done));

        return (int) floor($done / count($this->steps) * 100);
    }
}
