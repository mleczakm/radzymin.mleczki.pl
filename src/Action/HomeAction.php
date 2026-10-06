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
        $topics = $this->services->topics->all();

        $counts = [];
        $percents = [];
        foreach ($petitions as $petition) {
            // Not the array key: PHP turns a numeric slug such as "2026" into an int key.
            $counts[$petition->slug] = $this->services->signatures->countConfirmed($petition->slug);
            $percents[$petition->slug] = $petition->progressPercent($counts[$petition->slug]);
        }

        $html = $this->view->renderPage('home', [
            'petitions' => $petitions,
            'counts' => $counts,
            'percents' => $percents,
            'featured' => $petitions === [] ? null : reset($petitions),
            'topics' => $topics,
        ], 'Radzymińskie Petycje — podpisz i zaangażuj się',
            'Petycja mieszkańców Radzymina, udokumentowane sprawy lokalne i konkretne sposoby działania. Poznaj inicjatywę Michała Mleczki.',
            fullWidth: true,
            canonicalPath: '/',
        );

        Responder::html($response, $html);
    }
}
