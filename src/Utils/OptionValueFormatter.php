<?php

namespace WP_Statistics\Utils;

/**
 * Formats option/setting values for human-readable display.
 *
 * Used by tools/debug panels to show option values in a consistent format.
 *
 * @since 15.0.0
 */
class OptionValueFormatter
{
    /**
     * Format a value for display.
     *
     * Arrays/objects → JSON, bools → "true"/"false", null/empty → "-".
     *
     * @param mixed $value The value to format.
     * @return string Formatted value.
     */
    public static function format($value): string
    {
        if (is_array($value) || is_object($value)) {
            return wp_json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null || $value === '') {
            return '-';
        }

        return (string) $value;
    }
}
