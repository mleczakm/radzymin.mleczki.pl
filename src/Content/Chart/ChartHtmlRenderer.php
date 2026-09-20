<?php

declare(strict_types=1);

namespace App\Content\Chart;

/**
 * A chart as HTML: a <figure> with the visible title, the SVG, a legend, a caption and the same
 * data as a real table in a <details> element (the text alternative for screen readers, and the
 * exact numbers for everyone else).
 */
final class ChartHtmlRenderer
{
    private const DESCRIPTION_ITEMS = 12;

    public function __construct(private readonly SvgChartRenderer $svg = new SvgChartRenderer())
    {
    }

    public function render(Chart $chart): string
    {
        $id = 'chart-' . substr(md5(json_encode([
            $chart->title,
            $chart->labels,
            array_map(static fn (ChartSeries $series): array => [$series->name, $series->values], $chart->series),
        ]) ?: $chart->title), 0, 8);

        return '<figure class="chart">'
            . '<p class="chart-title">' . ChartFormat::escape($chart->title) . '</p>'
            . $this->svg->render($chart, $id, $this->describe($chart))
            . $this->legend($chart)
            . $this->caption($chart)
            . $this->table($chart)
            . "</figure>\n";
    }

    private function legend(Chart $chart): string
    {
        if (count($chart->series) < 2) {
            return '';
        }

        $items = '';
        foreach ($chart->series as $k => $series) {
            $items .= sprintf('<li><span class="chart-swatch chart-s%d"></span>%s</li>', $k, ChartFormat::escape($series->name));
        }

        return '<ul class="chart-legend">' . $items . '</ul>';
    }

    private function caption(Chart $chart): string
    {
        $parts = [];
        if ($chart->caption !== null) {
            $parts[] = ChartFormat::escape($chart->caption);
        }
        if ($chart->source !== null) {
            $parts[] = 'Źródło: ' . ChartFormat::escape($chart->source);
        }

        return $parts === [] ? '' : '<figcaption class="chart-caption">' . implode(' ', $parts) . '</figcaption>';
    }

    private function table(Chart $chart): string
    {
        $head = '<th scope="col"><span class="visually-hidden">Kategoria</span></th>';
        foreach ($chart->series as $series) {
            $head .= '<th scope="col">' . ChartFormat::escape($series->name) . '</th>';
        }

        $rows = '';
        foreach ($chart->labels as $index => $label) {
            $rows .= '<tr><th scope="row">' . ChartFormat::escape($label) . '</th>';
            foreach ($chart->series as $series) {
                $rows .= '<td>' . ChartFormat::display($series->values[$index]) . '</td>';
            }
            $rows .= '</tr>';
        }

        return '<details class="chart-data"><summary>Pokaż dane w tabeli</summary>'
            . '<table><caption class="visually-hidden">' . ChartFormat::escape($chart->title) . '</caption>'
            . '<thead><tr>' . $head . '</tr></thead><tbody>' . $rows . '</tbody></table></details>';
    }

    /** Text alternative for the SVG; the full data is in the table below the chart. */
    private function describe(Chart $chart): string
    {
        $text = 'Wykres ' . $chart->type->label() . '.';
        $shown = array_slice($chart->labels, 0, self::DESCRIPTION_ITEMS, true);
        $more = count($chart->labels) > self::DESCRIPTION_ITEMS ? ', …' : '';

        foreach ($chart->series as $series) {
            $pairs = [];
            foreach ($shown as $index => $label) {
                $pairs[] = $label . ' ' . ChartFormat::display($series->values[$index]);
            }
            $text .= sprintf(' %s: %s%s.', $series->name, implode(', ', $pairs), $more);
        }

        return $text;
    }
}
