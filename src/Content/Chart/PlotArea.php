<?php

declare(strict_types=1);

namespace App\Content\Chart;

/** Geometry of the drawing area inside the SVG: where each category and value lands. */
final class PlotArea
{
    public function __construct(
        public readonly float $left,
        public readonly float $right,
        public readonly float $top,
        public readonly float $bottom,
        public readonly int $categories,
        public readonly ChartAxis $axis,
    ) {
    }

    /** Horizontal space of one category. */
    public function band(): float
    {
        return ($this->right - $this->left) / $this->categories;
    }

    public function centerX(int $index): float
    {
        return $this->left + ($index + 0.5) * $this->band();
    }

    public function y(float $value): float
    {
        return $this->bottom - ($value / $this->axis->max) * ($this->bottom - $this->top);
    }
}
