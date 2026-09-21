<?php

declare(strict_types=1);

namespace App\Security;

use Swoole\Table;

/**
 * Per-IP fixed-window limiter backed by a Swoole\Table, which lives in shared memory so counts
 * are consistent across all HTTP worker processes.
 *
 * The table is allocated (and zeroed) up front, so it is kept small: it only needs one row per
 * distinct client that submitted the form within the last hour. Expired rows are purged before
 * the table fills up; if it is still full (a flood from thousands of addresses), new clients are
 * simply not tracked rather than blocked.
 */
final class RateLimiter
{
    private const WINDOW_SECONDS = 3600;
    private const MAX_ATTEMPTS = 5;

    /** Swoole\Table starts refusing writes at roughly 2/3 of its nominal size; purge before that. */
    private const PURGE_AT_FILL = 0.55;

    /** @var \Closure(): int */
    private readonly \Closure $clock;

    /** @param (\Closure(): int)|null $clock injectable for tests; defaults to the wall clock */
    public function __construct(private readonly Table $table, ?\Closure $clock = null)
    {
        $this->clock = $clock ?? time(...);
    }

    /** 4096 rows use ~0.5 MB; 65 536 would pin ~8.6 MB of shared memory for a site this size. */
    public static function createTable(int $rows = 4_096): Table
    {
        $table = new Table($rows);
        $table->column('count', Table::TYPE_INT);
        $table->column('window_start', Table::TYPE_INT);
        $table->create();

        return $table;
    }

    public function tooManyAttempts(string $ipHash): bool
    {
        $now = ($this->clock)();
        $row = $this->table->get($ipHash);

        if (!is_array($row) || ($now - (int) $row['window_start']) > self::WINDOW_SECONDS) {
            $this->startWindow($ipHash, $now);

            return false;
        }

        if ((int) $row['count'] >= self::MAX_ATTEMPTS) {
            return true;
        }

        $this->table->incr($ipHash, 'count', 1);

        return false;
    }

    private function startWindow(string $ipHash, int $now): void
    {
        if ($this->isNearlyFull()) {
            $this->purgeExpired($now);
        }

        // An existing row is overwritten in place; only a brand-new key needs a free slot.
        if ($this->table->exists($ipHash) || !$this->isNearlyFull()) {
            $this->table->set($ipHash, ['count' => 1, 'window_start' => $now]);
        }
    }

    private function isNearlyFull(): bool
    {
        return $this->table->count() >= (int) ($this->table->getSize() * self::PURGE_AT_FILL);
    }

    private function purgeExpired(int $now): void
    {
        $expired = [];
        foreach ($this->table as $key => $row) {
            if (!is_array($row) || ($now - (int) $row['window_start']) <= self::WINDOW_SECONDS) {
                continue;
            }

            $expired[] = $key;
        }

        foreach ($expired as $key) {
            $this->table->del($key);
        }
    }
}
