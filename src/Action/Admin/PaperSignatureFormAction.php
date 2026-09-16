<?php

declare(strict_types=1);

namespace App\Action\Admin;

use App\Http\Responder;
use App\Runtime\WorkerServices;
use App\View\Renderer;
use Swoole\Http\Request;
use Swoole\Http\Response;

final class PaperSignatureFormAction
{
    public function __construct(private readonly WorkerServices $services, private readonly Renderer $view)
    {
    }

    /** @param array<string, string> $params */
    public function __invoke(Request $request, Response $response, array $params): void
    {
        if (!$this->services->adminAuth->check($request, $response)) {
            return;
        }

        $petition = $this->services->petitions->find($params['slug']);

        if ($petition === null) {
            $response->status(404);
            $response->end('Nie znaleziono takiej petycji.');

            return;
        }

        Responder::html($response, $this->view->renderPage('admin/paper_form', [
            'petition' => $petition,
            'added' => null,
            'skipped' => [],
        ], 'Dopisz podpisy z listy papierowej'));
    }
}
