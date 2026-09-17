<?php

declare(strict_types=1);

namespace App\Domain;

use League\CommonMark\CommonMarkConverter;
use Symfony\Component\Yaml\Yaml;

/**
 * Loads petitions from Markdown files (YAML front matter + Markdown body) in
 * content/petitions/*.md. Parsing happens once per worker process, in the
 * constructor — the rendered HTML is cached in the Petition objects for the
 * lifetime of the worker instead of being re-parsed on every request.
 */
final class PetitionRepository
{
    private const FRONT_MATTER_PATTERN = '/^---\s*\n(.*?)\n---\s*\n?(.*)$/s';

    /** @var array<string, Petition> */
    private array $petitions = [];

    public function __construct(string $contentDir)
    {
        $markdown = new CommonMarkConverter();

        foreach (glob(rtrim($contentDir, '/') . '/*.md') ?: [] as $file) {
            $petition = self::parseFile($file, $markdown);
            $this->petitions[$petition->slug] = $petition;
        }
    }

    private static function parseFile(string $file, CommonMarkConverter $markdown): Petition
    {
        $raw = file_get_contents($file);
        if ($raw === false) {
            throw new \RuntimeException("Cannot read petition file: $file");
        }

        if (!preg_match(self::FRONT_MATTER_PATTERN, $raw, $matches)) {
            throw new \RuntimeException("Petition file is missing YAML front matter delimited by \"---\": $file");
        }

        /** @var array<string, mixed> $frontMatter */
        $frontMatter = Yaml::parse($matches[1]) ?? [];

        foreach (['slug', 'title', 'lead'] as $required) {
            if (empty($frontMatter[$required])) {
                throw new \RuntimeException("Petition file \"$file\" is missing required front matter field \"$required\".");
            }
        }

        $goal = $frontMatter['goal'] ?? null;

        return new Petition(
            slug: (string) $frontMatter['slug'],
            title: (string) $frontMatter['title'],
            lead: (string) $frontMatter['lead'],
            bodyHtml: (string) $markdown->convert(trim($matches[2]))->getContent(),
            createdAt: self::normalizeDate($frontMatter['createdAt'] ?? null)
                ?? date('Y-m-d', filemtime($file) ?: time()),
            goal: is_numeric($goal) ? (int) $goal : null,
            deadline: self::normalizeDate($frontMatter['deadline'] ?? null),
        );
    }

    /** YAML parses an unquoted "2026-01-15" as a Unix timestamp (int), not a string — normalize either form. */
    private static function normalizeDate(mixed $date): ?string
    {
        return match (true) {
            $date instanceof \DateTimeInterface => $date->format('Y-m-d'),
            is_numeric($date) => date('Y-m-d', (int) $date),
            is_string($date) && $date !== '' => $date,
            default => null,
        };
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
