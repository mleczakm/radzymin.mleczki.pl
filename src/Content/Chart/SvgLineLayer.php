<?php

declare(strict_types=1);

namespace App\Content\Chart;

/** One polyline with a dot per value for each series. */
final class SvgLineLayer
{
    private const VALUE_LABEL_LIMIT = 14;

    public function render(Chart $chart, PlotArea $plot): string
    {
        $showValues = count($chart->series) === 1 && $plot->categories <= self::VALUE_LABEL_LIMIT;
        $svg = '';

        foreach ($chart->series as $k => $series) {
            $points = [];
            foreach ($series->values as $index => $value) {
                $points[] = [$plot->centerX($index), $plot->y($value)];
            }

            $svg .= sprintf(
                '<polyline class="chart-line chart-s%d" fill="none" points="%s"/>',
                $k,
                implode(' ', array_map(
                    static fn (array $point): string => ChartFormat::attr($point[0]) . ',' . ChartFormat::attr($point[1]),
                    $points,
                )),
            );

            foreach ($points as $index => [$x, $y]) {
                $value = $series->values[$index];
                $svg .= sprintf(
                    '<circle class="chart-dot chart-s%d" cx="%s" cy="%s" r="3.5"><title>%s</title></circle>',
                    $k,
                    ChartFormat::attr($x),
                    ChartFormat::attr($y),
                    ChartFormat::escape(ChartFormat::tooltip($chart, $chart->labels[$index], $series, $value)),
                );

                if ($showValues) {
                    $svg .= sprintf(
                        '<text class="chart-value" x="%s" y="%s" text-anchor="middle">%s</text>',
                        ChartFormat::attr($x),
                        ChartFormat::attr($y - 9),
                        ChartFormat::display($value),
                    );
                }
            }
        }

        return $svg;
    }
}
