<?php

declare(strict_types=1);

namespace App\Storage;

use PDO;

final class Database
{
    /**
     * Opens a per-process PDO connection and applies migrations.
     *
     * Must only be called after the Swoole worker process has forked
     * (e.g. from an onWorkerStart/onTask handler) — SQLite file handles
     * are not safe to share across forked processes.
     */
    public static function connect(string $path): PDO
    {
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0o775, true);
        }

        $pdo = new PDO('sqlite:' . $path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // WAL mode is deliberately not used: it needs shared-memory-mapped -wal/-shm files,
        // which break with "disk I/O error" over FUSE/virtiofs bind mounts (e.g. Docker
        // Desktop on macOS) and some VPS overlay filesystems. Traffic here is low enough
        // that the default rollback journal + busy_timeout retries are plenty.
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA busy_timeout = 5000');

        self::migrate($pdo);

        return $pdo;
    }

    private static function migrate(PDO $pdo): void
    {
        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS signatures (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                petition_slug TEXT NOT NULL,
                first_name TEXT NOT NULL,
                last_name TEXT NOT NULL,
                city TEXT NOT NULL,
                email TEXT NULL,
                email_normalized TEXT NULL,
                token TEXT NULL,
                status TEXT NOT NULL DEFAULT 'pending',
                source TEXT NOT NULL DEFAULT 'online',
                ip_hash TEXT NULL,
                created_at TEXT NOT NULL,
                confirmed_at TEXT NULL
            )
            SQL);

        $pdo->exec(
            'CREATE UNIQUE INDEX IF NOT EXISTS idx_signatures_token
                ON signatures(token) WHERE token IS NOT NULL'
        );
        $pdo->exec(
            'CREATE UNIQUE INDEX IF NOT EXISTS idx_signatures_petition_email
                ON signatures(petition_slug, email_normalized) WHERE email_normalized IS NOT NULL'
        );
        $pdo->exec(
            'CREATE INDEX IF NOT EXISTS idx_signatures_petition_status
                ON signatures(petition_slug, status)'
        );
    }
}
