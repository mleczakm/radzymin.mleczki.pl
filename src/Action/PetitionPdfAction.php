<?php

declare(strict_types=1);

namespace App\Action;

use App\Http\Responder;
use App\Runtime\WorkerServices;
use App\View\Renderer;
use Dompdf\Dompdf;
use Dompdf\Options;
use Swoole\Http\Request;
use Swoole\Http\Response;

/** Generates a blank, printable A4 signature sheet for offline (paper) collection of a petition's signatures. */
final class PetitionPdfAction
{
    public function __construct(private readonly WorkerServices $services, private readonly Renderer $view)
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

        $organizer = require dirname(__DIR__, 2) . '/config/organizer.php';

        $html = $this->view->render('pdf/lista', [
            'petition' => $petition,
            'organizer' => $organizer,
            'rows' => 28,
        ]);

        $options = new Options();
        $options->setIsRemoteEnabled(false);
        $options->setDefaultFont('DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $response->header('Content-Type', 'application/pdf');
        $response->header('Content-Disposition', 'attachment; filename="lista-podpisow-' . $petition->slug . '.pdf"');
        $response->end($dompdf->output());
    }
}
