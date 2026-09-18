<?php

declare(strict_types=1);

namespace App\Http;

use Swoole\Http\Request;
use Swoole\Http\Response;

final class Responder
{
    public static function html(Response $response, string $html, int $status = 200): void
    {
        $response->status($status);
        $response->header('Content-Type', 'text/html; charset=utf-8');
        $response->end($html);
    }

    /** Trusts X-Forwarded-For as set by the Cloudflare/Caddy proxy in front of the app in production. */
    public static function clientIp(Request $request): string
    {
        $forwarded = RequestInput::header($request, 'x-forwarded-for');
        if ($forwarded !== '') {
            return trim(explode(',', $forwarded)[0]);
        }

        return RequestInput::server($request, 'remote_addr') ?: '0.0.0.0';
    }
}
