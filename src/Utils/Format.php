<?php

namespace WP_Statistics\Utils;

/**
 * Utility class for formatting values.
 *
 * Provides methods for formatting numbers, version strings,
 * and size representations. Useful for display logic and
 * consistent value transformations across the application.
 *
 * @package WP_Statistics\Utils
 * @since 15.0.0
 */
class Format
{
    /**
     * Converts a large number to a short “12.3K / 4.5M” style string.
     *
     * @param float|int $number The number to format.
     * @param int $decimals Decimal precision.
     * @return string
     */
    public static function formatNumberWithUnit($number, $precision = 1)
    {
        if (!is_numeric($number)) {
            return 0;
        }

        if ($number < 1000) {
            return !empty($precision) ? round($number, $precision) : $number;
        }

        $originalNumber = $number;
        $units          = ['', 'K', 'M', 'B', 'T'];

        $exponent = (int)floor(log($number, 1000));
        $exponent = min($exponent, count($units) - 1);

        $number /= pow(1000, $exponent);
        $unit   = $units[$exponent];

        $factor = ($originalNumber < 10000) ? 100 : 10;

        $formattedNumber = floor($number * $factor) / $factor . $unit;

        return $formattedNumber;
    }

    /**
     * Anonymise a semantic version by stripping the patch segment.
     *
     * @param string $version
     * @return string
     * @example 106.2.124.0 -> 106.0.0.0
     */
    public static function anonymizeVersion($version)
    {
        $mainVersion         = substr($version, 0, strpos($version, '.'));
        $subVersion          = substr($version, strpos($version, '.') + 1);
        $anonymousSubVersion = preg_replace('/[0-9]+/', '0', $subVersion);

        return "{$mainVersion}.{$anonymousSubVersion}";
    }

    /**
     * Extracts a segment from a delimiter‑separated string.
     *
     * Examples:
     *   "mobile:smart" → "mobile"   (default $index = 0, $separator = ':')
     *   "news/world"   → "world"    (with $index = 1,  $separator = '/')
     *
     * @param string $value The raw string to split.
     * @param string $separator Delimiter that separates segments.
     * @param int|string $index Segment index to return (0‑based).
     * @return string                The requested segment, or original string
     *                               if the index does not exist.
     */
    public static function getSegment($value, $separator = ':', $index = 0)
    {
        if (strpos($value, $separator) !== false) {
            $parts = explode($separator, $value);

            if (isset($parts[$index])) {
                return $parts[$index];
            }
        }

        return $value;
    }

    /**
     * Convert a size string (e.g. "128M", "1G") to its equivalent in bytes.
     *
     * Accepts size suffixes K, M, and G (case-insensitive).
     * The method multiplies values successively for larger units.
     *
     * @param string $input The size string to convert.
     * @return int Size in bytes.
     */
    public static function sizeToBytes($input)
    {
        $unit  = strtoupper(substr($input, -1));
        $value = (int)$input;
        switch ($unit) {
            case 'G':
                $value *= 1024;
            case 'M':
                $value *= 1024;
            case 'K':
                $value *= 1024;
        }
        return $value;
    }

    /**
     * Calculates the percentage change between two numbers.
     *
     * Returns a positive value for increase, negative for decrease, 0 if equal.
     *
     * @param int|float $previous The previous/baseline value.
     * @param int|float $current  The current value.
     * @param int       $decimals Number of decimal places to round to.
     *
     * @return float The percentage change.
     */
    public static function calculatePercentageChange($previous, $current, $decimals = 2)
    {
        $previous = intval($previous);
        $current  = intval($current);

        if ($previous == $current) {
            return 0;
        }

        $multiply = ($previous > $current) ? -1 : 1;
        $change   = abs($current - $previous);
        $result   = $previous == 0 ? $change : ($change / $previous);
        $result   *= 100 * $multiply;

        return round($result, $decimals);
    }
}
