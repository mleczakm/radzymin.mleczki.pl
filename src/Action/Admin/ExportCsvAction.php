<?php

declare(strict_types=1);

namespace App\Action\Admin;

use App\Runtime\WorkerServices;
use Swoole\Http\Request;
use Swoole\Http\Response;

final class ExportCsvAction
{
    public function __construct(private readonly WorkerServices $services)
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

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Polish diacritics correctly.
        fputcsv($handle, ['Imię', 'Nazwisko', 'Miejscowość', 'E-mail', 'Źródło', 'Data potwierdzenia'], ';');

        foreach ($this->services->signatures->allConfirmed($petition->slug) as $signature) {
            fputcsv($handle, [
                $signature->firstName,
                $signature->lastName,
                $signature->city,
                $signature->email ?? '',
                $signature->source->value === 'paper' ? 'papierowo' : 'online',
                $signature->confirmedAt ?? '',
            ], ';');
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $response->header('Content-Type', 'text/csv; charset=utf-8');
        $response->header('Content-Disposition', 'attachment; filename="podpisy-' . $petition->slug . '.csv"');
        $response->end($csv);
    }
}
