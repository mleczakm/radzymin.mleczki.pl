<?php

declare(strict_types=1);

namespace App\Domain;

final class Petition
{
    public function __construct(
        public readonly string $slug,
        public readonly string $title,
        public readonly string $lead,
        public readonly string $bodyHtml,
        public readonly string $createdAt,
        /** Signature target shown as a progress bar; null hides it. */
        public readonly ?int $goal = null,
        /** ISO date (Y-m-d); when set with $goal, shows a "days remaining" countdown. */
        public readonly ?string $deadline = null,
    ) {
    }

    public function daysRemaining(): ?int
    {
        if ($this->deadline === null) {
            return null;
        }

        $today = new \DateTimeImmutable('today');
        $deadline = new \DateTimeImmutable($this->deadline);

        $days = (int) $today->diff($deadline)->days;

        return $deadline >= $today ? $days : 0;
    }

    public function progressPercent(int $confirmedCount): ?int
    {
        if ($this->goal === null || $this->goal <= 0) {
            return null;
        }

        return (int) min(100, floor($confirmedCount / $this->goal * 100));
    }
}
