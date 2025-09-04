<?php

namespace WP_Statistics\CLI;

use Exception;
use WP_Statistics\Service\Analytics\VisitorProfile;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Models\OnlineModel;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Hits;

/**
 * WP Statistics CLI Command
 */
class CLI
{
    /**
     * Show summary of statistics.
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
     * @alias overview
     * @throws Exception
     */
    function summary($args, $assoc_args)
    {
        $visitorsModel = new VisitorsModel();
        $onlineModel   = new OnlineModel();

        $usersOnline = Helper::formatNumberWithUnit($onlineModel->countOnlines(), 1);

        \WP_CLI::line("Users Online: " . $usersOnline);

        $args = [
            'ignore_post_type' => true,
            'include_total'    => true,
            'exclude'          => ['last_week', 'last_month', '7days', '30days', '90days', '6months'],
        ];

        $data = $visitorsModel->getVisitorsHitsSummary($args);

        $items = [];
        foreach ($data as $key => $info) {
            $items[] = [
                'Time'     => $info['label'],
                'Visitors' => Helper::formatNumberWithUnit($info['visitors'], 1),
                'Views'    => Helper::formatNumberWithUnit($info['hits'], 1),
            ];
        }

        \WP_CLI\Utils\format_items(
            \WP_CLI\Utils\get_flag_value($assoc_args, 'format', 'table'),
            $items,
            array('Time', 'Visitors', 'Views')
        );
    }

    /**
     * Show list of users online.
     *
     * ## OPTIONS
     *
     * [--number=<number>]
     * : Number of users to return. Default 15.
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
     * @throws Exception
     */
    public function online($args, $assoc_args)
    {
        $number      = \WP_CLI\Utils\get_flag_value($assoc_args, 'number', 15);
        $onlineModel = new OnlineModel();

        $lists = $onlineModel->getOnlineVisitorsData(['page' => 1, 'per_page' => $number]);

        if (empty($lists)) {
            \WP_CLI::error("There are no users online.");
        }

        $columns = ['IP', 'Browser', 'Online For', 'Referrer', 'Page', 'User ID', 'Country'];
        $items   = [];

        foreach ($lists as $row) {
            $items[] = [
                'IP'         => $row->getIp() ?? '-',
                'Browser'    => $row->getBrowser()->getName() ?? '-',
                'Online For' => $row->getOnlineTime() ?? '-',
                'Referrer'   => wp_strip_all_tags($row->getReferral()->getRawReferrer() ?? '-'),
                'Page'       => $row->getLastPage()['title'] ?? '-',
                'User ID'    => !empty($row->getUserId()) ? $row->getUserId() : '-',
                'Country'    => $row->getLocation()->getCountryName() ?? '-',
            ];
        }

        \WP_CLI\Utils\format_items(
            \WP_CLI\Utils\get_flag_value($assoc_args, 'format', 'table'),
            $items,
            $columns
        );
    }

    /**
     * Show list of visitors.
     *
     * ## OPTIONS
     *
     * [--number=<number>]
     * : Number of visitors to return. Default 15.
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
     * @alias visitor
     * @throws Exception
     */
    public function visitors($args, $assoc_args)
    {
        $number       = \WP_CLI\Utils\get_flag_value($assoc_args, 'number', 15);
        $visitorModel = new VisitorsModel();
        $lists        = $visitorModel->getVisitorsData(['page' => 1, 'per_page' => $number]);

        if (empty($lists)) {
            \WP_CLI::error("There are no visitors.");
        }

        $columns = array('IP', 'Last View', 'Browser', 'Referrer', 'Operating System', 'User ID', 'Country');
        $items   = array();

        foreach ($lists as $row) {
            $items[] = [
                'IP'               => $row->getIp() ?? '-',
                'Last View'        => $row->getLastView() ?? '-',
                'Browser'          => $row->getBrowser()->getName() ?? '-',
                'Referrer'         => wp_strip_all_tags($row->getReferral()->getRawReferrer() ?? '-'),
                'Operating System' => $row->getOs()->getName() ?? '-',
                'User ID'          => !empty($row->getUserId()) ? $row->getUserId() : '-',
                'Country'          => $row->getLocation()->getCountryName() ?? '-',
            ];
        }

        \WP_CLI\Utils\format_items(
            \WP_CLI\Utils\get_flag_value($assoc_args, 'format', 'table'),
            $items,
            $columns
        );
    }

    /**
     * Create all database tables if missing.
     *
     * @throws Exception
     */
    public function create_tables($args, $assoc_args)
    {
        TableHandler::createAllTables();
        \WP_CLI::success('All WP Statistics tables created (if not already existing).');
    }

    /**
     * Record a hit.
     *
     * ## OPTIONS
     *
     * [--url=<url>]
     * [--ip=<ip>]
     * [--user_agent=<user_agent>]
     * [--referrer=<referrer>]
     * [--user_id=<user_id>]
     * [--request_uri=<request_uri>]
     *
     * @throws Exception
     */
    public function record($args, $assoc_args)
    {
        $visitorProfile = new VisitorProfile();
        $visitorProfile->__set('currentPageType', [
            'type'         => 'post',
            'id'           => 1,
            'search_query' => ''
        ]);

        // Map CLI args to VisitorProfile
        $map = [
            'url'         => 'referrer',
            'referrer'    => 'referrer',
            'user_id'     => 'userId',
            'request_uri' => 'requestUri',
        ];

        foreach ($map as $arg => $property) {
            if (!empty($assoc_args[$arg])) {
                $visitorProfile->__set($property, $assoc_args[$arg]);
            }
        }

        // Override server variables
        if (!empty($assoc_args['ip'])) {
            $_SERVER['REMOTE_ADDR'] = $assoc_args['ip'];
        }
        if (!empty($assoc_args['user_agent'])) {
            $_SERVER['HTTP_USER_AGENT'] = $assoc_args['user_agent'];
        }

        if (!empty($assoc_args['request_uri'])) {
            add_filter('wp_statistics_page_uri', function () use ($visitorProfile) {
                return $visitorProfile->getRequestUri();
            });
        }

        try {
            Hits::record($visitorProfile);
            \WP_CLI::success('Hit recorded successfully.');
        } catch (Exception $e) {
            \WP_CLI::error(sprintf('Exclusion matched: %s', $e->getMessage()));
        }
    }
}