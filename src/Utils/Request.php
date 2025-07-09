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

    /**
     * Validates parameters against rules and returns detailed error messages
     *
     * @param array $params Validation rules
     * @return array Array of validation error messages (empty if valid)
     */
    public static function getValidationErrors($params)
    {
        $errors = [];

        foreach ($params as $param => $validation) {
            // Check required parameters
            if (!isset($_REQUEST[$param])) {
                if (!empty($validation['required'])) {
                    $errors[] = sprintf("Missing required parameter: %s", $param);
                }
                continue;
            }

            $paramValue = $_REQUEST[$param];

            // Skip validation for empty nullable values
            if (!empty($validation['nullable']) && !is_numeric($paramValue) && empty($paramValue)) {
                continue;
            }

            // Check type specification
            if (!isset($validation['type'])) {
                $errors[] = sprintf("No validation type specified for parameter: %s", $param);
                continue;
            }

            // Handle encoding
            if (!empty($validation['encoding'])) {
                try {
                    if ($validation['encoding'] === 'base64') {
                        $decoded = base64_decode($paramValue, true);
                        if ($decoded === false) {
                            $errors[] = sprintf("Failed to base64 decode parameter: %s", $param);
                            continue;
                        }
                        $paramValue = $decoded;
                    } else if ($validation['encoding'] === 'url') {
                        $paramValue = urldecode($paramValue);
                    }
                } catch (Exception $e) {
                    $errors[] = sprintf("Decoding failed for parameter %s: %s", $param, $e->getMessage());
                    continue;
                }
            }

            // Type-specific validation
            switch ($validation['type']) {
                case 'string':
                    if (!is_string($paramValue)) {
                        $errors[] = sprintf("Parameter %s must be a string, %s given", $param, gettype($paramValue));
                        break;
                    }

                    if (isset($validation['minlength']) && strlen($paramValue) < $validation['minlength']) {
                        $errors[] = sprintf(
                            "Parameter %s too short (min %d characters, got %d)",
                            $param,
                            $validation['minlength'],
                            strlen($paramValue)
                        );
                    }

                    if (isset($validation['maxlength']) && strlen($paramValue) > $validation['maxlength']) {
                        $errors[] = sprintf(
                            "Parameter %s too long (max %d characters, got %d)",
                            $param,
                            $validation['maxlength'],
                            strlen($paramValue)
                        );
                    }
                    break;

                case 'number':
                    if (!is_numeric($paramValue)) {
                        $errors[] = sprintf("Parameter %s must be numeric, %s given", $param, gettype($paramValue));
                        break;
                    }

                    if (isset($validation['min']) && $paramValue < $validation['min']) {
                        $errors[] = sprintf(
                            "Parameter %s value %s below minimum allowed %s",
                            $param,
                            $paramValue,
                            $validation['min']
                        );
                    }

                    if (isset($validation['max']) && $paramValue > $validation['max']) {
                        $errors[] = sprintf(
                            "Parameter %s value %s above maximum allowed %s",
                            $param,
                            $paramValue,
                            $validation['max']
                        );
                    }
                    break;

                case 'url':
                    if (!filter_var($paramValue, FILTER_VALIDATE_URL)) {
                        $errors[] = sprintf("Parameter %s is not a valid URL: %s", $param, $paramValue);
                    }
                    break;

                default:
                    $errors[] = sprintf("Unknown validation type '%s' for parameter %s", $validation['type'], $param);
                    break;
            }

            // Check for invalid patterns
            if (isset($validation['invalid_pattern'])) {
                $patterns = is_array($validation['invalid_pattern'])
                    ? $validation['invalid_pattern']
                    : [$validation['invalid_pattern']];

                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $paramValue)) {
                        $errors[] = sprintf(
                            "Parameter %s contains potentially dangerous content matching pattern: %s",
                            $param,
                            $pattern
                        );
                        break;
                    }
                }
            }

            // Check for required valid patterns
            if (isset($validation['valid_pattern'])) {
                $patterns = is_array($validation['valid_pattern'])
                    ? $validation['valid_pattern']
                    : [$validation['valid_pattern']];

                foreach ($patterns as $pattern) {
                    if (!preg_match($pattern, $paramValue)) {
                        $errors[] = sprintf(
                            "Parameter %s does not match required format pattern: %s",
                            $param,
                            $pattern
                        );
                        break;
                    }
                }
            }
        }

        return $errors;
    }
}
