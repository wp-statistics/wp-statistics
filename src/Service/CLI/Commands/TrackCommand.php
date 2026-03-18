<?php

namespace WP_Statistics\Service\CLI\Commands;

use WP_CLI;
use WP_Statistics\Service\Tracking\Core\Tracker;
use WP_Statistics\Utils\Signature;
use Exception;

/**
 * Track a hit manually.
 *
 * @since 15.0.0
 */
class TrackCommand
{
    /**
     * Track a hit.
     *
     * ## OPTIONS
     *
     * [--url=<url>]
     * : The URL to record the hit for.
     *
     * [--ip=<ip>]
     * : The IP address of the visitor.
     *
     * [--user_agent=<user_agent>]
     * : The HTTP user agent of the visitor.
     *
     * [--referrer=<referrer>]
     * : The referrer URL.
     *
     * [--user_id=<user_id>]
     * : The user ID of the visitor.
     *
     * [--request_uri=<request_uri>]
     * : The request URI.
     *
     * ## EXAMPLES
     *
     *      # Track a hit for a specific URL
     *      $ wp statistics track --url="https://example.com"
     *
     *      # Track a hit for a specific URL and IP address
     *      $ wp statistics track --url="https://example.com" --ip="123.456.789.0"
     *
     *      # Track a hit with additional user agent and referrer
     *      $ wp statistics track --url="https://example.com" --ip="123.456.789.0" \
     *        --user_agent="Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)" \
     *        --referrer="https://referrer.com"
     *
     *      # Track a hit with full details
     *      $ wp statistics track --url="https://example.com" --ip="123.456.789.0" \
     *        --user_agent="Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)" \
     *        --referrer="https://referrer.com" --user_id="1" --request_uri="/example-post"
     *
     * ## BASH SCRIPT EXAMPLE
     *
     *      #!/bin/bash
     *      for i in {1..10}
     *      do
     *         wp statistics track --url="https://example.com" --ip="192.168.1.$i" \
     *           --user_agent="Mozilla/5.0" --referrer="https://referrer.com"
     *      done
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function __invoke($args, $assoc_args)
    {
        // Set server globals from CLI arguments
        if (isset($assoc_args['ip'])) {
            $_SERVER['REMOTE_ADDR'] = $assoc_args['ip'];
        }

        if (isset($assoc_args['user_agent'])) {
            $_SERVER['HTTP_USER_AGENT'] = $assoc_args['user_agent'];
        }

        // Build the $_REQUEST params expected by Payload::parse()
        $resourceType = 'post';
        $resourceId   = 1;
        $userId       = isset($assoc_args['user_id']) ? (int) $assoc_args['user_id'] : 0;
        $requestUri   = isset($assoc_args['request_uri']) ? $assoc_args['request_uri'] : '/';

        $_REQUEST['resource_type'] = $resourceType;
        $_REQUEST['resource_id']   = $resourceId;
        $_REQUEST['resource_uri']  = base64_encode($requestUri);
        $_REQUEST['user_id']       = $userId;
        $_REQUEST['timezone']      = wp_timezone_string();
        $_REQUEST['language_code'] = get_locale();
        $_REQUEST['language_name'] = get_locale();
        $_REQUEST['screen_width']  = '1920';
        $_REQUEST['screen_height'] = '1080';

        if (isset($assoc_args['referrer'])) {
            $_REQUEST['referrer'] = base64_encode($assoc_args['referrer']);
        } else {
            $_REQUEST['referrer'] = '';
        }

        // Generate a valid signature so the pipeline accepts the request
        $_REQUEST['signature'] = Signature::generate([
            $resourceType,
            $resourceId,
            $userId,
        ]);

        // Record the hit through the standard pipeline
        try {
            (new Tracker())->record();
            WP_CLI::success('Hit tracked successfully.');
        } catch (Exception $e) {
            WP_CLI::error(sprintf('Exclusion matched: %s', $e->getMessage()));
        }
    }
}
