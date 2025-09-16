<?php

namespace WP_Statistics\CLI;

use Exception;
use WP_Statistics\Service\Analytics\VisitorProfile;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Hits;

/**
 * WP Statistics CLI Command
 */
class CliCommands
{
    /**
     * Data provider instance for CLI commands.
     *
     * @var CliDataProvider
     */
    protected $dataProvider;

    /**
     * Constructor.
     *
     * Initializes the CLI commands.
     */
    public function __construct()
    {
        $this->dataProvider = new CliDataProvider();
    }

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
    public function summary($args, $assocArgs)
    {
        $data        = $this->dataProvider->getSummaryData();
        $usersOnline = Helper::formatNumberWithUnit($data['online'], 1);

        \WP_CLI::line("Users Online: " . $usersOnline);

        $items = [];
        foreach ($data['labels'] as $i => $label) {
            $items[] = [
                'Time'     => $label,
                'Visitors' => Helper::formatNumberWithUnit($data['visitors'][$i], 1),
                'Views'    => Helper::formatNumberWithUnit($data['hits'][$i], 1),
            ];
        }

        \WP_CLI\Utils\format_items(
            \WP_CLI\Utils\get_flag_value($assocArgs, 'format', 'table'),
            $items,
            ['Time', 'Visitors', 'Views']
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
    public function online($args, $assocArgs)
    {
        $number = \WP_CLI\Utils\get_flag_value($assocArgs, 'number', 15);
        $lists  = $this->dataProvider->getOnlineData(['per_page' => $number]);

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
            \WP_CLI\Utils\get_flag_value($assocArgs, 'format', 'table'),
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
    public function visitors($args, $assocArgs)
    {
        $number = \WP_CLI\Utils\get_flag_value($assocArgs, 'number', 15);
        $lists  = $this->dataProvider->getVisitorsData(['per_page' => $number]);

        if (empty($lists)) {
            \WP_CLI::error("There are no visitors.");
        }

        $columns = ['IP', 'Last View', 'Browser', 'Referrer', 'Operating System', 'User ID', 'Country'];
        $items   = [];

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
            \WP_CLI\Utils\get_flag_value($assocArgs, 'format', 'table'),
            $items,
            $columns
        );
    }

    /**
     * Create all database tables if missing.
     *
     * @throws Exception
     */
    public function create_tables($args, $assocArgs)
    {
        TableHandler::createAllTables();
        \WP_CLI::success('All WP Statistics tables created (if not already existing).');
    }

    /**
     * Record a hit.
     *
     * ## OPTIONS
     *
     * [--ip=<ip>]
     * [--user_agent=<user_agent>]
     * [--referrer=<referrer>]
     * [--user_id=<user_id>]
     * [--request_uri=<request_uri>]
     * [--resource_type=<resource_type>]
     * [--resource_id=<resource_id>]
     *
     * @throws Exception
     */
    public function record($args, $assocArgs)
    {
        $visitorProfile = new VisitorProfile();

        add_filter('wp_statistics_current_page', function ($currentPage) use ($assocArgs) {
            return [
                'type'         => $assocArgs['resource_type'] ?? 'post',
                'id'           => (int)($assocArgs['resource_id'] ?? 1),
                'search_query' => ''
            ];
        });

        if (!empty($assocArgs['ip'])) {
            $_SERVER['REMOTE_ADDR'] = $assocArgs['ip'];
        }

        if (!empty($assocArgs['user_agent'])) {
            $_SERVER['HTTP_USER_AGENT'] = $assocArgs['user_agent'];
        }

        if (!empty($assocArgs['referrer'])) {
            $_SERVER['HTTP_REFERER'] = $assocArgs['referrer'];
        }

        if (!empty($assocArgs['request_uri'])) {
            $uri                    = trim($assocArgs['request_uri'], '/');
            $_SERVER['REQUEST_URI'] = '/' . $uri . '/';
        }

        if (!empty($assocArgs['user_id'])) {
            $userId = (int)$assocArgs['user_id'];
            add_filter('wp_statistics_user_id', function () use ($userId) {
                return $userId;
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