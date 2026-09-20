<?php

declare(strict_types=1);

namespace App\Content\Chart;

/** Validates the data part of a chart block: plain text values, category labels and the series. */
final class ChartDataParser
{
    /** A non-empty scalar as a string, or null. YAML turns bare `2024` into an int, so scalars are accepted. */
    public static function text(mixed $value): ?string
    {
        if (!is_scalar($value) || is_bool($value)) {
            return null;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    /** @return list<string> */
    public static function labels(mixed $raw, string $title): array
    {
        if (!is_array($raw) || $raw === []) {
            throw new \RuntimeException(sprintf('Chart "%s": "labels" must be a non-empty list.', $title));
        }

        if (count($raw) > Chart::MAX_LABELS) {
            throw new \RuntimeException(sprintf('Chart "%s": at most %d labels are supported.', $title, Chart::MAX_LABELS));
        }

        $labels = [];
        foreach (array_values($raw) as $index => $label) {
            $text = self::text($label);
            if ($text === null) {
                throw new \RuntimeException(sprintf('Chart "%s": label #%d is empty or not a plain value.', $title, $index + 1));
            }
            $labels[] = $text;
        }

        return $labels;
    }

    /**
     * @return non-empty-list<ChartSeries>
     */
    public static function series(mixed $raw, int $labelCount, string $title): array
    {
        if (!is_array($raw) || $raw === []) {
            throw new \RuntimeException(sprintf('Chart "%s": "series" must be a non-empty list of {name, values}.', $title));
        }

        if (count($raw) > Chart::MAX_SERIES) {
            throw new \RuntimeException(sprintf('Chart "%s": at most %d series are supported.', $title, Chart::MAX_SERIES));
        }

        $series = [];
        foreach (array_values($raw) as $index => $entry) {
            $position = $index + 1;
            $name = is_array($entry) ? self::text($entry['name'] ?? null) : null;
            $values = is_array($entry) ? ($entry['values'] ?? null) : null;

            if ($name === null) {
                throw new \RuntimeException(sprintf('Chart "%s": series #%d needs a "name".', $title, $position));
            }

            if (!is_array($values) || count($values) !== $labelCount) {
                throw new \RuntimeException(sprintf(
                    'Chart "%s": series "%s" needs exactly %d values (one per label).',
                    $title,
                    $name,
                    $labelCount,
                ));
            }

            $numbers = [];
            foreach (array_values($values) as $valueIndex => $value) {
                if (!is_int($value) && !is_float($value) || $value < 0 || !is_finite((float) $value)) {
                    throw new \RuntimeException(sprintf(
                        'Chart "%s": series "%s" value #%d must be a number >= 0.',
                        $title,
                        $name,
                        $valueIndex + 1,
                    ));
                }
                $numbers[] = (float) $value;
            }

            $series[] = new ChartSeries($name, $numbers);
        }

        return $series;
    }
}
