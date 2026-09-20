<?php

declare(strict_types=1);

namespace App\Content\Chart;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Turns the YAML inside a fenced `chart` block into a validated Chart. Everything is checked
 * up front so a typo in a content file fails at worker start with a clear message, instead of
 * rendering a broken chart for visitors.
 */
final class ChartParser
{
    public static function parse(string $yaml): Chart
    {
        try {
            $data = Yaml::parse($yaml);
        } catch (ParseException $e) {
            throw new \RuntimeException('Chart block is not valid YAML: ' . $e->getMessage(), previous: $e);
        }

        if (!is_array($data)) {
            throw new \RuntimeException('Chart block must be a YAML mapping (type, title, labels, series, ...).');
        }

        $title = ChartDataParser::text($data['title'] ?? null);
        if ($title === null) {
            throw new \RuntimeException('Chart block needs a "title" (it is also the accessible name of the chart).');
        }

        $typeName = ChartDataParser::text($data['type'] ?? 'bar');
        $type = $typeName === null ? null : ChartType::tryFrom($typeName);
        if ($type === null) {
            throw new \RuntimeException(sprintf(
                'Chart "%s": unknown type "%s" (allowed: %s).',
                $title,
                (string) $typeName,
                implode(', ', array_map(static fn (ChartType $t): string => $t->value, ChartType::cases())),
            ));
        }

        $labels = ChartDataParser::labels($data['labels'] ?? null, $title);
        $series = ChartDataParser::series($data['series'] ?? null, count($labels), $title);

        $stacked = ($data['stacked'] ?? false) === true;
        if ($stacked && $type !== ChartType::Bar) {
            throw new \RuntimeException(sprintf('Chart "%s": "stacked" only works with type "bar".', $title));
        }

        return new Chart(
            type: $type,
            title: $title,
            labels: $labels,
            series: $series,
            stacked: $stacked,
            unit: ChartDataParser::text($data['unit'] ?? null),
            caption: ChartDataParser::text($data['caption'] ?? null),
            source: ChartDataParser::text($data['source'] ?? null),
        );
    }
}
