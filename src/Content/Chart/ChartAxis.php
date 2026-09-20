<?php

declare(strict_types=1);

namespace App\Content\Chart;

/** Y axis with a "nice" maximum and step (1, 2, 2.5, 5 × 10^n). Counts never get fractional ticks. */
final class ChartAxis
{
    private function __construct(
        public readonly float $max,
        public readonly float $step,
    ) {
    }

    public static function forChart(Chart $chart): self
    {
        $integers = $chart->hasOnlyIntegers();
        $max = max($chart->maxValue(), 0.0);
        $max = $max > 0.0 ? $max : 1.0;

        // Aim for about five intervals; the step is then rounded up to the next "nice" value.
        $rough = $integers ? max($max / 5, 1.0) : $max / 5;
        $base = 10.0 ** floor(log10($rough));
        $fraction = $rough / $base;
        $candidates = $integers ? [1.0, 2.0, 5.0] : [1.0, 2.0, 2.5, 5.0];
        $step = (array_find($candidates, static fn (float $candidate): bool => $fraction <= $candidate) ?? 10.0) * $base;

        return new self(ceil(round($max / $step, 6)) * $step, $step);
    }

    /** @return list<float> tick values from 0 up to and including the axis maximum */
    public function ticks(): array
    {
        $ticks = [];
        $count = (int) round($this->max / $this->step);
        for ($i = 0; $i <= $count; $i++) {
            $ticks[] = $i * $this->step;
        }

        return $ticks;
    }
}
