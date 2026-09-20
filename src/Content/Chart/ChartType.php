<?php

declare(strict_types=1);

namespace App\Content\Chart;

enum ChartType: string
{
    case Bar = 'bar';
    case Line = 'line';

    public function label(): string
    {
        return match ($this) {
            self::Bar => 'słupkowy',
            self::Line => 'liniowy',
        };
    }
}
