<?php

declare(strict_types=1);

namespace App\Server;

use Swoole\Http\Server;

final class Factory
{
    public static function create(): Server
    {
        // Same port in dev and prod: the deploy role publishes container port 8080 (like cargo.mleczki.pl).
        $server = new Server('0.0.0.0', 8080, SWOOLE_BASE);

        // One coroutine worker serves hundreds of requests per second, and every extra process costs
        // ~8 MB of private memory. Mail is sent from a coroutine (no task workers). Raise WORKER_NUM
        // only on a multi-core host with real traffic.
        $server->set([
            'worker_num' => max(1, (int) env('WORKER_NUM', '1')),
            'enable_static_handler' => true,
            'document_root' => dirname(__DIR__, 2) . '/public',
            'http_parse_post' => true,
            'http_parse_cookie' => false,
        ]);

        return $server;
    }
}
