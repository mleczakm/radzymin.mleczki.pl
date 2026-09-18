<?php

declare(strict_types=1);

namespace App\Domain;

final class TopicStep
{
    public function __construct(
        public readonly string $title,
        /** ISO date (Y-m-d); optional — for done steps the date it happened, for pending ones a planned date. */
        public readonly ?string $date = null,
        public readonly bool $done = false,
    ) {
    }
}
