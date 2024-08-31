<?php

namespace WP_STATISTICS\MetaBox;

use Exception;
use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_STATISTICS\SearchEngine;
use WP_STATISTICS\TimeZone;

class summary
{
    /**
     * Get Summary Meta Box Data
     *
     * @param array $args
     * @return array
     * @throws Exception
     */
    public static function get($args = array())
    {
        /**
         * Filters the args used from metabox for query stats
         *
         * @param array $args The args passed to query stats
         * @since 14.2.1
         *
         */
        $args = apply_filters('wp_statistics_meta_box_summary_args', $args);

        return self::getSummaryHits(array('user-online', 'visitors', 'visits'));
    }

    /**
     * Get Summary Hits in WP Statistics
     *
     * @param array $component
     * @return array
     * @throws Exception
     */
    public static function getSummaryHits($component = array())
    {
        $data = array();

        // Get first Day Install Plugin
        $first_day_install_plugin = Helper::get_date_install_plugin();
        if (!$first_day_install_plugin) {
            $first_day_install_plugin = 365;
        }

        // User Online
        if (in_array('user-online', $component)) {
            if (Option::get('useronline')) {
                $data['user_online'] = array(
                    'value' => wp_statistics_useronline(),
                    'link'  => Menus::admin_url('wps_visitors_page', ['tab' => 'online'])
                );
            }
        }

        $is_realtime_active    = Helper::isAddOnActive('realtime-stats');
        $realtime_button_class = $is_realtime_active ? 'wps-realtime-btn' : 'wps-realtime-btn disabled';
        $realtime_button_title = $is_realtime_active ? 'Real-time stats are available! Click here to view.' : 'Real-Time add-on required to enable this feature';
        $realtime_button_href  = $is_realtime_active ? Menus::admin_url('wp_statistics_realtime_stats') : WP_STATISTICS_SITE_URL . '/product/wp-statistics-realtime-stats/?utm_source=wp-statistics&utm_medium=link&utm_campaign=realtime';

        $data['real_time_button'] = array(
            'class' => esc_html__($realtime_button_class, 'wp-statistics'),
            'title' => esc_html__($realtime_button_title, 'wp-statistics'),
            'link'  => esc_url($realtime_button_href)
        );

        // Get Visitors
        if (in_array('visitors', $component)) {
            $data['visitors'] = array();

            // Today
            $data['visitors']['today'] = array(
                'link'  => Menus::admin_url('visitors', DateRange::get('today')),
                'value' => number_format_i18n(wp_statistics_visitor('today', null, true))
            );

            // Yesterday
            $data['visitors']['yesterday'] = array(
                'link'  => Menus::admin_url('visitors', DateRange::get('yesterday')),
                'value' => number_format_i18n(wp_statistics_visitor('yesterday', null, true))
            );

            // This Week
            $data['visitors']['this-week'] = array(
                'link'  => Menus::admin_url('visitors', DateRange::get('this_week')),
                'value' => number_format_i18n(wp_statistics_visitor('this-week', null, true))
            );

            // Last Week
            $data['visitors']['last-week'] = array(
                'link'  => Menus::admin_url('visitors', DateRange::get('last_week')),
                'value' => number_format_i18n(wp_statistics_visitor('last-week', null, true))
            );

            // This Month
            $data['visitors']['this-month'] = array(
                'link'  => Menus::admin_url('visitors', DateRange::get('this_month')),
                'value' => number_format_i18n(wp_statistics_visitor('this-month', null, true))
            );

            // Last Month
            $data['visitors']['last-month'] = array(
                'link'  => Menus::admin_url('visitors', DateRange::get('last_month')),
                'value' => number_format_i18n(wp_statistics_visitor('last-month', null, true))
            );

            // 7 Days
            $data['visitors']['7days'] = array(
                'link'  => Menus::admin_url('visitors', DateRange::get('7days')),
                'value' => number_format_i18n(wp_statistics_visitor('7days', null, true))
            );

            // 30 Days
            $data['visitors']['30days'] = array(
                'link'  => Menus::admin_url('visitors', DateRange::get('30days')),
                'value' => number_format_i18n(wp_statistics_visitor('30days', null, true))
            );

            // 90 Days
            $data['visitors']['90days'] = array(
                'link'  => Menus::admin_url('visitors', DateRange::get('90days')),
                'value' => number_format_i18n(wp_statistics_visitor('90days', null, true))
            );

            // 6 Months
            $data['visitors']['6months'] = array(
                'link'  => Menus::admin_url('visitors', DateRange::get('6months')),
                'value' => number_format_i18n(wp_statistics_visitor('6months', null, true))
            );

            // This Year
            $data['visitors']['this-year'] = array(
                'link'  => Menus::admin_url('visitors', DateRange::get('this_year')),
                'value' => number_format_i18n(wp_statistics_visitor('this-year', null, true))
            );

            // Total
            $data['visitors']['total'] = array(
                'link'  => Menus::admin_url('visitors'),
                'value' => number_format_i18n(wp_statistics_visitor('total', null, true))
            );
        }

        // Get Views
        if (in_array('visits', $component)) {
            $data['visits'] = array();

            // Today
            $data['visits']['today'] = array(
                'link'  => Menus::admin_url('visitors', array_merge(DateRange::get('today'), ['tab' => 'views'])),
                'value' => number_format_i18n(wp_statistics_visit('today'))
            );

            // Yesterday
            $data['visits']['yesterday'] = array(
                'link'  => Menus::admin_url('visitors', array_merge(DateRange::get('yesterday'), ['tab' => 'views'])),
                'value' => number_format_i18n(wp_statistics_visit('yesterday'))
            );

            // This Week
            $data['visits']['this-week'] = array(
                'link'  => Menus::admin_url('visitors', array_merge(DateRange::get('this_week'), ['tab' => 'views'])),
                'value' => number_format_i18n(wp_statistics_visit('this-week'))
            );

            // Last Week
            $data['visits']['last-week'] = array(
                'link'  => Menus::admin_url('visitors', array_merge(DateRange::get('last_week'), ['tab' => 'views'])),
                'value' => number_format_i18n(wp_statistics_visit('last-week'))
            );

            // This Month
            $data['visits']['this-month'] = array(
                'link'  => Menus::admin_url('visitors', array_merge(DateRange::get('this_month'), ['tab' => 'views'])),
                'value' => number_format_i18n(wp_statistics_visit('this-month'))
            );

            // Last Month
            $data['visits']['last-month'] = array(
                'link'  => Menus::admin_url('visitors', array_merge(DateRange::get('last_month'), ['tab' => 'views'])),
                'value' => number_format_i18n(wp_statistics_visit('last-month'))
            );

            // 7 Days
            $data['visits']['7days'] = array(
                'link'  => Menus::admin_url('visitors', array_merge(DateRange::get('7days'), ['tab' => 'views'])),
                'value' => number_format_i18n(wp_statistics_visit('7days'))
            );

            // 30 Days
            $data['visits']['30days'] = array(
                'link'  => Menus::admin_url('visitors', array_merge(DateRange::get('30days'), ['tab' => 'views'])),
                'value' => number_format_i18n(wp_statistics_visit('30days'))
            );

            // 90 Days
            $data['visits']['90days'] = array(
                'link'  => Menus::admin_url('visitors', array_merge(DateRange::get('90days'), ['tab' => 'views'])),
                'value' => number_format_i18n(wp_statistics_visit('90days'))
            );

            // 6 Months
            $data['visits']['6months'] = array(
                'link'  => Menus::admin_url('visitors', array_merge(DateRange::get('6months'), ['tab' => 'views'])),
                'value' => number_format_i18n(wp_statistics_visit('6months'))
            );

            // This Year
            $data['visits']['this-year'] = array(
                'link'  => Menus::admin_url('visitors', array_merge(DateRange::get('this_year'), ['tab' => 'views'])),
                'value' => number_format_i18n(wp_statistics_visit('this-year'))
            );

            // Total
            $data['visits']['total'] = array(
                'link'  => Menus::admin_url('visitors'),
                'value' => number_format_i18n(wp_statistics_visit('total'))
            );
        }

        // Get Search Engine Detail
        if (in_array('search-engine', $component)) {
            $data['search-engine'] = array();
            $total_today           = 0;
            $total_yesterday       = 0;
            foreach (SearchEngine::getList() as $key => $value) {

                // Get Statistics
                $today     = wp_statistics_searchengine($value['tag'], 'today');
                $yesterday = wp_statistics_searchengine($value['tag'], 'yesterday');

                // Push to List
                $data['search-engine'][$key] = array(
                    'name'      => sprintf(__('%s', 'wp-statistics'), $value['name']),
                    'logo'      => $value['logo_url'],
                    'today'     => number_format_i18n($today),
                    'yesterday' => number_format_i18n($yesterday)
                );

                // Sum Search engine
                $total_today     += $today;
                $total_yesterday += $yesterday;
            }
            $data['search-engine-total'] = array(
                'today'     => number_format_i18n($total_today),
                'yesterday' => number_format_i18n($total_yesterday),
                'total'     => number_format_i18n(wp_statistics_searchengine('all')),
            );
        }

        // Get Current Date and Time
        if (in_array('timezone', $component)) {
            $data['timezone'] = array(
                'option-link' => admin_url('options-general.php'),
                'date'        => TimeZone::getCurrentDate_i18n(get_option('date_format')),
                'time'        => TimeZone::getCurrentDate_i18n(get_option('time_format'))
            );
        }

        // Get Hits chartJs (20 Day Ago)
        if (in_array('hit-chart', $component)) {
            $data['hits-chart'] = hits::HitsChart((isset($component['days']) ? array('ago' => $component['days']) : array('ago' => 20)));
        }

        return $data;
    }

    /**
     * Summary Meta Box Lang
     *
     * @return array
     */
    public static function lang()
    {
        return array(
            'search_engine'     => __('Overview of Search Engine Referrals', 'wp-statistics'),
            'current_time_date' => __('Current Time and Date', 'wp-statistics'),
            'adjustment'        => __('(Adjustment)', 'wp-statistics')
        );
    }

}