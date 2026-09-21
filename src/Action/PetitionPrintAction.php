<?php

declare(strict_types=1);

namespace App\Action;

use App\Http\Responder;
use App\Runtime\WorkerServices;
use App\View\Renderer;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * Shows a blank signature sheet as a plain HTML page styled for A4 printing, for offline (paper)
 * collection of a petition's signatures. The browser does the printing, so there is no PDF
 * generation on the server.
 */
final class PetitionPrintAction
{
    /** Empty rows on the sheet; sized so title, notice and table fit one A4 page. */
    private const ROWS = 22;

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

        Responder::html($response, $this->view->render('print/lista', [
            'petition' => $petition,
            'rows' => self::ROWS,
        ]));
    }
}
