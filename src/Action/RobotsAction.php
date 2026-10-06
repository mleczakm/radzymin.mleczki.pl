<?php

declare(strict_types=1);

namespace App\Action;

use Swoole\Http\Request;
use Swoole\Http\Response;

final class RobotsAction
{
    public function __construct(private readonly string $baseUrl)
    {
    }

    public function __invoke(Request $request, Response $response): void
    {
        $response->header('Content-Type', 'text/plain; charset=utf-8');
        $response->end("User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /zdrowie\nDisallow: /petycja/*/lista\nDisallow: /petycja/*/qr\nDisallow: /petycja/*/qr.svg\nDisallow: /potwierdz/\n\nSitemap: " . rtrim($this->baseUrl, '/') . "/sitemap.xml\n");
    }
}
