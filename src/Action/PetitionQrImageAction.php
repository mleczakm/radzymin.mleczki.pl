<?php

declare(strict_types=1);

namespace App\Action;

use App\Content\PetitionQrCode;
use App\Runtime\WorkerServices;
use Swoole\Http\Request;
use Swoole\Http\Response;

final class PetitionQrImageAction
{
    public function __construct(private readonly WorkerServices $services)
    {
    }

    /** @param array<string, string> $params */
    public function __invoke(Request $request, Response $response, array $params): void
    {
        $petition = $this->services->petitions->find($params['slug']);
        if ($petition === null) {
            $response->status(404);
            $response->end('Nie znaleziono takiej petycji.');

            return;
        }

        $url = rtrim($this->services->baseUrl, '/') . '/petycja/' . rawurlencode($petition->slug);
        $response->header('Content-Type', 'image/svg+xml; charset=utf-8');
        $response->header('Cache-Control', 'public, max-age=3600');
        $response->end(PetitionQrCode::svg($url, 180));
    }
}
