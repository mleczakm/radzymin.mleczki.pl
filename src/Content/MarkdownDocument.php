<?php

declare(strict_types=1);

namespace App\Content;

final class MarkdownDocument
{
    /** @param array<string, mixed> $frontMatter */
    public function __construct(
        public readonly array $frontMatter,
        public readonly string $html,
        public readonly string $file,
    ) {
    }
}
