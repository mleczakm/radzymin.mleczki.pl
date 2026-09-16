<?php

declare(strict_types=1);

namespace App\Domain;

final class PetitionRepository
{
    /** @var array<string, Petition> */
    private array $petitions = [];

    public function __construct(string $configFile)
    {
        /** @var list<array{slug: string, title: string, lead: string, body: string, createdAt: string}> $data */
        $data = require $configFile;

        foreach ($data as $item) {
            $petition = Petition::fromArray($item);
            $this->petitions[$petition->slug] = $petition;
        }
    }

    /** @return array<string, Petition> */
    public function all(): array
    {
        return $this->petitions;
    }

    public function find(string $slug): ?Petition
    {
        return $this->petitions[$slug] ?? null;
    }
}
