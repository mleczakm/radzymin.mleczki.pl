<?php

declare(strict_types=1);

namespace App\Security;

use Swoole\Table;

/**
 * Per-IP sliding-window limiter backed by a Swoole\Table, which lives in
 * shared memory so counts are consistent across all HTTP worker processes.
 */
final class RateLimiter
{
    private const WINDOW_SECONDS = 3600;
    private const MAX_ATTEMPTS = 5;

    public function __construct(private readonly Table $table)
    {
    }

    public static function createTable(int $rows = 65_536): Table
    {
        $table = new Table($rows);
        $table->column('count', Table::TYPE_INT);
        $table->column('window_start', Table::TYPE_INT);
        $table->create();

        return $table;
    }

    public function tooManyAttempts(string $ipHash): bool
    {
        $now = time();
        $row = $this->table->get($ipHash);

        if (!is_array($row) || ($now - (int) $row['window_start']) > self::WINDOW_SECONDS) {
            $this->table->set($ipHash, ['count' => 1, 'window_start' => $now]);

            return false;
        }

        if ((int) $row['count'] >= self::MAX_ATTEMPTS) {
            return true;
        }

        $this->table->incr($ipHash, 'count', 1);

        return false;
    }
}
