<?php

declare(strict_types=1);

namespace App\Action;

use App\Domain\TopicStatus;
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
        foreach ($petitions as $slug => $petition) {
            $counts[$slug] = $this->services->signatures->countConfirmed($slug);
            $percents[$slug] = $petition->progressPercent($counts[$slug]);
        }

        $activeTopics = array_filter(
            $topics,
            static fn ($topic): bool => in_array($topic->status, [TopicStatus::InProgress, TopicStatus::Waiting], true),
        );

        $html = $this->view->renderPage('home', [
            'petitions' => $petitions,
            'counts' => $counts,
            'percents' => $percents,
            'featured' => $petitions === [] ? null : reset($petitions),
            'totalSignatures' => array_sum($counts),
            'activeTopicCount' => count($activeTopics),
            'topics' => $topics,
            'about' => $this->services->about,
        ], 'Petycje Radzymin — podpisz i zaangażuj się',
            'Podpisz petycję w kilka minut, pomóż zebrać podpisy i napisz do mnie. Niezależna inicjatywa mieszkańców Radzymina.',
            fullWidth: true,
        );

        Responder::html($response, $html);
    }
}
