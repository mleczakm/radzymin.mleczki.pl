<?php

declare(strict_types=1);

namespace App\Content\Chart;

/** Number and text formatting shared by the chart renderers. */
final class ChartFormat
{
    /** Compact number for SVG attributes (max 2 decimals, no trailing zeros). */
    public static function attr(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    /** Number as shown to readers: Polish decimal comma, non-breaking space as thousands separator. */
    public static function display(float $value): string
    {
        if ($value === floor($value)) {
            return number_format($value, 0, ',', "\u{00A0}");
        }

        return rtrim(rtrim(number_format($value, 2, ',', "\u{00A0}"), '0'), ',');
    }

    public static function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** Hover text for a bar or dot, e.g. "Sty: Złożone, 4 wniosków". */
    public static function tooltip(Chart $chart, string $label, ChartSeries $series, float $value): string
    {
        $prefix = count($chart->series) > 1 ? $series->name . ', ' : '';
        $unit = $chart->unit !== null ? ' ' . $chart->unit : '';

        return $label . ': ' . $prefix . self::display($value) . $unit;
    }
}
