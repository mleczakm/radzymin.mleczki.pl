<?php

declare(strict_types=1);

namespace App\Domain;

use App\Content\MarkdownDocument;
use App\Content\MarkdownLoader;

/**
 * Loads topics from content/topics/*.md (YAML front matter + Markdown body). Like
 * PetitionRepository, everything is parsed once per worker at boot and cached in memory;
 * the list is also sorted once here (active matters first, then by last update, newest first).
 */
final class TopicRepository
{
    /** @var array<string, Topic> */
    private array $topics = [];

    public function __construct(string $contentDir)
    {
        $loader = new MarkdownLoader();
        $topics = [];

        foreach ($loader->files($contentDir) as $file) {
            $topic = self::parseTopic($loader->load($file, ['slug', 'title', 'summary', 'status']));
            $topics[$topic->slug] = $topic;
        }

        uasort($topics, Topic::compare(...));

        $this->topics = $topics;
    }

    private static function parseTopic(MarkdownDocument $document): Topic
    {
        $frontMatter = $document->frontMatter;
        $steps = self::parseSteps($frontMatter['steps'] ?? [], $document->file);
        $institution = $frontMatter['institution'] ?? null;

        return new Topic(
            slug: (string) $frontMatter['slug'],
            title: (string) $frontMatter['title'],
            summary: (string) $frontMatter['summary'],
            bodyHtml: $document->html,
            status: self::parseStatus((string) $frontMatter['status'], $document->file),
            institution: $institution === null ? null : (string) $institution,
            updatedAt: MarkdownLoader::normalizeDate($frontMatter['updatedAt'] ?? null) ?? TopicStep::latestDoneDate($steps),
            steps: $steps,
        );
    }

    private static function parseStatus(string $value, string $file): TopicStatus
    {
        return TopicStatus::tryFrom($value) ?? throw new \RuntimeException(sprintf(
            'Topic file "%s" has invalid status "%s" (allowed: %s).',
            $file,
            $value,
            implode(', ', array_map(static fn (TopicStatus $status): string => $status->value, TopicStatus::cases())),
        ));
    }

    /** @return list<TopicStep> */
    private static function parseSteps(mixed $rawSteps, string $file): array
    {
        if (!is_array($rawSteps)) {
            throw new \RuntimeException("Topic file \"$file\": \"steps\" must be a list.");
        }

        $steps = [];
        foreach (array_values($rawSteps) as $index => $rawStep) {
            $steps[] = TopicStep::fromFrontMatter($rawStep, $file, $index + 1);
        }

        ResponseAssessment::assertSequence($steps, $file);

        return $steps;
    }

    /** @return array<string, Topic> */
    public function all(): array
    {
        return $this->topics;
    }

    public function find(string $slug): ?Topic
    {
        return $this->topics[$slug] ?? null;
    }
}
