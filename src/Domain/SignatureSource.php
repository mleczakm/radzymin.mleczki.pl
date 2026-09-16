<?php

declare(strict_types=1);

namespace App\Domain;

enum SignatureSource: string
{
    case Online = 'online';
    case Paper = 'paper';
}
