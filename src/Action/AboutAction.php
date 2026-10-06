<?php

declare(strict_types=1);

namespace App\Action;

use App\Http\Responder;
use App\Runtime\WorkerServices;
use App\View\Renderer;
use Swoole\Http\Request;
use Swoole\Http\Response;

final class AboutAction
{
    public function __construct(private readonly WorkerServices $services, private readonly Renderer $view)
    {
    }

    public function __invoke(Request $request, Response $response): void
    {
        $about = $this->services->about;

        if ($about === null) {
            Responder::html($response, $this->view->renderPage('error', ['message' => 'Nie znaleziono strony.'], 'Nie znaleziono'), 404);

            return;
        }

        Responder::html($response, $this->view->renderPage(
            'about',
            ['about' => $about],
            $about['heading'] . ' — Radzymińskie Petycje',
            'Poznaj Michała Mleczkę, mieszkańca Radzymina i autora niezależnej inicjatywy społecznej.',
            canonicalPath: '/o-mnie',
        ));
    }
}
