<?php

namespace WP_Statistics\Service\Content;

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Page;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Traits\TransientCacheTrait;

/**
 * Shortcode Manager for WP Statistics v15.
 *
 * Handles the [wpstatistics] shortcode for displaying statistics on the frontend.
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
     * Constructor.
     */
    public function __construct()
    {
        add_shortcode('wpstatistics', [$this, 'renderShortcode']);
        add_action('admin_init', [$this, 'registerShortcake']);
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
                $visitorsModel = new VisitorsModel();
                $args          = $this->parseTimeArgs($atts);
                return $visitorsModel->countHits($args);

            case 'visitors':
                return wp_statistics_visitor($atts['time'], null, true);

            case 'pagevisits':
                $viewsModel = new ViewsModel();
                $args       = $this->parsePageArgs('pagevisits', $atts);
                return $viewsModel->countViews($args);

            case 'pagevisitors':
                $visitorModel = new VisitorsModel();
                $args         = $this->parsePageArgs('pagevisitors', $atts);
                return $visitorModel->countVisitors($args);

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
     * Parse time-based arguments.
     *
     * @param array $atts Shortcode attributes.
     * @return array Parsed arguments.
     */
    private function parseTimeArgs($atts)
    {
        $args = [
            'date' => '',
        ];

        if (!empty($atts['time'])) {
            $args['date'] = $this->parseTimeValue($atts['time']);
        }

        return $args;
    }

    /**
     * Parse page-specific arguments.
     *
     * @param string $modelType Type of model (pagevisits or pagevisitors).
     * @param array  $atts      Shortcode attributes.
     * @return array Parsed arguments.
     */
    private function parsePageArgs($modelType, $atts)
    {
        $args = [
            'post_type'     => '',
            'post_id'       => '',
            'resource_id'   => '',
            'resource_type' => '',
            'date'          => '',
        ];

        // Set resource/post ID
        if (!empty($atts['id'])) {
            if ($modelType === 'pagevisits') {
                $args['post_id'] = $atts['id'];
            } else {
                $args['resource_id'] = $atts['id'];
            }
        }

        // Set resource type
        if (!empty($atts['type'])) {
            $args['resource_type'] = $atts['type'];
        } else {
            $args['resource_type'] = $this->getResourceType($atts['id']);
        }

        // Set time/date
        if (!empty($atts['time'])) {
            $args['date'] = $this->parseTimeValue($atts['time']);
        }

        return $args;
    }

    /**
     * Parse time value to date format.
     *
     * @param string $time Time value.
     * @return mixed Date value or array.
     */
    private function parseTimeValue($time)
    {
        $timeMap = [
            'week'  => '7days',
            'month' => '30days',
            'year'  => '12months',
        ];

        if (isset($timeMap[$time])) {
            return $timeMap[$time];
        }

        if (is_numeric($time)) {
            return [
                'from' => date('Y-m-d', strtotime("{$time} days")),
                'to'   => date('Y-m-d'),
            ];
        }

        return $time;
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
