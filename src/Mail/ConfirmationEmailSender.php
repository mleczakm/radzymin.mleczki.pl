<?php

declare(strict_types=1);

namespace App\Task;

use App\Runtime\WorkerServices;
use Swoole\Http\Server;
use Swoole\Server\Task;

/** Runs in task worker processes; keeps slow SMTP I/O off the HTTP request/response path. */
final class Handler
{
    public function __construct(private readonly WorkerServices $services)
    {
    }

    public function handle(Server $server, Task $task): void
    {
        $data = $task->data;

        if (!$data instanceof SendConfirmationEmailTask) {
            return;
        }

        try {
            $signature = $this->services->signatures->findById($data->signatureId);
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
