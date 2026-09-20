<?php

declare(strict_types=1);

namespace App\Content\Chart;

/** Grouped or stacked bars. */
final class SvgBarLayer
{
    private const VALUE_LABEL_LIMIT = 14;

    public function render(Chart $chart, PlotArea $plot): string
    {
        $seriesCount = count($chart->series);
        $band = $plot->band();
        $groupWidth = min($band * 0.72, $chart->stacked ? 64.0 : $seriesCount * 44.0);
        $barWidth = $chart->stacked ? $groupWidth : $groupWidth / $seriesCount;
        $showValues = $plot->categories * ($chart->stacked ? 1 : $seriesCount) <= self::VALUE_LABEL_LIMIT;
        $svg = '';

        foreach ($chart->labels as $index => $label) {
            $groupX = $plot->left + $index * $band + ($band - $groupWidth) / 2;
            $stackBase = 0.0;

            foreach ($chart->series as $k => $series) {
                $value = $series->values[$index];
                $barX = $chart->stacked ? $groupX : $groupX + $k * $barWidth;
                $yTop = $plot->y($chart->stacked ? $stackBase + $value : $value);
                $yBase = $chart->stacked ? $plot->y($stackBase) : $plot->bottom;
                $stackBase += $value;

                if ($value <= 0.0) {
                    continue;
                }

                $svg .= sprintf(
                    '<rect class="chart-bar chart-s%d" x="%s" y="%s" width="%s" height="%s" rx="2"><title>%s</title></rect>',
                    $k,
                    ChartFormat::attr($barX),
                    ChartFormat::attr($yTop),
                    ChartFormat::attr($barWidth),
                    ChartFormat::attr($yBase - $yTop),
                    ChartFormat::escape(ChartFormat::tooltip($chart, $label, $series, $value)),
                );

                if ($showValues && !$chart->stacked) {
                    $svg .= $this->valueLabel($barX + $barWidth / 2, $yTop - 5, $value);
                }
            }

            if ($showValues && $chart->stacked && $stackBase > 0.0) {
                $svg .= $this->valueLabel($groupX + $groupWidth / 2, $plot->y($stackBase) - 5, $stackBase);
            }
        }

        return $svg;
    }

    private function valueLabel(float $x, float $y, float $value): string
    {
        return sprintf(
            '<text class="chart-value" x="%s" y="%s" text-anchor="middle">%s</text>',
            ChartFormat::attr($x),
            ChartFormat::attr($y),
            ChartFormat::display($value),
        );
    }
}
