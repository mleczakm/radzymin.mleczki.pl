<?php

declare(strict_types=1);

namespace App\Action;

use App\Domain\SignatureStatus;
use App\Http\Responder;
use App\Runtime\WorkerServices;
use App\View\Renderer;
use DateTimeImmutable;
use Swoole\Http\Request;
use Swoole\Http\Response;

final class ConfirmAction
{
    private const TOKEN_TTL_HOURS = 48;

    public function __construct(private readonly WorkerServices $services, private readonly Renderer $view)
    {
    }

    /** @param array<string, string> $params */
    public function __invoke(Request $request, Response $response, array $params): void
    {
        $signature = $this->services->signatures->findByToken($params['token']);

        if ($signature === null) {
            Responder::html($response, $this->view->renderPage('error', [
                'message' => 'Link jest nieprawidłowy. Sprawdź, czy skopiowałeś/aś go w całości.',
            ], 'Nieprawidłowy link'), 404);

            return;
        }

        $petition = $this->services->petitions->find($signature->petitionSlug);

        if ($signature->status === SignatureStatus::Confirmed) {
            Responder::html($response, $this->view->renderPage('confirmed', [
                'petition' => $petition,
                'alreadyConfirmed' => true,
                'confirmedCount' => $petition !== null ? $this->services->signatures->countConfirmed($petition->slug) : null,
            ]));

            return;
        }

        $createdAt = new DateTimeImmutable($signature->createdAt);
        $expired = $createdAt->modify('+' . self::TOKEN_TTL_HOURS . ' hours') < new DateTimeImmutable();

        if ($expired) {
            Responder::html($response, $this->view->renderPage('error', [
                'message' => 'Ten link do potwierdzenia wygasł. Podpisz petycję ponownie, aby otrzymać nowy.',
            ], 'Link wygasł'), 410);

            return;
        }

        $this->services->signatures->confirm($signature);

        Responder::html($response, $this->view->renderPage('confirmed', [
            'petition' => $petition,
            'alreadyConfirmed' => false,
            'confirmedCount' => $petition !== null ? $this->services->signatures->countConfirmed($petition->slug) : null,
        ]));
    }
}
