<?php

declare(strict_types=1);

namespace App\Server;

use Swoole\Http\Server;

final class Factory
{
    public static function create(string $environment): Server
    {
        $isDev = $environment === 'dev';
        $server = new Server('0.0.0.0', $isDev ? 8080 : 80, SWOOLE_BASE);

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
