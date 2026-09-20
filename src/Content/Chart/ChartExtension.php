<?php

declare(strict_types=1);

namespace App\Content\Chart;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\ExtensionInterface;

/** Adds fenced `chart` blocks to the Markdown converter. */
final class ChartExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        // Priority above the default renderer (0), so charts are tried first.
        $environment->addRenderer(FencedCode::class, new ChartFencedCodeRenderer(), 10);
    }
}
