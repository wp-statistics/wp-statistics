<?php

namespace WP_Statistics\Service\CLI\Commands;

use WP_CLI;
use WP_STATISTICS\Hits;
use WP_Statistics\Service\Analytics\VisitorProfile;
use Exception;

/**
 * Record a hit manually.
 *
 * @since 15.0.0
 */
class RecordCommand
{
    /**
     * Record a hit.
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
     *      # Record a hit for a specific URL
     *      $ wp statistics record --url="https://example.com"
     *
     *      # Record a hit for a specific URL and IP address
     *      $ wp statistics record --url="https://example.com" --ip="123.456.789.0"
     *
     *      # Record a hit with additional user agent and referrer
     *      $ wp statistics record --url="https://example.com" --ip="123.456.789.0" \
     *        --user_agent="Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)" \
     *        --referrer="https://referrer.com"
     *
     *      # Record a hit with full details
     *      $ wp statistics record --url="https://example.com" --ip="123.456.789.0" \
     *        --user_agent="Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)" \
     *        --referrer="https://referrer.com" --user_id="1" --request_uri="/example-post"
     *
     * ## BASH SCRIPT EXAMPLE
     *
     *      #!/bin/bash
     *      for i in {1..10}
     *      do
     *         wp statistics record --url="https://example.com" --ip="192.168.1.$i" \
     *           --user_agent="Mozilla/5.0" --referrer="https://referrer.com"
     *      done
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function __invoke($args, $assoc_args)
    {
        // Create a new VisitorProfile instance
        $visitorProfile = new VisitorProfile();
        $visitorProfile->__set('currentPageType', [
            'type'         => 'post',
            'id'           => 1,
            'search_query' => '',
        ]);

        // Set properties from command line arguments
        if (isset($assoc_args['url'])) {
            $visitorProfile->__set('referrer', $assoc_args['url']);
        }

        if (isset($assoc_args['ip'])) {
            $_SERVER['REMOTE_ADDR'] = $assoc_args['ip'];
        }

        if (isset($assoc_args['user_agent'])) {
            $_SERVER['HTTP_USER_AGENT'] = $assoc_args['user_agent'];
        }

        if (isset($assoc_args['referrer'])) {
            $visitorProfile->__set('referrer', $assoc_args['referrer']);
        }

        if (isset($assoc_args['user_id'])) {
            $visitorProfile->__set('userId', $assoc_args['user_id']);
        }

        if (isset($assoc_args['request_uri'])) {
            $visitorProfile->__set('requestUri', $assoc_args['request_uri']);

            add_filter('wp_statistics_page_uri', function () use ($visitorProfile) {
                return $visitorProfile->getRequestUri();
            });
        }

        // Record the hit
        try {
            Hits::record($visitorProfile);
            WP_CLI::success('Hit recorded successfully.');
        } catch (Exception $e) {
            WP_CLI::error(sprintf('Exclusion matched: %s', $e->getMessage()));
        }
    }
}
