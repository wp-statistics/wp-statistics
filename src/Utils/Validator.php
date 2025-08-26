<?php

namespace WP_Statistics\Utils;

/**
 * Utility class for validating input values.
 *
 * Provides methods for detecting patterns commonly associated with
 * injection or XSS attacks and for validating input length ranges.
 * Designed to support basic input sanitization and security checks.
 *
 * @package WP_Statistics\Utils
 * @since 15.0.0
 */
class Validator
{
    /**
     * Returns a list of regular‑expression patterns that match potentially
     * malicious input such as SQL‑injection or XSS attempts.
     *
     * @return array Array of regex patterns.
     */
    public static function getThreatPatterns()
    {
        $patterns = [
            // SQL keywords
            '/[\'"\(](?:\s|%20)*UNION(?:\s|%20)*SELECT\b/i',
            '/[\'"\(](?:\s|%20)*INSERT(?:\s|%20)*INTO\b/i',
            '/[\'"\(](?:\s|%20)*UPDATE\b/i',
            '/[\'"\(](?:\s|%20)*DELETE\b/i',
            '/[\'"\(](?:\s|%20)*SELECT\b/i',
            '/[\'"\(](?:\s|%20)*DROP\b/i',
            '/[\'"\(](?:\s|%20)*ALTER\b/i',

            // SQL comments
            '/[\'"\(](?:\s|%20)*--(?:\s|%20)*/i',
            '/[\'"\(](?:\s|%20)*#(?:\s|%20)*/i',

            // Logical‑operator injection
            '/[\'"\(](?:\s|%20)*OR(?:\s|%20)*\d+(?:\s|%20)*=(?:\s|%20)*\d+/i',
            '/[\'"\(](?:\s|%20)*XOR(?:\s|%20)*/i',

            // Function‑based injection
            '/(?:\s|%20)*now\(/i',
            '/(?:\s|%20)*sysdate\(/i',
            '/(?:\s|%20)*sleep\(/i',
            '/[\'"\(](?:\s|%20)*benchmark(?:\s|%20)*\(\d+,(?:\s|%20)*/i',

            // XSS
            '/<script\b[^>]*>(.*?)<\/script>/is',
            '/<[^>]+on[a-z]+\s*=\s*"[^"]*"/i',
            '/<[^>]+on[a-z]+\s*=\s*\'[^\']*\'/i',

            // URL‑encoded variants
            '/(?:%27|%22|%28)(?:\s|%20)*UNION(?:\s|%20)*SELECT/i',
            '/(?:%27|%22|%28)(?:\s|%20)*OR(?:\s|%20)*1(?:\s|%20)*=(?:\s|%20)*1/i',
            '/(?:%27|%22|%28)(?:\s|%20)*XOR(?:\s|%20)*/i',
            '/(?:%27|%22|%28)(?:\s|%20)*SLEEP(?:\s|%20)*\(/i',
        ];

        return apply_filters('wp_statistics_injection_patterns', $patterns);
    }

    /**
     * Checks whether a string's length falls within a specified inclusive range.
     *
     * @param string $string The input string to test.
     * @param int $min Minimum accepted length.
     * @param int $max Maximum accepted length.
     *
     * @return bool True when the length is between $min and $max, inclusive.
     */
    public static function isLengthInRange($string, $min, $max)
    {
        $len = strlen($string);

        return $len >= $min && $len <= $max;
    }

    /**
     * Determines whether a given value represents an "unknown" state.
     *
     * This method considers the value unknown if it is empty, equals the string
     * "Unknown", or matches the translated string "Unknown" in the
     * 'wp-statistics' text domain.
     *
     * @param mixed $value The value to evaluate.
     * @return bool True if the value is considered unknown, false otherwise.
     */
    public static function isUnknown($value)
    {
        if (
            empty($value) ||
            $value === 'Unknown' ||
            $value === __('Unknown', 'wp-statistics')
        ) {
            return true;
        }

        return false;
    }
}