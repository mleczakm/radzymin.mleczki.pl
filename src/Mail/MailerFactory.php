<?php

declare(strict_types=1);

namespace App\Mail;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;

final class MailerFactory
{
    public static function create(): MailerInterface
    {
        $dsn = env('MAILER_DSN', 'smtp://localhost:1025');

        return new Mailer(Transport::fromDsn($dsn));
    }
}
