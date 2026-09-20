<?php

declare(strict_types=1);

namespace App\Text;

/**
 * Picks the grammatical form of a noun after a number in Polish. Polish has three forms for whole
 * numbers (CLDR "pl": one / few / many), e.g. 1 sprawa, 2 sprawy, 5 spraw, 12 spraw, 22 sprawy.
 * The rule is small enough that a library (or ext-intl) would add more weight than it saves.
 */
final class PolishPlural
{
    /**
     * @param string $one used for exactly 1
     * @param string $few used for numbers ending in 2-4, except 12-14
     * @param string $many used for everything else (0, 5-21, 25-31, ...)
     */
    public static function form(int $count, string $one, string $few, string $many): string
    {
        if ($count === 1) {
            return $one;
        }

        $lastDigit = abs($count % 10);
        $lastTwoDigits = abs($count % 100);

        return $lastDigit >= 2 && $lastDigit <= 4 && ($lastTwoDigits < 12 || $lastTwoDigits > 14) ? $few : $many;
    }
}
