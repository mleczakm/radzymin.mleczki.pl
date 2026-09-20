<?php

declare(strict_types=1);

namespace App\Content\Chart;

final class Chart
{
    public const MAX_LABELS = 60;
    public const MAX_SERIES = 5;

    /**
     * @param list<string> $labels category labels (x axis)
     * @param non-empty-list<ChartSeries> $series
     */
    public function __construct(
        public readonly ChartType $type,
        public readonly string $title,
        public readonly array $labels,
        public readonly array $series,
        public readonly bool $stacked = false,
        public readonly ?string $unit = null,
        public readonly ?string $caption = null,
        public readonly ?string $source = null,
    ) {
    }

    /** True when every value is a whole number (counts), which keeps axis ticks whole too. */
    public function hasOnlyIntegers(): bool
    {
        foreach ($this->series as $series) {
            foreach ($series->values as $value) {
                if ($value !== floor($value)) {
                    return false;
                }
            }
        }

        return true;
    }

    /** Largest value on the y axis: the tallest stack when stacked, otherwise the largest single value. */
    public function maxValue(): float
    {
        $max = 0.0;

        foreach (array_keys($this->labels) as $index) {
            if ($this->stacked) {
                $max = max($max, array_sum(array_map(static fn (ChartSeries $s): float => $s->values[$index], $this->series)));

                continue;
            }

            foreach ($this->series as $series) {
                $max = max($max, $series->values[$index]);
            }
        }

        return $max;
    }
}
