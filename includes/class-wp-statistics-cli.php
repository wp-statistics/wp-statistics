<?php

namespace WP_STATISTICS;

use Exception;
use WP_Statistics\Service\Analytics\VisitorProfile;

/**
 * WP Statistics
 *
 * ## EXAMPLES
 *
 *      # show summary of statistics
 *      $ wp statistics summary
 *
 *      # get list of users online in WordPress
 *      $ wp statistics online
 *
 *      # show list of last visitors
 *      $ wp statistics visitors
 *
 * @package wp-cli
 */
class WP_STATISTICS_CLI extends \WP_CLI_Command
{

    /**
     * Show Summary of statistics.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Render output in a particular format.
     * ---
     * default: table
     * options:
     *   - table
     *   - csv
     *   - json
     *   - count
     *   - yaml
     * ---
     *
     * ## EXAMPLES
     *
     *      # show summary of statistics
     *      $ wp statistics summary
     *
     * @alias overview
     * @throws Exception
     */
    function summary($args, $assoc_args)
    {
        // Prepare Item
        \WP_CLI::line("Users Online: " . number_format(wp_statistics_useronline()));
        $items = array();

        foreach (array("Today", "Yesterday", "Week", "Month", "Year", "Total") as $time) {
            $item = array(
                'Time' => $time
            );

            foreach (array("Visitors", "Views") as $state) {
                $item[$state] = number_format((strtolower($state) == "visitors" ? wp_statistics_visitor(strtolower($time), null, true) : wp_statistics_visit(strtolower($time))));
            }

            $items[] = $item;
        }

        \WP_CLI\Utils\format_items($assoc_args['format'], $items, array('Time', 'Visitors', 'Views'));
    }

    /**
     * Show list of users online.
     *
     * ## OPTIONS
     *
     * [--number=<number>]
     * : Number of return user.
     *
     * [--format=<format>]
     * : Render output in a particular format.
     * ---
     * default: table
     * options:
     *   - table
     *   - csv
     *   - json
     *   - count
     *   - yaml
     * ---
     *
     * ## EXAMPLES
     *
     *      # show list of users online
     *      $ wp statistics online
     *
     *      # show list of five users online
     *      $ wp statistics online --number=5
     *
     * @throws Exception
     */
    public function online($args, $assoc_args)
    {
        // Get Number Of result
        $number = \WP_CLI\Utils\get_flag_value($assoc_args, 'number', 15);

        // Get List Of Users Online
        $lists = UserOnline::get(array('per_page' => $number));
        if (count($lists) < 1) {
            \WP_CLI::error("There are no users online.");
        }

        // Set Column
        $column = array('IP', 'Browser', 'Online For', 'Referrer', 'Page', 'User ID', 'Country');

        // Show List
        $items = array();
        foreach ($lists as $row) {
            $item = array(
                'IP'         => (isset($row['hash_ip']) ? $row['hash_ip'] : $row['ip']['value']),
                'Browser'    => $row['browser']['name'],
                'Online For' => $row['online_for'],
                'Referrer'   => wp_strip_all_tags($row['referred']),
                'Page'       => $row['page']['title'],
                'User ID'    => ((isset($row['user']) and isset($row['user']['ID']) and $row['user']['ID'] > 0) ? $row['user']['ID'] : '-')
            );

            $item['Country'] = $row['country']['name'];

            $items[] = $item;
        }

        \WP_CLI\Utils\format_items($assoc_args['format'], $items, $column);
    }

    /**
     * Show list of visitors.
     *
     * ## OPTIONS
     *
     * [--number=<number>]
     * : Number of return user.
     *
     * [--format=<format>]
     * : Render output in a particular format.
     * ---
     * default: table
     * options:
     *   - table
     *   - csv
     *   - json
     *   - count
     *   - yaml
     * ---
     *
     * ## EXAMPLES
     *
     *      # show list of visitors
     *      $ wp statistics visitors
     *
     *      # show list of last ten visitors
     *      $ wp statistics online --number=10
     *
     * @alias visitor
     * @throws Exception
     */
    public function visitors($args, $assoc_args)
    {
        // Get Number Of result
        $number = \WP_CLI\Utils\get_flag_value($assoc_args, 'number', 15);

        // Get List Of Users Online
        $lists = Visitor::get(array('per_page' => $number));
        if (count($lists) < 1) {
            \WP_CLI::error("There are no visitors.");
        }

        // Set Column
        $column = array('IP', 'Date', 'Browser', 'Referrer', 'Operating System', 'User ID', 'Country');

        // Show List
        $items = array();
        foreach ($lists as $row) {
            $item = array(
                'IP'               => (isset($row['hash_ip']) ? $row['hash_ip'] : $row['ip']['value']),
                'Date'             => $row['date'],
                'Browser'          => $row['browser']['name'],
                'Referrer'         => wp_strip_all_tags($row['referred']),
                'Operating System' => $row['platform'],
                'User ID'          => ((isset($row['user']) and isset($row['user']['ID']) and $row['user']['ID'] > 0) ? $row['user']['ID'] : '-')
            );

            $item['Country'] = $row['country']['name'];

            $items[] = $item;
        }

        \WP_CLI\Utils\format_items($assoc_args['format'], $items, $column);
    }

    /**
     * Reinitialize
     *
     * ## OPTIONS
     * ---
     * ## EXAMPLES
     *
     *      # Reinitialize WP Statistics plugin
     *      $ wp statistics reinitialize
     *
     * @throws Exception
     */
    public function reinitialize($args, $assoc_args)
    {
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-db.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-install.php';
        global $wpdb;
        Install::create_table(false);
        \WP_CLI::Success('Reinitialized WP Statistics Database!');
    }

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
     *      $ wp statistics record --url="https://example.com" --ip="123.456.789.0" --user_agent="Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_0_9; en-US) AppleWebKit/602.29 (KHTML, like Gecko) Chrome/49.0.1185.311 Safari/533" --referrer="https://referrer.com"
     *
     *      # Record a hit with full details
     *      $ wp statistics record --url="https://example.com" --ip="123.456.789.0" --user_agent="Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_0_9; en-US) AppleWebKit/602.29 (KHTML, like Gecko) Chrome/49.0.1185.311 Safari/533" --referrer="https://referrer.com" --user_id="1" --request_uri="/example-post"
     *
     *## Bash Script Example
     *
     *      #!/bin/bash
     *
     *      for i in {1..10}
     *      do
     *         wp statistics record --url="https://example.com" --ip="192.168.1.$i" --user_agent="Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_0_9; en-US) AppleWebKit/602.29 (KHTML, like Gecko) Chrome/49.0.1185.311 Safari/533" --referrer="https://referrer.com"
     *      done
     *
     * @throws Exception
     */
    public function record($args, $assoc_args)
    {
        // Create a new VisitorProfile instance
        $visitorProfile = new VisitorProfile();
        $visitorProfile->__set('currentPageType', [
            'type'         => 'post',
            'id'           => 1,
            'search_query' => ''
        ]);

        // Set properties from the command line arguments
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

            \WP_CLI::success('Hit recorded successfully.');
        } catch (Exception $e) {
            \WP_CLI::error(sprintf('Exclusion matched: %s', $e->getMessage()));
        }
    }
}

/**
 * Register Command
 */
\WP_CLI::add_command('statistics', '\\WP_STATISTICS\WP_STATISTICS_CLI');