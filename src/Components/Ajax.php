<?php

namespace WP_Statistics\Components;

class Ajax
{
    /**
     * Registers an Ajax action with WordPress.
     *
     * @param string $action The name of the action to register.
     * @param callable $callback The function to call when the action is triggered.
     * @param bool $public Whether to register the action as public (i.e. accessible via non-logged-in users).
     *
     * @since 1.0.0
     */
    public static function register($action, $callback, $public = true)
    {
        add_action('wp_ajax_wp_statistics_' . $action, $callback);

        if ($public) {
            add_action('wp_ajax_nopriv_wp_statistics_' . $action, $callback);
        }
    }

    /**
     * Sends a JSON response back to an Ajax request.
     *
     * @param bool $success Whether the request was successful.
     * @param string $status The status of the request (e.g. "success", "error", "warning").
     * @param string $message Optional. The message to send back to the client.
     * @param mixed $data Optional. The data to send back to the client.
     * @param int $code Optional. The HTTP status code to output. Default null.
     */
    public static function send($success, $status, $message = null, $data = null, $code = null)
    {
        $response = [
            'success' => (bool) $success,
            'status'  => $status
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        wp_send_json($response, $code);
    }

    /**
     * Sends a JSON response back to an Ajax request, indicating success.
     *
     * @param string|null $message Optional. The message to send back to the client.
     * @param mixed|null $data Optional. The data to send back to the client.
     * @param int|null $code Optional. The HTTP status code to output. Default 200.
     */
    public static function success($message = null, $data = null, $code = 200)
    {
        self::send(true, 'success', $message, $data, $code);
    }

    /**
     * Sends a JSON response back to an Ajax request, indicating failure.
     *
     * @param string $message The message to send back to the client.
     * @param mixed|null $data Optional. The data to send back to the client.
     * @param int|null $code Optional. The HTTP status code to output. Default 400.
     */
    public static function error($message, $data = null, $code = 400)
    {
        self::send(false, 'error', $message, $data, $code);
    }
}