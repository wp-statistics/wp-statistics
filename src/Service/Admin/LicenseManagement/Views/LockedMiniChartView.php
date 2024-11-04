<?php

namespace WP_Statistics\Service\Admin\LicenseManagement\Views;

use WP_Statistics\Components\View;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseView;

class LockedMiniChartView extends BaseView
{
    public function render()
    {
        $args = [
            'page_title'         => esc_html__('Mini Chart: Easy Insights, Right in Your Dashboard', 'wp-statistics'),
            'page_second_title'  => esc_html__('WP Statistics Premium: Beyond Just Mini Chart', 'wp-statistics'),
            'addon_name'         => esc_html__('Mini Chart', 'wp-statistics'),
            'addon_slug'         => 'wp-statistics-mini-chart',
            'campaign'           => 'mini-chart',
            'more_title'         => esc_html__('Learn More About Mini Chart', 'wp-statistics'),
            'premium_btn_title'  => esc_html__('Upgrade Now to Unlock All Premium Features!', 'wp-statistics'),
            'images'             => ['mini-chart-1.png', 'mini-chart-2.png', 'mini-chart-3.png'],
            'description'        => esc_html__('Mini Chart is a premium add-on for WP Statistics that gives you quick, clear insights into how your posts, pages, and products are doing. It shows small, customizable charts right in your admin area, so you can easily track views and engagement. You can change the chart types and colors to fit your style. With Mini Chart, it\'s simple to keep an eye on important numbers without spending a lot of time.', 'wp-statistics'),
            'second_description' => esc_html__('When you upgrade to WP Statistics Premium, you don\'t just get Mini Chart — you unlock all premium add-ons, providing complete insights for your site. ', 'wp-statistics')
        ];

        Admin_Template::get_template(['layout/header']);
        View::load("pages/lock-page", $args);
        Admin_Template::get_template(['layout/footer']);
    }
}
