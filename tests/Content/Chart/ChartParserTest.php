<?php

declare(strict_types=1);

namespace App\Tests\Content\Chart;

use App\Content\Chart\ChartParser;
use App\Content\Chart\ChartType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ChartParserTest extends TestCase
{
    private const VALID = <<<'YAML'
        title: Wnioski wg miesięcy
        labels: [Sty, Lut, Mar]
        series:
          - name: Złożone
            values: [4, 9, 14]
        YAML;

    public function testParsesAMinimalChartWithBarAsTheDefaultType(): void
    {
        $chart = ChartParser::parse(self::VALID);

        self::assertSame(ChartType::Bar, $chart->type);
        self::assertSame('Wnioski wg miesięcy', $chart->title);
        self::assertSame(['Sty', 'Lut', 'Mar'], $chart->labels);
        self::assertSame('Złożone', $chart->series[0]->name);
        self::assertSame([4.0, 9.0, 14.0], $chart->series[0]->values);
        self::assertFalse($chart->stacked);
        self::assertNull($chart->unit);
    }

    public function testParsesOptionalFields(): void
    {
        $chart = ChartParser::parse(<<<'YAML'
            type: bar
            stacked: true
            title: T
            unit: wniosków
            caption: Opis
            source: Urząd
            labels: [A, B]
            series:
              - {name: X, values: [1, 2]}
              - {name: Y, values: [3, 4]}
            YAML);

        self::assertTrue($chart->stacked);
        self::assertSame('wniosków', $chart->unit);
        self::assertSame('Opis', $chart->caption);
        self::assertSame('Urząd', $chart->source);
        self::assertCount(2, $chart->series);
        self::assertSame(6.0, $chart->maxValue(), 'a stacked chart scales to its tallest stack: max(1 + 3, 2 + 4)');
    }

    public function testNumericLabelsSuchAsYearsBecomeStrings(): void
    {
        $chart = ChartParser::parse("title: T\nlabels: [2023, 2024]\nseries:\n  - {name: X, values: [1, 2]}\n");

        self::assertSame(['2023', '2024'], $chart->labels);
    }

    /** @return iterable<string, array{string, string}> */
    public static function invalidCharts(): iterable
    {
        yield 'not yaml' => ["title: [unclosed\n", 'not valid YAML'];
        yield 'not a mapping' => ['just text', 'YAML mapping'];
        yield 'no title' => ["labels: [A]\nseries:\n  - {name: X, values: [1]}\n", '"title"'];
        yield 'unknown type' => ["title: T\ntype: pie\nlabels: [A]\nseries:\n  - {name: X, values: [1]}\n", 'unknown type "pie" (allowed: bar, line)'];
        yield 'no labels' => ["title: T\nseries:\n  - {name: X, values: [1]}\n", '"labels"'];
        yield 'empty label' => ["title: T\nlabels: [A, '']\nseries:\n  - {name: X, values: [1, 2]}\n", 'label #2'];
        yield 'no series' => ["title: T\nlabels: [A]\n", '"series"'];
        yield 'series without name' => ["title: T\nlabels: [A]\nseries:\n  - {values: [1]}\n", 'series #1 needs a "name"'];
        yield 'too few values' => ["title: T\nlabels: [A, B]\nseries:\n  - {name: X, values: [1]}\n", 'exactly 2 values'];
        yield 'negative value' => ["title: T\nlabels: [A]\nseries:\n  - {name: X, values: [-1]}\n", 'value #1 must be a number >= 0'];
        yield 'text value' => ["title: T\nlabels: [A]\nseries:\n  - {name: X, values: [abc]}\n", 'value #1 must be a number >= 0'];
        yield 'stacked line' => ["title: T\ntype: line\nstacked: true\nlabels: [A]\nseries:\n  - {name: X, values: [1]}\n", '"stacked" only works with type "bar"'];
    }

    #[DataProvider('invalidCharts')]
    public function testRejectsInvalidChartsWithAClearMessage(string $yaml, string $expectedMessage): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote($expectedMessage, '/') . '/');

        ChartParser::parse($yaml);
    }

    public function testRejectsMoreThanFiveSeries(): void
    {
        $series = implode("\n", array_map(static fn (int $i): string => "  - {name: S$i, values: [1]}", range(1, 6)));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/at most 5 series/');

        ChartParser::parse("title: T\nlabels: [A]\nseries:\n$series\n");
    }
}
