<?php

declare(strict_types=1);

namespace App\Content;

use League\CommonMark\CommonMarkConverter;
use Symfony\Component\Yaml\Yaml;

/**
 * Reads content files (YAML front matter + Markdown body). One instance reuses a single
 * CommonMark converter across all files; it is meant to be short-lived (worker boot only),
 * after which only the rendered HTML/parsed values stay in memory.
 */
final class MarkdownLoader
{
    private const FRONT_MATTER_PATTERN = '/^---\s*\n(.*?)\n---\s*\n?(.*)$/s';

    private readonly CommonMarkConverter $markdown;

    public function __construct()
    {
        $this->markdown = new CommonMarkConverter();
    }

    /** @return list<string> */
    public function files(string $dir): array
    {
        return glob(rtrim($dir, '/') . '/*.md') ?: [];
    }

    /** @param list<string> $requiredFields */
    public function load(string $file, array $requiredFields = []): MarkdownDocument
    {
        $raw = file_get_contents($file);
        if ($raw === false) {
            throw new \RuntimeException("Cannot read content file: $file");
        }

        $matches = [];
        if (!preg_match(self::FRONT_MATTER_PATTERN, $raw, $matches)) {
            throw new \RuntimeException("Content file is missing YAML front matter delimited by \"---\": $file");
        }

        $parsed = Yaml::parse($matches[1]);
        $frontMatter = is_array($parsed) ? $parsed : [];

        foreach ($requiredFields as $field) {
            $value = $frontMatter[$field] ?? null;
            if ($value === null || $value === '' || $value === false || $value === []) {
                throw new \RuntimeException("Content file \"$file\" is missing required front matter field \"$field\".");
            }
        }

        return new MarkdownDocument(
            $frontMatter,
            $this->markdown->convert(trim($matches[2]))->getContent(),
            $file,
        );
    }

    /** YAML parses an unquoted "2026-01-15" as a Unix timestamp (int), not a string — normalize either form. */
    public static function normalizeDate(mixed $date): ?string
    {
        return match (true) {
            $date instanceof \DateTimeInterface => $date->format('Y-m-d'),
            is_numeric($date) => date('Y-m-d', (int) $date),
            is_string($date) && $date !== '' => $date,
            default => null,
        };
    }
}
