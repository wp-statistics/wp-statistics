<?php

namespace WP_Statistics\Service\Admin\LicenseManagement\Views;

use WP_Statistics\Components\View;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseView;

class LockedRealTimeStatView extends BaseView
{
    public function render()
    {
        $args = [
            'page_title' => __('Watch your site live', 'wp-statistics'),
            'benefits'   => [
                __('Visitors online right now', 'wp-statistics'),
                __('Live views chart and map', 'wp-statistics'),
                __('Updates without a page refresh', 'wp-statistics'),
            ],
            'addon_name'         => esc_html__('Real-Time Stats', 'wp-statistics'),
            'addon_slug'         => 'wp-statistics-realtime-stats',
            'campaign'           => 'real-time-stats',
            'images'             => ['realtime-stats.png'],
        ];

        Admin_Template::get_template(['layout/header']);
        View::load("pages/lock-page", $args);
        Admin_Template::get_template(['layout/footer']);
    }
}
