<?php

declare(strict_types=1);

namespace App\Action;

use App\Runtime\WorkerServices;
use Swoole\Http\Request;
use Swoole\Http\Response;

final class SitemapAction
{
    public function __construct(private readonly WorkerServices $services)
    {
    }

    public function __invoke(Request $request, Response $response): void
    {
        $baseUrl = $this->services->baseUrl;
        $paths = ['/', '/o-mnie', '/polityka-prywatnosci'];

        foreach ($this->services->petitions->all() as $petition) {
            $paths[] = '/petycja/' . $petition->slug;
        }
        foreach ($this->services->topics->all() as $topic) {
            $paths[] = '/sprawy/' . $topic->slug;
        }

        $paths = array_values(array_unique($paths));
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($paths as $path) {
            $xml .= '  <url><loc>' . htmlspecialchars($baseUrl . $path, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc></url>' . "\n";
        }
        $xml .= '</urlset>';

        $response->header('Content-Type', 'application/xml; charset=utf-8');
        $response->end($xml);
    }
}
