<?php

namespace Common\Database\Metrics\Traits;

trait RoundingPrecision
{
    public int $roundingPrecision = 0;
    public int $roundingMode = PHP_ROUND_HALF_UP;

    /**
     * Set the precision level used when rounding the value.
     */
    public function precision(
        int $precision = 0,
        int $mode = PHP_ROUND_HALF_UP,
    ): static {
        $this->roundingPrecision = $precision;

        if (
            in_array($mode, [
                PHP_ROUND_HALF_UP,
                PHP_ROUND_HALF_DOWN,
                PHP_ROUND_HALF_EVEN,
                PHP_ROUND_HALF_ODD,
            ])
        ) {
            $this->roundingMode = $mode;
        }

        return $this;
    }
}
