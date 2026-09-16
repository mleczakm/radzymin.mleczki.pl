<?php

declare(strict_types=1);

namespace App\Action;

use Swoole\Http\Request;
use Swoole\Http\Response;

final class HealthAction
{
    public function __invoke(Request $request, Response $response): void
    {
        $response->header('Content-Type', 'text/plain');
        $response->end('ok');
    }
}
