<?php

declare(strict_types=1);

namespace App\Tests\Content\Chart;

use App\Content\Chart\ChartAxis;
use App\Content\Chart\ChartParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ChartAxisTest extends TestCase
{
    private static function axis(string $values): ChartAxis
    {
        return ChartAxis::forChart(ChartParser::parse("title: T\nlabels: [A, B]\nseries:\n  - {name: X, values: $values}\n"));
    }

    /** @return iterable<string, array{string, float, float}> */
    public static function axes(): iterable
    {
        yield 'small counts get step 1' => ['[1, 3]', 3.0, 1.0];
        yield 'max 12 -> step 5' => ['[2, 12]', 15.0, 5.0];
        yield 'hundreds' => ['[80, 430]', 500.0, 100.0];
        yield 'thousands' => ['[900, 4200]', 5000.0, 1000.0];
        yield 'fractions keep a fractional step' => ['[0.5, 1.5]', 1.5, 0.5];
    }

    #[DataProvider('axes')]
    public function testPicksANiceMaximumAndStep(string $values, float $expectedMax, float $expectedStep): void
    {
        $axis = self::axis($values);

        self::assertSame($expectedMax, $axis->max);
        self::assertSame($expectedStep, $axis->step);
    }

    public function testTicksRunFromZeroToTheMaximum(): void
    {
        self::assertSame([0.0, 5.0, 10.0, 15.0], self::axis('[2, 12]')->ticks());
    }

    public function testAnAllZeroChartStillHasAUsableAxis(): void
    {
        $axis = self::axis('[0, 0]');

        self::assertGreaterThan(0.0, $axis->max);
        self::assertSame([0.0, 1.0], $axis->ticks());
    }

    public function testCountsNeverGetFractionalSteps(): void
    {
        foreach (['[1, 2]', '[3, 7]', '[13, 26]', '[101, 480]'] as $values) {
            self::assertSame(floor(self::axis($values)->step), self::axis($values)->step, $values);
        }
    }
}
