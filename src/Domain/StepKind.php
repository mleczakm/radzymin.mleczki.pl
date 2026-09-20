<?php

declare(strict_types=1);

namespace App\Domain;

/** What a step of a matter represents; only filings and answers take part in the deadline check. */
enum StepKind: string
{
    case Other = 'other';
    /** The request/letter was sent to the institution; starts the clock for the answer. */
    case Submission = 'submission';
    /** The institution's answer to the nearest preceding submission. */
    case Response = 'response';
}
