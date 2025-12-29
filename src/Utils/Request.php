<?php

namespace WP_Statistics\Utils;

use WP_STATISTICS\Helper;
use WP_Statistics\Service\Tracking\TrackerHelper;

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
     * Determine whether the current HTTP request targets the WordPress REST API.
     *
     * The check order is:
     *  1. `wp_doing_rest()` – earliest, cheapest and most reliable on WP 5.0+.
     *  2. The plugin’s legacy “Bypass Ad‑Blockers” tracking‑pixel helper.
     *  3. A string match against the REST prefix inside `REQUEST_URI`.
     *  4. Existence of a `rest_route` query argument in `GET` or `POST`.
     *
     * @return bool True when handling a REST request, false otherwise.
     */
    public static function isRestApiCall()
    {
        if (empty($_SERVER['REQUEST_URI'])) {
            return false;
        }

        if (TrackerHelper::isBypassAdBlockersRequest()) {
            return true;
        }

        $restPrefix = trailingslashit(rest_get_url_prefix());
        return (false !== strpos($_SERVER['REQUEST_URI'], $restPrefix)) || isset($_REQUEST['rest_route']);
    }

    /**
     * Cached request data.
     *
     * @var array|null
     */
    private static $requestDataCache = null;

    /**
     * Get request data from JSON body or POST parameters.
     *
     * Reads the request body and attempts to parse it as JSON first (for React/API requests
     * with Content-Type: application/json), then falls back to $_POST for form-urlencoded requests.
     *
     * Note: php://input can only be read once per request, so the result is cached.
     *
     * @return array The request data as an associative array.
     */
    public static function getRequestData()
    {
        // Return cached data if already read (php://input can only be read once)
        if (self::$requestDataCache !== null) {
            return self::$requestDataCache;
        }

        // Read php://input once
        $rawBody = file_get_contents('php://input');

        // Try to parse JSON body (React sends application/json)
        if (!empty($rawBody)) {
            $decoded = json_decode($rawBody, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                self::$requestDataCache = $decoded;
                return self::$requestDataCache;
            }
        }

        // Fallback to $_POST for form-urlencoded requests
        self::$requestDataCache = $_POST;
        return self::$requestDataCache;
    }

    /**
     * Reset cached request data.
     *
     * Used primarily for testing to clear the static cache
     * between test methods.
     *
     * @return void
     */
    public static function resetRequestDataCache(): void
    {
        self::$requestDataCache = null;
    }
}
