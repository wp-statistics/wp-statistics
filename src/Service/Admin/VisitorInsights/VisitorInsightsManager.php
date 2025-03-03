<?php

namespace WP_Statistics\Service\Admin\VisitorInsights;

use WP_STATISTICS\Helper;
use WP_STATISTICS\User;
use WP_Statistics\Utils\Query;
use WP_Statistics\Utils\Request;
use WP_Statistics\Utils\Url;

class VisitorInsightsManager
{

    public function __construct()
    {
        add_filter('wp_statistics_admin_menu_list', [$this, 'addMenuItem']);
    }

    /**
     * Add menu item
     *
     * @param array $items
     * @return array
     */
    public function addMenuItem($items)
    {
        $items['visitor_insights'] = [
            'sub'       => 'overview',
            'title'     => esc_html__('Visitor Insights', 'wp-statistics'),
            'page_url'  => 'visitors',
            'callback'  => VisitorInsightsPage::class,
            'priority'  => 25,
        ];

        return $items;
    }
}
