<?php

declare(strict_types=1);

namespace App\Domain;

enum SignatureStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
}
