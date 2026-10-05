<?php

declare(strict_types=1);

namespace App\Action;

use App\Http\Responder;
use App\Runtime\WorkerServices;
use App\View\Renderer;
use App\Content\PetitionQrCode;
use Swoole\Http\Request;
use Swoole\Http\Response;

final class PetitionQrAction
{
    public function __construct(private readonly WorkerServices $services, private readonly Renderer $view)
    {
    }

    /** @param array<string, string> $params */
    public function __invoke(Request $request, Response $response, array $params): void
    {
        $petition = $this->services->petitions->find($params['slug']);

        if ($petition === null) {
            Responder::html($response, $this->view->renderPage('error', [
                'message' => 'Nie znaleziono takiej petycji.',
            ], 'Nie znaleziono'), 404);

            return;
        }

        $url = rtrim($this->services->baseUrl, '/') . '/petycja/' . rawurlencode($petition->slug);
        Responder::html($response, $this->view->render('print/qr', [
            'petition' => $petition,
            'petitionUrl' => $url,
            'qrDataUri' => PetitionQrCode::dataUri($url),
        ]));
    }
}
