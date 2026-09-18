<?php

declare(strict_types=1);

namespace App\Domain;

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
            $document = $loader->load($file, ['slug', 'title', 'summary', 'status']);
            $frontMatter = $document->frontMatter;

            $status = TopicStatus::tryFrom((string) $frontMatter['status'])
                ?? throw new \RuntimeException(sprintf(
                    'Topic file "%s" has invalid status "%s" (allowed: %s).',
                    $file,
                    (string) $frontMatter['status'],
                    implode(', ', array_map(static fn (TopicStatus $s): string => $s->value, TopicStatus::cases())),
                ));

            $steps = self::parseSteps($frontMatter['steps'] ?? [], $file);

            $topic = new Topic(
                slug: (string) $frontMatter['slug'],
                title: (string) $frontMatter['title'],
                summary: (string) $frontMatter['summary'],
                bodyHtml: $document->html,
                status: $status,
                institution: isset($frontMatter['institution']) ? (string) $frontMatter['institution'] : null,
                updatedAt: MarkdownLoader::normalizeDate($frontMatter['updatedAt'] ?? null) ?? self::latestDoneStepDate($steps),
                steps: $steps,
            );

            $topics[$topic->slug] = $topic;
        }

        uasort($topics, static fn (Topic $a, Topic $b): int => [$a->status->sortOrder(), $b->updatedAt ?? '', $a->title]
            <=> [$b->status->sortOrder(), $a->updatedAt ?? '', $b->title]);

        $this->topics = $topics;
    }

    /** @return list<TopicStep> */
    private static function parseSteps(mixed $rawSteps, string $file): array
    {
        if (!is_array($rawSteps)) {
            throw new \RuntimeException("Topic file \"$file\": \"steps\" must be a list.");
        }

        $steps = [];
        foreach ($rawSteps as $index => $rawStep) {
            if (!is_array($rawStep) || empty($rawStep['title'])) {
                throw new \RuntimeException(sprintf('Topic file "%s": step #%d is missing a "title".', $file, $index + 1));
            }

            $steps[] = new TopicStep(
                title: (string) $rawStep['title'],
                date: MarkdownLoader::normalizeDate($rawStep['date'] ?? null),
                done: (bool) ($rawStep['done'] ?? false),
            );
        }

        return $steps;
    }

    /** @param list<TopicStep> $steps */
    private static function latestDoneStepDate(array $steps): ?string
    {
        $dates = array_filter(
            array_map(static fn (TopicStep $step): ?string => $step->done ? $step->date : null, $steps),
        );

        return $dates === [] ? null : max($dates);
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
