<?php

declare(strict_types=1);

namespace App\Tests\Text;

use App\Text\PolishPlural;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PolishPluralTest extends TestCase
{
    /** @return iterable<string, array{int, string}> */
    public static function casesProvider(): iterable
    {
        yield '0' => [0, 'spraw'];
        yield '1' => [1, 'sprawa'];
        yield '2' => [2, 'sprawy'];
        yield '3' => [3, 'sprawy'];
        yield '4' => [4, 'sprawy'];
        yield '5' => [5, 'spraw'];
        yield '11' => [11, 'spraw'];
        yield '12 (teen)' => [12, 'spraw'];
        yield '13 (teen)' => [13, 'spraw'];
        yield '14 (teen)' => [14, 'spraw'];
        yield '15' => [15, 'spraw'];
        yield '20' => [20, 'spraw'];
        yield '21 is not "one"' => [21, 'spraw'];
        yield '22' => [22, 'sprawy'];
        yield '24' => [24, 'sprawy'];
        yield '25' => [25, 'spraw'];
        yield '100' => [100, 'spraw'];
        yield '101' => [101, 'spraw'];
        yield '102' => [102, 'sprawy'];
        yield '112 (teen)' => [112, 'spraw'];
        yield '1012 (teen)' => [1012, 'spraw'];
        yield '1022' => [1022, 'sprawy'];
        yield 'negative' => [-2, 'sprawy'];
    }

    #[DataProvider('casesProvider')]
    public function testPicksTheFormForTheNumber(int $count, string $expected): void
    {
        self::assertSame($expected, PolishPlural::form($count, 'sprawa', 'sprawy', 'spraw'));
    }

    public function testAgreesWithTheCldrRulesInIcu(): void
    {
        if (!class_exists(\MessageFormatter::class)) {
            self::markTestSkipped('ext-intl is not available.');
        }

        for ($count = 0; $count <= 2000; ++$count) {
            $icu = \MessageFormatter::formatMessage('pl', '{0, plural, one{one} few{few} many{many} other{other}}', [$count]);

            self::assertSame($icu, PolishPlural::form($count, 'one', 'few', 'many'), "count $count");
        }
    }

    public function testHelpersReturnTheFormAndSubstituteTheNumber(): void
    {
        self::assertSame('sprawy', plural_form(3, 'sprawa', 'sprawy', 'spraw'));
        self::assertSame('1 sprawa w toku', plural(1, '%d sprawa w toku', '%d sprawy w toku', '%d spraw w toku'));
        self::assertSame('pozostały 2 dni', plural(2, 'pozostał %d dzień', 'pozostały %d dni', 'pozostało %d dni'));
        self::assertSame('pozostało 0 dni', plural(0, 'pozostał %d dzień', 'pozostały %d dni', 'pozostało %d dni'));
    }
}
