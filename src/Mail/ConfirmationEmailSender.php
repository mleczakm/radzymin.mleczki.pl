<?php

declare(strict_types=1);

namespace App\Mail;

use App\Runtime\WorkerServices;

/**
 * Sends the double opt-in e-mail for a stored signature. Meant to run in its own coroutine
 * (see PetitionSignAction): Swoole's runtime hooks make the SMTP socket I/O yield instead of
 * block, so a slow mail server never holds up other requests — and no extra task worker
 * processes are needed. Failures are logged, never thrown into the request.
 */
final class ConfirmationEmailSender
{
    public function __construct(private readonly WorkerServices $services)
    {
    }

    public function send(int $signatureId): void
    {
        try {
            $signature = $this->services->signatures->findById($signatureId);
            $petition = $this->services->petitions->find($signature->petitionSlug);

            if ($petition === null) {
                $this->services->logger->error('Cannot send confirmation e-mail: unknown petition', [
                    'signature_id' => $signature->id,
                    'petition_slug' => $signature->petitionSlug,
                ]);

                return;
            }

            $this->services->mailer->sendConfirmation($signature, $petition);
        } catch (\Throwable $e) {
            $this->services->logger->error('Failed to send confirmation e-mail', ['exception' => $e]);
        }
    }
}
