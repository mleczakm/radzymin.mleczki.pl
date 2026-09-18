<?php

declare(strict_types=1);

namespace App\Domain;

use App\Content\MarkdownLoader;

/**
 * Loads petitions from Markdown files (YAML front matter + Markdown body) in
 * content/petitions/*.md. Parsing happens once per worker process, in the
 * constructor — the rendered HTML is cached in the Petition objects for the
 * lifetime of the worker instead of being re-parsed on every request.
 */
final class PetitionRepository
{
    /** @var array<string, Petition> */
    private array $petitions = [];

    public function __construct(string $contentDir)
    {
        $loader = new MarkdownLoader();

        foreach ($loader->files($contentDir) as $file) {
            $document = $loader->load($file, ['slug', 'title', 'lead']);
            $frontMatter = $document->frontMatter;
            $goal = $frontMatter['goal'] ?? null;

            $petition = new Petition(
                slug: (string) $frontMatter['slug'],
                title: (string) $frontMatter['title'],
                lead: (string) $frontMatter['lead'],
                bodyHtml: $document->html,
                createdAt: MarkdownLoader::normalizeDate($frontMatter['createdAt'] ?? null)
                    ?? date('Y-m-d', filemtime($file) ?: time()),
                goal: is_numeric($goal) ? (int) $goal : null,
                deadline: MarkdownLoader::normalizeDate($frontMatter['deadline'] ?? null),
            );

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
