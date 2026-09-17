<?php

declare(strict_types=1);

namespace App\Domain;

final class Signature
{
    public function __construct(
        public readonly int $id,
        public readonly string $petitionSlug,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $city,
        public readonly ?string $email,
        public readonly ?string $token,
        public readonly SignatureStatus $status,
        public readonly SignatureSource $source,
        public readonly string $createdAt,
        public readonly ?string $confirmedAt,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromRow(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            petitionSlug: (string) $row['petition_slug'],
            firstName: (string) $row['first_name'],
            lastName: (string) $row['last_name'],
            city: (string) $row['city'],
            email: $row['email'] !== null ? (string) $row['email'] : null,
            token: $row['token'] !== null ? (string) $row['token'] : null,
            status: SignatureStatus::from((string) $row['status']),
            source: SignatureSource::from((string) $row['source']),
            createdAt: (string) $row['created_at'],
            confirmedAt: $row['confirmed_at'] !== null ? (string) $row['confirmed_at'] : null,
        );
    }

    public function fullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    /** First name + last-initial for public display, e.g. "Jan K." — never the full surname. */
    public function publicDisplayName(): string
    {
        $initial = mb_substr(trim($this->lastName), 0, 1);

        return trim(trim($this->firstName) . ($initial !== '' ? ' ' . $initial . '.' : ''));
    }
}
