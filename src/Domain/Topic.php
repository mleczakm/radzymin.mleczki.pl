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

    /**
     * Deadline verdicts for the institution's answers, keyed by index in $steps. Computed per call
     * (not at boot) because a pending answer turns overdue as time passes.
     *
     * @return array<int, ResponseAssessment>
     */
    public function responseAssessments(?\DateTimeImmutable $today = null): array
    {
        $today ??= new \DateTimeImmutable('today', new \DateTimeZone('Europe/Warsaw'));
        $open = $this->status !== TopicStatus::Completed && $this->status !== TopicStatus::Rejected;

        return $open
            ? ResponseAssessment::forSteps($this->steps, $today)
            : ResponseAssessment::forAnsweredSteps($this->steps);
    }

    /** True when any answer came after its deadline, or is still missing past it. */
    public function hasLateResponse(?\DateTimeImmutable $today = null): bool
    {
        foreach ($this->responseAssessments($today) as $assessment) {
            if ($assessment->timing->isPastDeadline()) {
                return true;
            }
        }

        return false;
    }

    /** Display order: active matters first, then by last update (newest first), then by title. */
    public static function compare(self $a, self $b): int
    {
        return [$a->status->sortOrder(), $b->updatedAt ?? '', $a->title]
            <=> [$b->status->sortOrder(), $a->updatedAt ?? '', $b->title];
    }
}
