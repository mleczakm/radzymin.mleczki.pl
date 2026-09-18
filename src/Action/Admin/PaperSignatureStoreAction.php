<?php

declare(strict_types=1);

namespace App\Action\Admin;

use App\Http\RequestInput;
use App\Http\Responder;
use App\Runtime\WorkerServices;
use App\View\Renderer;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * Bulk-inserts signatures transcribed by the admin from a printed paper list.
 * Each line of the textarea: "Imię Nazwisko;Miejscowość".
 */
final class PaperSignatureStoreAction
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

        $raw = RequestInput::stringFields(RequestInput::post($request))['lines'] ?? '';
        $added = 0;
        $skipped = [];

        foreach (preg_split('/\r\n|\r|\n/', $raw) ?: [] as $lineNumber => $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = array_map('trim', explode(';', $line, 2));
            $fullName = $parts[0];
            $city = $parts[1] ?? '';
            $nameParts = preg_split('/\s+/', $fullName, 2) ?: [];
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';

            if ($firstName === '' || $lastName === '' || $city === '') {
                $skipped[] = sprintf('Linia %d: „%s” (oczekiwano „Imię Nazwisko;Miejscowość”)', $lineNumber + 1, $line);
                continue;
            }

            $this->services->signatures->createPaper($petition->slug, $firstName, $lastName, $city);
            $added++;
        }

        Responder::html($response, $this->view->renderPage('admin/paper_form', [
            'petition' => $petition,
            'added' => $added,
            'skipped' => $skipped,
        ], 'Dopisz podpisy z listy papierowej'));
    }
}
