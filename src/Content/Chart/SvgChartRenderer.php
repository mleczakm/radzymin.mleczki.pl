<?php

declare(strict_types=1);

namespace App\Content\Chart;

/**
 * The SVG itself: frame, grid, axis labels, then the bar or line layer. Colours come from CSS
 * (`.chart-s0` … `.chart-s4` set `--series`), so the chart follows the site theme and dark mode.
 */
final class SvgChartRenderer
{
    public const WIDTH = 560;
    /** The plot area always ends at this y; only the space below it (axis labels) varies. */
    private const PLOT_BOTTOM = 280;
    private const MARGIN_LEFT = 48;
    private const MARGIN_RIGHT = 16;
    private const MARGIN_TOP = 24;
    private const LABEL_SPACE = 40;
    /** Room for labels drawn at 35°: up to MAX_LABEL_CHARS characters hang about 72 units below their anchor. */
    private const LABEL_SPACE_ROTATED = 96;
    private const APPROX_CHAR_WIDTH = 7.0;
    private const MAX_LABEL_CHARS = 18;
    private const MAX_LEFT_MARGIN = 140.0;

    public function __construct(
        private readonly SvgBarLayer $bars = new SvgBarLayer(),
        private readonly SvgLineLayer $lines = new SvgLineLayer(),
    ) {
    }

    public function render(Chart $chart, string $id, string $description): string
    {
        $rotate = $this->needsRotation($chart);
        $plot = new PlotArea(
            left: $this->leftMargin($chart),
            right: (float) (self::WIDTH - self::MARGIN_RIGHT),
            top: (float) self::MARGIN_TOP,
            bottom: (float) self::PLOT_BOTTOM,
            categories: count($chart->labels),
            axis: ChartAxis::forChart($chart),
        );

        $svg = sprintf(
            '<svg class="chart-svg" viewBox="0 0 %d %d" role="img" aria-labelledby="%s-t %s-d" focusable="false">',
            self::WIDTH,
            self::PLOT_BOTTOM + ($rotate ? self::LABEL_SPACE_ROTATED : self::LABEL_SPACE),
            $id,
            $id,
        );
        $svg .= sprintf('<title id="%s-t">%s</title>', $id, ChartFormat::escape($chart->title));
        $svg .= sprintf('<desc id="%s-d">%s</desc>', $id, ChartFormat::escape($description));
        $svg .= $this->grid($plot);

        if ($chart->unit !== null) {
            $svg .= sprintf('<text class="chart-unit" x="4" y="12">%s</text>', ChartFormat::escape($chart->unit));
        }

        $svg .= $this->categoryLabels($chart, $plot);
        $svg .= $chart->type === ChartType::Line ? $this->lines->render($chart, $plot) : $this->bars->render($chart, $plot);

        return $svg . '</svg>';
    }

    private function grid(PlotArea $plot): string
    {
        $svg = '';

        foreach ($plot->axis->ticks() as $index => $value) {
            $y = ChartFormat::attr($plot->y($value));
            $svg .= sprintf(
                '<line class="chart-grid%s" x1="%s" x2="%s" y1="%s" y2="%s"/>',
                $index === 0 ? ' chart-axis' : '',
                ChartFormat::attr($plot->left),
                ChartFormat::attr($plot->right),
                $y,
                $y,
            );
            $svg .= sprintf(
                '<text class="chart-tick" x="%s" y="%s" dy=".32em" text-anchor="end">%s</text>',
                ChartFormat::attr($plot->left - 8),
                $y,
                ChartFormat::display($value),
            );
        }

        return $svg;
    }

    private function categoryLabels(Chart $chart, PlotArea $plot): string
    {
        $rotate = $this->needsRotation($chart);
        $svg = '';
        $y = ChartFormat::attr($plot->bottom + ($rotate ? 12 : 20));

        foreach ($chart->labels as $index => $label) {
            $x = ChartFormat::attr($plot->centerX($index) + ($rotate ? 4 : 0));
            $attributes = $rotate
                ? sprintf('text-anchor="end" transform="rotate(-35 %s %s)"', $x, $y)
                : 'text-anchor="middle"';

            $shown = $this->displayLabel($label);
            $tooltip = $shown === $label ? '' : '<title>' . ChartFormat::escape($label) . '</title>';

            $svg .= sprintf(
                '<text class="chart-label" x="%s" y="%s" %s>%s%s</text>',
                $x,
                $y,
                $attributes,
                ChartFormat::escape($shown),
                $tooltip,
            );
        }

        return $svg;
    }

    /** Long labels would overlap on a narrow band, so they are drawn at an angle instead. */
    private function needsRotation(Chart $chart): bool
    {
        $band = (self::WIDTH - self::MARGIN_LEFT - self::MARGIN_RIGHT) / count($chart->labels);
        $longest = max(array_map(fn (string $label): int => mb_strlen($this->displayLabel($label)), $chart->labels));

        return $longest * self::APPROX_CHAR_WIDTH > $band - 6;
    }

    /** Labels are shortened for the axis; the full text stays in the hover tooltip and the data table. */
    private function displayLabel(string $label): string
    {
        return mb_strlen($label) > self::MAX_LABEL_CHARS ? mb_substr($label, 0, self::MAX_LABEL_CHARS - 1) . '…' : $label;
    }

    /**
     * Room on the left for the y axis numbers and, when labels are drawn at an angle, for the
     * part of a category label that hangs to the left of its anchor point (it would be clipped).
     */
    private function leftMargin(Chart $chart): float
    {
        $tickChars = max(array_map(
            static fn (float $tick): int => mb_strlen(ChartFormat::display($tick)),
            ChartAxis::forChart($chart)->ticks(),
        ));
        $margin = max((float) self::MARGIN_LEFT, $tickChars * self::APPROX_CHAR_WIDTH + 14);

        if (!$this->needsRotation($chart)) {
            return $margin;
        }

        $band = (self::WIDTH - $margin - self::MARGIN_RIGHT) / count($chart->labels);
        $needed = $margin;
        foreach ($chart->labels as $index => $label) {
            // A label at 35° hangs left of its anchor by about its width × cos(35°).
            $overhang = mb_strlen($this->displayLabel($label)) * self::APPROX_CHAR_WIDTH * 0.82;
            $needed = max($needed, $overhang - ($index + 0.5) * $band + $margin + 8);
        }

        return min(self::MAX_LEFT_MARGIN, $needed);
    }
}
