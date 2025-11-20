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
     * @todo Register ajax using Ajax::register() and send response in a standard format using Ajax::success() or Ajax::error()
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

        return $list;
    }
}
