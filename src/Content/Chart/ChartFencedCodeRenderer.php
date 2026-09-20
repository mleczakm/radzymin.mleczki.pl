<?php

declare(strict_types=1);

namespace App\Content\Chart;

use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

/**
 * Renders fenced blocks tagged `chart` as SVG charts. Any other fenced block returns null, which
 * makes CommonMark fall through to its normal <pre><code> renderer.
 */
final class ChartFencedCodeRenderer implements NodeRendererInterface
{
    public function __construct(private readonly ChartHtmlRenderer $renderer = new ChartHtmlRenderer())
    {
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): ?string
    {
        if (!$node instanceof FencedCode || ($node->getInfoWords()[0] ?? '') !== 'chart') {
            return null;
        }

        return $this->renderer->render(ChartParser::parse($node->getLiteral()));
    }
}
