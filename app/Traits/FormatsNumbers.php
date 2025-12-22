<?php

namespace App\Traits;

trait FormatsNumbers
{
    /**
     * number format ("nf")
     *
     * Formats an integer using German number formatting.
     *
     * Example: nf(1234567) => "1.234.567"
     */
    public function nf(int $number): string
    {
        return number_format($number, 0, ',', '.');
    }

    /**
     * percentage format ("pf")
     *
     * Formats a percentage based on a value and a base.
     *
     * Example: pf(16, 100) => "16.0"
     */
    public function pf(int|float $number, int|float $base, int $decimals = 1): string
    {
        $roundedResult = round((0 === $base) ? 0 : ($number / $base) * 100, $decimals);
        return number_format($roundedResult, $decimals);
    }

    /**
     * percentage format complement ("pfc")
     *
     * Calculates the complementary percentage to the above pf function (100 − percentage).
     * The decimals should match the ones used for the `pf()` function.
     *
     * Example: Example: pfc('16,0') => "84,0"
     */
    public function pfc(string $percentage, int $decimals = 1): float|int
    {
        // 1. Calculate complement
        $result = 100 - (float) $percentage;

        // 2. Round to be safe of floating-point imprecision (leading to many decimals)
        $rounded = round($result, $decimals);

        // 3. Cast the value to int in case of 0 decimals
        return $decimals === 0 ? (int) $rounded : $rounded;
    }
}
