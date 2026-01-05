<?php

namespace WP_Statistics\Service\Content;

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Page;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Traits\TransientCacheTrait;

/**
 * Shortcode Manager for WP Statistics v15.
 *
 * Handles the [wpstatistics] shortcode for displaying statistics on the frontend.
 *
 * Uses lazy loading - the AnalyticsQueryHandler is only instantiated when
 * a shortcode is actually rendered, not during manager initialization.
 *
 * ## Usage
 *
 * [wpstatistics stat=xxx time=xxxx provider=xxxx format=xxxxxx id=xxx]
 *
 * ## Parameters
 *
 * - stat: The statistic to display (usersonline, visits, visitors, pagevisits, etc.)
 * - time: Time frame (today, yesterday, week, month, year, total, or strtotime format)
 * - provider: Search provider for search statistics
 * - format: Number format (i18n, english, abbreviated, none)
 * - id: Post/page ID for page-specific statistics
 *
 * @since 15.0.0
 */
class ShortcodeManager
{
    use TransientCacheTrait;

    /**
     * Analytics query handler for v15 API (lazy loaded).
     *
     * @var AnalyticsQueryHandler|null
     */
    private $queryHandler = null;

    /**
     * Constructor.
     *
     * Registers the shortcode without instantiating the query handler.
     * The handler is created on-demand when a shortcode is rendered.
     */
    public function __construct()
    {
        add_shortcode('wpstatistics', [$this, 'renderShortcode']);
        add_action('admin_init', [$this, 'registerShortcake']);
    }

    /**
     * Get the analytics query handler (lazy loading).
     *
     * @return AnalyticsQueryHandler
     */
    private function getQueryHandler()
    {
        if ($this->queryHandler === null) {
            $this->queryHandler = new AnalyticsQueryHandler();
        }
        return $this->queryHandler;
    }

    /**
     * Render the shortcode output.
     *
     * @param array $atts Shortcode attributes.
     * @return string|int Formatted statistic value.
     */
    public function renderShortcode($atts)
    {
        if (!is_array($atts) || !isset($atts['stat'])) {
            return '';
        }

        // Set default values
        $atts = wp_parse_args($atts, [
            'stat'     => '',
            'time'     => null,
            'provider' => 'all',
            'format'   => '',
            'id'       => null,
            'type'     => '',
        ]);

        // Get current page info if ID not specified
        if (empty($atts['id'])) {
            $atts['id']   = get_the_ID();
            $currentPage  = Page::getType();
            $atts['type'] = $currentPage['type'] ?? '';
        } else {
            $atts['type'] = $this->getResourceType($atts['id']);
        }

        // Get the statistic value
        $result = $this->getStatistic($atts);

        // Format the result
        return $this->formatResult($result, $atts);
    }

    /**
     * Get the requested statistic value.
     *
     * @param array $atts Shortcode attributes.
     * @return mixed Statistic value.
     */
    private function getStatistic($atts)
    {
        switch ($atts['stat']) {
            case 'usersonline':
                return wp_statistics_useronline();

            case 'visits':
                return $this->getViewsCount($atts);

            case 'visitors':
                return $this->getVisitorsCount($atts);

            case 'pagevisits':
                return $this->getPageViewsCount($atts);

            case 'pagevisitors':
                return $this->getPageVisitorsCount($atts);

            case 'searches':
                return wp_statistics_searchengine($atts['provider'], $atts['time']);

            case 'referrer':
                return wp_statistics_referrer($atts['time']);

            case 'postcount':
                return Helper::getCountPosts();

            case 'pagecount':
                return Helper::getCountPages();

            case 'commentcount':
                return Helper::getCountComment();

            case 'spamcount':
                return Helper::getCountSpam();

            case 'usercount':
                return Helper::getCountUsers();

            case 'postaverage':
                return Helper::getAveragePost();

            case 'commentaverage':
                return Helper::getAverageComment();

            case 'useraverage':
                return Helper::getAverageRegisterUser();

            case 'lpd':
                return Helper::getLastPostDate();

            default:
                return '';
        }
    }

    /**
     * Get total views count using AnalyticsQueryHandler.
     *
     * @param array $atts Shortcode attributes.
     * @return int Views count.
     */
    private function getViewsCount($atts)
    {
        try {
            $request = [
                'sources' => ['views'],
                'format'  => 'flat',
            ];

            // Add date filter if specified
            $dateRange = $this->parseDateRange($atts);
            if (!empty($dateRange)) {
                $request['date_from'] = $dateRange['from'];
                $request['date_to']   = $dateRange['to'];
            }

            $result = $this->getQueryHandler()->handle($request);

            return $result['totals']['views'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get total visitors count using AnalyticsQueryHandler.
     *
     * @param array $atts Shortcode attributes.
     * @return int Visitors count.
     */
    private function getVisitorsCount($atts)
    {
        try {
            $request = [
                'sources' => ['visitors'],
                'format'  => 'flat',
            ];

            // Add date filter if specified
            $dateRange = $this->parseDateRange($atts);
            if (!empty($dateRange)) {
                $request['date_from'] = $dateRange['from'];
                $request['date_to']   = $dateRange['to'];
            }

            $result = $this->getQueryHandler()->handle($request);

            return $result['totals']['visitors'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get page views count using AnalyticsQueryHandler.
     *
     * @param array $atts Shortcode attributes.
     * @return int Page views count.
     */
    private function getPageViewsCount($atts)
    {
        try {
            $request = [
                'sources' => ['views'],
                'format'  => 'flat',
                'filters' => [],
            ];

            // Add resource filter
            if (!empty($atts['id'])) {
                $request['filters']['resource_id'] = $atts['id'];
            }

            if (!empty($atts['type'])) {
                $request['filters']['resource_type'] = $atts['type'];
            }

            // Add date filter if specified
            $dateRange = $this->parseDateRange($atts);
            if (!empty($dateRange)) {
                $request['date_from'] = $dateRange['from'];
                $request['date_to']   = $dateRange['to'];
            }

            $result = $this->getQueryHandler()->handle($request);

            return $result['totals']['views'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get page visitors count using AnalyticsQueryHandler.
     *
     * @param array $atts Shortcode attributes.
     * @return int Page visitors count.
     */
    private function getPageVisitorsCount($atts)
    {
        try {
            $request = [
                'sources' => ['visitors'],
                'format'  => 'flat',
                'filters' => [],
            ];

            // Add resource filter
            if (!empty($atts['id'])) {
                $request['filters']['resource_id'] = $atts['id'];
            }

            if (!empty($atts['type'])) {
                $request['filters']['resource_type'] = $atts['type'];
            }

            // Add date filter if specified
            $dateRange = $this->parseDateRange($atts);
            if (!empty($dateRange)) {
                $request['date_from'] = $dateRange['from'];
                $request['date_to']   = $dateRange['to'];
            }

            $result = $this->getQueryHandler()->handle($request);

            return $result['totals']['visitors'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Parse date range from shortcode attributes.
     *
     * @param array $atts Shortcode attributes.
     * @return array Date range with 'from' and 'to' keys, or empty array.
     */
    private function parseDateRange($atts)
    {
        if (empty($atts['time'])) {
            return [];
        }

        $time = $atts['time'];

        // Handle predefined time ranges
        switch ($time) {
            case 'today':
                return [
                    'from' => date('Y-m-d 00:00:00'),
                    'to'   => date('Y-m-d 23:59:59'),
                ];

            case 'yesterday':
                return [
                    'from' => date('Y-m-d 00:00:00', strtotime('-1 day')),
                    'to'   => date('Y-m-d 23:59:59', strtotime('-1 day')),
                ];

            case 'week':
                return [
                    'from' => date('Y-m-d 00:00:00', strtotime('-7 days')),
                    'to'   => date('Y-m-d 23:59:59'),
                ];

            case 'month':
                return [
                    'from' => date('Y-m-d 00:00:00', strtotime('-30 days')),
                    'to'   => date('Y-m-d 23:59:59'),
                ];

            case 'year':
                return [
                    'from' => date('Y-m-d 00:00:00', strtotime('-1 year')),
                    'to'   => date('Y-m-d 23:59:59'),
                ];

            case 'total':
                // For total, we don't set date filters (returns all data)
                return [];
        }

        // Handle numeric values (days ago)
        if (is_numeric($time)) {
            return [
                'from' => date('Y-m-d 00:00:00', strtotime("{$time} days")),
                'to'   => date('Y-m-d 23:59:59'),
            ];
        }

        // Handle strtotime format
        $timestamp = strtotime($time);
        if ($timestamp !== false) {
            return [
                'from' => date('Y-m-d 00:00:00', $timestamp),
                'to'   => date('Y-m-d 23:59:59'),
            ];
        }

        return [];
    }

    /**
     * Get resource type for a given ID.
     *
     * @param int $resourceId Resource ID.
     * @return string Resource type.
     */
    private function getResourceType($resourceId = null)
    {
        if (empty($resourceId)) {
            return '';
        }

        $cacheKey = $this->getCacheKey('resourceType_' . $resourceId);
        $resourceType = $this->getCachedResult($cacheKey);

        if (!$resourceType) {
            $resourceType = get_post_type($resourceId);
            $this->setCachedResult($cacheKey, $resourceType);
        }

        return $resourceType;
    }

    /**
     * Format the result based on format setting.
     *
     * @param mixed $result Result value.
     * @param array $atts   Shortcode attributes.
     * @return string|int Formatted result.
     */
    private function formatResult($result, $atts)
    {
        // Don't format dates
        if ($atts['stat'] === 'lpd') {
            return $result;
        }

        if (empty($atts['format'])) {
            return $result;
        }

        switch (strtolower($atts['format'])) {
            case 'i18n':
                return number_format_i18n($result);

            case 'english':
                return number_format($result);

            case 'abbreviated':
                return $this->formatAbbreviated($result);

            default:
                return $result;
        }
    }

    /**
     * Format number in abbreviated notation (1K, 1M, 1B).
     *
     * @param int|float $number Number to format.
     * @return string Formatted number.
     */
    private function formatAbbreviated($number)
    {
        $abbreviations = [
            'B' => 1000000000,
            'M' => 1000000,
            'K' => 1000,
        ];

        foreach ($abbreviations as $symbol => $value) {
            if ($number >= $value) {
                $formatted = $number / $value;
                return round($formatted, 1) . $symbol;
            }
        }

        return $number;
    }

    /**
     * Register Shortcake UI for the shortcode.
     *
     * @return void
     */
    public function registerShortcake()
    {
        if (!function_exists('shortcode_ui_register_for_shortcode')) {
            return;
        }

        shortcode_ui_register_for_shortcode('wpstatistics', [
            'label'         => 'WP Statistics',
            'listItemImage' => '<img alt="logo" src="' . WP_STATISTICS_URL . 'public/images/logo-250.png" width="128" height="128">',
            'attrs'         => $this->getShortcakeAttributes(),
        ]);
    }

    /**
     * Get Shortcake attribute definitions.
     *
     * @return array Shortcake attributes.
     */
    private function getShortcakeAttributes()
    {
        return [
            [
                'label'       => __('Statistic', 'wp-statistics'),
                'attr'        => 'stat',
                'type'        => 'select',
                'description' => __('Select the statistic you wish to display.', 'wp-statistics'),
                'value'       => 'usersonline',
                'options'     => [
                    'usersonline'    => __('Online Visitors', 'wp-statistics'),
                    'visits'         => __('Views', 'wp-statistics'),
                    'visitors'       => __('Visitors', 'wp-statistics'),
                    'pagevisits'     => __('Page Views', 'wp-statistics'),
                    'pagevisitors'   => __('Page Visitors', 'wp-statistics'),
                    'searches'       => __('Searches', 'wp-statistics'),
                    'postcount'      => __('Post Count', 'wp-statistics'),
                    'pagecount'      => __('Page Count', 'wp-statistics'),
                    'commentcount'   => __('Comment Count', 'wp-statistics'),
                    'spamcount'      => __('Spam Count', 'wp-statistics'),
                    'usercount'      => __('User Count', 'wp-statistics'),
                    'postaverage'    => __('Post Average', 'wp-statistics'),
                    'commentaverage' => __('Comment Average', 'wp-statistics'),
                    'useraverage'    => __('User Average', 'wp-statistics'),
                    'lpd'            => __('Last Post Date', 'wp-statistics'),
                    'referrer'       => __('Referrer', 'wp-statistics'),
                ],
            ],
            [
                'label'       => __('Time Frame', 'wp-statistics'),
                'attr'        => 'time',
                'type'        => 'url',
                'description' => __('The time frame for the statistic. Use "today", "yesterday", "week", "month", "year", "total", or strtotime() format.', 'wp-statistics'),
                'meta'        => ['size' => '10'],
            ],
            [
                'label'       => __('Number Format', 'wp-statistics'),
                'attr'        => 'format',
                'type'        => 'select',
                'description' => __('The format to display numbers in.', 'wp-statistics'),
                'value'       => 'none',
                'options'     => [
                    'none'        => __('None', 'wp-statistics'),
                    'english'     => __('English', 'wp-statistics'),
                    'i18n'        => __('International', 'wp-statistics'),
                    'abbreviated' => __('Abbreviated', 'wp-statistics'),
                ],
            ],
            [
                'label'       => __('Post/Page ID', 'wp-statistics'),
                'attr'        => 'id',
                'type'        => 'number',
                'description' => __('The post/page ID for page-specific statistics.', 'wp-statistics'),
                'meta'        => ['size' => '5'],
            ],
        ];
    }
}
