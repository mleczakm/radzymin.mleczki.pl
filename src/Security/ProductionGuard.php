<?php

declare(strict_types=1);

namespace App\Security;

/**
 * The repository's .env holds public, insecure development defaults. This makes sure they
 * can't silently end up serving production (e.g. by pasting .env into the DOTENV secret).
 */
final class ProductionGuard
{
    public const DEV_SECRET_PREFIX = 'dev-insecure-';
    // The dev password is public on purpose (see .env); it is only compared against, never used.
    // @mago-ignore lint:no-literal-password
    private const DEV_ADMIN_PASSWORD = 'admin';

    public static function assertSafe(
        string $environment,
        #[\SensitiveParameter] string $appSecret,
        #[\SensitiveParameter] string $adminPasswordHash,
    ): void {
        if ($environment === 'dev') {
            return;
        }

        if (str_starts_with($appSecret, self::DEV_SECRET_PREFIX)) {
            throw new \RuntimeException(
                'Refusing to start: APP_SECRET is the public development default. '
                . 'Generate one with: php -r "echo bin2hex(random_bytes(32));"'
            );
        }

        if (password_verify(self::DEV_ADMIN_PASSWORD, $adminPasswordHash)) {
            throw new \RuntimeException(
                'Refusing to start: ADMIN_PASSWORD_HASH is the hash of the public development password. '
                . 'Generate your own with: bin/hash-password "your-password"'
            );
        }
    }
}
