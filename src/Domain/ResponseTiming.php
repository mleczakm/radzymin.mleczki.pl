<?php

declare(strict_types=1);

namespace App\Domain;

enum ResponseTiming: string
{
    /** Answered within the deadline. */
    case OnTime = 'on_time';
    /** Answered after the deadline. */
    case Late = 'late';
    /** Not answered yet, deadline still ahead. */
    case Awaiting = 'awaiting';
    /** Not answered and the deadline has passed. */
    case Overdue = 'overdue';

    /** Answer came (or should have come) too late — worth flagging visually. */
    public function isPastDeadline(): bool
    {
        return $this === self::Late || $this === self::Overdue;
    }
}
