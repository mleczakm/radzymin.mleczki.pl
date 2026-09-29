<?php

declare(strict_types=1);

namespace App\Action\Admin;

use App\Http\Responder;
use App\Runtime\WorkerServices;
use App\View\Renderer;
use Swoole\Http\Request;
use Swoole\Http\Response;

/** Page with browser-only tools for anonymizing text and PDFs; nothing is uploaded to the server. */
final class AnonymizeAction
{
    public function __construct(private readonly WorkerServices $services, private readonly Renderer $view)
    {
    }

    public function __invoke(Request $request, Response $response): void
    {
        if (!$this->services->adminAuth->check($request, $response)) {
            return;
        }

        Responder::html($response, $this->view->renderPage('admin/anonymize', [], 'Anonimizacja dokumentów'));
    }
}
