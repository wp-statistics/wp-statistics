<?php

namespace WP_Statistics\Service\Shortcode\Formatters;

/**
 * Formats numeric values for shortcode output.
 *
 * Supports multiple formatting styles:
 * - none: Raw number
 * - english: Comma-separated (1,234,567)
 * - i18n: Localized format via WordPress
 * - abbreviated: Short notation (1.5K, 2.3M, 1.2B)
 *
 * @since 15.0.0
 */
class NumberFormatter
{
    /**
     * Format a numeric value.
     *
     * @param mixed  $value  The value to format.
     * @param string $format Format type.
     * @return string Formatted value.
     */
    public function format($value, string $format = ''): string
    {
        if (!is_numeric($value)) {
            return (string) $value;
        }

        // Determine decimal places for floating point values
        $decimals = $this->getDecimalPlaces($value);

        switch (strtolower($format)) {
            case 'i18n':
                return number_format_i18n($value, $decimals);

            case 'english':
                return number_format((float) $value, $decimals);

            case 'abbreviated':
                return $this->abbreviate($value);

            case 'none':
            default:
                return (string) $value;
        }
    }

    /**
     * Get the number of decimal places for a value.
     *
     * @param int|float $value The value to check.
     * @return int Number of decimal places (0 for integers, 2 for floats).
     */
    private function getDecimalPlaces($value): int
    {
        // If it's an integer or has no decimal part, use 0 decimals
        if ((float) $value == (int) $value) {
            return 0;
        }

        // For floating point values, use 2 decimal places
        return 2;
    }

    /**
     * Format number in abbreviated notation.
     *
     * @param int|float $number Number to format.
     * @return string Abbreviated format (1K, 1.5M, 2B).
     */
    private function abbreviate($number): string
    {
        $thresholds = [
            'B' => 1000000000,
            'M' => 1000000,
            'K' => 1000,
        ];

        foreach ($thresholds as $suffix => $threshold) {
            if ($number >= $threshold) {
                $formatted = $number / $threshold;
                return round($formatted, 1) . $suffix;
            }
        }

        return (string) $number;
    }
}
