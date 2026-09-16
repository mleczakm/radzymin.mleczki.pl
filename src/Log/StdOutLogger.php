<?php

declare(strict_types=1);

namespace App\Log;

use DateTimeImmutable;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Stringable;

/** Minimal PSR-3 logger writing one JSON line per record to stdout (picked up by `docker logs`). */
final class StdOutLogger extends AbstractLogger
{
    public function __construct(private readonly bool $debug)
    {
    }

    public function log($level, string|Stringable $message, array $context = []): void
    {
        if ($level === LogLevel::DEBUG && !$this->debug) {
            return;
        }

        $record = [
            'timestamp' => (new DateTimeImmutable())->format(DATE_ATOM),
            'level' => $level,
            'message' => (string) $message,
        ];

        if (isset($context['exception']) && $context['exception'] instanceof \Throwable) {
            $record['exception'] = (string) $context['exception'];
            unset($context['exception']);
        }

        if ($context !== []) {
            $record['context'] = $context;
        }

        fwrite(STDOUT, json_encode($record, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL);
    }
}
