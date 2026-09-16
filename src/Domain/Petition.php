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

    /** @param array{slug: string, title: string, lead: string, body: string, createdAt: string} $data */
    public static function fromArray(array $data): self
    {
        return new self(
            slug: $data['slug'],
            title: $data['title'],
            lead: $data['lead'],
            bodyHtml: $data['body'],
            createdAt: $data['createdAt'],
        );
    }
}
