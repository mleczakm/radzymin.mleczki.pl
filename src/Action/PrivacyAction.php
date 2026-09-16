<?php

declare(strict_types=1);

namespace App\Action;

use App\Http\Responder;
use App\View\Renderer;
use Swoole\Http\Request;
use Swoole\Http\Response;

final class PrivacyAction
{
    public function __construct(private readonly Renderer $view)
    {
    }

    public function __invoke(Request $request, Response $response): void
    {
        $organizer = require dirname(__DIR__, 2) . '/config/organizer.php';

        Responder::html($response, $this->view->renderPage('privacy', [
            'organizer' => $organizer,
        ], 'Polityka prywatności'));
    }
}
