<?php

namespace WP_Statistics\Utils;

use WP_STATISTICS\Helper;

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

        switch ($return) {
            case 'number':
                return intval($value);
            case 'text':
                return sanitize_textarea_field($value);
            case 'string':
            default:
                return sanitize_text_field($value);
        }
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
     * Clean a URI by removing invalid characters and preserving valid URL parts including query parameters.
     *
     * @param string $input The input URI string to clean.
     * @return string The cleaned URI with valid URL parts.
     */
    public static function cleanUri($input)
    {
        // Define a regular expression to match valid URL parts up to invalid characters.
        // The pattern matches:
        // - ^\/: The URI must start with a slash.
        // - [a-zA-Z0-9-\/]*: Any combination of letters, numbers, hyphens, and additional slashes.
        // - (\?[a-zA-Z0-9-_=&%]*)?: An optional query string starting with '?', followed by any combination of allowed characters.
        $valid_pattern = '/^\/[a-zA-Z0-9-\/]*(\?[a-zA-Z0-9-_=&%]*)?/';

        // Use preg_match to find the valid URL part.
        // preg_match returns the matches in the $matches array.
        if (preg_match($valid_pattern, $input, $matches)) {
            // Return the first match which is the valid URL part.
            return $matches[0];
        }

        // Return an empty string if no valid URL part is found.
        return '';
    }
}
