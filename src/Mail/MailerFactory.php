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
        return new Mailer(Transport::fromDsn(self::normalizeDsn(env('MAILER_DSN', 'smtp://localhost:1025'))));
    }

    /**
     * Cleans up a DSN as it tends to arrive from a production env file: `docker run --env-file` keeps surrounding
     * quotes in values, and Google shows app passwords in groups separated by spaces. A DSN never legitimately
     * contains whitespace, so it is removed outright.
     */
    public static function normalizeDsn(string $dsn): string
    {
        return (string) preg_replace('/\s+/', '', trim($dsn, " \t\r\n\"'"));
    }
}
