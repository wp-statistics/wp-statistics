<?php

namespace WP_Statistics\Service\Analytics;

use WP_STATISTICS\Option;

class AnalyticsManager
{
    public function __construct()
    {
        if (Option::get('use_cache_plugin') && Option::get('bypass_ad_blockers', false)) {
            add_filter('wp_statistics_ajax_list', [$this, 'registerAjaxCallbacks']);
        }
    }

    /**
     * Registers AJAX actions.
     *
     * @param array $list
     *
     * @return  array
     */
    public function registerAjaxCallbacks($list)
    {
        $analyticsController = new AnalyticsController();

        $list[] = [
            'class'  => $analyticsController,
            'action' => 'hit_record',
            'public' => true,
        ];
        $list[] = [
            'class'  => $analyticsController,
            'action' => 'online_check',
            'public' => true,
        ];

        return $list;
    }
}
