<?php

declare(strict_types=1);

namespace App\Domain;

enum TopicStatus: string
{
    case Planned = 'planned';
    case InProgress = 'in_progress';
    case Waiting = 'waiting';
    case Completed = 'completed';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Planowane',
            self::InProgress => 'W toku',
            self::Waiting => 'Oczekuje na odpowiedź',
            self::Completed => 'Zakończone',
            self::Rejected => 'Odrzucone',
        };
    }

    /** Active matters first, finished ones last. */
    public function sortOrder(): int
    {
        return match ($this) {
            self::InProgress => 0,
            self::Waiting => 1,
            self::Planned => 2,
            self::Completed => 3,
            self::Rejected => 4,
        };
    }
}
