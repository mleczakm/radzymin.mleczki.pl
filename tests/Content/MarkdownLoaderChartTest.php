<?php

declare(strict_types=1);

namespace App\Tests\Content;

use App\Content\MarkdownLoader;
use PHPUnit\Framework\TestCase;

final class MarkdownLoaderChartTest extends TestCase
{
    private const FIXTURES = __DIR__ . '/../fixtures/content';

    public function testAChartBlockBecomesASvgFigure(): void
    {
        $html = (new MarkdownLoader())->load(self::FIXTURES . '/with-chart.md')->html;

        self::assertStringContainsString('<p>Akapit przed wykresem.</p>', $html);
        self::assertStringContainsString('<figure class="chart">', $html);
        self::assertStringContainsString('<p class="chart-title">Wnioski wg miesięcy</p>', $html);
        self::assertStringNotContainsString('```', $html);
        self::assertStringNotContainsString('language-chart', $html);
    }

    public function testOtherFencedBlocksAreStillPlainCode(): void
    {
        $html = (new MarkdownLoader())->load(self::FIXTURES . '/with-chart.md')->html;

        self::assertStringContainsString('<pre><code class="language-php">', $html);
        self::assertStringContainsString('zwykły blok kodu', $html);
    }

    public function testAnInvalidChartNamesTheFileAndTheProblem(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('#with-bad-chart\.md.*"series"#');

        (new MarkdownLoader())->load(self::FIXTURES . '/with-bad-chart.md');
    }
}
