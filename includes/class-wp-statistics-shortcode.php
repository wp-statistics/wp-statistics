<?php

namespace WP_STATISTICS;

use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Traits\TransientCacheTrait;

class ShortCode
{
    use TransientCacheTrait;

    public function __construct()
    {

        //init ShortCode
        add_action('admin_init', array($this, 'shortcake'));

        // Add ShortCode
        add_shortcode('wpstatistics', array($this, 'shortcodes'));
    }

    /**
     * WP Statistics ShortCode is in the format of:
     * [wpstatistics stat=xxx time=xxxx provider=xxxx format=xxxxxx id=xxx]
     *
     * Where:
     * stat = the statistic you want.
     * time = is the timeframe, strtotime() (http://php.net/manual/en/datetime.formats.php) will be used to calculate
     * it. provider = the search provider to get stats on. format = i18n, english, abbreviated, none. id = the page/post id to get
     * stats on.
     *
     * @param $atts
     * @return array|false|int|null|object|string|void
     */
    public function shortcodes($atts)
    {

        if (!is_array($atts)) {
            return;
        }
        if (!array_key_exists('stat', $atts)) {
            return;
        }

        if (!array_key_exists('time', $atts)) {
            $atts['time'] = null;
        }
        if (!array_key_exists('provider', $atts)) {
            $atts['provider'] = 'all';
        }
        if (!array_key_exists('format', $atts)) {
            $atts['format'] = '';
        }

        $atts['type'] = '';
        if (!array_key_exists('id', $atts)) {
            $atts['id']   = get_the_ID();
            $currentPage  = Pages::get_page_type();
            $atts['type'] = $currentPage['type'];
        } else {
            $atts['type'] = $this->getResourceType($atts['id']);
        }

        $formatnumber = array_key_exists('format', $atts);
        $result       = '';

        switch ($atts['stat']) {
            case 'usersonline':
                $result = wp_statistics_useronline();
                break;

            case 'visits':
                $visitorsModel = new VisitorsModel();
                $args          = $this->parseArgs($atts['time'], $atts);
                $result        = $visitorsModel->countHits($args);
                break;

            case 'visitors':
                $result = wp_statistics_visitor($atts['time'], null, true);
                break;

            case 'pagevisits':
                $viewsModel = new ViewsModel();
                $args       = $this->parseArgs($atts['stat'], $atts);
                $result     = $viewsModel->countViews($args);
                break;

            case 'pagevisitors':
                $visitorModel = new VisitorsModel();
                $args         = $this->parseArgs($atts['stat'], $atts);
                $result       = $visitorModel->countVisitors($args);
                break;

            case 'searches':
                $result = wp_statistics_searchengine($atts['provider'], $atts['time']);
                break;

            case 'referrer':
                $result = wp_statistics_referrer($atts['time']);
                break;

            case 'postcount':
                $result = Helper::getCountPosts();
                break;

            case 'pagecount':
                $result = Helper::getCountPages();
                break;

            case 'commentcount':
                $result = Helper::getCountComment();
                break;

            case 'spamcount':
                $result = Helper::getCountSpam();
                break;

            case 'usercount':
                $result = Helper::getCountUsers();
                break;

            case 'postaverage':
                $result = Helper::getAveragePost();
                break;

            case 'commentaverage':
                $result = Helper::getAverageComment();
                break;

            case 'useraverage':
                $result = Helper::getAverageRegisterUser();
                break;

            case 'lpd':
                $result       = Helper::getLastPostDate();
                $formatnumber = false;
                break;
        }

        if ($formatnumber) {
            switch (strtolower($atts['format'])) {
                case 'i18n':
                    $result = number_format_i18n($result);

                    break;
                case 'english':
                    $result = number_format($result);

                    break;
                // In this line a new function is added so that the abbreviation of larger numbers is displayed with the symbols 1K, 1M or 1B
                case 'abbreviated':
                    $result = $this->formatNumber($result);
                    break;
            }
        }

        return $result;
    }

    /**
     * Parse the shortcode arguments.
     *
     * @param array $atts The shortcode arguments.
     * @return array The parsed arguments.
     */
    public function parseArgs($modelType, $atts)
    {
        // Set the default arguments.
        $args = [
            'post_type'     => '',
            'post_id'       => '',
            'resource_id'   => '',
            'resource_type' => '',
            'date'          => '',
        ];

        // Parse the post_id parameter.
        if (isset($atts['id'])) {
            if ($modelType == 'pagevisits') {
                $args['post_id'] = $atts['id'];
            } else {
                $args['resource_id'] = $atts['id'];
            }
        }

        // Parse the resource_type parameter.
        if (!empty($atts['type'])) {
            $args['resource_type'] = $atts['type'];
        } else {
            $args['resource_type'] = $this->getResourceType($atts['id']);
        }

        // Parse the time parameter.
        if (isset($atts['time'])) {
            $timeMap = [
                'week'  => '7days',
                'month' => '30days',
                'year'  => '12months',
            ];

            if (array_key_exists($atts['time'], $timeMap)) {
                $args['date'] = $timeMap[$atts['time']];
            } elseif (is_numeric($atts['time'])) {
                $args['date'] = [
                    'from' => date('Y-m-d', strtotime("{$atts['time']} days")),
                    'to'   => date('Y-m-d'),
                ];
            } else {
                $args['date'] = $atts['time'];
            }
        }

        return $args;
    }

    public function getResourceType($resourceID = null)
    {
        $cacheKey = $this->getCacheKey('resourceType_' . $resourceID);

        $resourceType = $this->getCachedResult($cacheKey);
        if (!$resourceType) {
            $this->setCachedResult($cacheKey, get_post_type($resourceID));
        }

        return $resourceType;
    }

    /**
     * Format a number in shorthand notation (1K, 1M, 1B).
     *
     * @param int|float $number El número que se formateará.
     * @return string El número formateado en notación abreviada.
     */
    private function formatNumber($number)
    {
        $abbreviations = array(
            'K' => 1000,
            'M' => 1000000,
            'B' => 1000000000,
        );

        foreach ($abbreviations as $symbol => $value) {
            if ($number >= $value) {
                $formatted = $number / $value;
                return round($formatted, 1) . $symbol;
            }
        }

        return $number;
    }

    public function shortcake()
    {

        // ShortCake support if loaded.
        if (function_exists('shortcode_ui_register_for_shortcode')) {
            $se_list = SearchEngine::getList();

            $se_options = array('' => 'None');

            foreach ($se_list as $se) {
                $se_options[$se['tag']] = $se['translated'];
            }

            shortcode_ui_register_for_shortcode('wpstatistics',
                array(

                    // Display label. String. Required.
                    'label'         => 'WP Statistics',

                    // Icon/image for shortcode. Optional. src or dashicons-$icon. Defaults to carrot.
                    'listItemImage' => '<img alt="logo" src="' . WP_STATISTICS_URL . 'assets/images/logo-250.png" width="128" height="128">',

                    // Available shortCode attributes and default values. Required. Array.
                    // Attribute model expects 'attr', 'type' and 'label'
                    // Supported field types: text, checkbox, textarea, radio, select, email, url, number, and date.
                    'attrs'         => array(
                        array(
                            'label'       => __('Statistic', 'wp-statistics'),
                            'attr'        => 'stat',
                            'type'        => 'select',
                            'description' => __('Select the statistic you wish to display.', 'wp-statistics'),
                            'value'       => 'usersonline',
                            'options'     => array(
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
                            ),
                        ),
                        array(
                            'label'       => __('Time Frame', 'wp-statistics'),
                            'attr'        => 'time',
                            'type'        => 'url',
                            'description' => __(
                                'The time frame to get the statistic for, strtotime() (http://php.net/manual/en/datetime.formats.php) will be used to calculate it. Use "total" to get all recorded dates.',
                                'wp-statistics'
                            ),
                            'meta'        => array('size' => '10'),
                        ),
                        array(
                            'label'       => __('Search Provider', 'wp-statistics'),
                            'attr'        => 'provider',
                            'type'        => 'select',
                            'description' => __('The search provider to get statistics on.', 'wp-statistics'),
                            'options'     => $se_options,
                        ),
                        array(
                            'label'       => __('Number Format', 'wp-statistics'),
                            'attr'        => 'format',
                            'type'        => 'select',
                            'description' => __(
                                'The format to display numbers in: i18n, english, abbreviated, none.',
                                'wp-statistics'
                            ),
                            'value'       => 'none',
                            'options'     => array(
                                'none'        => __('None', 'wp-statistics'),
                                'english'     => __('English', 'wp-statistics'),
                                'i18n'        => __('International', 'wp-statistics'),
                                'abbreviated' => __('Abbreviated', 'wp-statistics'), // Added for shorthand notation
                            ),
                        ),
                        array(
                            'label'       => __('Post/Page ID', 'wp-statistics'),
                            'attr'        => 'id',
                            'type'        => 'number',
                            'description' => __('The post/page ID to get page statistics on.', 'wp-statistics'),
                            'meta'        => array('size' => '5'),
                        ),
                    ),
                )
            );
        }
    }
}

new ShortCode;
