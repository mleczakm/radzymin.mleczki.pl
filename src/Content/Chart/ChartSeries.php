<?php

declare(strict_types=1);

namespace App\Content\Chart;

final class ChartSeries
{
    /** @param list<float> $values one value per label of the chart, all >= 0 */
    public function __construct(
        public readonly string $name,
        public readonly array $values,
    ) {
    }
}
