<?php

declare(strict_types=1);

namespace App\Security;

use Swoole\Http\Request;
use Swoole\Http\Response;

/** Guards the /admin area with a single HTTP Basic Auth account configured via env vars. */
final class BasicAuth
{
    public function __construct(
        private readonly string $username,
        private readonly string $passwordHash,
    ) {
    }

    public function check(Request $request, Response $response): bool
    {
        $header = $request->header['authorization'] ?? '';

        if (str_starts_with($header, 'Basic ')) {
            $decoded = base64_decode(substr($header, 6), true);
            if ($decoded !== false && str_contains($decoded, ':')) {
                [$user, $pass] = explode(':', $decoded, 2);
                if (hash_equals($this->username, $user) && password_verify($pass, $this->passwordHash)) {
                    return true;
                }
            }
        }

        $response->status(401);
        $response->header('WWW-Authenticate', 'Basic realm="Panel administracyjny"');
        $response->end('Wymagane logowanie.');

        return false;
    }
}
