<?php

declare(strict_types=1);

namespace App\Tests\Content\Chart;

use App\Content\Chart\Chart;
use App\Content\Chart\ChartParser;
use App\Content\Chart\ChartSeries;
use App\Content\Chart\ChartType;
use App\Content\Chart\ChartHtmlRenderer;
use PHPUnit\Framework\TestCase;

final class ChartHtmlRendererTest extends TestCase
{
    private ChartHtmlRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new ChartHtmlRenderer();
    }

    private function render(string $yaml): string
    {
        return $this->renderer->render(ChartParser::parse($yaml));
    }

    public function testBarChartIsAnAccessibleFigureWithADataTable(): void
    {
        $html = $this->render("title: Wnioski\nunit: wniosków\nlabels: [Sty, Lut, Mar]\nseries:\n  - {name: Złożone, values: [4, 9, 14]}\n");

        self::assertStringContainsString('<figure class="chart">', $html);
        self::assertStringContainsString('role="img"', $html);
        self::assertStringContainsString('<title id=', $html);
        self::assertStringContainsString('<desc id=', $html);
        self::assertSame(3, substr_count($html, 'class="chart-bar chart-s0"'));
        self::assertStringContainsString('Sty: 4 wniosków', $html, 'tooltip');
        self::assertStringContainsString('<details class="chart-data">', $html);
        self::assertStringContainsString('<th scope="row">Lut</th><td>9</td>', $html);
        self::assertStringNotContainsString('chart-legend', $html, 'a single series needs no legend');
    }

    public function testEveryPositiveValueGetsABarAndZerosDoNot(): void
    {
        $html = $this->render("title: T\nlabels: [A, B, C]\nseries:\n  - {name: X, values: [5, 0, 7]}\n");

        self::assertSame(2, substr_count($html, 'class="chart-bar '));
    }

    public function testSeveralSeriesGetALegendAndGroupedBars(): void
    {
        $html = $this->render("title: T\nlabels: [A, B]\nseries:\n  - {name: Złożone, values: [5, 6]}\n  - {name: Uwzględnione, values: [1, 2]}\n");

        self::assertStringContainsString('<ul class="chart-legend">', $html);
        self::assertSame(2, substr_count($html, 'class="chart-swatch '));
        self::assertSame(2, substr_count($html, 'chart-bar chart-s1'));
        self::assertStringContainsString('A: Uwzględnione, 1', $html, 'tooltip names the series when there are several');
    }

    public function testStackedBarsShowTheTotalAboveEachStack(): void
    {
        $html = $this->render("title: T\ntype: bar\nstacked: true\nlabels: [A]\nseries:\n  - {name: X, values: [4]}\n  - {name: Y, values: [3]}\n");

        self::assertStringContainsString('class="chart-value"', $html);
        self::assertMatchesRegularExpression('#<text class="chart-value"[^>]*>7</text>#', $html);
    }

    public function testLineChartDrawsOnePolylinePerSeriesAndADotPerValue(): void
    {
        $html = $this->render("title: T\ntype: line\nlabels: [A, B, C]\nseries:\n  - {name: X, values: [1, 2, 3]}\n  - {name: Y, values: [3, 2, 1]}\n");

        self::assertSame(2, substr_count($html, '<polyline class="chart-line '));
        self::assertSame(6, substr_count($html, '<circle class="chart-dot '));
        self::assertStringNotContainsString('chart-bar', $html);
    }

    public function testCountsGetWholeNumberTicks(): void
    {
        $html = $this->render("title: T\nlabels: [A, B]\nseries:\n  - {name: X, values: [2, 12]}\n");

        // max 12 -> step 5, axis up to 15; never 2.5-style steps for counts
        foreach (['0', '5', '10', '15'] as $tick) {
            self::assertStringContainsString('>' . $tick . '</text>', $html);
        }
        self::assertStringNotContainsString('2,5', $html);
    }

    public function testDecimalsUsePolishFormatting(): void
    {
        $html = $this->render("title: T\nlabels: [A]\nseries:\n  - {name: X, values: [2.5]}\n");

        self::assertStringContainsString('>2,5<', $html);
    }

    public function testThousandsAreSeparatedWithANonBreakingSpace(): void
    {
        $html = $this->render("title: T\nlabels: [A]\nseries:\n  - {name: X, values: [1200]}\n");

        self::assertStringContainsString("1\u{00A0}200", $html);
    }

    public function testAnAllZeroChartDoesNotDivideByZero(): void
    {
        $html = $this->render("title: T\nlabels: [A, B]\nseries:\n  - {name: X, values: [0, 0]}\n");

        self::assertStringContainsString('<svg', $html);
        self::assertStringNotContainsString('NAN', strtoupper($html));
        self::assertStringNotContainsString('INF', strtoupper($html));
    }

    public function testLongLabelsAreRotatedInsteadOfOverlapping(): void
    {
        $long = "title: T\nlabels: ['Styczeń 2026', 'Luty 2026', 'Marzec 2026', 'Kwiecień 2026', 'Maj 2026', 'Czerwiec 2026', 'Lipiec 2026', 'Sierpień 2026']\nseries:\n  - {name: X, values: [1, 2, 3, 4, 5, 6, 7, 8]}\n";
        $short = "title: T\nlabels: [S, L, M]\nseries:\n  - {name: X, values: [1, 2, 3]}\n";

        self::assertStringContainsString('rotate(-35', $this->render($long));
        self::assertStringNotContainsString('rotate(', $this->render($short));
    }

    public function testVeryLongLabelsAreShortenedButKeepTheFullTextAsATooltipAndInTheTable(): void
    {
        $full = 'Zarząd Dróg Wojewódzkich w Warszawie';
        $html = $this->render("title: T\nlabels: ['$full', Straż]\nseries:\n  - {name: X, values: [1, 2]}\n");

        self::assertStringContainsString('Zarząd Dróg Wojew…', $html);
        self::assertStringContainsString("<title>$full</title>", $html);
        self::assertStringContainsString("<th scope=\"row\">$full</th>", $html);
    }

    public function testRotatedLabelsGetExtraRoomOnTheLeftSoTheyAreNotClipped(): void
    {
        $rotated = $this->render("title: T\nlabels: ['Urząd Miasta i Gminy', 'Starostwo Powiatowe', 'Straż Miejska', 'Zarząd Dróg', 'Sąd Rejonowy']\nseries:\n  - {name: X, values: [1, 2, 3, 4, 5]}\n");
        $plain = $this->render("title: T\nlabels: [S, L, M]\nseries:\n  - {name: X, values: [1, 2, 3]}\n");

        self::assertGreaterThan($this->plotLeft($plain), $this->plotLeft($rotated));
    }

    private function plotLeft(string $html): float
    {
        $match = [];
        self::assertSame(1, preg_match('#<line class="chart-grid chart-axis" x1="([\d.]+)"#', $html, $match));

        self::assertArrayHasKey(1, $match);
        self::assertIsNumeric($match[1]);

        return (float) $match[1];
    }

    public function testCaptionAndSourceAreRendered(): void
    {
        $html = $this->render("title: T\ncaption: Dane z rejestru\nsource: Urząd Gminy\nlabels: [A]\nseries:\n  - {name: X, values: [1]}\n");

        self::assertStringContainsString('<figcaption class="chart-caption">Dane z rejestru Źródło: Urząd Gminy</figcaption>', $html);
    }

    public function testAllTextFromTheContentFileIsEscaped(): void
    {
        $chart = new Chart(
            type: ChartType::Bar,
            title: '<script>alert(1)</script>',
            labels: ['"><img src=x onerror=alert(1)>'],
            series: [new ChartSeries('<b>x</b>', [1.0])],
            unit: '<i>u</i>',
            caption: '<u>c</u>',
            source: '<s>s</s>',
        );

        $html = $this->renderer->render($chart);

        self::assertStringNotContainsString('<script', $html);
        self::assertStringNotContainsString('<img', $html);
        self::assertStringNotContainsString('<b>', $html);
        self::assertStringNotContainsString('<i>', $html);
        self::assertStringContainsString('&lt;script&gt;', $html);
    }
}
