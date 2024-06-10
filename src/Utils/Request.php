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
     * @param mixed $value The value to compare against.
     * @param mixed $strict If true, the comparison will be strict. If not, the comparison will be loose.
     * @return bool Returns true if the parameter value is equal to the specified value, false otherwise.
     */
    public static function compare($param, $value, $strict = false)
    {
        if (empty($_REQUEST[$param])) return false;

        $paramValue = $_REQUEST[$param];

        return $strict
            ? $paramValue === $value 
            : $paramValue == $value;
    }
}
