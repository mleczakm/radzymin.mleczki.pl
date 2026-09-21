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

        $server->set([
            'worker_num' => (int) env('WORKER_NUM', '2'),
            'task_worker_num' => (int) env('TASK_WORKER_NUM', '2'),
            'task_enable_coroutine' => true,
            'enable_static_handler' => true,
            'document_root' => dirname(__DIR__, 2) . '/public',
            'http_parse_post' => true,
            'http_parse_cookie' => false,
        ]);

        return $server;
    }
}
