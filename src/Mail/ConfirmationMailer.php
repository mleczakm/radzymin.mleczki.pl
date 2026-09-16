<?php

declare(strict_types=1);

namespace App\Mail;

use App\Domain\Petition;
use App\Domain\Signature;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class ConfirmationMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $fromAddress,
        private readonly string $baseUrl,
    ) {
    }

    public function sendConfirmation(Signature $signature, Petition $petition): void
    {
        if ($signature->email === null || $signature->token === null) {
            throw new \LogicException('Cannot send a confirmation e-mail for a signature without an email/token.');
        }

        $link = rtrim($this->baseUrl, '/') . '/potwierdz/' . $signature->token;

        $text = "Cześć {$signature->firstName},\n\n"
            . "Aby potwierdzić podpisanie petycji \"{$petition->title}\", kliknij w poniższy link:\n"
            . "$link\n\n"
            . "Link jest ważny przez 48 godzin. Jeśli to nie Ty podpisywałeś/aś tę petycję, "
            . "po prostu zignoruj tę wiadomość — Twój podpis nie zostanie zaliczony bez potwierdzenia.\n";

        $html = sprintf(
            '<p>Cześć %s,</p>'
                . '<p>Aby potwierdzić podpisanie petycji „<strong>%s</strong>”, kliknij w poniższy link:</p>'
                . '<p><a href="%s">%s</a></p>'
                . '<p>Link jest ważny przez 48 godzin. Jeśli to nie Ty podpisywałeś/aś tę petycję, '
                . 'zignoruj tę wiadomość — podpis nie zostanie zaliczony bez potwierdzenia.</p>',
            e($signature->firstName),
            e($petition->title),
            e($link),
            e($link),
        );

        $email = (new Email())
            ->from(new Address($this->fromAddress, 'Petycje Radzymin'))
            ->to($signature->email)
            ->subject('Potwierdź podpis pod petycją: ' . $petition->title)
            ->text($text)
            ->html($html);

        $this->mailer->send($email);
    }
}
