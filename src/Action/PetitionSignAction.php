<?php

declare(strict_types=1);

namespace App\Action;

use App\Domain\DuplicateSignatureException;
use App\Http\RequestInput;
use App\Http\Responder;
use App\Runtime\WorkerServices;
use App\Security\Honeypot;
use App\Security\SignatureFormValidator;
use App\Task\SendConfirmationEmailTask;
use App\View\PetitionFormView;
use App\View\Renderer;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

final class PetitionSignAction
{
    public function __construct(
        private readonly Server $server,
        private readonly WorkerServices $services,
        private readonly Renderer $view,
        private readonly SignatureFormValidator $validator,
    ) {
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

        $rawPost = RequestInput::post($request);
        $post = RequestInput::stringFields($rawPost);
        $ipHash = $this->services->ipHasher->hash(Responder::clientIp($request));

        // Silently "succeed" for obvious bots (honeypot filled) so scripts don't learn to adapt.
        if (Honeypot::looksLikeBot($rawPost)) {
            $this->services->logger->info('Rejected submission: honeypot triggered', ['petition' => $petition->slug]);
            Responder::html($response, $this->view->renderPage('thank_you', ['petition' => $petition]));

            return;
        }

        if ($this->services->rateLimiter->tooManyAttempts($ipHash)) {
            Responder::html($response, PetitionFormView::render(
                $this->view,
                $this->services,
                $petition,
                errors: ['_global' => 'Zbyt wiele prób w krótkim czasie. Spróbuj ponownie za godzinę.'],
                old: $post,
            ), 429);

            return;
        }

        if (!$this->services->timingToken->isValid($post['timing_token'] ?? null)) {
            Responder::html($response, PetitionFormView::render(
                $this->view,
                $this->services,
                $petition,
                errors: ['_global' => 'Formularz wygasł lub został przesłany zbyt szybko. Spróbuj ponownie.'],
                old: $post,
            ), 400);

            return;
        }

        $errors = $this->validator->validate($post);

        if ($errors !== []) {
            Responder::html($response, PetitionFormView::render($this->view, $this->services, $petition, $errors, $post), 422);

            return;
        }

        try {
            $signature = $this->services->signatures->createOnline(
                petitionSlug: $petition->slug,
                firstName: trim($post['first_name']),
                lastName: trim($post['last_name']),
                city: trim($post['city']),
                email: trim($post['email']),
                ipHash: $ipHash,
            );
        } catch (DuplicateSignatureException) {
            Responder::html($response, PetitionFormView::render(
                $this->view,
                $this->services,
                $petition,
                errors: ['_global' => 'Ten adres e-mail już podpisał tę petycję (lub oczekuje na potwierdzenie).'],
                old: $post,
            ), 409);

            return;
        }

        $this->server->task(new SendConfirmationEmailTask($signature->id));

        Responder::html($response, $this->view->renderPage('thank_you', ['petition' => $petition]));
    }
}
