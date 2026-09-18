<?php

declare(strict_types=1);

namespace App\Tests\Http;

use App\Http\RequestInput;
use PHPUnit\Framework\TestCase;

final class RequestInputTest extends TestCase
{
    public function testKeepsOnlyPlainStringFields(): void
    {
        $fields = RequestInput::stringFields([
            'first_name' => 'Jan',
            'city' => 'Radzymin',
            'nested' => ['a', 'b'],
            'number' => 5,
            0 => 'positional',
        ]);

        self::assertSame(['first_name' => 'Jan', 'city' => 'Radzymin'], $fields);
    }

    public function testNestedFieldIsNotTurnedIntoTheStringArray(): void
    {
        // Without this, `first_name[]=x` becomes "Array" and even passes a letters-only name check.
        self::assertArrayNotHasKey('first_name', RequestInput::stringFields(['first_name' => ['x']]));
    }

    public function testEmptyInput(): void
    {
        self::assertSame([], RequestInput::stringFields([]));
    }
}
