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
            'page_title'         => esc_html__('Real-Time: Live Traffic Insights, Right When You Need Them', 'wp-statistics'),
            'page_second_title'  => esc_html__('WP Statistics Premium: Much More Than Real-Time Stats', 'wp-statistics'),
            'addon_name'         => esc_html__('Real-Time Stats', 'wp-statistics'),
            'addon_slug'         => 'wp-statistics-realtime-stats',
            'campaign'           => 'real-time-stats',
            'more_title'         => esc_html__('Learn More About Real-Time', 'wp-statistics'),
            'premium_btn_title'  => esc_html__('Upgrade Now to Unlock All Premium Features!', 'wp-statistics'),
            'images'             => ['realtime-stats.png'],
            'description'        => esc_html__('The Real-Time add-on lets you monitor your site’s traffic as it happens. Watch live data stream in, see online users in real-time, and track their activity without needing to refresh the page. Stay connected with instant insights to make quick, informed decisions about your site’s performance at crucial moments.', 'wp-statistics'),
            'second_description' => esc_html__('Upgrade to WP Statistics Premium gives you access not only to Real-Time but to all premium add-ons, providing you with complete insights and tools to maximize your site’s potential.', 'wp-statistics')
        ];

        Admin_Template::get_template(['layout/header']);
        View::load("pages/lock-page", $args);
        Admin_Template::get_template(['layout/footer']);
    }
}
