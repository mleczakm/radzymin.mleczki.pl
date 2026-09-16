<?php

declare(strict_types=1);

namespace App\Task;

final class SendConfirmationEmailTask
{
    public function __construct(public readonly int $signatureId)
    {
    }
}
