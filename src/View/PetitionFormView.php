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
        $confirmedCount = $services->signatures->countConfirmed($petition->slug);

        $progressHtml = $view->render('_progress', [
            'petition' => $petition,
            'confirmedCount' => $confirmedCount,
            'percent' => $petition->progressPercent($confirmedCount),
            'daysRemaining' => $petition->daysRemaining(),
        ]);

        $recentSignaturesHtml = $view->render('_recent_signatures', [
            'recentSignatures' => $services->signatures->recentConfirmed($petition->slug),
        ]);

        return $view->renderPage('petition', [
            'petition' => $petition,
            'progressHtml' => $progressHtml,
            'recentSignaturesHtml' => $recentSignaturesHtml,
            'errors' => $errors,
            'old' => $old,
            'notice' => $notice,
            'timingToken' => $services->timingToken->generate(),
            'honeypotField' => Honeypot::FIELD_NAME,
        ], $petition->title, $petition->lead);
    }
}
