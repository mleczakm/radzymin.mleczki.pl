<?php

declare(strict_types=1);

namespace App\Action;

use App\Http\Responder;
use App\Runtime\WorkerServices;
use App\View\Renderer;
use Swoole\Http\Request;
use Swoole\Http\Response;

final class HomeAction
{
    public function __construct(private readonly WorkerServices $services, private readonly Renderer $view)
    {
    }

    public function __invoke(Request $request, Response $response): void
    {
        $petitions = $this->services->petitions->all();
        $counts = [];
        foreach ($petitions as $slug => $petition) {
            $counts[$slug] = $this->services->signatures->countConfirmed($slug);
        }

        $html = $this->view->renderPage('home', [
            'petitions' => $petitions,
            'counts' => $counts,
            'topics' => $this->services->topics->all(),
        ], 'Petycje Radzymin');

        Responder::html($response, $html);
    }
}
