<?php

declare(strict_types=1);

namespace App\Action;

use App\Http\Responder;
use App\Runtime\WorkerServices;
use App\View\Renderer;
use Swoole\Http\Request;
use Swoole\Http\Response;

final class TopicShowAction
{
    public function __construct(private readonly WorkerServices $services, private readonly Renderer $view)
    {
    }

    /** @param array<string, string> $params */
    public function __invoke(Request $request, Response $response, array $params): void
    {
        $topic = $this->services->topics->find($params['slug']);

        if ($topic === null) {
            Responder::html($response, $this->view->renderPage('error', [
                'message' => 'Nie znaleziono takiej sprawy.',
            ], 'Nie znaleziono'), 404);

            return;
        }

        Responder::html($response, $this->view->renderPage('topic', ['topic' => $topic], $topic->title, $topic->summary));
    }
}
