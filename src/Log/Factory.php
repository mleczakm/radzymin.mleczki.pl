<?php

declare(strict_types=1);

namespace App\Log;

use Psr\Log\LoggerInterface;

final class Factory
{
    public static function create(bool $debug): LoggerInterface
    {
        return new StdOutLogger($debug);
    }
}
