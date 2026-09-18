<?php

declare(strict_types=1);

namespace App\Security;

/** Hashes client IPs with a server-side pepper so raw IPs are never stored or used as table keys. */
final class IpHasher
{
    public function __construct(#[\SensitiveParameter] private readonly string $secret)
    {
    }

    public function hash(string $ip): string
    {
        return substr(hash_hmac('sha256', $ip, $this->secret), 0, 32);
    }
}
