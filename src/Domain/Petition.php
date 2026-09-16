<?php

declare(strict_types=1);

namespace App\Domain;

final class Petition
{
    public function __construct(
        public readonly string $slug,
        public readonly string $title,
        public readonly string $lead,
        public readonly string $bodyHtml,
        public readonly string $createdAt,
    ) {
    }
}
