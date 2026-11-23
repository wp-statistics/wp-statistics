<?php

namespace WP_Statistics\Service\Admin\LicenseManagement\Views;

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\AddonCatalog;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseView;

class LockedMiniChartView extends BaseView
{
    public function render()
    {
        $copy = AddonCatalog::get('wp-statistics-mini-chart');
        $args = [
            'page_title' => $copy['headline'],
            'benefits'   => $copy['benefits'],
            'addon_name'         => esc_html__('Mini Chart', 'wp-statistics'),
            'addon_slug'         => 'wp-statistics-mini-chart',
            'campaign'           => 'mini-chart',
            'images'             => ['mini-chart-1.png', 'mini-chart-2.png', 'mini-chart-3.png'],
        ];

        Admin_Template::get_template(['layout/header']);
        View::load("pages/lock-page", $args);
        Admin_Template::get_template(['layout/footer']);
    }
}
