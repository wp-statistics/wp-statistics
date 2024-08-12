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
            case 'array':
                return array_map('sanitize_text_field', $value);
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
     * Validates query params value.
     *
     * @param array $params Array of params to validate, each param can be an array with type, minlength and regex.
     * @return bool Returns true if all params are valid, false otherwise.
     *
     * Example usage:
     * $params = [
     *     'username' => [
     *         'type'           => 'string',
     *         'required'       => true,
     *         'nullable'       => false,
     *         'minlength'      => 5,
     *         'valid_pattern'  => '/^[a-zA-Z0-9_]+$/'
     *     ],
     *     'age' => [
     *         'type'            => 'integer',
     *         'required'        => false,
     *         'minlength'       => 1,
     *         'invalid_pattern' => '/^\d+$/'
     *     ]
     * ];
     *
     * if (validate($params)) {
     *     // All parameters are valid
     * } else {
     *     // One or more parameters are invalid
     * }
     */
    public static function validate($params)
    {
        foreach ($params as $param => $validation) {
            // Skip if value is not required and param is not set
            if (!isset($_REQUEST[$param])) {
                if (empty($validation['required'])) {
                    continue;
                } else {
                    return false;
                }
            }

            $paramValue = $_REQUEST[$param];

            // Skip if value is empty and param is nullable
            if (!empty($validation['nullable']) && !is_numeric($paramValue) && empty($paramValue)) {
                continue;
            }

            // Return false if type is not specified
            if (!isset($validation['type'])) {
                return false;
            }

            // Decode if it's base64 encoded
            if (!empty($validation['encoding'])) {
                if ($validation['encoding'] === 'base64') {
                    $paramValue = base64_decode($paramValue);
                } else if ($validation['encoding'] === 'url') {
                    $paramValue = urldecode($paramValue);
                }
            }

            switch ($validation['type']) {
                case 'string':
                    // Validate type
                    if (!is_string($paramValue)) {
                        return false;
                    }

                    // Validate minlength
                    if (isset($validation['minlength']) && strlen($paramValue) < $validation['minlength']) {
                        return false;
                    }

                    // Validate maxlength
                    if (isset($validation['maxlength']) && strlen($paramValue) > $validation['maxlength']) {
                        return false;
                    }
                    break;
                case 'number':
                    // Validate type
                    if (!is_numeric($paramValue)) {
                        return false;
                    }

                    // Validate min
                    if (isset($validation['min']) && $paramValue < $validation['min']) {
                        return false;
                    }

                    // Validate max
                    if (isset($validation['max']) && $paramValue > $validation['max']) {
                        return false;
                    }
                    break;
                case 'url':
                    // Validate url
                    if (!filter_var($paramValue, FILTER_VALIDATE_URL)) {
                        return false;
                    }
                    break;
                default:
                    return false;
            }

            // Invalid pattern
            if (isset($validation['invalid_pattern'])) {
                if (is_string($validation['invalid_pattern'])) {
                    if (preg_match($validation['invalid_pattern'], $paramValue)) {
                        return false;
                    }
                }

                if (is_array($validation['invalid_pattern'])) {
                    foreach ($validation['invalid_pattern'] as $pattern) {
                        if (preg_match($pattern, $paramValue)) {
                            return false;
                        }
                    }
                }
            }

            // Valid pattern
            if (isset($validation['valid_pattern'])) {
                if (is_string($validation['valid_pattern'])) {
                    if (!preg_match($validation['valid_pattern'], $paramValue)) {
                        return false;
                    }
                }

                if (is_array($validation['valid_pattern'])) {
                    foreach ($validation['valid_pattern'] as $pattern) {
                        if (!preg_match($pattern, $paramValue)) {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

}
