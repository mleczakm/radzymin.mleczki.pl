<?php

declare(strict_types=1);

namespace App\View;

use App\Domain\Petition;
use App\Runtime\WorkerServices;
use App\Security\Honeypot;

/** Shared render for the petition detail + sign form, used by both the GET and POST petition actions. */
final class PetitionFormView
{
    /**
     * @param array<string, string> $errors
     * @param array<string, string> $old
     */
    public static function render(
        Renderer $view,
        WorkerServices $services,
        Petition $petition,
        array $errors = [],
        array $old = [],
        ?string $notice = null,
    ): string {
        return $view->renderPage('petition', [
            'petition' => $petition,
            'confirmedCount' => $services->signatures->countConfirmed($petition->slug),
            'errors' => $errors,
            'old' => $old,
            'notice' => $notice,
            'timingToken' => $services->timingToken->generate(),
            'honeypotField' => Honeypot::FIELD_NAME,
        ], $petition->title);
    }
}
