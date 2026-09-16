<?php

declare(strict_types=1);

namespace App\Domain;

final class DuplicateSignatureException extends \RuntimeException
{
    public function __construct(public readonly string $petitionSlug, public readonly string $emailNormalized)
    {
        parent::__construct(sprintf(
            'Signature for petition "%s" and email "%s" already exists.',
            $petitionSlug,
            $emailNormalized,
        ));
    }
}
