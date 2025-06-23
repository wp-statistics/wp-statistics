<?php

namespace WP_Statistics\Utils;

final class Format
{
    /**
     * Converts a large number to a short “12.3K / 4.5M” style string.
     *
     * @param float|int $number   The number to format.
     * @param int       $decimals Decimal precision.
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
     * @example 106.2.124.0 -> 106.0.0.0
     * @param string $version
     * @return string
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
     * @param string     $value      The raw string to split.
     * @param string     $separator  Delimiter that separates segments.
     * @param int|string $index      Segment index to return (0‑based).
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
}
