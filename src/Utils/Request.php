<?php

namespace WP_Statistics\Utils;

use WP_STATISTICS\Helper;
use Exception;

class Request
{
    /**
     * Retrieves a value from the $_REQUEST array based on the given parameter.
     *
     * @param string $param The parameter to retrieve from the $_REQUEST array.
     * @param mixed $default The default value to return if the parameter is not found in $_REQUEST.
     * @param string $return The type of value to return. Valid options are 'number', 'text', and 'string'.
     * @return mixed The retrieved value from the $_REQUEST array, or the default value if the parameter is not found.
     */
    public static function get($param, $default = false, $return = 'string')
    {
        if (empty($_REQUEST[$param])) return $default;

        $value = $_REQUEST[$param];

        if ($return === 'string') {
            return sanitize_text_field($value);
        }

        if ($return === 'url') {
            return sanitize_url($value);
        }

        if ($return === 'number') {
            return intval($value);
        }

        if ($return === 'text') {
            return sanitize_textarea_field($value);
        }

        if ($return === 'bool') {
            return boolval($value);
        }

        if ($return === 'array' && is_array($value)) {
            return array_map('sanitize_text_field', $value);
        }

        return $value;
    }


    /**
     * Retrieves parameters from the $_REQUEST array based on the given keys,
     * sanitizes the values, and returns the result.
     *
     * @param array $params The keys to filter the $_REQUEST array by.
     * @return array The filtered and sanitized parameters.
     */
    public static function getParams($params)
    {
        $result = Helper::filterArrayByKeys($_REQUEST, $params);

        foreach ($result as $key => $value) {
            if (is_string($value)) {
                $result[$key] = sanitize_text_field($value);
            }
        }

        return $result;
    }

    /**
     * Checks if a parameter is set in the $_REQUEST super-global.
     *
     * @param string $param The name of the parameter to check.
     * @return bool Returns true if the parameter is set, false otherwise.
     */
    public static function has($param)
    {
        return !empty($_REQUEST[$param]);
    }


    /**
     * Compares the value of a given parameter with a specified value.
     *
     * @param string $param The name of the parameter to compare.
     * @param mixed $value The value to compare against. Can be an array to compare against multiple values.
     * @param mixed $strict If true, the comparison will be strict. If not, the comparison will be loose.
     * @return bool Returns true if the parameter value is equal to the specified value, false otherwise.
     */
    public static function compare($param, $value, $strict = false)
    {
        if (empty($_REQUEST[$param])) return false;

        $paramValue = $_REQUEST[$param];

        if (is_array($value)) {
            return in_array($paramValue, $value);
        } else {
            return $strict ? $paramValue === $value : $paramValue == $value;
        }

    }

    /**
     * Validates a request parameter against the given rules.
     *
     * Supported rules:
     * - 'required'        => (bool) Whether the parameter is required.
     * - 'nullable'        => (bool) If true, allows empty value (except numeric zero).
     * - 'type'            => (string) 'string', 'number', or 'url'.
     * - 'encoding'        => (string) Optional decoding: 'base64' or 'url'.
     * - 'minlength'       => (int) Minimum string length (for 'string' type).
     * - 'maxlength'       => (int) Maximum string length (for 'string' type).
     * - 'min'             => (int|float) Minimum numeric value (for 'number' type).
     * - 'max'             => (int|float) Maximum numeric value (for 'number' type).
     * - 'invalid_pattern' => (string|array) One or more regex patterns that should not match.
     * - 'valid_pattern'   => (string|array) One or more regex patterns that must match.
     *
     * @param string $param The name of the parameter to validate (from $_REQUEST).
     * @param array $rules An associative array of validation rules.
     *
     * @return bool True if validation passes, false otherwise.
     */
    public static function validate($param, $rules)
    {
        if (!isset($_REQUEST[$param])) {
            if (empty($rules['required'])) {
                return true;
            } else {
                return false;
            }
        }

        $paramValue = $_REQUEST[$param];

        if (!empty($rules['nullable']) && !is_numeric($paramValue) && empty($paramValue)) {
            return true;
        }

        // Type must be specified
        if (!isset($rules['type'])) {
            return false;
        }

        // Decode value if needed
        if (!empty($rules['encoding'])) {
            if ($rules['encoding'] === 'base64') {
                $decoded = base64_decode($paramValue, true);
                if ($decoded === false) return false;
                $paramValue = $decoded;
            } elseif ($rules['encoding'] === 'url') {
                $paramValue = urldecode($paramValue);
            }
        }

        switch ($rules['type']) {
            case 'string':
                if (!is_string($paramValue)) return false;
                if (isset($rules['minlength']) && strlen($paramValue) < $rules['minlength']) return false;
                if (isset($rules['maxlength']) && strlen($paramValue) > $rules['maxlength']) return false;
                break;
            case 'number':
                if (!is_numeric($paramValue)) return false;
                if (isset($rules['min']) && $paramValue < $rules['min']) return false;
                if (isset($rules['max']) && $paramValue > $rules['max']) return false;
                break;
            case 'url':
                if (!filter_var($paramValue, FILTER_VALIDATE_URL)) return false;
                break;
            default:
                return false;
        }

        // Check invalid patterns
        if (!empty($rules['invalid_pattern'])) {
            $patterns = is_array($rules['invalid_pattern']) ? $rules['invalid_pattern'] : [$rules['invalid_pattern']];
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $paramValue)) return false;
            }
        }

        // Check valid patterns
        if (!empty($rules['valid_pattern'])) {
            $patterns = is_array($rules['valid_pattern']) ? $rules['valid_pattern'] : [$rules['valid_pattern']];
            foreach ($patterns as $pattern) {
                if (!preg_match($pattern, $paramValue)) return false;
            }
        }

        return true;
    }

    /**
     * Checks where the current request is coming from.
     *
     * @param string $type The type of request to check for. Can be 'admin', 'ajax', 'cron', 'wp-cli', or 'public'.
     * @return bool True if the request is from the specified type, false otherwise.
     */
    public static function isFrom($type)
    {
        if ($type === 'admin') {
            return is_admin();
        }

        if ($type === 'ajax') {
            return defined('DOING_AJAX');
        }

        if ($type === 'cron') {
            return defined('DOING_CRON');
        }

        if ($type === 'wp-cli') {
            return defined('WP_CLI') && WP_CLI;
        }

        return false;
    }
}
